<?php
/**
 * Inventory Control Models Functions
 * 
 * This file contains functions for inventory control calculations:
 * - Economic Order Quantity (EOQ)
 * - Reorder Point (ROP) 
 * - Safety Stock calculations
 * - ABC Analysis
 */

/**
 * Calculate Economic Order Quantity (EOQ)
 * 
 * @param float $annual_demand Annual demand for the product
 * @param float $ordering_cost Cost per order
 * @param float $holding_cost Annual holding cost per unit
 * @return array EOQ calculation details
 */
function calculateEOQ($annual_demand, $ordering_cost, $holding_cost): array
{
    if ($holding_cost <= 0) {
        throw new Exception("Holding cost must be greater than 0");
    }
    
    $eoq = sqrt((2 * $annual_demand * $ordering_cost) / $holding_cost);
    
    // Calculate related metrics
    $number_of_orders = $annual_demand / $eoq;
    $total_ordering_cost = $number_of_orders * $ordering_cost;
    $average_inventory = $eoq / 2;
    $total_holding_cost = $average_inventory * $holding_cost;
    $total_cost = $total_ordering_cost + $total_holding_cost;
    
    return [
        'eoq' => round($eoq, 2),
        'annual_demand' => $annual_demand,
        'ordering_cost' => $ordering_cost,
        'holding_cost' => $holding_cost,
        'number_of_orders_per_year' => round($number_of_orders, 2),
        'total_ordering_cost' => round($total_ordering_cost, 2),
        'average_inventory' => round($average_inventory, 2),
        'total_holding_cost' => round($total_holding_cost, 2),
        'total_annual_cost' => round($total_cost, 2),
        'days_between_orders' => round(365 / $number_of_orders, 1)
    ];
}

/**
 * Calculate Reorder Point (ROP)
 * 
 * @param float $daily_demand Average daily demand
 * @param int $lead_time Lead time in days
 * @param float $safety_stock Safety stock quantity
 * @return array ROP calculation details
 */
function calculateROP($daily_demand, $lead_time, $safety_stock = 0): array
{
    $rop = ($daily_demand * $lead_time) + $safety_stock;
    
    return [
        'reorder_point' => round($rop, 2),
        'daily_demand' => $daily_demand,
        'lead_time_days' => $lead_time,
        'safety_stock' => $safety_stock,
        'demand_during_lead_time' => round($daily_demand * $lead_time, 2)
    ];
}

/**
 * Calculate Safety Stock using standard deviation method
 * 
 * @param float $z_score Z-score for desired service level (e.g., 1.65 for 95%)
 * @param float $demand_std_dev Standard deviation of demand
 * @param float $lead_time_std_dev Standard deviation of lead time
 * @param float $avg_demand Average demand
 * @param float $avg_lead_time Average lead time
 * @return array Safety stock calculation details
 */
function calculateSafetyStock($z_score, $demand_std_dev, $lead_time_std_dev, $avg_demand, $avg_lead_time): array
{
    // Formula: SS = Z * sqrt((avg_lead_time * demand_variance) + (avg_demand^2 * lead_time_variance))
    $demand_variance = pow($demand_std_dev, 2);
    $lead_time_variance = pow($lead_time_std_dev, 2);
    
    $safety_stock = $z_score * sqrt(
        ($avg_lead_time * $demand_variance) + 
        (pow($avg_demand, 2) * $lead_time_variance)
    );
    
    return [
        'safety_stock' => round($safety_stock, 2),
        'z_score' => $z_score,
        'service_level_percent' => getServiceLevelFromZScore($z_score),
        'demand_std_dev' => $demand_std_dev,
        'lead_time_std_dev' => $lead_time_std_dev,
        'avg_demand' => $avg_demand,
        'avg_lead_time' => $avg_lead_time
    ];
}

/**
 * Calculate safety stock using simple method (percentage of average demand)
 * 
 * @param float $avg_demand Average demand during lead time
 * @param float $safety_percentage Safety stock as percentage of average demand
 * @return array Simple safety stock calculation
 */
function calculateSimpleSafetyStock($avg_demand, $safety_percentage): array
{
    $safety_stock = $avg_demand * ($safety_percentage / 100);
    
    return [
        'safety_stock' => round($safety_stock, 2),
        'avg_demand' => $avg_demand,
        'safety_percentage' => $safety_percentage,
        'method' => 'Simple Percentage Method'
    ];
}

/**
 * Get service level percentage from Z-score
 * 
 * @param float $z_score Z-score value
 * @return float Service level percentage
 */
function getServiceLevelFromZScore($z_score): float
{
    $service_levels = [
        1.28 => 90.0,
        1.65 => 95.0,
        1.96 => 97.5,
        2.33 => 99.0,
        2.58 => 99.5,
        3.09 => 99.9
    ];
    
    // Find closest match
    $closest_z = array_keys($service_levels)[0];
    $min_diff = abs($z_score - $closest_z);
    
    foreach (array_keys($service_levels) as $z) {
        $diff = abs($z_score - $z);
        if ($diff < $min_diff) {
            $min_diff = $diff;
            $closest_z = $z;
        }
    }
    
    return $service_levels[$closest_z];
}

/**
 * Perform ABC Analysis on products
 * 
 * @param array $products Array of products with annual_value calculated
 * @return array ABC classification results
 */
function performABCAnalysis($products = null): array
{
    // If no products provided, get all products with their annual values
    if ($products === null) {
        $products = getProductsForABCAnalysis();
    }
    
    if (empty($products)) {
        return ['error' => 'No products found for ABC analysis'];
    }
    
    // Calculate annual value for each product and sort by value descending
    usort($products, function($a, $b) {
        return $b['annual_value'] <=> $a['annual_value'];
    });
    
    $total_value = array_sum(array_column($products, 'annual_value'));
    $cumulative_value = 0;
    $cumulative_percentage = 0;
    
    $classified_products = [];
    $a_items = [];
    $b_items = [];
    $c_items = [];
    
    foreach ($products as $index => $product) {
        $product['product_name'] = html_entity_decode($product['product_name']);
        $cumulative_value += $product['annual_value'];
        $cumulative_percentage = ($total_value > 0) ? ($cumulative_value / $total_value) * 100 : 0;
        
        // ABC Classification based on Pareto Principle
        if ($cumulative_percentage <= 80) {
            $classification = 'A';
            $a_items[] = $product;
        } elseif ($cumulative_percentage <= 95) {
            $classification = 'B';
            $b_items[] = $product;
        } else {
            $classification = 'C';
            $c_items[] = $product;
        }
        
        $classified_products[] = array_merge($product, [
            'classification' => $classification,
            'cumulative_value' => round($cumulative_value, 2),
            'cumulative_percentage' => round($cumulative_percentage, 2),
            'value_percentage' => $total_value > 0 ? round(($product['annual_value'] / $total_value) * 100, 2) : 0
        ]);
    }
    
    return [
        'total_products' => count($products),
        'total_annual_value' => round($total_value, 2),
        'products' => $classified_products,
        'summary' => [
            'a_items' => [
                'count' => count($a_items),
                'percentage' => round((count($a_items) / count($products)) * 100, 1),
                'value' => round(array_sum(array_column($a_items, 'annual_value')), 2),
                'value_percentage' => $total_value > 0 ? round((array_sum(array_column($a_items, 'annual_value')) / $total_value) * 100, 1) : 0
            ],
            'b_items' => [
                'count' => count($b_items),
                'percentage' => round((count($b_items) / count($products)) * 100, 1),
                'value' => round(array_sum(array_column($b_items, 'annual_value')), 2),
                'value_percentage' => $total_value > 0 ? round((array_sum(array_column($b_items, 'annual_value')) / $total_value) * 100, 1) : 0
            ],
            'c_items' => [
                'count' => count($c_items),
                'percentage' => round((count($c_items) / count($products)) * 100, 1),
                'value' => round(array_sum(array_column($c_items, 'annual_value')), 2),
                'value_percentage' => $total_value > 0 ? round((array_sum(array_column($c_items, 'annual_value')) / $total_value) * 100, 1) : 0
            ]
        ]
    ];
}

/**
 * Get products data for ABC analysis
 * 
 * @return array Products with calculated annual values
 */
function getProductsForABCAnalysis(): array
{
    global $conn;
    
    // Calculate annual sales value for each product
    $sql = "SELECT 
                p.product_id,
                p.product_name,
                p.purchase_price,
                p.selling_price,
                COALESCE(sales.annual_quantity, 0) as annual_quantity,
                COALESCE(sales.annual_quantity * p.purchase_price, 0) as annual_value,
                COALESCE(stock.current_stock, 0) as current_stock
            FROM `tbl_products` p
            LEFT JOIN (
                SELECT 
                    sod.product_id,
                    SUM(sod.quantity) as annual_quantity
                FROM `tbl_sale_order_details` sod
                INNER JOIN `tbl_sale_order` so ON sod.sale_order_id = so.order_id
                WHERE so.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                GROUP BY sod.product_id
            ) sales ON p.product_id = sales.product_id
            LEFT JOIN (
                SELECT 
                    product_id,
                    SUM(quantity) as current_stock
                FROM `tbl_stock`
                GROUP BY product_id
            ) stock ON p.product_id = stock.product_id
            ORDER BY annual_value DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Calculate inventory control parameters for a specific product
 * 
 * @param int $product_id Product ID
 * @param array $params Optional parameters for calculations
 * @return array Complete inventory control analysis
 */
function getInventoryControlAnalysis($product_id, $params = []): array
{
    // Get product details
    $product = getProductById($product_id);
    if (!$product) {
        throw new Exception("Product not found");
    }
    
    // Calculate annual demand from historical data
    $annual_demand = getAnnualDemand($product_id);
    $daily_demand = $annual_demand / 365;
    
    // Get average lead time (you might want to store this in supplier data)
    $lead_time = $params['lead_time'] ?? 7; // Default 7 days
    $ordering_cost = $params['ordering_cost'] ?? 50; // Default $50 per order
    $holding_cost_percentage = $params['holding_cost_percentage'] ?? 0.2; // 20% of unit cost
    $holding_cost = $product['purchase_price'] * $holding_cost_percentage;
    
    $results = [];
    
    try {
        // EOQ Calculation
        if ($annual_demand > 0 && $holding_cost > 0) {
            $results['eoq'] = calculateEOQ($annual_demand, $ordering_cost, $holding_cost);
        }
        
        // ROP Calculation (with simple safety stock)
        $safety_stock = $params['safety_stock'] ?? ($daily_demand * 2); // 2 days safety stock
        $results['rop'] = calculateROP($daily_demand, $lead_time, $safety_stock);
        
        // Current stock status
        $current_stock = getCurrentStock($product_id);
        $results['current_status'] = [
            'current_stock' => $current_stock,
            'reorder_needed' => $current_stock <= ($results['rop']['reorder_point'] ?? 0),
            'stock_days_remaining' => $daily_demand > 0 ? round($current_stock / $daily_demand, 1) : 'N/A'
        ];
        
    } catch (Exception $e) {
        $results['error'] = $e->getMessage();
    }
    
    return [
        'product_id' => $product_id,
        'product_name' => $product['product_name'],
        'annual_demand' => $annual_demand,
        'daily_demand' => round($daily_demand, 2),
        'analysis' => $results
    ];
}

/**
 * Get annual demand for a product from historical data
 * 
 * @param int $product_id Product ID
 * @return float Annual demand quantity
 */
function getAnnualDemand($product_id): float
{
    global $conn;
    
    $sql = "SELECT SUM(sod.quantity) as annual_demand
            FROM `tbl_sale_order_details` sod
            INNER JOIN `tbl_sale_order` so ON sod.sale_order_id = so.order_id
            WHERE sod.product_id = :product_id
            AND so.order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return floatval($result['annual_demand'] ?? 0);
}

/**
 * Get current total stock for a product
 * 
 * @param int $product_id Product ID
 * @return float Current stock quantity
 */
function getCurrentStock($product_id): float
{
    global $conn;
    
    $sql = "SELECT SUM(quantity) as current_stock
            FROM `tbl_stock`
            WHERE product_id = :product_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return floatval($result['current_stock'] ?? 0);
}
