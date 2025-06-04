<?php
// filepath: e:\SGP\5th Sem\Major Project\ims-project\location-history.php
/**
 * Location History Page
 * 
 * This page displays the location history for products and stock items
 */
include 'includes/config/after-login.php';

$title = "Location History";

// Get all product categories for filter
$categories = getAllProductCategories();

// Get the selected category filter
$selectedCategory = isset($_GET['category_id']) ? $_GET['category_id'] : null;

// Get product list for the dropdown - only show products with stock
$sql = "SELECT p.product_id, p.product_name 
        FROM tbl_products p
        INNER JOIN tbl_stock s ON p.product_id = s.product_id
        WHERE s.quantity > 0 ";

// If category filter is applied
if ($selectedCategory) {
    $sql .= " AND p.category = :category_id ";
}

$sql .= "GROUP BY p.product_id, p.product_name ORDER BY p.product_name";

$stmt = $conn->prepare($sql);

// If category filter is applied, bind the parameter
if ($selectedCategory) {
    $stmt->bindParam(':category_id', $selectedCategory);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Default to showing a list of products if no specific product is selected
$selectedProduct = isset($_GET['product_id']) ? $_GET['product_id'] : null;
$selectedStock = isset($_GET['stock_id']) ? $_GET['stock_id'] : null;

// Get history data based on parameters
$history = [];
$stockInfo = null;
$productInfo = null;
$stockItems = [];

if ($selectedStock) {
    // Get stock item details
    $sql = "SELECT s.stock_id, s.product_id, s.batch_number, p.product_name
            FROM tbl_stock s
            JOIN tbl_products p ON s.product_id = p.product_id
            WHERE s.stock_id = :stock_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':stock_id', $selectedStock);
    $stmt->execute();
    $stockInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stockInfo) {
        // Get location history for this stock item
        $history = getStockLocationHistory($selectedStock);
        $selectedProduct = $stockInfo['product_id']; // Set the product ID for consistency
    }
} else if ($selectedProduct) {
    // Get product details
    $sql = "SELECT product_id, product_name FROM tbl_products WHERE product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $selectedProduct);
    $stmt->execute();
    $productInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($productInfo) {
        // Get stock items for this product
        $sql = "SELECT stock_id, batch_number, quantity FROM tbl_stock 
                WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $selectedProduct);
        $stmt->execute();
        $stockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get location history for this product
        $history = getProductLocationHistory($selectedProduct);
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include './includes/layouts/styles.php'; ?>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './includes/layouts/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './includes/layouts/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <div class="card">
                            <div class="card-header p-3 border-bottom">
                                <div class="row justify-content-between align-items-center">
                                    <div class="col-md-6">
                                        <h4 class="fw-bold mb-0">Location History</h4>
                                        <p class="mb-0">Track product movement across warehouses and locations</p>
                                    </div>
                                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                        <a href="stock-list" class="btn btn-primary">
                                            <i class="tf-icons bx bx-arrow-back"></i> Back to Stock List
                                        </a>
                                        <a href="product-list" class="btn btn-outline-secondary ms-2">
                                            <i class="tf-icons bx bx-box"></i> View Products
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                <!-- Filter Form -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">                                                <h5 class="card-title">Filter Location History</h5>
                                                <form method="get" action="" class="row g-3">
                                                    <div class="col-md-4">
                                                        <label for="category_id" class="form-label">Filter by Category</label>
                                                        <select class="form-select" name="category_id" id="category_id">
                                                            <option value="">-- All Categories --</option>
                                                            <?php foreach ($categories as $category): ?>
                                                                <option value="<?= $category['category_id'] ?>" <?= $selectedCategory == $category['category_id'] ? 'selected' : '' ?>>
                                                                    <?= special_echo($category['category_name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    
                                                    <div class="col-md-4">
                                                        <label for="product_id" class="form-label">Select Product</label>
                                                        <select class="form-select" name="product_id" id="product_id" <?= empty($products) ? 'disabled' : '' ?>>
                                                            <option value="">-- Select Product --</option>
                                                            <?php foreach ($products as $product): ?>
                                                                <option value="<?= $product['product_id'] ?>" <?= $selectedProduct == $product['product_id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($product['product_name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <?php if (empty($products)): ?>
                                                            <small class="form-text text-muted">No products available with stock in the selected category.</small>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if ($selectedProduct && !$selectedStock && !empty($stockItems)): ?>
                                                        <div class="col-md-4">
                                                            <label for="stock_id" class="form-label">Select Batch/Stock Item</label>
                                                            <select class="form-select" name="stock_id" id="stock_id">
                                                                <option value="">-- All Batches --</option>
                                                                <?php foreach ($stockItems as $item): ?>
                                                                    <option value="<?= $item['stock_id'] ?>" <?= $selectedStock == $item['stock_id'] ? 'selected' : '' ?>>
                                                                        Batch: <?= $item['batch_number'] ? htmlspecialchars($item['batch_number']) : 'N/A' ?>
                                                                        (Qty: <?= $item['quantity'] ?>)
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    <?php endif; ?>

                                                    <div class="col-12">
                                                        <button type="submit" class="btn btn-primary">Filter</button>
                                                        <a href="location-history.php" class="btn btn-outline-secondary ms-2">Reset</a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Location History Table -->
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <?php if ($selectedProduct || $selectedStock): ?>
                                                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
                                                        <div class="flex-grow-1 me-3">
                                                            <?php if ($productInfo): ?>
                                                                <div class="alert alert-info mb-0">
                                                                    <h5 class="mb-1">Product: <?= htmlspecialchars($productInfo['product_name']) ?></h5>
                                                                </div>
                                                            <?php elseif ($stockInfo): ?>
                                                                <div class="alert alert-info mb-0">
                                                                    <h5 class="mb-1">Product: <?= htmlspecialchars($stockInfo['product_name']) ?></h5>
                                                                    <p class="mb-0">Batch: <?= $stockInfo['batch_number'] ? htmlspecialchars($stockInfo['batch_number']) : 'N/A' ?></p>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if (!empty($history)): ?>                                                            <div class="mt-3 mt-md-0">
                                                                <?php
                                                                $exportUrl = 'location-history-pdf.php?' .
                                                                    ($selectedCategory ? 'category_id=' . $selectedCategory . '&' : '') .
                                                                    ($selectedProduct ? 'product_id=' . $selectedProduct : '') .
                                                                    ($selectedStock ? 'stock_id=' . $selectedStock : '');
                                                                ?>
                                                                <a href="<?= $exportUrl ?>" class="btn btn-primary" target="_blank">
                                                                    <i class="bx bxs-file-pdf me-1"></i> Export to PDF
                                                                </a>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if (empty($history)): ?>
                                                        <div class="alert alert-warning mb-0">
                                                            <p class="mb-0">No location history found for this item.</p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="table-responsive text-nowrap">
                                                            <table class="table table-hover">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Date & Time</th>
                                                                        <?php if (!$selectedStock): ?>
                                                                            <th>Batch</th>
                                                                        <?php endif; ?>
                                                                        <th>From</th>
                                                                        <th>To</th>
                                                                        <th>Moved By</th>
                                                                        <th>Notes</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php foreach ($history as $record): ?>
                                                                        <tr>
                                                                            <td><?= date('M d, Y h:i A', strtotime($record['created_at'])) ?></td>

                                                                            <?php if (!$selectedStock): ?>
                                                                                <td><?= $record['batch_number'] ? htmlspecialchars($record['batch_number']) : 'N/A' ?></td>
                                                                            <?php endif; ?>

                                                                            <td>
                                                                                <?php if ($record['old_warehouse_id']): ?>
                                                                                    <?= htmlspecialchars($record['old_warehouse_name']) ?>
                                                                                    <?php if ($record['old_location_id']): ?>
                                                                                        <br><small><?= htmlspecialchars($record['old_location_type'] . ': ' . $record['old_location_name']) ?></small>
                                                                                    <?php endif; ?>
                                                                                <?php else: ?>
                                                                                    <span class="badge bg-label-info">Initial Assignment</span>
                                                                                <?php endif; ?>
                                                                            </td>

                                                                            <td>
                                                                                <?= htmlspecialchars($record['new_warehouse_name']) ?>
                                                                                <?php if ($record['new_location_id']): ?>
                                                                                    <br><small><?= htmlspecialchars($record['new_location_type'] . ': ' . $record['new_location_name']) ?></small>
                                                                                <?php endif; ?>
                                                                            </td>

                                                                            <td><?= htmlspecialchars($record['moved_by_name']) ?></td>
                                                                            <td><?= $record['notes'] ? htmlspecialchars($record['notes']) : '<em>No notes</em>' ?></td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="alert alert-info mb-0">
                                                        <p class="mb-0">Select a product to view its location history.</p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php include './includes/layouts/footer.php'; ?>
            </div>
        </div>
    </div>
    <?php include 'includes/layouts/scripts.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-submit form when category is changed
            document.getElementById('category_id').addEventListener('change', function() {
                // Reset product and stock selection when changing category
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('product_id')) {
                    urlParams.delete('product_id');
                }
                if (urlParams.has('stock_id')) {
                    urlParams.delete('stock_id');
                }
                
                document.querySelector('form').submit();
            });
            
            // Auto-submit form when product is changed
            document.getElementById('product_id').addEventListener('change', function() {
                if (this.value) {
                    // If stock_id is in the URL but no product is selected, remove it
                    const urlParams = new URLSearchParams(window.location.search);
                    if (urlParams.has('stock_id')) {
                        urlParams.delete('stock_id');
                    }

                    document.querySelector('form').submit();
                }
            });

            // Auto-submit form when stock is changed
            const stockSelect = document.getElementById('stock_id');
            if (stockSelect) {
                stockSelect.addEventListener('change', function() {
                    document.querySelector('form').submit();
                });
            }
        });
    </script>
</body>

</html>