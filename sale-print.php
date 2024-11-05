<?php
require_once 'includes/config/after-login.php';
$title = 'Purchase Print';

if(isset($_REQUEST['order_id'])){
    $order_id = filtervar($_REQUEST['order_id']);
    $order = getPurchaseOrderById($order_id);
    $order_details = getPurchaseOrderDetails($order_id);
    $supplier = getSupplierById($order['supplier_id']);
    $supplier_payments = getSupplierPayments($order_id);
    $total_paid = 0;
    foreach ($supplier_payments as $payment) {
        $total_paid += $payment['amount'];
    }
    $total_due = $order['total_amount'] - $total_paid;
}

include './includes/layouts/ui/purchase-order.php';


?>
