<?php
/**
 * Database Optimization Validation Script
 * 
 * This script validates that the database optimization SQL file
 * is compatible with the actual database structure.
 */

require_once 'includes/config/database.php';

header('Content-Type: application/json');

try {
    // Connect to database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $validationResults = [];
    
    // Check if all required tables exist with correct column names
    $tables = [
        'tbl_products' => ['product_id', 'product_name', 'stock', 'selling_price', 'purchase_price', 'category'],
        'tbl_purchase_order' => ['order_id', 'supplier_id', 'order_date', 'status'],
        'tbl_purchase_order_details' => ['order_detail_id', 'purchase_order_id', 'product_id', 'quantity', 'unit_cost_price'],
        'tbl_sale_order' => ['order_id', 'customer_id', 'order_date', 'status'],
        'tbl_sale_order_details' => ['order_detail_id', 'sale_order_id', 'product_id', 'quantity', 'sale_price'],
        'tbl_stock' => ['stock_id', 'product_id', 'quantity', 'warehouse_id'],
        'tbl_stock_transactions' => ['transaction_id', 'product_id', 'transaction_type', 'quantity_change', 'transaction_date'],
        'tbl_product_categories' => ['category_id', 'category_name']
    ];
    
    foreach ($tables as $tableName => $expectedColumns) {
        // Check if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        $tableExists = $stmt->rowCount() > 0;
        
        $validationResults[$tableName] = [
            'exists' => $tableExists,
            'columns' => []
        ];
        
        if ($tableExists) {
            // Check columns
            $stmt = $pdo->prepare("DESCRIBE $tableName");
            $stmt->execute();
            $actualColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($expectedColumns as $column) {
                $validationResults[$tableName]['columns'][$column] = in_array($column, $actualColumns);
            }
        }
    }
    
    // Test a sample query that the optimization views would use
    $testQueries = [
        'products_basic' => "SELECT product_id, product_name, stock FROM tbl_products LIMIT 1",
        'purchase_details_basic' => "SELECT product_id, quantity, unit_cost_price FROM tbl_purchase_order_details LIMIT 1",
        'sale_details_basic' => "SELECT product_id, quantity, sale_price FROM tbl_sale_order_details LIMIT 1"
    ];
    
    $queryResults = [];
    foreach ($testQueries as $queryName => $query) {
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $queryResults[$queryName] = ['success' => true, 'error' => null];
        } catch (Exception $e) {
            $queryResults[$queryName] = ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    // Check for existing indexes (basic check)
    $indexCheck = [];
    try {
        $stmt = $pdo->prepare("SHOW INDEX FROM tbl_products");
        $stmt->execute();
        $indexCheck['tbl_products'] = $stmt->rowCount();
        
        $stmt = $pdo->prepare("SHOW INDEX FROM tbl_purchase_order_details");
        $stmt->execute();
        $indexCheck['tbl_purchase_order_details'] = $stmt->rowCount();
    } catch (Exception $e) {
        $indexCheck['error'] = $e->getMessage();
    }
    
    // Overall compatibility score
    $totalTables = count($tables);
    $validTables = 0;
    $totalColumns = 0;
    $validColumns = 0;
    
    foreach ($validationResults as $tableResult) {
        if ($tableResult['exists']) {
            $validTables++;
        }
        
        foreach ($tableResult['columns'] as $columnValid) {
            $totalColumns++;
            if ($columnValid) {
                $validColumns++;
            }
        }
    }
    
    $compatibilityScore = $totalColumns > 0 ? round(($validColumns / $totalColumns) * 100, 2) : 0;
    
    echo json_encode([
        'success' => true,
        'validation_date' => date('Y-m-d H:i:s'),
        'compatibility_score' => $compatibilityScore,
        'summary' => [
            'total_tables' => $totalTables,
            'valid_tables' => $validTables,
            'total_columns' => $totalColumns,
            'valid_columns' => $validColumns
        ],
        'tables' => $validationResults,
        'test_queries' => $queryResults,
        'index_info' => $indexCheck,
        'recommendations' => [
            'run_optimizations' => $compatibilityScore >= 80,
            'notes' => $compatibilityScore >= 80 
                ? 'Database structure is compatible with optimization script.'
                : 'Some columns may be missing. Review the optimization script before running.'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'validation_date' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
}
?>
