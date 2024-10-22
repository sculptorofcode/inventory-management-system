<?php
function createPurchaseOrder($customerId, $totalAmount) {
    global $conn, $table_purchase_orders;

    $stmt = $conn->prepare("INSERT INTO $table_purchase_orders (customer_id, total_amount) VALUES (:customer_id, :total_amount)");
    $stmt->bindParam(':customer_id', $customerId);
    $stmt->bindParam(':total_amount', $totalAmount);
    
    if ($stmt->execute()) {
        return $conn->lastInsertId();
    } else {
        return false;
    }
}

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

function getPurchaseOrdersByCustomer($customerId) {
    global $conn, $table_purchase_orders;

    $stmt = $conn->prepare("SELECT * FROM $table_purchase_orders WHERE customer_id = :customer_id");
    $stmt->bindParam(':customer_id', $customerId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updatePurchaseOrderStatus($orderId, $status) {
    global $conn, $table_purchase_orders;

    $stmt = $conn->prepare("UPDATE $table_purchase_orders SET status = :status WHERE order_id = :order_id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':order_id', $orderId);
    
    return $stmt->execute();
}

function createSalesOrder($orderId, $supplierId, $totalAmount) {
    global $conn, $table_sales_orders;

    $stmt = $conn->prepare("INSERT INTO $table_sales_orders (order_id, supplier_id, total_amount) VALUES (:order_id, :supplier_id, :total_amount)");
    $stmt->bindParam(':order_id', $orderId);
    $stmt->bindParam(':supplier_id', $supplierId);
    $stmt->bindParam(':total_amount', $totalAmount);
    
    return $stmt->execute();
}

function getAllSalesOrders() {
    global $conn, $table_sales_orders;

    $stmt = $conn->prepare("SELECT * FROM $table_sales_orders");
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

function deleteOrder($orderId) {
    global $conn, $table_order_items, $table_purchase_orders;

    $stmt = $conn->prepare("DELETE FROM $table_order_items WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $orderId);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM $table_purchase_orders WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $orderId);
    
    return $stmt->execute();
}
?>