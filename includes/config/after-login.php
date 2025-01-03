<?php
include 'database.php';

if (!$session->has('logged_in')) {
    header('Location: login.php');
    exit;
}

if (isset($_POST['logout'])) {
    $session->destroy();
    header('Location: login.php');
    exit;
}

if ($session->has('userdata') && $session->has('customer_id')) {
    $userdata = $session->get('userdata');
} else {
    if($session->has('customer_id')){
        $customer_id = $session->get('customer_id');
        $userdata = getCustomerById($customer_id);
        if ($userdata) {
            $session->set('userdata', $userdata);
        }else{
            $session->destroy();
            header('Location: login.php');
            exit;
        }
    }else{
        $session->destroy();
        header('Location: login.php');
        exit;
    }
}

$filename = basename($_SERVER['PHP_SELF']);
$filename = str_split($filename, strrpos($filename, '.'))[0];

$today = date('Y-m-d');
$time = date('H:i:s');
$now = date('Y-m-d H:i:s');
$order_status = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
$order_status_bg = ['pending' => 'warning', 'confirmed' => 'info', 'shipped' => 'primary', 'delivered' => 'success', 'cancelled' => 'danger'];
$payment_mode = [
    'cod' => 'Cash on Delivery',
    'bank' => 'Bank Transfer',
    'cheque' => 'Cheque',
    'dd' => 'Demand Draft',
    'cash' => 'Cash',
    'card' => 'Credit/Debit Card',
    'netbanking' => 'Net Banking',
    'upi' => 'UPI',
    'wallet' => 'Wallet',
    'emi' => 'EMI',
    'others' => 'Others'
];

$user_id = $userdata['customer_id'];