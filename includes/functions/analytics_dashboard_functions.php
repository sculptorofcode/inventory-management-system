<?php
/**
 * Inventory Analytics Dashboard Helper Functions
 * 
 * These functions provide data for the inventory analytics dashboard
 * and work with the actual database structure. They serve as lightweight
 * wrappers around existing functions to avoid conflicts.
 */

/**
 * Get valuation methods summary for dashboard display
 */
function getDashboardValuationSummary($product_id, $quantity = null): array
{
    try {
        // Use the existing compareValuationMethods function from inventory_valuation_functions.php
        $comparison = compareValuationMethods($product_id, $quantity);
        
        // Format for dashboard display
        return [
            'methods' => [
                'fifo' => [
                    'method' => 'FIFO',
                    'total_value' => $comparison['fifo']['total_value'],
                    'cost_per_unit' => $comparison['fifo']['cost_per_unit']
                ],
                'lifo' => [
                    'method' => 'LIFO', 
                    'total_value' => $comparison['lifo']['total_value'],
                    'cost_per_unit' => $comparison['lifo']['cost_per_unit']
                ],
                'wac' => [
                    'method' => 'Weighted Average',
                    'total_value' => $comparison['wac']['total_value'],
                    'cost_per_unit' => $comparison['wac']['cost_per_unit']
                ]
            ],
            'summary' => $comparison['comparison']
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Get inventory control analysis for dashboard display
 */
function getDashboardInventoryControlAnalysis($product_id, $params): array
{
    try {
        // Use the existing getInventoryControlAnalysis function
        return getInventoryControlAnalysis($product_id, $params);
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Get financial dashboard data for analytics display
 */
function getDashboardFinancialData($start_date, $end_date, $product_id = null): array
{
    try {
        // Use the existing getFinancialDashboard function
        return getFinancialDashboard($product_id, $start_date, $end_date);
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Get demand forecast data for dashboard display
 */
function getDashboardDemandForecast($product_id): array
{
    try {
        // Use the existing getDemandForecastDashboard function
        return getDemandForecastDashboard($product_id);
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

/**
 * Get quick dashboard statistics
 */
function getQuickDashboardStats(): array
{
    global $conn;
    
    try {
        // Total products
        $sql = "SELECT COUNT(*) as total_products FROM tbl_products";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];
        
        // Total inventory value
        $sql = "SELECT SUM(stock * purchase_price) as total_value FROM tbl_products WHERE stock > 0";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $total_value = $stmt->fetch(PDO::FETCH_ASSOC)['total_value'] ?: 0;
          // Average turnover ratio (last 12 months)
        $avg_turnover_data = calculateInventoryTurnover(
            null,
            date('Y-m-d', strtotime('-12 months')), 
            date('Y-m-d')
        );
        $avg_turnover = $avg_turnover_data['inventory_turnover_ratio'] ?? 0;
        
        // Average days in inventory
        $avg_days = $avg_turnover > 0 ? round(365 / $avg_turnover) : 0;
          return [
            'total_products' => $total_products,
            'total_value' => number_format($total_value, 2),
            'turnover_ratio' => round(floatval($avg_turnover), 2),
            'days_inventory' => $avg_days
        ];    } catch (Exception $e) {
        return [
            'total_products' => 0,
            'total_value' => '0.00',
            'turnover_ratio' => 0.00,
            'days_inventory' => 0
        ];
    }
}
?>
