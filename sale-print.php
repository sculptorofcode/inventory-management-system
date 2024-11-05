<?php
require_once 'includes/config/after-login.php';
$title = 'Sale Print';

if(isset($_REQUEST['order_id'])){
    $order_id = filtervar($_REQUEST['order_id']);
    $order = getSaleOrderById($order_id);
    $order_details = getSaleOrderDetails($order_id);
    $customer = getCustomerById($order['customer_id']);
    $customer_payments = getCustomerPayments($order_id);
    $total_paid = 0;
    foreach ($customer_payments as $payment) {
        $total_paid += $payment['amount'];
    }
    $total_due = $order['total_amount'] - $total_paid;

    $print_type = $order['status'] == 'pending' ? 'Quotation' : 'Invoice';
}

include './includes/layouts/ui/sale-order.php';


?>
