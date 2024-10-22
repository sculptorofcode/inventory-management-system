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

if ($session->has('userdata')) {
    $userdata = $session->get('userdata');
} else {
    $customer_id = $session->get('customer_id');
    $userdata = getCustomerById($customer_id);
    if ($userdata) {
        $session->set('userdata', $userdata);
    }
}

$filename = basename($_SERVER['PHP_SELF']);
$filename = str_split($filename, strrpos($filename, '.'))[0];