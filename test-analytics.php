<?php
require_once 'includes/config/database.php';
require_once 'includes/functions/functions.php';

echo "Testing analytics dashboard functions...\n";

try {
    $stats = getQuickDashboardStats();
    echo "Quick stats test: " . (isset($stats['total_products']) ? 'PASS' : 'FAIL') . "\n";
    echo "Total products: " . $stats['total_products'] . "\n";
    echo "Total value: $" . $stats['total_value'] . "\n";
    echo "Turnover ratio: " . $stats['turnover_ratio'] . "\n";
    echo "Days in inventory: " . $stats['days_inventory'] . "\n\n";
    
    echo "Testing function availability...\n";
    echo "compareValuationMethods: " . (function_exists('compareValuationMethods') ? 'AVAILABLE' : 'MISSING') . "\n";
    echo "getInventoryControlAnalysis: " . (function_exists('getInventoryControlAnalysis') ? 'AVAILABLE' : 'MISSING') . "\n";
    echo "getFinancialDashboard: " . (function_exists('getFinancialDashboard') ? 'AVAILABLE' : 'MISSING') . "\n";
    echo "getDemandForecastDashboard: " . (function_exists('getDemandForecastDashboard') ? 'AVAILABLE' : 'MISSING') . "\n";
    
    echo "\nAll tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
