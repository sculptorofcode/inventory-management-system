<?php
// filepath: e:\SGP\5th Sem\Major Project\ims-project\location-history
/**
 * Location History Page
 * 
 * This page displays the location history for products and stock items
 */
include 'includes/config/after-login.php';

$title = "Location History";

// Set up initial data for page load
$selectedCategory = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$selectedProduct = isset($_GET['product_id']) ? $_GET['product_id'] : null;
$selectedStock = isset($_GET['stock_id']) ? $_GET['stock_id'] : null;

// Get all active categories
$sqlCategories = "SELECT category_id, category_name FROM tbl_product_categories ORDER BY category_name";
$stmtCategories = $conn->prepare($sqlCategories);
$stmtCategories->execute();
$categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

// If stock item is selected, get its details
if ($selectedStock) {
    $sqlStockInfo = "SELECT s.stock_id, s.product_id, s.batch_number, p.product_name
                    FROM tbl_stock s
                    JOIN tbl_products p ON s.product_id = p.product_id
                    WHERE s.stock_id = :stock_id";
    $stmtStockInfo = $conn->prepare($sqlStockInfo);
    $stmtStockInfo->bindParam(':stock_id', $selectedStock);
    $stmtStockInfo->execute();
    $stockInfo = $stmtStockInfo->fetch(PDO::FETCH_ASSOC);
    
    if ($stockInfo && !$selectedProduct) {
        $selectedProduct = $stockInfo['product_id'];
    }
}


// Get stock items if product is selected
$stockItems = [];
$productInfo = null;
$stockInfo = null;

if ($selectedProduct) {
    // Get product details
    $sqlProduct = "SELECT product_id, product_name, category FROM tbl_products WHERE product_id = :product_id ";
    $stmtProduct = $conn->prepare($sqlProduct);
    $stmtProduct->bindParam(':product_id', $selectedProduct);
    $stmtProduct->execute();
    $productInfo = $stmtProduct->fetch(PDO::FETCH_ASSOC);

    if ($productInfo && !$selectedCategory) {
        $selectedCategory = $productInfo['category'];
    }
    
    // Get stock items for this product
    $sqlStock = "SELECT stock_id, batch_number, quantity 
                FROM tbl_stock 
                WHERE product_id = :product_id AND quantity > 0
                ORDER BY added_on DESC";
    $stmtStock = $conn->prepare($sqlStock);
    $stmtStock->bindParam(':product_id', $selectedProduct);
    $stmtStock->execute();
    $stockItems = $stmtStock->fetchAll(PDO::FETCH_ASSOC);
}

// Get products if category is selected
$products = [];
if ($selectedCategory) {
    $sqlProducts = "SELECT DISTINCT p.product_id, p.product_name 
                   FROM tbl_products p
                   JOIN tbl_stock s ON p.product_id = s.product_id
                   WHERE p.category = :category_id
                   ORDER BY p.product_name";
    $stmtProducts = $conn->prepare($sqlProducts);
    $stmtProducts->bindParam(':category_id', $selectedCategory);
    $stmtProducts->execute();
    $products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
}

// Process DataTables AJAX request
// Handle AJAX request for products by category
if (isset($_GET['getProducts']) && isset($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];
    $products = [];
    
    if ($categoryId) {
        // Get products with stock in this category
        $sql = "SELECT DISTINCT p.product_id, p.product_name 
                FROM tbl_products p
                JOIN tbl_stock s ON p.product_id = s.product_id
                WHERE p.category = :category_id
                ORDER BY p.product_name";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['products' => $products]);
    exit;
}

// Handle AJAX request for stock items by product
if (isset($_GET['getStockItems']) && isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];
    $stockItems = [];
    
    if ($productId) {
        // Get stock items for this product
        $sql = "SELECT stock_id, batch_number, quantity 
                FROM tbl_stock 
                WHERE product_id = :product_id AND quantity > 0
                ORDER BY added_on DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $stockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['stockItems' => $stockItems]);
    exit;
}

// Handle AJAX request for product info
if (isset($_GET['getProductInfo']) && isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];
    $productInfo = null;
    
    if ($productId) {
        // Get product details
        $sql = "SELECT product_id, product_name 
                FROM tbl_products 
                WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId);
        $stmt->execute();
        $productInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['productInfo' => $productInfo]);
    exit;
}

// Handle AJAX request for stock info
if (isset($_GET['getStockInfo']) && isset($_GET['stock_id'])) {
    $stockId = $_GET['stock_id'];
    $stockInfo = null;
    
    if ($stockId) {
        // Get stock details with product info
        $sql = "SELECT s.stock_id, s.product_id, s.batch_number, p.product_name
                FROM tbl_stock s
                JOIN tbl_products p ON s.product_id = p.product_id
                WHERE s.stock_id = :stock_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':stock_id', $stockId);
        $stmt->execute();
        $stockInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['stockInfo' => $stockInfo]);
    exit;
}

// Process DataTables AJAX request
if (isset($_REQUEST['draw']) && isset($_REQUEST['location_history'])) {
    $draw = intval($_REQUEST['draw']);
    $start = intval($_REQUEST['start']);
    $length = intval($_REQUEST['length']);
    $search = $_REQUEST['search']['value'];

    $selectedCategory = isset($_REQUEST['category_id']) ? $_REQUEST['category_id'] : null;
    $selectedProduct = isset($_REQUEST['product_id']) ? $_REQUEST['product_id'] : null;
    $selectedStock = isset($_REQUEST['stock_id']) ? $_REQUEST['stock_id'] : null;

    $history = [];
    $totalRecords = 0;

    // Prepare base SQL and parameters
    $params = [];
    $where = [];
    $joinStock = false;

    if ($selectedCategory) {
        // If category selected but no product, join products and get all products in category
        if (!$selectedProduct) {
            $joinProduct = true;
            $where[] = 'p.category = :category_id';
            $params[':category_id'] = $selectedCategory;
        }
    }
    
    if ($selectedStock) {
        $where[] = 'slh.stock_id = :stock_id';
        $params[':stock_id'] = $selectedStock;
    }
    
    if ($selectedProduct) {
        $where[] = 'slh.product_id = :product_id';
        $params[':product_id'] = $selectedProduct;
        $joinStock = true;
    }

    if (empty($where)) {
        // Default - show limited records if no filters
        $totalRecords = 0;
    } else {
        // Join tables as needed
        $joinClause = '';
        if (isset($joinProduct) && $joinProduct) {
            $joinClause .= " LEFT JOIN `tbl_products` p ON slh.product_id = p.product_id";
        }
        if ($joinStock) {
            $joinClause .= " LEFT JOIN `tbl_stock` s ON slh.stock_id = s.stock_id";
        }
        
        // Count total records
        $countSql = "SELECT COUNT(*) as total FROM `tbl_stock_location_history` slh $joinClause WHERE " . implode(' AND ', $where);
        $countStmt = $conn->prepare($countSql);
        foreach ($params as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Build main SQL
        $sql = "SELECT slh.*, 
                ow.warehouse_name as old_warehouse_name, 
                nw.warehouse_name as new_warehouse_name,
                ol.name as old_location_name, ol.type as old_location_type,
                nl.name as new_location_name, nl.type as new_location_type,
                c.full_name as moved_by_name";
        if ($joinStock) {
            $sql .= ", s.original_batch";
        }
        $sql .= " FROM `tbl_stock_location_history` slh
                LEFT JOIN `tbl_warehouse` ow ON slh.old_warehouse_id = ow.warehouse_id
                LEFT JOIN `tbl_warehouse` nw ON slh.new_warehouse_id = nw.warehouse_id
                LEFT JOIN `tbl_warehouse_location` ol ON slh.old_location_id = ol.location_id
                LEFT JOIN `tbl_warehouse_location` nl ON slh.new_location_id = nl.location_id
                LEFT JOIN `tbl_customers` c ON slh.moved_by = c.customer_id";
                
        // Add extra joins if needed
        if (isset($joinProduct) && $joinProduct) {
            $sql .= " JOIN `tbl_products` p ON slh.product_id = p.product_id";
        }
        if ($joinStock) {
            $sql .= " LEFT JOIN `tbl_stock` s ON slh.stock_id = s.stock_id";
        }
        
        $sql .= " WHERE " . implode(' AND ', $where);

        if ($search) {
            $sql .= " AND (";
            $searchFields = [];
            if ($selectedStock) {
                $searchFields[] = "nw.warehouse_name LIKE :search";
                $searchFields[] = "nl.name LIKE :search";
                $searchFields[] = "c.full_name LIKE :search";
                $searchFields[] = "slh.notes LIKE :search";
            } else {
                $searchFields[] = "slh.batch_number LIKE :search";
                $searchFields[] = "nw.warehouse_name LIKE :search";
                $searchFields[] = "nl.name LIKE :search";
                $searchFields[] = "c.full_name LIKE :search";
                $searchFields[] = "slh.notes LIKE :search";
            }
            $sql .= implode(' OR ', $searchFields) . ")";
        }

        $sql .= " ORDER BY slh.created_at DESC LIMIT :start, :length";

        $stmt = $conn->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        if ($search) {
            $searchTerm = "%$search%";
            $stmt->bindValue(':search', $searchTerm);
        }
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);
        $stmt->execute();

        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add notes for split batches if product view
        if ($joinStock && $history) {
            foreach ($history as $key => $record) {
                if (!empty($record['original_batch'])) {
                    $history[$key]['notes'] = $record['notes'] .
                        ' (This batch was split from original batch: ' . $record['original_batch'] . ')';
                }
            }
        }
    }

    // Format data for DataTables
    $formattedHistory = [];

    foreach ($history as $index => $record) {
        $item = [];

        // Date & Time
        $item['created_at'] = date('M d, Y h:i A', strtotime($record['created_at']));

        // Batch (only for product view)
        if (!$selectedStock) {
            $item['batch_number'] = $record['batch_number'] ? htmlspecialchars($record['batch_number']) : 'N/A';
        }else{
            $item['batch_number'] = '';
        }

        // From location
        $fromLocation = '';
        if ($record['old_warehouse_id']) {
            $fromLocation = htmlspecialchars($record['old_warehouse_name']);
            if ($record['old_location_id']) {
                $fromLocation .= '<br><small>' . htmlspecialchars($record['old_location_type'] . ': ' . $record['old_location_name']) . '</small>';
            }
        } else {
            $fromLocation = '<span class="badge bg-label-info">Initial Assignment</span>';
        }
        $item['from_location'] = $fromLocation;

        // To location
        $toLocation = htmlspecialchars($record['new_warehouse_name'] ?? 'N/A');
        if ($record['new_location_id']) {
            $toLocation .= '<br><small>' . htmlspecialchars($record['new_location_type'] . ': ' . $record['new_location_name']) . '</small>';
        }
        $item['to_location'] = $toLocation;

        // Moved by
        $item['moved_by'] = htmlspecialchars($record['moved_by_name']);

        // Notes
        $item['notes'] = $record['notes'] ? htmlspecialchars($record['notes']) : '<em>No notes</em>';

        $formattedHistory[] = $item;
    }

    $response = [
        'draw' => $draw,
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $totalRecords,
        'data' => $formattedHistory
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
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
                                            <div class="card-body">
                                                <h5 class="card-title">Filter Location History</h5>
                                                <form id="filter-form" class="row g-3">
                                                    <div class="col-md-4">
                                                        <label for="category_id" class="form-label">Filter by Category</label>
                                                        <select name="category_id" id="category_id">
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
                                                        <select name="product_id" id="product_id">
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

                                                    <div class="col-md-4">
                                                        <label for="stock_id" class="form-label">Select Batch/Stock Item</label>
                                                        <select name="stock_id" id="stock_id">
                                                            <option value="">-- All Batches --</option>
                                                            <?php foreach ($stockItems as $item): ?>
                                                                <option value="<?= $item['stock_id'] ?>" <?= $selectedStock == $item['stock_id'] ? 'selected' : '' ?>>
                                                                    Batch: <?= $item['batch_number'] ? htmlspecialchars($item['batch_number']) : 'N/A' ?>
                                                                    (Qty: <?= $item['quantity'] ?>)
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>

                                                    <div class="col-12">
                                                        <button type="button" id="filter-button" class="btn btn-primary">Filter</button>
                                                        <button type="button" id="reset-button" class="btn btn-outline-secondary ms-2">Reset</button>
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

                                                        <div class="mt-3 mt-md-0">
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
                                                    </div>

                                                    <div class="table-responsive text-nowrap">
                                                        <table class="table table-hover" id="location-history-table">
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
                                                        </table>
                                                    </div>
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
        $(function() {
            // Initialize select2 for better UX
            $('#category_id, #product_id, #stock_id').selectize({
                create: false,
                sortField: 'text',
                placeholder: '-- Select --',
                onChange: function(value) {
                    if (value === '') {
                        this.clear();
                    }
                }
            });

            // Handle filter form submission
            // Variable to store current filters
            let currentFilters = {
                category_id: <?= $selectedCategory ?? '""' ?>,
                product_id: <?= $selectedProduct ?? '""' ?>,
                stock_id: <?= $selectedStock ?? '""' ?>
            };

            // Handle category change - load products based on category
            $('#category_id').on('change', function() {
                let categoryId = $(this).val();
                currentFilters.category_id = categoryId;
                currentFilters.product_id = '';
                currentFilters.stock_id = '';
                
                // Reset product and stock dropdowns
                resetSelectize($('#product_id')[0].selectize, true);
                resetSelectize($('#stock_id')[0].selectize, true);

                if (categoryId) {
                    $.ajax({
                        url: 'location-history',
                        type: 'GET',
                        data: {
                            category_id: categoryId,
                            getProducts: true
                        },
                        success: function(response) {
                            let productSelectize = $('#product_id')[0].selectize;
                            productSelectize.clearOptions();
                            
                            if (response.products && response.products.length > 0) {
                                response.products.forEach(function(product) {
                                    productSelectize.addOption({value: product.product_id, text: product.product_name});
                                });
                                productSelectize.enable();
                            } else {
                                productSelectize.disable();
                            }
                            
                            // Refresh the DataTable with the new category filter
                            refreshTable();
                            // Update product info header
                            updateProductInfoHeader();
                            // Update export URL
                            updateExportUrl();
                        }
                    });
                } else {
                    resetSelectize($('#product_id')[0].selectize, true);
                    resetSelectize($('#stock_id')[0].selectize, true);
                    refreshTable();
                    updateProductInfoHeader();
                    updateExportUrl();
                }
            });

            // Handle product change - load stock items based on product
            $('#product_id').on('change', function() {
                let productId = $(this).val();
                currentFilters.product_id = productId;
                currentFilters.stock_id = '';
                
                // Reset stock dropdown
                resetSelectize($('#stock_id')[0].selectize, true);

                if (productId) {
                    $.ajax({
                        url: 'location-history',
                        type: 'GET',
                        data: {
                            product_id: productId,
                            getStockItems: true
                        },
                        success: function(response) {
                            let stockSelectize = $('#stock_id')[0].selectize;
                            stockSelectize.clearOptions();
                            
                            if (response.stockItems && response.stockItems.length > 0) {
                                response.stockItems.forEach(function(item) {
                                    let batchText = item.batch_number ? item.batch_number : 'N/A';
                                    stockSelectize.addOption({
                                        value: item.stock_id, 
                                        text: 'Batch: ' + batchText + ' (Qty: ' + item.quantity + ')'
                                    });
                                });
                                stockSelectize.enable();
                            } else {
                                stockSelectize.disable();
                            }
                            
                            // Refresh the DataTable with the new product filter
                            refreshTable();
                            // Get product info and update header
                            getProductInfo(productId);
                            // Update export URL
                            updateExportUrl();
                        }
                    });
                } else {
                    resetSelectize($('#stock_id')[0].selectize, true);
                    refreshTable();
                    updateProductInfoHeader();
                    updateExportUrl();
                }
            });

            // Handle stock selection change
            $('#stock_id').on('change', function() {
                let stockId = $(this).val();
                currentFilters.stock_id = stockId;
                
                if (stockId) {
                    // Get stock info and update header
                    $.ajax({
                        url: 'location-history',
                        type: 'GET',
                        data: {
                            stock_id: stockId,
                            getStockInfo: true
                        },
                        success: function(response) {
                            if (response.stockInfo) {
                                updateStockInfoHeader(response.stockInfo);
                            }
                            // Refresh the DataTable with the new stock filter
                            refreshTable();
                            // Update export URL
                            updateExportUrl();
                        }
                    });
                } else {
                    // Refresh with just the product filter
                    refreshTable();
                    // Update header with product info if a product is selected
                    if (currentFilters.product_id) {
                        getProductInfo(currentFilters.product_id);
                    } else {
                        updateProductInfoHeader();
                    }
                    // Update export URL
                    updateExportUrl();
                }
            });

            // Function to reset selectize dropdown
            function resetSelectize(selectizeControl, disable = false) {
                if (selectizeControl) {
                    selectizeControl.clearOptions();
                    selectizeControl.addOption({value: '', text: '-- Select --'});
                    selectizeControl.setValue('');
                    if (disable) {
                        selectizeControl.disable();
                    } else {
                        selectizeControl.enable();
                    }
                }
            }

            // Function to refresh the DataTable with current filters
            function refreshTable() {
                if (locationHistoryTable) {
                    // Update column visibility before reload based on current filters
                    locationHistoryTable.column(1).visible(!currentFilters.stock_id);
                    // Reload with current filters
                    locationHistoryTable.ajax.reload();
                    // Update browser URL without reloading page
                    updateBrowserUrl();
                }
            }
            
            // Function to update browser URL with current filters
            function updateBrowserUrl() {
                let url = new URL(window.location.href);
                url.search = ''; // Clear existing parameters
                
                // Add current filters to URL
                let params = [];
                if (currentFilters.category_id) {
                    params.push('category_id=' + currentFilters.category_id);
                }
                if (currentFilters.product_id) {
                    params.push('product_id=' + currentFilters.product_id);
                }
                if (currentFilters.stock_id) {
                    params.push('stock_id=' + currentFilters.stock_id);
                }
                
                // Update URL without reloading
                if (params.length > 0) {
                    history.pushState({}, '', '?' + params.join('&'));
                } else {
                    history.pushState({}, '', window.location.pathname);
                }
            }

            // Function to update product info header
            function updateProductInfoHeader(productInfo = null) {
                const infoContainer = $('.flex-grow-1.me-3');
                
                if (!productInfo && !currentFilters.product_id) {
                    infoContainer.empty();
                    return;
                }

                if (productInfo) {
                    // Create or update the product info header
                    infoContainer.html(
                        '<div class="alert alert-info mb-0">' +
                        '<h5 class="mb-1">Product: ' + productInfo.product_name + '</h5>' +
                        '</div>'
                    );
                }
            }

            // Function to update stock info header
            function updateStockInfoHeader(stockInfo) {
                const infoContainer = $('.flex-grow-1.me-3');
                
                infoContainer.html(
                    '<div class="alert alert-info mb-0">' +
                    '<h5 class="mb-1">Product: ' + stockInfo.product_name + '</h5>' +
                    '<p class="mb-0">Batch: ' + (stockInfo.batch_number ? stockInfo.batch_number : 'N/A') + '</p>' +
                    '</div>'
                );
            }

            // Function to get product info and update header
            function getProductInfo(productId) {
                $.ajax({
                    url: 'location-history',
                    type: 'GET',
                    data: {
                        product_id: productId,
                        getProductInfo: true
                    },
                    success: function(response) {
                        if (response.productInfo) {
                            updateProductInfoHeader(response.productInfo);
                        }
                    }
                });
            }

            // Function to update export PDF URL
            function updateExportUrl() {
                let baseUrl = 'location-history-pdf.php?';
                let params = [];
                
                if (currentFilters.category_id) {
                    params.push('category_id=' + currentFilters.category_id);
                }
                
                if (currentFilters.product_id) {
                    params.push('product_id=' + currentFilters.product_id);
                }
                
                if (currentFilters.stock_id) {
                    params.push('stock_id=' + currentFilters.stock_id);
                }
                
                let exportUrl = baseUrl + params.join('&');
                $('a[href*="location-history-pdf.php"]').attr('href', exportUrl);
            }

            // Initialize DataTable with server-side processing
            let locationHistoryTable = $('#location-history-table').DataTable({
                "processing": true,
                "serverSide": true,
                "searching": true,
                "responsive": true,
                "ajax": {
                    url: 'location-history',
                    type: 'POST',
                    data: function(d) {
                        d.location_history = true;
                        d.category_id = currentFilters.category_id;
                        d.product_id = currentFilters.product_id;
                        d.stock_id = currentFilters.stock_id;
                    }
                },
                "columns": [{
                        "data": "created_at",
                        "title": "Date & Time"
                    },
                    {
                        "data": "batch_number",
                        "title": "Batch",
                        "visible": !currentFilters.stock_id
                    },
                    {
                        "data": "from_location",
                        "title": "From"
                    },
                    {
                        "data": "to_location",
                        "title": "To"
                    },
                    {
                        "data": "moved_by",
                        "title": "Moved By"
                    },
                    {
                        "data": "notes",
                        "title": "Notes"
                    }
                ],
                "order": [
                    [0, "desc"]
                ],
                "language": {
                    "emptyTable": "No location history found for the selected filters.",
                    "processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                },
                "drawCallback": function(settings) {
                    // Check if we need to hide the batch column
                    const batchColumnVisible = !currentFilters.stock_id;
                    this.api().column(1).visible(batchColumnVisible);
                    
                    // If no data is found, show a message when filters are applied
                    if (settings.json && settings.json.recordsTotal === 0) {
                        if (currentFilters.product_id || currentFilters.category_id) {
                            const noDataMessage = currentFilters.stock_id ? 
                                'No location history found for this batch.' :
                                'No location history found for the selected product.';
                            
                            $(this).parent().find('.dataTables_empty').html(
                                '<div class="alert alert-info mb-0 text-center">' + 
                                '<i class="bx bx-info-circle me-2"></i>' + noDataMessage + 
                                '<br><small>Try selecting a different product or batch.</small></div>'
                            );
                        }
                    }
                }
            });
            // Initialize current filters from any existing selections
            currentFilters.category_id = $('#category_id').val() || '';
            currentFilters.product_id = $('#product_id').val() || '';
            currentFilters.stock_id = $('#stock_id').val() || '';
            
            // Update column visibility based on initial filters
            if (locationHistoryTable && currentFilters.stock_id) {
                locationHistoryTable.column(1).visible(false);
            }

            // Handler for filter button click
            $('#filter-button').on('click', function() {
                // Simply refresh the table - the dropdowns already updated the filters
                refreshTable();
                // Show a brief filter applied message
                let message = 'Filters applied';
                if (currentFilters.product_id || currentFilters.category_id || currentFilters.stock_id) {
                    message += ': ';
                    let filters = [];
                    if (currentFilters.category_id) filters.push('Category');
                    if (currentFilters.product_id) filters.push('Product');
                    if (currentFilters.stock_id) filters.push('Batch');
                    message += filters.join(', ');
                }
                
                toastr.success(message);
            });

            // Handler for reset button click
            $('#reset-button').on('click', function() {
                // Reset all filters
                currentFilters = {
                    category_id: '',
                    product_id: '',
                    stock_id: ''
                };
                
                // Reset all dropdowns
                resetSelectize($('#category_id')[0].selectize);
                resetSelectize($('#product_id')[0].selectize, true);
                resetSelectize($('#stock_id')[0].selectize, true);
                
                // Refresh table and UI
                refreshTable();
                updateProductInfoHeader();
                updateExportUrl();
                
                // Clear the product info area
                $('.flex-grow-1.me-3').empty();
                
                toastr.info('All filters have been reset');
            });

            // Handle form submission prevention
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                $('#filter-button').click();
            });
            
            // Initialize the table if filters are already set
            if (currentFilters.product_id) {
                if (currentFilters.stock_id) {
                    $.ajax({
                        url: 'location-history',
                        type: 'GET',
                        data: {
                            stock_id: currentFilters.stock_id,
                            getStockInfo: true
                        },
                        success: function(response) {
                            if (response.stockInfo) {
                                updateStockInfoHeader(response.stockInfo);
                            }
                        }
                    });
                } else {
                    getProductInfo(currentFilters.product_id);
                }
            }
            
            // Update export URL on page load
            updateExportUrl();
        });
    </script>
</body>

</html>