<?php

/**
 * Warehouse and Location related functions
 */

/**
 * Get all active warehouse locations
 * 
 * @param int|null $warehouse_id Optional warehouse ID to filter by
 * @return array List of warehouse locations
 */
function getAllWarehouseLocations($warehouse_id = null): array
{
    global $conn;

    $sql = "SELECT l.*, w.warehouse_name, p.name as parent_name 
            FROM `tbl_warehouse_location` l
            JOIN `tbl_warehouse` w ON l.warehouse_id = w.warehouse_id
            LEFT JOIN `tbl_warehouse_location` p ON l.parent_location_id = p.location_id
            WHERE l.is_deleted = 0";

    if ($warehouse_id) {
        $sql .= " AND l.warehouse_id = :warehouse_id";
    }

    $sql .= " ORDER BY w.warehouse_name, l.type, l.name";

    $stmt = $conn->prepare($sql);

    if ($warehouse_id) {
        $stmt->bindParam(':warehouse_id', $warehouse_id);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get warehouse location by ID
 * 
 * @param int $location_id Location ID
 * @return array|false Location details or false if not found
 */
function getWarehouseLocationById($location_id)
{
    global $conn;

    $sql = "SELECT l.*, w.warehouse_name, p.name as parent_name 
            FROM `tbl_warehouse_location` l
            JOIN `tbl_warehouse` w ON l.warehouse_id = w.warehouse_id
            LEFT JOIN `tbl_warehouse_location` p ON l.parent_location_id = p.location_id
            WHERE l.location_id = :location_id AND l.is_deleted = 0";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':location_id', $location_id);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get locations by warehouse ID
 * 
 * @param int $warehouse_id Warehouse ID
 * @return array List of locations in the warehouse
 */
function getLocationsByWarehouse($warehouse_id): array
{
    global $conn;

    $sql = "SELECT * FROM `tbl_warehouse_location` 
            WHERE warehouse_id = :warehouse_id AND is_deleted = 0
            ORDER BY type, name";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':warehouse_id', $warehouse_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get locations by warehouse ID and type
 * 
 * @param int $warehouse_id Warehouse ID
 * @param string $type Location type (Zone, Aisle, Rack, Shelf, Bin)
 * @return array List of locations of the specified type in the warehouse
 */
function getLocationsByWarehouseAndType($warehouse_id, $type): array
{
    global $conn;

    $sql = "SELECT * FROM `tbl_warehouse_location` 
            WHERE warehouse_id = :warehouse_id AND type = :type AND is_deleted = 0
            ORDER BY name";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':warehouse_id', $warehouse_id);
    $stmt->bindParam(':type', $type);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get formatted location path for display
 * 
 * @param int $location_id Location ID
 * @return string Formatted location path (e.g., "Warehouse A / Zone 1 / Aisle 2 / Rack 3 / Shelf A / Bin 1")
 */
function getFormattedLocationPath($location_id)
{
    global $conn;

    if (!$location_id) {
        return '';
    }

    $location = getWarehouseLocationById($location_id);

    if (!$location) {
        return '';
    }

    $path = $location['warehouse_name'] . ' / ' . $location['name'] . ' (' . $location['type'] . ')';

    $parent_id = $location['parent_location_id'];
    $visited = [$location_id]; // Prevent infinite loops

    while ($parent_id && !in_array($parent_id, $visited)) {
        $parent = getWarehouseLocationById($parent_id);

        if (!$parent) {
            break;
        }

        $path = $parent['name'] . ' (' . $parent['type'] . ') / ' . $path;
        $visited[] = $parent_id;
        $parent_id = $parent['parent_location_id'];
    }

    return $path;
}

/**
 * Get stock by product ID, batch number, warehouse ID, and location ID
 * 
 * @param int $product_id Product ID
 * @param string $batch_number Batch number
 * @param int|null $warehouse_id Optional warehouse ID
 * @param int|null $location_id Optional location ID
 * @return array|false Stock details or false if not found
 */
function getStockByProductBatchWarehouseLocation($product_id, $batch_number, $warehouse_id = null, $location_id = null)
{
    global $conn;

    $sql = "SELECT s.*, p.product_name, w.warehouse_name 
            FROM `tbl_stock` s
            JOIN `tbl_products` p ON s.product_id = p.product_id
            LEFT JOIN `tbl_warehouse` w ON s.warehouse_id = w.warehouse_id
            WHERE s.product_id = :product_id AND s.batch_number = :batch_number";

    $params = [
        ':product_id' => $product_id,
        ':batch_number' => $batch_number
    ];

    if ($warehouse_id) {
        $sql .= " AND s.warehouse_id = :warehouse_id";
        $params[':warehouse_id'] = $warehouse_id;
    }

    if ($location_id) {
        $sql .= " AND s.location_id = :location_id";
        $params[':location_id'] = $location_id;
    }

    $stmt = $conn->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Move stock to a different location
 * 
 * @param int $stock_id Stock ID
 * @param int $warehouse_id Warehouse ID
 * @param int $location_id Location ID
 * @param string $notes Optional notes for the transaction
 * @param int $user_id User performing the action
 * @return bool True on success, false on failure
 */
function moveStockToLocation($stock_id, $warehouse_id, $location_id, $notes = '', $user_id = null): bool
{
    global $conn;

    try {
        $conn->beginTransaction();

        // Get current stock information
        $stmt = $conn->prepare("SELECT * FROM `tbl_stock` WHERE stock_id = :stock_id");
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->execute();
        $stock = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stock) {
            throw new Exception('Stock not found');
        }

        // Update stock location
        $stmt = $conn->prepare("UPDATE `tbl_stock` SET warehouse_id = :warehouse_id, location_id = :location_id WHERE stock_id = :stock_id");
        $stmt->bindParam(':warehouse_id', $warehouse_id);
        $stmt->bindParam(':location_id', $location_id);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->execute();

        // Record location change in transaction log
        $old_location = $stock['location_id'] ? getWarehouseLocationById($stock['location_id']) : null;
        $new_location = getWarehouseLocationById($location_id);

        $old_location_name = $old_location ? ($old_location['name'] . ' (' . $old_location['type'] . ')') : 'Unassigned';
        $new_location_name = $new_location ? ($new_location['name'] . ' (' . $new_location['type'] . ')') : 'Unassigned';

        $transaction_note = "Location changed from {$old_location_name} to {$new_location_name}";

        if ($notes) {
            $transaction_note .= ". Notes: {$notes}";
        }

        $stmt = $conn->prepare("INSERT INTO `tbl_stock_transactions` (product_id, stock_id, quantity_change, previous_quantity, transaction_type, notes, user_id, transaction_location) 
                                VALUES (:product_id, :stock_id, 0, :quantity, 'location', :notes, :user_id, :transaction_location)");
        $stmt->bindParam(':product_id', $stock['product_id']);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->bindParam(':quantity', $stock['quantity']);
        $stmt->bindParam(':notes', $transaction_note);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':transaction_location', $new_location_name);
        $stmt->execute();

        // Record stock location change in history table
        recordStockLocationChange($stock_id, $stock['product_id'], $stock['batch_number'], $old_location ? $old_location['warehouse_id'] : null, $warehouse_id, $old_location ? $old_location['location_id'] : null, $location_id, $user_id, $notes);

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log('Error moving stock to location: ' . $e->getMessage());
        return false;
    }
}

/**
 * Record stock location change in history table
 * 
 * @param int $stock_id Stock ID
 * @param int $product_id Product ID
 * @param string $batch_number Batch number
 * @param int|null $old_warehouse_id Old warehouse ID (null if previously unassigned)
 * @param int $new_warehouse_id New warehouse ID
 * @param int|null $old_location_id Old location ID (null if previously unassigned)
 * @param int|null $new_location_id New location ID (null if not assigned)
 * @param int $user_id User making the change
 * @param string $notes Optional notes about the change
 * @return bool True on success, false on failure
 */
function recordStockLocationChange($stock_id, $product_id, $batch_number, $old_warehouse_id, $new_warehouse_id, $old_location_id, $new_location_id, $user_id, $notes = '', $quantity = null): bool
{
    global $conn;

    try {
        // Get the current quantity if not provided
        if ($quantity === null) {
            $stmt = $conn->prepare("SELECT quantity FROM tbl_stock WHERE stock_id = :stock_id");
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->execute();
            $stockData = $stmt->fetch(PDO::FETCH_ASSOC);
            $quantity = $stockData ? $stockData['quantity'] : null;
        }
        
        $stmt = $conn->prepare("INSERT INTO `tbl_stock_location_history` 
                               (stock_id, product_id, batch_number, quantity, old_warehouse_id, new_warehouse_id, 
                                old_location_id, new_location_id, moved_by, notes)
                               VALUES 
                               (:stock_id, :product_id, :batch_number, :quantity, :old_warehouse_id, :new_warehouse_id, 
                                :old_location_id, :new_location_id, :moved_by, :notes)");

        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':batch_number', $batch_number);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':old_warehouse_id', $old_warehouse_id);
        $stmt->bindParam(':new_warehouse_id', $new_warehouse_id);
        $stmt->bindParam(':old_location_id', $old_location_id);
        $stmt->bindParam(':new_location_id', $new_location_id);
        $stmt->bindParam(':moved_by', $user_id);
        $stmt->bindParam(':notes', $notes);

        return $stmt->execute();
    } catch (Exception $e) {
        error_log('Error recording stock location change: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get stock by location ID
 * 
 * @param int $location_id Location ID
 * @return array List of stock items in the location
 */
function getStockByLocation($location_id): array
{
    global $conn;

    $sql = "SELECT s.*, p.product_name, w.warehouse_name 
            FROM `tbl_stock` s
            JOIN `tbl_products` p ON s.product_id = p.product_id
            LEFT JOIN `tbl_warehouse` w ON s.warehouse_id = w.warehouse_id
            WHERE s.location_id = :location_id AND s.quantity > 0";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':location_id', $location_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all stock entries for a product with location information
 * 
 * @param int $product_id Product ID
 * @return array List of stock entries with location details
 */
function getStockByProductId($product_id): array
{
    global $conn;

    $sql = "SELECT s.*, 
            w.warehouse_name,
            wl.name as location_name,
            wl.type as location_type
            FROM `tbl_stock` s
            LEFT JOIN `tbl_warehouse` w ON s.warehouse_id = w.warehouse_id
            LEFT JOIN `tbl_warehouse_location` wl ON s.location_id = wl.location_id
            WHERE s.product_id = :product_id AND s.quantity > 0
            ORDER BY s.added_on DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get location history for a specific stock item
 * 
 * @param int $stock_id Stock ID
 * @return array List of location changes for the stock item
 */
function getStockLocationHistory($stock_id): array
{
    global $conn;

    // Get the stock details first to check if it's a split batch
    $stmt = $conn->prepare("SELECT * FROM `tbl_stock` WHERE stock_id = :stock_id");
    $stmt->bindParam(':stock_id', $stock_id);
    $stmt->execute();
    $stock = $stmt->fetch(PDO::FETCH_ASSOC);

    // If this is a split batch, also include history from the original batch in the notes
    $originalBatchNote = '';
    if ($stock && !empty($stock['original_batch'])) {
        $originalBatchNote = "This batch was split from original batch: {$stock['original_batch']}";
    }

    $sql = "SELECT slh.*, 
            ow.warehouse_name as old_warehouse_name, 
            nw.warehouse_name as new_warehouse_name,
            ol.name as old_location_name, ol.type as old_location_type,
            nl.name as new_location_name, nl.type as new_location_type,
            c.full_name as moved_by_name
            FROM `tbl_stock_location_history` slh
            LEFT JOIN `tbl_warehouse` ow ON slh.old_warehouse_id = ow.warehouse_id
            LEFT JOIN `tbl_warehouse` nw ON slh.new_warehouse_id = nw.warehouse_id
            LEFT JOIN `tbl_warehouse_location` ol ON slh.old_location_id = ol.location_id
            LEFT JOIN `tbl_warehouse_location` nl ON slh.new_location_id = nl.location_id
            LEFT JOIN `tbl_customers` c ON slh.moved_by = c.customer_id
            WHERE slh.stock_id = :stock_id
            ORDER BY slh.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':stock_id', $stock_id);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If this is a split batch and there's history, add a note to the first record
    if (!empty($originalBatchNote) && !empty($history)) {
        $history[0]['notes'] = $history[0]['notes'] . ' (' . $originalBatchNote . ')';
    }

    return $history;
}

/**
 * Get location history for a specific product across all batches
 * 
 * @param int $product_id Product ID
 * @return array List of location changes for the product
 */
function getProductLocationHistory($product_id): array
{
    global $conn;

    $sql = "SELECT slh.*, 
            ow.warehouse_name as old_warehouse_name, 
            nw.warehouse_name as new_warehouse_name,
            ol.name as old_location_name, ol.type as old_location_type,
            nl.name as new_location_name, nl.type as new_location_type,
            c.full_name as moved_by_name,
            s.original_batch
            FROM `tbl_stock_location_history` slh
            LEFT JOIN `tbl_warehouse` ow ON slh.old_warehouse_id = ow.warehouse_id
            JOIN `tbl_warehouse` nw ON slh.new_warehouse_id = nw.warehouse_id
            LEFT JOIN `tbl_warehouse_location` ol ON slh.old_location_id = ol.location_id
            LEFT JOIN `tbl_warehouse_location` nl ON slh.new_location_id = nl.location_id
            LEFT JOIN `tbl_customers` c ON slh.moved_by = c.customer_id
            LEFT JOIN `tbl_stock` s ON slh.stock_id = s.stock_id
            WHERE slh.product_id = :product_id
            ORDER BY slh.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add notes for split batches
    foreach ($history as $key => $record) {
        if (!empty($record['original_batch'])) {
            $history[$key]['notes'] = $record['notes'] .
                ' (This batch was split from original batch: ' . $record['original_batch'] . ')';
        }
    }

    return $history;
}

/**
 * Move a partial quantity of stock to a new location
 * 
 * When moving a partial quantity, this function creates a new batch for the moved quantity
 * while keeping the original batch in its current location with the remaining quantity.
 * 
 * @param int $stock_id Original stock ID
 * @param int $warehouse_id Target warehouse ID
 * @param int $location_id Target location ID
 * @param int $quantity Quantity to move (must be less than total quantity)
 * @param string $notes Optional notes about the move
 * @param int|null $user_id User making the change
 * @return bool True on success, false on failure
 */
function movePartialStockToLocation($stock_id, $warehouse_id, $location_id, $quantity, $notes = '', $user_id = null): bool
{
    global $conn;

    try {
        $conn->beginTransaction();

        // Get current stock information
        $stmt = $conn->prepare("SELECT * FROM `tbl_stock` WHERE stock_id = :stock_id");
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->execute();
        $stock = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$stock) {
            throw new Exception('Stock not found');
        }

        // Ensure the quantity to move is valid
        if ($quantity <= 0 || $quantity > $stock['quantity']) {
            throw new Exception('Invalid quantity for partial move');
        }

        // Update the original stock record with reduced quantity
        $remainingQty = $stock['quantity'] - $quantity;
        $stmt = $conn->prepare("UPDATE `tbl_stock` SET quantity = :quantity WHERE stock_id = :stock_id");
        $stmt->bindParam(':quantity', $remainingQty);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->execute();

        // Create a new batch with a reference to the original batch
        $newBatchNumber = $stock['batch_number'] . '-SPLIT-' . date('YmdHis');

        $stmt = $conn->prepare("INSERT INTO `tbl_stock` 
                      (product_id, batch_number, quantity, unit_cost_price, warehouse_id, location_id, original_batch, supplier_id)
                      VALUES
                      (:product_id, :batch_number, :quantity, :unit_cost_price, :warehouse_id, :location_id, :original_batch, :supplier_id)");

        $stmt->bindParam(':product_id', $stock['product_id']);
        $stmt->bindParam(':batch_number', $newBatchNumber);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':unit_cost_price', $stock['unit_cost_price']);
        $stmt->bindParam(':warehouse_id', $warehouse_id);
        $stmt->bindParam(':location_id', $location_id);
        $stmt->bindParam(':original_batch', $stock['batch_number']);
        $stmt->bindParam(':supplier_id', $stock['supplier_id']);
        $stmt->execute();

        $newStockId = $conn->lastInsertId();

        // Record location change in transaction log for both records
        $old_location = $stock['location_id'] ? getWarehouseLocationById($stock['location_id']) : null;
        $new_location = getWarehouseLocationById($location_id);

        $old_location_name = $old_location ? ($old_location['name'] . ' (' . $old_location['type'] . ')') : 'Unassigned';
        $new_location_name = $new_location ? ($new_location['name'] . ' (' . $new_location['type'] . ')') : 'Unassigned';

        // Transaction note for original stock
        $transaction_note_original = "Split batch: {$quantity} units moved to new location at {$new_location_name}";
        if ($notes) {
            $transaction_note_original .= ". Notes: {$notes}";
        }

        // Record transaction for original stock
        $stmt = $conn->prepare("INSERT INTO `tbl_stock_transactions` 
                              (product_id, stock_id, quantity_change, previous_quantity, transaction_type, notes, user_id, transaction_location) 
                              VALUES 
                              (:product_id, :stock_id, :quantity_change, :previous_quantity, 'split', :notes, :user_id, :transaction_location)");

        $quantity_change = -$quantity;
        $stmt->bindParam(':product_id', $stock['product_id']);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->bindParam(':quantity_change', $quantity_change);
        $stmt->bindParam(':previous_quantity', $stock['quantity']);
        $stmt->bindParam(':notes', $transaction_note_original);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':transaction_location', $old_location_name);
        $stmt->execute();

        // Transaction note for new stock
        $transaction_note_new = "New batch created from split: {$quantity} units from batch {$stock['batch_number']}";
        if ($notes) {
            $transaction_note_new .= ". Notes: {$notes}";
        }

        // Record transaction for new stock
        $stmt = $conn->prepare("INSERT INTO `tbl_stock_transactions` 
                              (product_id, stock_id, quantity_change, previous_quantity, transaction_type, notes, user_id, transaction_location) 
                              VALUES 
                              (:product_id, :stock_id, :quantity_change, 0, 'split_new', :notes, :user_id, :transaction_location)");

        $stmt->bindParam(':product_id', $stock['product_id']);
        $stmt->bindParam(':stock_id', $newStockId);
        $stmt->bindParam(':quantity_change', $quantity);
        $stmt->bindParam(':notes', $transaction_note_new);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':transaction_location', $new_location_name);
        $stmt->execute();

        // Record stock location change in history table for the new batch
        recordStockLocationChange(
            $newStockId,
            $stock['product_id'],
            $newBatchNumber,
            $stock['warehouse_id'],
            $warehouse_id,
            $stock['location_id'],
            $location_id,
            $user_id,
            "Split batch: {$quantity} units moved from batch {$stock['batch_number']}. " . $notes
        );

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollBack();
        print_r($e->getMessage());
        error_log('Error moving partial stock to location: ' . $e->getMessage());
        return false;
    }
}
