<?php
/**
 * Stock Management Functions
 * 
 * Functions for handling stock deduction, restoring, and tracking
 */

/**
 * Deduct stock for a sale order
 * 
 * @param int $product_id Product ID
 * @param int $quantity Quantity to deduct
 * @param string $order_reference Order reference number
 * @param string $notes Additional notes
 * @param int $user_id User ID making the change
 * @param string $shipping_address Customer shipping address for location history
 * @return array Stock information including batch number and stock ID
 * @throws Exception If insufficient stock
 */
function deductStockForSale($product_id, $quantity, $order_reference, $notes = '', $user_id = null, $shipping_address = ''): array
{
    global $conn;
    
    try {
        // First try to find a single batch with enough stock
        $stmt = $conn->prepare("SELECT s.*, w.warehouse_id, w.warehouse_name, l.location_id, l.name as location_name, 
                               l.type as location_type 
                               FROM `tbl_stock` s
                               LEFT JOIN `tbl_warehouse` w ON s.warehouse_id = w.warehouse_id
                               LEFT JOIN `tbl_warehouse_location` l ON s.location_id = l.location_id
                               WHERE s.product_id = :product_id AND s.quantity >= :quantity 
                               ORDER BY s.added_on ASC LIMIT 1");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->execute();
        $stock = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // If no single batch has enough stock, use multiple batches
        if (empty($stock)) {
            // Check if there's enough total stock across all batches
            $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM `tbl_stock` WHERE product_id = :product_id");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($total && $total['total'] >= $quantity) {
                // We have enough stock across multiple batches
                return deductStockFromMultipleBatches(
                    $product_id, 
                    $quantity, 
                    $order_reference, 
                    $notes, 
                    $user_id, 
                    $shipping_address
                );
            } else {
                // Get product name for better error message
                $stmt = $conn->prepare("SELECT product_name FROM tbl_products WHERE product_id = :product_id");
                $stmt->bindParam(':product_id', $product_id);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);
                $product_name = $product ? $product['product_name'] : 'Unknown product';
                
                throw new Exception('Insufficient stock for product: ' . $product_name . ' (ID: ' . $product_id . ')');
            }
        }
        
        // Single batch is available with enough stock - original implementation
        $batch_number = $stock['batch_number'];
        $stock_id = $stock['stock_id'];
        $previous_quantity = $stock['quantity'];
        $new_quantity = $previous_quantity - $quantity;
        
        // Update stock quantity
        $stmt = $conn->prepare("UPDATE `tbl_stock` SET quantity = :new_quantity WHERE stock_id = :stock_id");
        $stmt->bindParam(':new_quantity', $new_quantity);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->execute();
        
        $now = date('Y-m-d H:i:s');
        
        // Record stock transaction
        $quantity_change = -$quantity; // Negative for outgoing stock
        $transaction_note = 'Sale Order: ' . $order_reference . ($notes ? ' - ' . $notes : '');
        
        $stmt = $conn->prepare("INSERT INTO `tbl_stock_transactions` 
                               (product_id, stock_id, quantity_change, previous_quantity, transaction_type, 
                               transaction_date, notes, user_id, order_reference) 
                               VALUES 
                               (:product_id, :stock_id, :quantity_change, :previous_quantity, :transaction_type, 
                               :transaction_date, :notes, :user_id, :order_reference)");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->bindParam(':quantity_change', $quantity_change);
        $stmt->bindParam(':previous_quantity', $previous_quantity);
        $stmt->bindValue(':transaction_type', 'sale');
        $stmt->bindParam(':transaction_date', $now);
        $stmt->bindParam(':notes', $transaction_note);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':order_reference', $order_reference);
        $stmt->execute();
        
        if (!empty($shipping_address)) {
            // Create "virtual" location for customer delivery
            $old_warehouse_id = $stock['warehouse_id'];
            $old_location_id = $stock['location_id'];
            
            $customer_location_note = 'Delivered to customer: ' . $shipping_address;
            
            // Record stock location history for sales order
            // Note: We pass null for new_warehouse_id and new_location_id since it's going to customer
            $stmt = $conn->prepare("INSERT INTO `tbl_stock_location_history` 
                                  (stock_id, product_id, batch_number, quantity, old_warehouse_id, new_warehouse_id, 
                                   old_location_id, new_location_id, moved_by, notes)
                                  VALUES 
                                  (:stock_id, :product_id, :batch_number, :quantity, :old_warehouse_id, NULL, 
                                   :old_location_id, NULL, :moved_by, :notes)");
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':batch_number', $batch_number);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':old_warehouse_id', $old_warehouse_id);
            $stmt->bindParam(':old_location_id', $old_location_id);
            $stmt->bindParam(':moved_by', $user_id);
            $stmt->bindParam(':notes', $customer_location_note);
            $stmt->execute();
        }
        
        // Update main product stock count
        $stmt = $conn->prepare("UPDATE `tbl_products` SET stock = stock - :quantity WHERE product_id = :product_id");
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        return [
            'stock_id' => $stock_id,
            'batch_number' => $batch_number,
            'previous_quantity' => $previous_quantity,
            'new_quantity' => $new_quantity,
            'used_multiple_batches' => false
        ];
    } catch (Exception $e) {
        throw $e; // Re-throw for handling at caller level
    }
}

/**
 * Restore stock when cancelling an order
 * 
 * @param int $product_id Product ID
 * @param int $quantity Quantity to restore
 * @param string $batch_number Batch number
 * @param string $order_reference Order reference number
 * @param string $notes Additional notes
 * @param int $user_id User ID making the change
 * @return array Stock information
 */
function restoreStockFromCancelledOrder($product_id, $quantity, $batch_number, $order_reference, $notes = '', $user_id = null): array
{
    global $conn;
    
    try {
        // Find the relevant stock record
        $stmt = $conn->prepare("SELECT s.*, w.warehouse_id, w.warehouse_name, l.location_id, l.name as location_name, 
                              l.type as location_type 
                              FROM `tbl_stock` s
                              LEFT JOIN `tbl_warehouse` w ON s.warehouse_id = w.warehouse_id
                              LEFT JOIN `tbl_warehouse_location` l ON s.location_id = l.location_id
                              WHERE s.product_id = :product_id AND s.batch_number = :batch_number
                              LIMIT 1");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':batch_number', $batch_number);
        $stmt->execute();
        $stock = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (empty($stock)) {
            // If batch not found, create a new stock entry with "returned" note
            $stmt = $conn->prepare("INSERT INTO `tbl_stock` 
                                  (product_id, batch_number, quantity, unit_cost_price, notes) 
                                  VALUES 
                                  (:product_id, :batch_number, :quantity, 
                                  (SELECT purchase_price FROM tbl_products WHERE product_id = :product_id2), 
                                  :notes)");
            $return_note = 'Returned stock from cancelled order: ' . $order_reference . ($notes ? ' - ' . $notes : '');
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':product_id2', $product_id);
            $stmt->bindParam(':batch_number', $batch_number);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':notes', $return_note);
            $stmt->execute();
            
            $stock_id = $conn->lastInsertId();
            $previous_quantity = 0;
            $new_quantity = $quantity;
        } else {
            // Update existing stock quantity
            $stock_id = $stock['stock_id'];
            $previous_quantity = $stock['quantity'];
            $new_quantity = $previous_quantity + $quantity;
            
            $stmt = $conn->prepare("UPDATE `tbl_stock` SET quantity = :new_quantity WHERE stock_id = :stock_id");
            $stmt->bindParam(':new_quantity', $new_quantity);
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->execute();
        }
        
        $now = date('Y-m-d H:i:s');
        
        // Record stock transaction for the return
        $transaction_note = 'Order cancelled and stock returned: ' . $order_reference . ($notes ? ' - ' . $notes : '');
        
        $stmt = $conn->prepare("INSERT INTO `tbl_stock_transactions` 
                              (product_id, stock_id, quantity_change, previous_quantity, transaction_type, 
                               transaction_date, notes, user_id, order_reference) 
                              VALUES 
                              (:product_id, :stock_id, :quantity, :previous_quantity, :transaction_type, 
                               :transaction_date, :notes, :user_id, :order_reference)");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':previous_quantity', $previous_quantity);
        $stmt->bindValue(':transaction_type', 'return');
        $stmt->bindParam(':transaction_date', $now);
        $stmt->bindParam(':notes', $transaction_note);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':order_reference', $order_reference);
        $stmt->execute();
        
        if (!empty($stock)) {
            // Record stock location return in history table
            // Note: returning from customer (NULL) to original warehouse
            $return_note = 'Stock returned from cancelled order: ' . $order_reference . ($notes ? ' - ' . $notes : '');
            
            $stmt = $conn->prepare("INSERT INTO `tbl_stock_location_history` 
                                 (stock_id, product_id, batch_number, quantity, old_warehouse_id, new_warehouse_id, 
                                  old_location_id, new_location_id, moved_by, notes)
                                 VALUES 
                                 (:stock_id, :product_id, :batch_number, :quantity, NULL, :new_warehouse_id, 
                                  NULL, :new_location_id, :moved_by, :notes)");
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':batch_number', $batch_number);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':new_warehouse_id', $stock['warehouse_id']);
            $stmt->bindParam(':new_location_id', $stock['location_id']);
            $stmt->bindParam(':moved_by', $user_id);
            $stmt->bindParam(':notes', $return_note);
            $stmt->execute();
        }
        
        // Update main product stock count
        $stmt = $conn->prepare("UPDATE `tbl_products` SET stock = stock + :quantity WHERE product_id = :product_id");
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        return [
            'stock_id' => $stock_id,
            'batch_number' => $batch_number,
            'previous_quantity' => $previous_quantity,
            'new_quantity' => $new_quantity
        ];
    } catch (Exception $e) {
        throw $e; // Re-throw for handling at caller level
    }
}

/**
 * Check if a product has sufficient stock
 * 
 * @param int $product_id Product ID
 * @param int $quantity Quantity needed
 * @return array|false Stock information if available, false if insufficient
 */
function checkSufficientStock($product_id, $quantity)
{
    global $conn;
    
    $stmt = $conn->prepare("SELECT s.*, w.warehouse_id, w.warehouse_name, l.location_id, l.name as location_name, 
                           l.type as location_type, p.product_name
                           FROM `tbl_stock` s
                           LEFT JOIN `tbl_warehouse` w ON s.warehouse_id = w.warehouse_id
                           LEFT JOIN `tbl_warehouse_location` l ON s.location_id = l.location_id
                           LEFT JOIN `tbl_products` p ON s.product_id = p.product_id
                           WHERE s.product_id = :product_id AND s.quantity >= :quantity 
                           ORDER BY s.added_on ASC LIMIT 1");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        return $result;
    }
    
    // If no single stock entry has enough, check if total available stock is sufficient
    $stmt = $conn->prepare("SELECT SUM(quantity) as total_quantity, p.product_name 
                           FROM `tbl_stock` s
                           JOIN `tbl_products` p ON s.product_id = p.product_id
                           WHERE s.product_id = :product_id");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($total && $total['total_quantity'] >= $quantity) {
        // Sufficient in total, but needs to be combined from multiple batches
        return [
            'product_id' => $product_id,
            'product_name' => $total['product_name'],
            'available_quantity' => $total['total_quantity'],
            'required_quantity' => $quantity,
            'needs_multiple_batches' => true
        ];
    }
    
    return false;
}

/**
 * Deduct stock from multiple batches when a single batch doesn't have enough
 * 
 * @param int $product_id Product ID
 * @param int $quantity Quantity to deduct
 * @param string $order_reference Order reference number
 * @param string $notes Additional notes
 * @param int $user_id User ID making the change
 * @param string $shipping_address Customer shipping address for location history
 * @return array Info about the batches used, with primary batch as default
 * @throws Exception If insufficient stock
 */
function deductStockFromMultipleBatches($product_id, $quantity, $order_reference, $notes = '', $user_id = null, $shipping_address = ''): array
{
    global $conn;
    
    try {
        // Get all available stock for this product sorted by oldest first (FIFO)
        $stmt = $conn->prepare("SELECT s.*, w.warehouse_id, w.warehouse_name, l.location_id, l.name as location_name, 
                              l.type as location_type
                              FROM `tbl_stock` s
                              LEFT JOIN `tbl_warehouse` w ON s.warehouse_id = w.warehouse_id
                              LEFT JOIN `tbl_warehouse_location` l ON s.location_id = l.location_id
                              WHERE s.product_id = :product_id AND s.quantity > 0
                              ORDER BY s.added_on ASC");
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $stock_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Check if we have enough total stock
        $total_available = 0;
        foreach ($stock_items as $item) {
            $total_available += $item['quantity'];
        }
        
        if ($total_available < $quantity) {
            // Get product name for better error message
            $stmt = $conn->prepare("SELECT product_name FROM tbl_products WHERE product_id = :product_id");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            $product_name = $product ? $product['product_name'] : 'Unknown product';
            
            throw new Exception('Insufficient stock for product: ' . $product_name . ' (ID: ' . $product_id . ')');
        }
        
        $remaining_quantity = $quantity;
        $batches_used = [];
        $primary_batch = null; // We'll use the first batch as the primary one
        $now = date('Y-m-d H:i:s');
        
        foreach ($stock_items as $index => $stock) {
            $batch_quantity = min($stock['quantity'], $remaining_quantity);
            $stock_id = $stock['stock_id'];
            $batch_number = $stock['batch_number'];
            $previous_quantity = $stock['quantity'];
            $new_quantity = $previous_quantity - $batch_quantity;
            
            // Update stock quantity
            $stmt = $conn->prepare("UPDATE `tbl_stock` SET quantity = :new_quantity WHERE stock_id = :stock_id");
            $stmt->bindParam(':new_quantity', $new_quantity);
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->execute();
            
            // Record stock transaction
            $quantity_change = -$batch_quantity; // Negative for outgoing stock
            $transaction_note = 'Sale Order: ' . $order_reference;
            
            if (count($stock_items) > 1) {
                $transaction_note .= ' (Multiple batches used: ' . $batch_quantity . ' units from batch ' . $batch_number . ')';
            }
            
            if ($notes) {
                $transaction_note .= ' - ' . $notes;
            }
            
            $stmt = $conn->prepare("INSERT INTO `tbl_stock_transactions` 
                                  (product_id, stock_id, quantity_change, previous_quantity, transaction_type, 
                                   transaction_date, notes, user_id, order_reference) 
                                  VALUES 
                                  (:product_id, :stock_id, :quantity_change, :previous_quantity, :transaction_type, 
                                   :transaction_date, :notes, :user_id, :order_reference)");
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->bindParam(':quantity_change', $quantity_change);
            $stmt->bindParam(':previous_quantity', $previous_quantity);
            $stmt->bindValue(':transaction_type', 'sale');
            $stmt->bindParam(':transaction_date', $now);
            $stmt->bindParam(':notes', $transaction_note);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':order_reference', $order_reference);
            $stmt->execute();
            
            if (!empty($shipping_address)) {
                // Create "virtual" location for customer delivery
                $old_warehouse_id = $stock['warehouse_id'];
                $old_location_id = $stock['location_id'];
                
                $customer_location_note = 'Delivered to customer: ' . $shipping_address;
                
                if (count($stock_items) > 1) {
                    $customer_location_note .= ' (Multiple batches used: ' . $batch_quantity . ' units from this batch)';
                }
                
                // Record stock location history for sales order
                // Note: We pass null for new_warehouse_id and new_location_id since it's going to customer
                $stmt = $conn->prepare("INSERT INTO `tbl_stock_location_history` 
                                      (stock_id, product_id, batch_number, quantity, old_warehouse_id, new_warehouse_id, 
                                       old_location_id, new_location_id, moved_by, notes)
                                      VALUES 
                                      (:stock_id, :product_id, :batch_number, :quantity, :old_warehouse_id, NULL, 
                                       :old_location_id, NULL, :moved_by, :notes)");
                $stmt->bindParam(':stock_id', $stock_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':batch_number', $batch_number);
                $stmt->bindParam(':quantity', $batch_quantity);
                $stmt->bindParam(':old_warehouse_id', $old_warehouse_id);
                $stmt->bindParam(':old_location_id', $old_location_id);
                $stmt->bindParam(':moved_by', $user_id);
                $stmt->bindParam(':notes', $customer_location_note);
                $stmt->execute();
            }
            
            // Add this batch to our list of used batches
            $batches_used[] = [
                'stock_id' => $stock_id,
                'batch_number' => $batch_number,
                'quantity' => $batch_quantity
            ];
            
            // Set the primary batch (first one used)
            if ($index === 0) {
                $primary_batch = [
                    'stock_id' => $stock_id,
                    'batch_number' => $batch_number,
                    'previous_quantity' => $previous_quantity,
                    'new_quantity' => $new_quantity
                ];
            }
            
            // Reduce the remaining quantity and check if we're done
            $remaining_quantity -= $batch_quantity;
            if ($remaining_quantity <= 0) {
                break;
            }
        }
        
        // Update main product stock count
        $stmt = $conn->prepare("UPDATE `tbl_products` SET stock = stock - :quantity WHERE product_id = :product_id");
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        
        // Add multiple batches flag and list to the primary batch info
        $primary_batch['used_multiple_batches'] = (count($batches_used) > 1);
        $primary_batch['batches'] = $batches_used;
        
        return $primary_batch;
    } catch (Exception $e) {
        throw $e;
    }
}
