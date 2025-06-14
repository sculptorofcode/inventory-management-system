<?php
function addPayment($orderId, $customerId, $paymentMethod, $amountPaid): bool
{
    global $conn, $table_payments;

    $stmt = $conn->prepare("INSERT INTO $table_payments (order_id, customer_id, payment_method, amount_paid) 
                            VALUES (:order_id, :customer_id, :payment_method, :amount_paid)");
    $stmt->bindParam(':order_id', $orderId);
    $stmt->bindParam(':customer_id', $customerId);
    $stmt->bindParam(':payment_method', $paymentMethod);
    $stmt->bindParam(':amount_paid', $amountPaid);

    return $stmt->execute();
}

function getPaymentsByCustomer($customerId): array
{
    global $conn, $table_payments;

    $stmt = $conn->prepare("SELECT * FROM $table_payments WHERE customer_id = :customer_id");
    $stmt->bindParam(':customer_id', $customerId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPaymentByOrder($orderId) {
    global $conn, $table_payments;

    $stmt = $conn->prepare("SELECT * FROM $table_payments WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $orderId);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updatePaymentStatus($paymentId, $status): bool
{
    global $conn, $table_payments;

    $stmt = $conn->prepare("UPDATE $table_payments SET transaction_status = :status WHERE payment_id = :payment_id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':payment_id', $paymentId);

    return $stmt->execute();
}

function getAllPaymentsWithOrders(): array
{
    global $conn, $table_payments;

    $stmt = $conn->prepare("
        SELECT p.payment_id, p.payment_method, p.amount_paid, p.transaction_status, p.payment_date, 
               o.order_id, o.total_amount, c.first_name, c.last_name, c.email 
        FROM $table_payments p
        JOIN `tbl_purchase_order` o ON p.order_id = o.order_id
        JOIN `tbl_customers` c ON p.customer_id = c.customer_id
    ");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deletePayment($paymentId): bool
{
    global $conn, $table_payments;

    $stmt = $conn->prepare("DELETE FROM $table_payments WHERE payment_id = :payment_id");
    $stmt->bindParam(':payment_id', $paymentId);

    return $stmt->execute();
}
?>