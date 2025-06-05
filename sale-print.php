<?php
require_once 'includes/config/after-login.php';
$title = 'Sale Print';

if(isset($_REQUEST['order_id'])){
    $order_id = filtervar($_REQUEST['order_id']);
    $order = getSaleOrderById($order_id);
    $order_details = getSaleOrderDetails($order_id);
    
    // Get warehouse and location info for order details with batch numbers
    foreach ($order_details as &$detail) {
        if (!empty($detail['batch_number'])) {
            // Get warehouse and location information for this batch
            $stmt = $conn->prepare("SELECT s.*, 
                                   w.warehouse_name, 
                                   wl.name as location_name, 
                                   wl.type as location_type 
                                   FROM tbl_stock s
                                   LEFT JOIN tbl_warehouse w ON s.warehouse_id = w.warehouse_id
                                   LEFT JOIN tbl_warehouse_location wl ON s.location_id = wl.location_id
                                   WHERE s.product_id = :product_id AND s.batch_number = :batch_number
                                   LIMIT 1");
            $stmt->bindParam(':product_id', $detail['product_id']);
            $stmt->bindParam(':batch_number', $detail['batch_number']);
            $stmt->execute();
            $stock_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stock_info) {
                $detail['warehouse_name'] = $stock_info['warehouse_name'] ?? 'N/A';
                $detail['location_info'] = '';
                if (!empty($stock_info['location_name'])) {
                    $detail['location_info'] = $stock_info['location_name'] . ' (' . $stock_info['location_type'] . ')';
                }
            }
        }
    }
    unset($detail); // Break the reference
    
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
