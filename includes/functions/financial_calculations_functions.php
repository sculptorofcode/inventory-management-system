<?php
/**
 * Financial Calculations Functions
 * 
 * This file contains functions for financial metrics and calculations:
 * - Inventory Turnover Ratio
 * - Days Sales Outstanding
 * - Gross Profit Margin
 * - Other financial KPIs
 */

/**
 * Calculate Inventory Turnover Ratio
 * 
 * @param int $product_id Product ID (optional, if null calculates for all products)
 * @param string $start_date Start date for calculation period
 * @param string $end_date End date for calculation period
 * @return array Inventory turnover calculation details
 */
function calculateInventoryTurnover($product_id = null, $start_date = null, $end_date = null): array
{
    // Default to last 12 months if no dates provided
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-12 months'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    // Calculate Cost of Goods Sold (COGS)
    $cogs = calculateCOGSForPeriod($start_date, $end_date, $product_id);
    
    // Calculate Average Inventory Value
    $avg_inventory = calculateAverageInventoryValue($start_date, $end_date, $product_id);
    
    if ($avg_inventory == 0) {
        return [
            'product_id' => $product_id,
            'period' => "$start_date to $end_date",
            'cogs' => $cogs,
            'average_inventory_value' => 0,
            'inventory_turnover_ratio' => 0,
            'days_in_inventory' => 0,
            'error' => 'No inventory data available for the period'
        ];
    }
    
    $turnover_ratio = $cogs / $avg_inventory;
    $days_in_inventory = 365 / $turnover_ratio;
    
    return [
        'product_id' => $product_id,
        'period' => "$start_date to $end_date",
        'cogs' => round($cogs, 2),
        'average_inventory_value' => round($avg_inventory, 2),
        'inventory_turnover_ratio' => round($turnover_ratio, 2),
        'days_in_inventory' => round($days_in_inventory, 1),
        'turnover_frequency' => $turnover_ratio > 1 ? 'High' : ($turnover_ratio > 0.5 ? 'Medium' : 'Low')
    ];
}

/**
 * Calculate Days Sales Outstanding (DSO) - also known as Days in Inventory
 * 
 * @param int $product_id Product ID (optional)
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return array DSO calculation details
 */
function calculateDaysSalesOutstanding($product_id = null, $start_date = null, $end_date = null): array
{
    $turnover_data = calculateInventoryTurnover($product_id, $start_date, $end_date);
    
    return [
        'product_id' => $product_id,
        'period' => $turnover_data['period'],
        'days_sales_outstanding' => $turnover_data['days_in_inventory'],
        'inventory_turnover_ratio' => $turnover_data['inventory_turnover_ratio'],
        'interpretation' => getDSOInterpretation($turnover_data['days_in_inventory'])
    ];
}

/**
 * Calculate Gross Profit Margin
 * 
 * @param int $product_id Product ID (optional)
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return array Gross profit margin details
 */
function calculateGrossProfitMargin($product_id = null, $start_date = null, $end_date = null): array
{
    global $conn;
    
    // Default to last 12 months if no dates provided
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-12 months'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    // Calculate sales revenue and COGS
    $sales_data = calculateSalesRevenue($start_date, $end_date, $product_id);
    $cogs = calculateCOGSForPeriod($start_date, $end_date, $product_id);
    
    $gross_profit = $sales_data['total_revenue'] - $cogs;
    $gross_profit_margin = $sales_data['total_revenue'] > 0 ? 
        ($gross_profit / $sales_data['total_revenue']) * 100 : 0;
    
    return [
        'product_id' => $product_id,
        'period' => "$start_date to $end_date",
        'sales_revenue' => round($sales_data['total_revenue'], 2),
        'cost_of_goods_sold' => round($cogs, 2),
        'gross_profit' => round($gross_profit, 2),
        'gross_profit_margin_percent' => round($gross_profit_margin, 2),
        'units_sold' => $sales_data['units_sold'],
        'margin_category' => getMarginCategory($gross_profit_margin)
    ];
}

/**
 * Calculate sales revenue for a period
 * 
 * @param int $product_id Product ID (optional)
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return array Sales revenue details
 */
function calculateSalesRevenue($start_date, $end_date, $product_id = null): array
{
    global $conn;
    
    $where_clause = "WHERE so.order_date BETWEEN :start_date AND :end_date AND so.status = 'delivered'";
    $params = [
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ];
    
    if ($product_id) {
        $where_clause .= " AND sod.product_id = :product_id";
        $params[':product_id'] = $product_id;
    }
    
    $sql = "SELECT 
                SUM(sod.quantity * sod.sale_price) as total_revenue,
                SUM(sod.quantity) as units_sold,
                COUNT(DISTINCT so.order_id) as number_of_orders
            FROM `tbl_sale_order` so
            JOIN `tbl_sale_order_details` sod ON so.order_id = sod.sale_order_id
            $where_clause";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return [
        'total_revenue' => floatval($result['total_revenue'] ?? 0),
        'units_sold' => intval($result['units_sold'] ?? 0),
        'number_of_orders' => intval($result['number_of_orders'] ?? 0),
        'average_order_value' => $result['number_of_orders'] > 0 ? 
            round($result['total_revenue'] / $result['number_of_orders'], 2) : 0
    ];
}

/**
 * Calculate Cost of Goods Sold for a period
 * 
 * @param int $product_id Product ID (optional)
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return float COGS amount
 */
function calculateCOGSForPeriod($start_date, $end_date, $product_id = null): float
{
    global $conn;
    
    // Calculate COGS using tbl_sale_order and tbl_sale_order_details
    $where_clause = "WHERE so.order_date BETWEEN :start_date AND :end_date AND so.status = 'delivered'";
    $params = [
        ':start_date' => $start_date,
        ':end_date' => $end_date
    ];

    if ($product_id) {
        $where_clause .= " AND sod.product_id = :product_id";
        $params[':product_id'] = $product_id;
    }

    $sql = "SELECT 
                SUM(sod.quantity * sod.unit_cost_price) as total_cogs
            FROM `tbl_sale_order` so
            JOIN `tbl_sale_order_details` sod ON so.order_id = sod.sale_order_id
            $where_clause";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return floatval($result['total_cogs'] ?? 0);
}

/**
 * Calculate average inventory value for a period
 * 
 * @param int $product_id Product ID (optional)
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return float Average inventory value
 */
function calculateAverageInventoryValue($start_date, $end_date, $product_id = null): float
{
    global $conn;
      // Get opening inventory value
    $opening_value = getInventoryValueAtDate($start_date, $product_id);
    
    // Get closing inventory value
    $closing_value = getInventoryValueAtDate($end_date, $product_id);
    
    return ($opening_value + $closing_value) / 2;
}

/**
 * Get inventory value at a specific date
 * 
 * @param int $product_id Product ID (optional)
 * @param string $date Date for valuation
 * @return float Inventory value
 */
function getInventoryValueAtDate($date, $product_id = null): float
{
    global $conn;
    
    $where_clause = "WHERE s.added_on <= :date";
    $params = [':date' => $date . ' 23:59:59'];
    
    if ($product_id) {
        $where_clause .= " AND s.product_id = :product_id";
        $params[':product_id'] = $product_id;
    }
    
    $sql = "SELECT SUM(s.quantity * s.unit_cost_price) as inventory_value
            FROM `tbl_stock` s
            $where_clause";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return floatval($result['inventory_value'] ?? 0);
}

/**
 * Calculate Return on Investment (ROI) for inventory
 * 
 * @param int $product_id Product ID (optional)
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return array ROI calculation details
 */
function calculateInventoryROI($product_id = null, $start_date = null, $end_date = null): array
{
    if (!$start_date) {
        $start_date = date('Y-m-d', strtotime('-12 months'));
    }
    if (!$end_date) {
        $end_date = date('Y-m-d');
    }
    
    $gross_profit_data = calculateGrossProfitMargin($product_id, $start_date, $end_date);
    $avg_inventory = calculateAverageInventoryValue($start_date, $end_date, $product_id);
    
    $roi_percentage = $avg_inventory > 0 ? 
        ($gross_profit_data['gross_profit'] / $avg_inventory) * 100 : 0;
    
    return [
        'product_id' => $product_id,
        'period' => "$start_date to $end_date",
        'gross_profit' => $gross_profit_data['gross_profit'],
        'average_inventory_investment' => round($avg_inventory, 2),
        'roi_percentage' => round($roi_percentage, 2),
        'roi_category' => getROICategory($roi_percentage)
    ];
}

/**
 * Calculate comprehensive financial dashboard
 * 
 * @param int $product_id Product ID (optional)
 * @param string $start_date Start date
 * @param string $end_date End date
 * @return array Complete financial analysis
 */
function getFinancialDashboard($product_id = null, $start_date = null, $end_date = null): array
{
    try {
        $turnover = calculateInventoryTurnover($product_id, $start_date, $end_date);
        $dso = calculateDaysSalesOutstanding($product_id, $start_date, $end_date);
        $margin = calculateGrossProfitMargin($product_id, $start_date, $end_date);
        $roi = calculateInventoryROI($product_id, $start_date, $end_date);
        
        return [
            'product_id' => $product_id,
            'period' => $start_date . ' to ' . $end_date,
            'inventory_turnover' => $turnover,
            'days_sales_outstanding' => $dso,
            'gross_profit_margin' => $margin,
            'inventory_roi' => $roi,
            'summary_metrics' => [
                'turnover_ratio' => $turnover['inventory_turnover_ratio'],
                'days_in_inventory' => $turnover['days_in_inventory'],
                'gross_margin_percent' => $margin['gross_profit_margin_percent'],
                'roi_percent' => $roi['roi_percentage']
            ]
        ];
    } catch (Exception $e) {
        return [
            'error' => $e->getMessage(),
            'product_id' => $product_id,
            'period' => $start_date . ' to ' . $end_date
        ];
    }
}

/**
 * Get interpretation for DSO values
 * 
 * @param float $dso Days Sales Outstanding value
 * @return string Interpretation
 */
function getDSOInterpretation($dso): string
{
    if ($dso <= 30) {
        return 'Excellent - Very fast inventory turnover';
    } elseif ($dso <= 60) {
        return 'Good - Healthy inventory turnover';
    } elseif ($dso <= 90) {
        return 'Average - Monitor for improvement opportunities';
    } elseif ($dso <= 180) {
        return 'Slow - Consider inventory optimization';
    } else {
        return 'Very Slow - Urgent attention needed';
    }
}

/**
 * Get margin category based on percentage
 * 
 * @param float $margin_percent Gross profit margin percentage
 * @return string Margin category
 */
function getMarginCategory($margin_percent): string
{
    if ($margin_percent >= 50) {
        return 'Excellent';
    } elseif ($margin_percent >= 30) {
        return 'Good';
    } elseif ($margin_percent >= 20) {
        return 'Average';
    } elseif ($margin_percent >= 10) {
        return 'Low';
    } else {
        return 'Very Low';
    }
}

/**
 * Get ROI category based on percentage
 * 
 * @param float $roi_percent ROI percentage
 * @return string ROI category
 */
function getROICategory($roi_percent): string
{
    if ($roi_percent >= 100) {
        return 'Excellent';
    } elseif ($roi_percent >= 50) {
        return 'Good';
    } elseif ($roi_percent >= 25) {
        return 'Average';
    } elseif ($roi_percent >= 10) {
        return 'Low';
    } else {
        return 'Poor';
    }
}
