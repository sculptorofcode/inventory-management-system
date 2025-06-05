<?php
/**
 * Inventory Valuation Functions
 * 
 * This file contains functions for different inventory valuation methods:
 * - FIFO (First In, First Out)
 * - LIFO (Last In, First Out)
 * - Weighted Average Cost (WAC)
 */

/**
 * Calculate inventory value using FIFO method
 * 
 * @param int $product_id Product ID
 * @param int $quantity_to_value Quantity to calculate value for
 * @return array Contains total_value, cost_per_unit, and breakdown
 */
function calculateFIFOValue($product_id, $quantity_to_value = null): array
{
    global $conn;
    
    // Get all stock entries for this product ordered by date (oldest first)
    $sql = "SELECT stock_id, quantity, unit_cost_price, added_on, batch_number
            FROM `tbl_stock` 
            WHERE product_id = :product_id AND quantity > 0
            ORDER BY added_on ASC, stock_id ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $stock_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_available = array_sum(array_column($stock_entries, 'quantity'));
    
    // If no quantity specified, use all available stock
    if ($quantity_to_value === null) {
        $quantity_to_value = $total_available;
    }
    
    if ($quantity_to_value > $total_available) {
        throw new Exception("Requested quantity ($quantity_to_value) exceeds available stock ($total_available)");
    }
    
    $remaining_qty = $quantity_to_value;
    $total_value = 0;
    $breakdown = [];
    
    foreach ($stock_entries as $entry) {
        if ($remaining_qty <= 0) break;
        
        $qty_from_batch = min($remaining_qty, $entry['quantity']);
        $batch_value = $qty_from_batch * $entry['unit_cost_price'];
        
        $breakdown[] = [
            'batch_number' => $entry['batch_number'],
            'quantity_used' => $qty_from_batch,
            'unit_cost' => $entry['unit_cost_price'],
            'batch_value' => $batch_value,
            'date_added' => $entry['added_on']
        ];
        
        $total_value += $batch_value;
        $remaining_qty -= $qty_from_batch;
    }
    
    $average_cost = $quantity_to_value > 0 ? $total_value / $quantity_to_value : 0;
    
    return [
        'method' => 'FIFO',
        'quantity_valued' => $quantity_to_value,
        'total_value' => round($total_value, 2),
        'cost_per_unit' => round($average_cost, 2),
        'breakdown' => $breakdown
    ];
}

/**
 * Calculate inventory value using LIFO method
 * 
 * @param int $product_id Product ID
 * @param int $quantity_to_value Quantity to calculate value for
 * @return array Contains total_value, cost_per_unit, and breakdown
 */
function calculateLIFOValue($product_id, $quantity_to_value = null): array
{
    global $conn;
    
    // Get all stock entries for this product ordered by date (newest first)
    $sql = "SELECT stock_id, quantity, unit_cost_price, added_on, batch_number
            FROM `tbl_stock` 
            WHERE product_id = :product_id AND quantity > 0
            ORDER BY added_on DESC, stock_id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $stock_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_available = array_sum(array_column($stock_entries, 'quantity'));
    
    // If no quantity specified, use all available stock
    if ($quantity_to_value === null) {
        $quantity_to_value = $total_available;
    }
    
    if ($quantity_to_value > $total_available) {
        throw new Exception("Requested quantity ($quantity_to_value) exceeds available stock ($total_available)");
    }
    
    $remaining_qty = $quantity_to_value;
    $total_value = 0;
    $breakdown = [];
    
    foreach ($stock_entries as $entry) {
        if ($remaining_qty <= 0) break;
        
        $qty_from_batch = min($remaining_qty, $entry['quantity']);
        $batch_value = $qty_from_batch * $entry['unit_cost_price'];
        
        $breakdown[] = [
            'batch_number' => $entry['batch_number'],
            'quantity_used' => $qty_from_batch,
            'unit_cost' => $entry['unit_cost_price'],
            'batch_value' => $batch_value,
            'date_added' => $entry['added_on']
        ];
        
        $total_value += $batch_value;
        $remaining_qty -= $qty_from_batch;
    }
    
    $average_cost = $quantity_to_value > 0 ? $total_value / $quantity_to_value : 0;
    
    return [
        'method' => 'LIFO',
        'quantity_valued' => $quantity_to_value,
        'total_value' => round($total_value, 2),
        'cost_per_unit' => round($average_cost, 2),
        'breakdown' => $breakdown
    ];
}

/**
 * Calculate inventory value using Weighted Average Cost method
 * 
 * @param int $product_id Product ID
 * @param int $quantity_to_value Quantity to calculate value for
 * @return array Contains total_value, cost_per_unit, and breakdown
 */
function calculateWACValue($product_id, $quantity_to_value = null): array
{
    global $conn;
    
    // Get all stock entries for this product
    $sql = "SELECT stock_id, quantity, unit_cost_price, added_on, batch_number
            FROM `tbl_stock` 
            WHERE product_id = :product_id AND quantity > 0
            ORDER BY added_on ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $stock_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($stock_entries)) {
        return [
            'method' => 'WAC',
            'quantity_valued' => 0,
            'total_value' => 0,
            'cost_per_unit' => 0,
            'breakdown' => []
        ];
    }
    
    // Calculate weighted average cost
    $total_quantity = 0;
    $total_value = 0;
    
    foreach ($stock_entries as $entry) {
        $total_quantity += $entry['quantity'];
        $total_value += ($entry['quantity'] * $entry['unit_cost_price']);
    }
    
    $weighted_avg_cost = $total_quantity > 0 ? $total_value / $total_quantity : 0;
    
    // If no quantity specified, use all available stock
    if ($quantity_to_value === null) {
        $quantity_to_value = $total_quantity;
    }
    
    if ($quantity_to_value > $total_quantity) {
        throw new Exception("Requested quantity ($quantity_to_value) exceeds available stock ($total_quantity)");
    }
    
    $valuation_total = $quantity_to_value * $weighted_avg_cost;
    
    return [
        'method' => 'WAC',
        'quantity_valued' => $quantity_to_value,
        'total_value' => round($valuation_total, 2),
        'cost_per_unit' => round($weighted_avg_cost, 2),
        'weighted_average_cost' => round($weighted_avg_cost, 2),
        'total_available_quantity' => $total_quantity,
        'total_available_value' => round($total_value, 2),
        'breakdown' => $stock_entries
    ];
}

/**
 * Compare all three valuation methods for a product
 * 
 * @param int $product_id Product ID
 * @param int $quantity_to_value Quantity to calculate value for
 * @return array Comparison of all three methods
 */
function compareValuationMethods($product_id, $quantity_to_value = null): array
{
    try {
        $fifo = calculateFIFOValue($product_id, $quantity_to_value);
        $lifo = calculateLIFOValue($product_id, $quantity_to_value);
        $wac = calculateWACValue($product_id, $quantity_to_value);
        
        return [
            'product_id' => $product_id,
            'quantity_valued' => $quantity_to_value,
            'fifo' => $fifo,
            'lifo' => $lifo,
            'wac' => $wac,
            'comparison' => [
                'highest_value' => max($fifo['total_value'], $lifo['total_value'], $wac['total_value']),
                'lowest_value' => min($fifo['total_value'], $lifo['total_value'], $wac['total_value']),
                'value_difference' => max($fifo['total_value'], $lifo['total_value'], $wac['total_value']) - min($fifo['total_value'], $lifo['total_value'], $wac['total_value'])
            ]
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
            'product_id' => $product_id,
            'quantity_valued' => $quantity_to_value
        ];
    }
}

/**
 * Get product cost for sale using specified valuation method
 * 
 * @param int $product_id Product ID
 * @param int $quantity Quantity being sold
 * @param string $method Valuation method (FIFO, LIFO, WAC)
 * @return array Cost calculation details
 */
function getProductCostForSale($product_id, $quantity, $method = 'FIFO'): array
{
    switch (strtoupper($method)) {
        case 'FIFO':
            return calculateFIFOValue($product_id, $quantity);
        case 'LIFO':
            return calculateLIFOValue($product_id, $quantity);
        case 'WAC':
            return calculateWACValue($product_id, $quantity);
        default:
            throw new Exception("Invalid valuation method: $method");
    }
}

/**
 * Calculate Cost of Goods Sold (COGS) for a period using different methods
 * 
 * @param int $product_id Product ID
 * @param string $start_date Start date (Y-m-d format)
 * @param string $end_date End date (Y-m-d format)
 * @param string $method Valuation method
 * @return array COGS calculation details
 */
function calculateCOGS($product_id, $start_date, $end_date, $method = 'FIFO'): array
{
    global $conn;
    
    // Get all sales transactions for the period
    // Calculate total quantity sold for the product in the given period using sale order tables
    $sql = "SELECT SUM(sod.quantity) as total_sold
            FROM tbl_sale_order_details sod
            INNER JOIN tbl_sale_order so ON sod.sale_order_id = so.order_id
            WHERE sod.product_id = :product_id
            AND so.order_date BETWEEN :start_date AND :end_date
            AND so.status IN ('confirmed', 'shipped', 'delivered')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':start_date', $start_date);
    $stmt->bindParam(':end_date', $end_date);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_sold = abs($result['total_sold'] ?? 0);
    
    if ($total_sold == 0) {
        return [
            'product_id' => $product_id,
            'period' => "$start_date to $end_date",
            'method' => $method,
            'total_quantity_sold' => 0,
            'total_cogs' => 0,
            'average_cost_per_unit' => 0
        ];
    }
    
    // Calculate COGS using the specified method
    $valuation = getProductCostForSale($product_id, $total_sold, $method);
    
    return [
        'product_id' => $product_id,
        'period' => "$start_date to $end_date",
        'method' => $method,
        'total_quantity_sold' => $total_sold,
        'total_cogs' => $valuation['total_value'],
        'average_cost_per_unit' => $valuation['cost_per_unit'],
        'breakdown' => $valuation['breakdown'] ?? []
    ];
}
