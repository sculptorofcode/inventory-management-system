<?php

function addOrderItem($orderId, $productId, $quantity, $price) {
    global $conn, $table_order_items;

    $subtotal = $quantity * $price;

    $stmt = $conn->prepare("INSERT INTO $table_order_items (order_id, product_id, quantity, price, subtotal) VALUES (:order_id, :product_id, :quantity, :price, :subtotal)");
    $stmt->bindParam(':order_id', $orderId);
    $stmt->bindParam(':product_id', $productId);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':subtotal', $subtotal);
    
    return $stmt->execute();
}

function getPurchaseOrdersByCustomer($customerId): array
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM `tbl_purchase_order` WHERE customer_id = :customer_id");
    $stmt->bindParam(':customer_id', $customerId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updatePurchaseOrderStatus($orderId, $status): bool
{
    global $conn;

    $stmt = $conn->prepare("UPDATE `tbl_purchase_order` SET status = :status WHERE order_id = :order_id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $orderId);
    
    return $stmt->execute();
}

function getAllSalesOrders() {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM `tbl_sale_order`");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails($orderId) {
    global $conn, $table_order_items;

    $stmt = $conn->prepare("SELECT * FROM $table_order_items WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $orderId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>