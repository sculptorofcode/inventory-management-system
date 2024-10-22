<?php
function addStock($productId, $quantity, $location = null, $supplierId = null, $unitCostPrice = null)
{
    global $conn;

    if (stockExists($productId)) {
        return updateStock($productId, $quantity, $location, $supplierId, $unitCostPrice);
    }

    $stmt = $conn->prepare("INSERT INTO tbl_stock (product_id, quantity, location, supplier_id, unit_cost_price) 
                            VALUES (:product_id, :quantity, :location, :supplier_id, :unit_cost_price)");
    $stmt->bindParam(':product_id', $productId);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':supplier_id', $supplierId);
    $stmt->bindParam(':unit_cost_price', $unitCostPrice);

    if ($stmt->execute()) {
        logStockTransaction($productId, $quantity, 0, 'in', 'Stock added', $location);
        return true;
    } else {
        return "Error: Could not add stock";
    }
}

function updateStock($productId, $quantity, $location = null, $supplierId = null, $unitCostPrice = null)
{
    global $conn;

    $previousStock = getStockByProductId($productId);

    $sql = "UPDATE tbl_stock SET quantity = :quantity";
    if (!empty($location)) {
        $sql .= ", location = :location";
    }
    if (!empty($supplierId)) {
        $sql .= ", supplier_id = :supplier_id";
    }
    if (!empty($unitCostPrice)) {
        $sql .= ", unit_cost_price = :unit_cost_price";
    }
    $sql .= " WHERE product_id = :product_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':product_id', $productId);

    if (!empty($location)) {
        $stmt->bindParam(':location', $location);
    }
    if (!empty($supplierId)) {
        $stmt->bindParam(':supplier_id', $supplierId);
    }
    if (!empty($unitCostPrice)) {
        $stmt->bindParam(':unit_cost_price', $unitCostPrice);
    }

    if ($stmt->execute()) {
        logStockTransaction($productId, $quantity - $previousStock['quantity'], $previousStock['quantity'], 'in', 'Stock updated', $location);
        return true;
    } else {
        return "Error: Could not update stock";
    }
}

function adjustStock($productId, $quantityChange, $transactionType, $notes = '', $location = null)
{
    global $conn;

    $currentStock = getStockByProductId($productId);
    $newQuantity = $currentStock['quantity'] + ($transactionType === 'in' ? $quantityChange : -$quantityChange);

    $stmt = $conn->prepare("UPDATE tbl_stock SET quantity = :quantity WHERE product_id = :product_id");
    $stmt->bindParam(':quantity', $newQuantity);
    $stmt->bindParam(':product_id', $productId);

    if ($stmt->execute()) {
        logStockTransaction($productId, $quantityChange, $currentStock['quantity'], $transactionType, $notes, $location);
        return true;
    } else {
        return "Error: Could not adjust stock";
    }
}

function getStockByProductId($productId)
{
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM tbl_stock WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $productId);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllStock()
{
    global $conn;

    $stmt = $conn->query("SELECT * FROM tbl_stock");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteStockByProductId($productId)
{
    global $conn;

    $stmt = $conn->prepare("DELETE FROM tbl_stock WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function stockExists($productId)
{
    global $conn;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_stock WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $productId);
    $stmt->execute();

    return $stmt->fetchColumn() > 0;
}

function logStockTransaction($productId, $quantityChange, $previousQuantity, $transactionType, $notes = '', $location = null, $userId = null, $orderReference = null)
{
    global $conn;

    $stmt = $conn->prepare("INSERT INTO tbl_stock_transactions (product_id, quantity_change, previous_quantity, transaction_type, notes, transaction_location, user_id, order_reference) 
                            VALUES (:product_id, :quantity_change, :previous_quantity, :transaction_type, :notes, :transaction_location, :user_id, :order_reference)");
    $stmt->bindParam(':product_id', $productId);
    $stmt->bindParam(':quantity_change', $quantityChange);
    $stmt->bindParam(':previous_quantity', $previousQuantity);
    $stmt->bindParam(':transaction_type', $transactionType);
    $stmt->bindParam(':notes', $notes);
    $stmt->bindParam(':transaction_location', $location);
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':order_reference', $orderReference);

    return $stmt->execute();
}
?>