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


$mailer = new SMTPMailer();
$sms = new SMSAPI();
$session = new Session();