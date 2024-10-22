<?php
include 'config.php';
include 'includes/classes/All.php';
include 'includes/functions/functions.php';

/**
 * Create a database connection using PDO
 *
 * @return PDO
 */
function db_connect()
{
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

$conn = db_connect();


// Table names
$table_customers = 'tbl_customers';
$table_purchase_orders = 'tbl_purchase_orders';
$table_order_items = 'tbl_order_items';
$table_sales_orders = 'tbl_sales_orders';
$table_payments = 'tbl_payments';
$table_products = 'tbl_products';
$table_suppliers = 'tbl_suppliers';
$table_countries = 'tbl_countries';
$table_product_categories = 'tbl_product_categories';
$table_stock = 'tbl_stock';
$table_stock_transactions = 'tbl_stock_transactions';

$mailer = new SMTPMailer();
$sms = new SMSAPI();
$session = new Session();