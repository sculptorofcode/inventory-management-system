<?php
include 'config.php';
include 'includes/classes/All.php';
include 'includes/functions/functions.php';

function db_connect()
{
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (Exception $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}

$conn = db_connect();


// Table names
$table_customers = 'tbl_customers';
$table_purchase_orders = 'tbl_purchase_order';
$table_purchase_orders_details = 'tbl_purchase_order_details';
$table_purchase_order_status_log = 'tbl_purchase_order_status_log';
$table_sales_orders = 'tbl_sale_order';
$table_sales_orders_details = 'tbl_sale_order_details';
$table_sales_orders_status_log = 'tbl_sale_order_status_log';
//$table_payments = 'tbl_payments';
$table_products = 'tbl_products';
$table_suppliers = 'tbl_suppliers';
$table_countries = 'tbl_countries';
$table_product_categories = 'tbl_product_categories';
$table_stock = 'tbl_stock';
$table_stock_transactions = 'tbl_stock_transactions';
$table_supplier_payments = 'tbl_supplier_payments';
$table_customer_payments = 'tbl_customer_payments';


$mailer = new SMTPMailer();
$sms = new SMSAPI();
$session = new Session();