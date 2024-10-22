<?php
include 'auth_functions.php';
include 'order_functions.php';
include 'payment_functions.php';
include 'product_functions.php';
include 'supplier_functions.php';

function filtervar($var)
{
    $var = str_replace('&', 'and', $var);
    $var = trim($var);
    $var = stripslashes($var);
    $var = htmlspecialchars($var);
    $var = strip_tags($var);
    $var = htmlentities($var);
    $var = filter_var($var);
    return $var;
}

function generateOTP($length = 6){
    $numbers = '0123456789';
    $otp = '';
    for($i = 0; $i < $length; $i++){
        $otp .= $numbers[rand(0, strlen($numbers) - 1)];
    }
    return $otp;
}

function password_changed_email_template()
{
    $body = file_get_contents(SITE_URL . '/includes/layouts/password-change-email.php');
    return $body;
}

function email_otp_template($otp)
{
    ob_start();
    include 'includes/layouts/email-otp.php';
    $body = ob_get_contents();
    ob_end_clean();
    return $body;
}

function welcome_email_template($full_name, $email, $creation_date)
{
    ob_start();
    include 'includes/layouts/welcome-email.php';
    $body = ob_get_contents();
    ob_end_clean();
    return $body;
}

function get_countries() {
    global $conn, $table_countries;
    $sql = "SELECT * FROM $table_countries";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCount($table_name, $filters = []) {
    global $conn;
    $sql = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
    $params = [];
    foreach ($filters as $key => $value) {
        if (!empty($value)) {
            if ($key === 'search') {
                $sql .= " AND product_name LIKE :$key";
                $params[":$key"] = '%' . $value . '%';
            } else {
                $sql .= " AND $key = :$key";
                $params[":$key"] = $value;
            }
        }
    }
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return $stmt->fetchColumn();
}

function special_echo($data)
{
    echo html_entity_decode($data);
}