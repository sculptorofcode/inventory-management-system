<?php

function addProduct($supplierId, $productName, $category, $purchasePrice, $sellingPrice, $quantity, $description, $gst_type, $gst_rate, $hsn_code)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("INSERT INTO $table_products SET supplier_id = :supplier_id, product_name = :product_name, category = :category, purchase_price = :purchase_price, selling_price = :selling_price, quantity = :quantity, description = :description, gst_type = :gst_type, gst_rate = :gst_rate, hsn_code = :hsn_code");
    $stmt->bindParam(':supplier_id', $supplierId);
    $stmt->bindParam(':product_name', $productName);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':purchase_price', $purchasePrice);
    $stmt->bindParam(':selling_price', $sellingPrice);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':gst_type', $gst_type);
    $stmt->bindParam(':gst_rate', $gst_rate);
    $stmt->bindParam(':hsn_code', $hsn_code);

    return $stmt->execute();
}

function updateProduct($productId, $supplierId, $productName, $category, $purchasePrice, $sellingPrice, $quantity, $description, $gst_type, $gst_rate, $hsn_code)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("UPDATE $table_products 
                            SET supplier_id = :supplier_id, product_name = :product_name, category = :category, purchase_price = :purchase_price, 
                                selling_price = :selling_price, quantity = :quantity, description = :description, gst_type = :gst_type, gst_rate = :gst_rate, hsn_code = :hsn_code
                            WHERE product_id = :product_id");
    $stmt->bindParam(':supplier_id', $supplierId);
    $stmt->bindParam(':product_name', $productName);
    $stmt->bindParam(':category', $category);
    $stmt->bindParam(':purchase_price', $purchasePrice);
    $stmt->bindParam(':selling_price', $sellingPrice);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':product_id', $productId);
    $stmt->bindParam(':gst_type', $gst_type);
    $stmt->bindParam(':gst_rate', $gst_rate);
    $stmt->bindParam(':hsn_code', $hsn_code);

    return $stmt->execute();
}

function deleteProduct($productId)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("DELETE FROM $table_products WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function getProductById($productId)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("SELECT * FROM $table_products WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $productId);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllProducts($category = null, $min_stock = null)
{
    global $table_products, $conn;

    $sql = "SELECT * FROM $table_products WHERE 1=1";
    if ($category) {
        $sql .= " AND category = '$category'";
    }
    if ($min_stock) {
        $sql .= " AND stock >= $min_stock";
    }
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsByCategory($category)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("SELECT * FROM $table_products WHERE category = :category");
    $stmt->bindParam(':category', $category);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsBySupplier($supplierId)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("SELECT * FROM $table_products WHERE supplier_id = :supplier_id");
    $stmt->bindParam(':supplier_id', $supplierId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateProductQuantity($productId, $newQuantity)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("UPDATE $table_products SET quantity = :quantity WHERE product_id = :product_id");
    $stmt->bindParam(':quantity', $newQuantity);
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function updateProductSellingPrice($productId, $newSellingPrice)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("UPDATE $table_products SET selling_price = :selling_price WHERE product_id = :product_id");
    $stmt->bindParam(':selling_price', $newSellingPrice);
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function updateProductPurchasePrice($productId, $newPurchasePrice)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("UPDATE $table_products SET purchase_price = :purchase_price WHERE product_id = :product_id");
    $stmt->bindParam(':purchase_price', $newPurchasePrice);
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function updateProductsCategory($productId, $newCategory)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("UPDATE $table_products SET category = :category WHERE product_id = :product_id");
    $stmt->bindParam(':category', $newCategory);
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function updateProductSupplier($productId, $newSupplierId)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("UPDATE $table_products SET supplier_id = :supplier_id WHERE product_id = :product_id");
    $stmt->bindParam(':supplier_id', $newSupplierId);
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function updateProductDescription($productId, $newDescription)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("UPDATE $table_products SET description = :description WHERE product_id = :product_id");
    $stmt->bindParam(':description', $newDescription);
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function updateProductStatus($productId, $status)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("UPDATE $table_products SET status = :status WHERE product_id = :product_id");
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':product_id', $productId);

    return $stmt->execute();
}

function searchProducts($search)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("SELECT * FROM $table_products WHERE product_name LIKE :search");
    $stmt->bindValue(':search', '%' . $search . '%');
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsCount($filters = []) {
    global $table_products, $conn;

    $sql = "SELECT COUNT(*) FROM $table_products WHERE 1=1 ";
    $params = [];
    if (!empty($filters['category'])) {
        $sql .= " AND category = :category";
        $params[':category'] = $filters['category'];
    }
    if (!empty($filters['supplier_id'])) {
        $sql .= " AND supplier_id = :supplier_id";
        $params[':supplier_id'] = $filters['supplier_id'];
    }
    if (!empty($filters['status'])) {
        $sql .= " AND status = :status";
        $params[':status'] = $filters['status'];
    }
    if (!empty($filters['search'])) {
        $sql .= " AND product_name LIKE :search";
        $params[':search'] = '%' . $filters['search'] . '%';
    }
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();

    return $stmt->fetchColumn();
}

// Product Category Functions

function addProductCategory($categoryName, $description = '')
{
    global $table_product_categories, $conn;

    $stmt = $conn->prepare("INSERT INTO $table_product_categories SET `category_name` = :category_name, `description` = :description");
    $stmt->bindParam(':category_name', $categoryName);
    $stmt->bindParam(':description', $description);

    return $stmt->execute();
}

function updateProductCategory($categoryId, $categoryName, $description = '')
{
    global $table_product_categories, $conn;

    $stmt = $conn->prepare("UPDATE $table_product_categories SET `category_name` = :category_name, `description` = :description WHERE `category_id` = :category_id");
    $stmt->bindParam(':category_name', $categoryName);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category_id', $categoryId);

    return $stmt->execute();
}

function deleteProductCategory($categoryId)
{
    global $table_product_categories, $conn;

    $stmt = $conn->prepare("DELETE FROM $table_product_categories WHERE `category_id` = :category_id");
    $stmt->bindParam(':category_id', $categoryId);

    return $stmt->execute();
}

function getProductCategoryById($categoryId)
{
    global $table_product_categories, $conn;

    $stmt = $conn->prepare("SELECT * FROM $table_product_categories WHERE `category_id` = :category_id");
    $stmt->bindParam(':category_id', $categoryId);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAllProductCategories()
{
    global $table_product_categories, $conn;

    $stmt = $conn->prepare("SELECT * FROM $table_product_categories");
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductsBySupplierId($supplierId)
{
    global $table_products, $conn;

    $stmt = $conn->prepare("SELECT * FROM $table_products WHERE supplier_id = :supplier_id");
    $stmt->bindParam(':supplier_id', $supplierId);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}