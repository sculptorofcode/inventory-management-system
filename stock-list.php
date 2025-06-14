<?php
require_once 'includes/config/after-login.php';
$title = 'Stock List';
if (isset($_REQUEST['draw']) && isset($_REQUEST['stock_list'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];

    $total = getCount('tbl_products', [], 'stock > 0');

    $sql = "SELECT p.*,sp.supplier_name,c.category_name,s.*, 
            (s.quantity * p.purchase_price) as total_value, 
            w.warehouse_name,
            wl.name as location_name, 
            wl.type as location_type
            FROM `tbl_stock` s
            LEFT JOIN `tbl_products` p ON s.product_id = p.product_id
            LEFT JOIN `tbl_product_categories` c ON p.category = c.category_id
            LEFT JOIN `tbl_suppliers` sp ON p.supplier_id = sp.supplier_id
            LEFT JOIN `tbl_warehouse` w ON s.warehouse_id = w.warehouse_id
            LEFT JOIN `tbl_warehouse_location` wl ON s.location_id = wl.location_id
            WHERE 1=1";

    if ($search) {
        $sql .= " AND (p.product_name LIKE '%$search%' OR c.category_name LIKE '%$search%' OR sp.supplier_name LIKE '%$search%')";
    }

    if (isset($_REQUEST['product']) && $_REQUEST['product']) {
        $sql .= " AND p.product_id = {$_REQUEST['product']}";
    }

    if (isset($_REQUEST['category']) && $_REQUEST['category']) {
        $sql .= " AND p.category = {$_REQUEST['category']}";
    }

    if (isset($_REQUEST['batch_number']) && $_REQUEST['batch_number']) {
        $sql .= " AND s.batch_number LIKE '%{$_REQUEST['batch_number']}%'";
    }

    $sql .= " ORDER BY s.stock_id DESC LIMIT $start, $length";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sl = $start + 1;
    foreach ($data as &$row) {
        $batch_number = $row['batch_number'];
        $assign_warehouse = '<button class="btn btn-primary btn-sm px-2" onclick="assignWarehouse(' . $row['product_id'] . ', \'' . $batch_number . '\', \'' . $row['product_name'] . '\')">Assign</button>';
        $product_name = html_entity_decode($row['product_name']) . ' <br><small class="text-muted">' . html_entity_decode($row['category_name']) . '</small>';
        $row['sl_no'] = $sl++;
        $row['stock'] = $row['stock'] ?? 0;
        $row['purchase_price'] = $row['purchase_price'];
        $row['total_value'] = $row['total_value'];
        $row['added_date'] = !empty($row['added_on']) ? date('d M Y', strtotime($row['added_on'])) : '';
        $row['batch_number'] = '<a href="javascript:void(0)" onclick="stockReport(\'' . $row['batch_number'] . '\',' . $row['product_id'] . ', \'' . $row['product_name'] . '\')">' . $row['batch_number'] . '</a>';
        $row['category_name'] = html_entity_decode($row['category_name']);
        $row['product_name'] = html_entity_decode($row['product_name']);
        // Build warehouse and location display
        $warehouse_display = !empty($row['warehouse_name']) ? html_entity_decode($row['warehouse_name']) : $assign_warehouse;

        // Add location info if available
        if (!empty($row['location_name']) && !empty($row['location_type'])) {
            $location_info = $row['location_name'] . ' (' . $row['location_type'] . ')';
            $warehouse_display .= '<br><small class="text-muted text-nowrap">Location: ' . $location_info . '</small>';
        }

        $row['warehouse_name'] = $warehouse_display;
        $row['action'] = '<div class="d-flex gap-2">';
        $row['action'] .= '<a href="javascript:void(0)" onclick="manageStock(' . $row['product_id'] . ', \'' . $batch_number . '\', \'' . $row['product_name'] . '\')" class="btn btn-primary btn-sm px-2" title="Manage Stock"><i class="bx bxs-cog"></i></a>';

        $row['action'] .= '<a href="javascript:void(0)" onclick="moveToLocation(' . $row['product_id'] . ', \'' . $batch_number . '\', \'' . $row['product_name'] . '\')" class="btn btn-primary btn-sm px-2" title="Move to Location"><i class="bx bx-transfer"></i></a>';

        $row['action'] .= '<a href="location-history.php?stock_id=' . $row['stock_id'] . '" class="btn btn-info btn-sm px-2" title="View Location History"><i class="bx bx-history"></i></a>';
        $row['action'] .= '</div>';
        $row['product_name'] = $product_name;
    }

    $response = [
        'draw' => $draw,
        'recordsTotal' => $total,
        'recordsFiltered' => $total,
        'data' => $data
    ];
    echo json_encode($response);
    exit;
}

if (isset($_REQUEST['draw']) && isset($_REQUEST['stock_report'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];

    $product_id = $_REQUEST['product_id'];
    $batch_number = $_REQUEST['batch_number'];

    $sql = "SELECT *,`tbl_products`.product_name FROM tbl_stock_transactions 
            LEFT JOIN `tbl_stock` ON `tbl_stock`.stock_id = tbl_stock_transactions.stock_id
            LEFT JOIN `tbl_products` ON `tbl_products`.product_id = `tbl_stock`.product_id
            WHERE tbl_stock_transactions.product_id = :product_id AND `tbl_stock`.batch_number = :batch_number";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':batch_number', $batch_number);
    $stmt->execute();

    $total = $stmt->rowCount();

    $sql .= " ORDER BY tbl_stock_transactions.created_at DESC LIMIT $start, $length";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':batch_number', $batch_number);

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sl = $start + 1;
    $data = array_map(function ($row) use (&$sl) {
        $row['sl_no'] = $sl++;
        $row['added_on'] = date('d M Y', strtotime($row['created_at']));
        $row['last_updated'] = $row['updated_at'] ? date('d M Y h:i A', strtotime($row['updated_at'])) : '';
        $row['product_name'] = html_entity_decode($row['product_name']);
        return $row;
    }, $data);


    $response = [
        'draw' => $draw,
        'recordsTotal' => $total,
        'recordsFiltered' => $total,
        'data' => $data
    ];
    echo json_encode($response);
    exit;
}

if (isset($_REQUEST['getProducts'])) {
    $category_id = $_REQUEST['category'];
    $products = getAllProducts($category_id);
    echo json_encode($products);
    exit;
}

if (isset($_REQUEST['manageStock'])) {
    $product_id = $_REQUEST['product_id'];
    $batch_number = $_REQUEST['batch_number'];
    $stock = getStockByProductAndBatch($product_id, $batch_number); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <input type="hidden" name="product_id" id="product_id" value="<?= $product_id ?>">
                <input type="hidden" name="batch_number" id="batch_number" value="<?= $batch_number ?>">
                <div class="form-group">
                    <label for="quantity">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" placeholder="Quantity">
                </div>
                <div class="form-group">
                    <label for="transaction_type">Transaction Type</label>
                    <select name="transaction_type" id="transaction_type" class="form-select">
                        <option value="" disabled selected>Select Transaction Type</option>
                        <option value="in">In</option>
                        <option value="out">Out</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <input type="text" name="remarks" id="remarks" class="form-control" placeholder="Remarks">
                </div>
                <div class="text-center">
                    <button class="btn btn-primary" id="manage_stock">Manage Stock</button>
                </div>
            </div>
        </div>
    </div>
<?php
    exit;
}

if (isset($_REQUEST['manage_stock'])) {
    $product_id = filtervar($_REQUEST['product_id']);
    $batch_number = filtervar($_REQUEST['batch_number']);
    $quantity = filtervar($_REQUEST['quantity']);
    $transaction_type = filtervar($_REQUEST['transaction_type']);
    $remarks = filtervar($_REQUEST['remarks']);

    try {
        $conn->beginTransaction();

        $stock = getStockByProductAndBatch($product_id, $batch_number);
        if (!$stock) {
            throw new Exception('Stock not found!');
        }
        $stock_id = $stock['stock_id'];
        $current_stock = $stock['quantity'];

        if ($transaction_type == 'in') {
            $new_stock = $current_stock + $quantity;
            $stmt = $conn->prepare("UPDATE `tbl_stock` SET quantity = :stock WHERE stock_id = :stock_id");
            $stmt->bindParam(':stock', $new_stock);
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->execute();
        } else {
            if ($current_stock < $quantity) {
                throw new Exception('Insufficient stock!');
            }
            $new_stock = $current_stock - $quantity;
            $stmt = $conn->prepare("UPDATE `tbl_stock` SET quantity = :stock WHERE stock_id = :stock_id");
            $stmt->bindParam(':stock', $new_stock);
            $stmt->bindParam(':stock_id', $stock_id);
            $stmt->execute();
        }

        $stmt = $conn->prepare("INSERT INTO tbl_stock_transactions (stock_id, product_id, quantity_change, previous_quantity, transaction_type, order_reference, notes) VALUES (:stock_id, :product_id, :quantity_change, :previous_quantity, :transaction_type, :order_reference, :notes)");
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity_change', $quantity);
        $stmt->bindParam(':previous_quantity', $current_stock);
        $stmt->bindParam(':transaction_type', $transaction_type);
        $stmt->bindParam(':order_reference', $remarks);
        $stmt->bindParam(':notes', $remarks);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE `tbl_products` SET stock = :stock WHERE product_id = :product_id");
        $stmt->bindParam(':stock', $new_stock);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stock updated successfully!', 'redirect' => 'stock-list']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if (isset($_REQUEST['assignWarehouse'])) {
    $product_id = $_REQUEST['product_id'];
    $batch_number = $_REQUEST['batch_number'];
    $stock = getStockByProductAndBatch($product_id, $batch_number);
    $warehouses = getAllWarehouses();

    $current_warehouse_id = $stock['warehouse_id'] ?? null;
    $current_location_id = $stock['location_id'] ?? null;

    $warehouse_locations = [];
    if ($current_warehouse_id) {
        $warehouse_locations = getLocationsByWarehouse($current_warehouse_id);
    }
?>
    <form class="container" method="POST">
        <div class="row">
            <div class="col-md-12">
                <input type="hidden" name="product_id" id="product_id" value="<?= $product_id ?>">
                <input type="hidden" name="batch_number" id="batch_number" value="<?= $batch_number ?>">
                <input type="hidden" name="stock_id" value="<?= $stock['stock_id'] ?>">

                <div class="form-group mb-3">
                    <label for="warehouse_id">Warehouse</label>
                    <select name="warehouse_id" id="warehouse_id" required>
                        <option value="" disabled selected>Select Warehouse</option>
                        <?php foreach ($warehouses as $warehouse) {
                            $selected = ($warehouse['warehouse_id'] == $current_warehouse_id) ? 'selected' : '';
                        ?>
                            <option value="<?= $warehouse['warehouse_id'] ?>" <?= $selected ?>><?= html_entity_decode($warehouse['warehouse_name']) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="location_id">Location (Optional)</label>
                    <select name="location_id" id="location_id">
                        <option value="">Select Location (Optional)</option>
                        <?php foreach ($warehouse_locations as $location) {
                            $selected = ($location['location_id'] == $current_location_id) ? 'selected' : '';
                            $location_label = $location['name'] . ' (' . $location['type'] . ')';
                        ?>
                            <option value="<?= $location['location_id'] ?>" <?= $selected ?>><?= $location_label ?></option>
                        <?php } ?>
                    </select>
                    <small class="form-text text-muted">First select a warehouse to see available locations</small>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary" id="assign_warehouse" name="assign_warehouse">Assign Warehouse & Location</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(document).ready(function() {
            // When warehouse changes, load locations for that warehouse
            $('#warehouse_id').change(function() {
                var warehouseId = $(this).val();
                if (warehouseId) {
                    $.ajax({
                        url: 'ajax.php',
                        type: 'POST',
                        data: {
                            action: 'get_locations_by_warehouse',
                            warehouse_id: warehouseId
                        },
                        success: function(response) {
                            if ($('#location_id').data('selectize')) {
                                $('#location_id')[0].selectize.destroy();
                            }
                            $('#location_id').html(response);
                            $('#location_id').selectize({
                                create: false,
                                sortField: 'text',
                                placeholder: 'Select Location',
                                dropdownParent: 'body'
                            });
                        }
                    });
                } else {
                    $('#location_id').html('<option value="">Select Location (Optional)</option>');
                }
            });
        });
    </script>
    <?php
    exit;
}

if (isset($_POST['assign_warehouse'])) {
    $product_id = filtervar($_POST['product_id']);
    $batch_number = filtervar($_POST['batch_number']);
    $stock_id = filtervar($_POST['stock_id']);
    $warehouse_id = filtervar($_POST['warehouse_id']);
    $location_id = isset($_POST['location_id']) && !empty($_POST['location_id']) ? filtervar($_POST['location_id']) : null;

    try {
        $conn->beginTransaction();

        $stock = getStockByProductAndBatch($product_id, $batch_number);
        if (!$stock) {
            throw new Exception('Stock not found!');
        }

        // If location is specified, verify it belongs to the selected warehouse
        if ($location_id) {
            $location = getWarehouseLocationById($location_id);
            if (!$location || $location['warehouse_id'] != $warehouse_id) {
                throw new Exception('The selected location does not belong to the selected warehouse');
            }
        }

        // Update warehouse and location
        $stmt = $conn->prepare("UPDATE `tbl_stock` SET warehouse_id = :warehouse_id, location_id = :location_id WHERE stock_id = :stock_id");
        $stmt->bindParam(':warehouse_id', $warehouse_id);
        $stmt->bindParam(':location_id', $location_id);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->execute();

        // Record the change in stock transactions
        $warehouse = null;
        foreach (getAllWarehouses() as $wh) {
            if ($wh['warehouse_id'] == $warehouse_id) {
                $warehouse = $wh;
                break;
            }
        }

        $warehouse_name = $warehouse ? $warehouse['warehouse_name'] : 'Unknown Warehouse';
        $location_name = '';

        if ($location_id) {
            $location = getWarehouseLocationById($location_id);
            if ($location) {
                $location_name = " - " . $location['name'] . " (" . $location['type'] . ")";
            }
        }

        $notes = "Assigned to " . $warehouse_name . $location_name;
        $transaction_location = $warehouse_name . $location_name;

        $stmt = $conn->prepare("INSERT INTO `tbl_stock_transactions` (product_id, stock_id, quantity_change, previous_quantity, transaction_type, notes, user_id, transaction_location) 
                                VALUES (:product_id, :stock_id, 0, :quantity, 'warehouse', :notes, :user_id, :transaction_location)");
        $stmt->bindParam(':product_id', $stock['product_id']);
        $stmt->bindParam(':stock_id', $stock_id);
        $stmt->bindParam(':quantity', $stock['quantity']);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':user_id', $userdata['customer_id']);
        $stmt->bindParam(':transaction_location', $transaction_location);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Warehouse and location assigned successfully!', 'function' => 'reloadPage']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if (isset($_REQUEST['moveToLocation'])) {
    $product_id = $_REQUEST['product_id'];
    $batch_number = $_REQUEST['batch_number'];
    $stock = getStockByProductAndBatch($product_id, $batch_number);

    if (!$stock) { ?>
        <div class="alert alert-danger">Stock not found!</div>
    <?php exit;
    }

    $warehouses = getAllWarehouses();
    $current_warehouse_id = $stock['warehouse_id'] ?? null;
    $current_location_id = $stock['location_id'] ?? null;

    $warehouse_locations = [];
    if ($current_warehouse_id) {
        $warehouse_locations = getLocationsByWarehouse($current_warehouse_id);
    }
    ?>
    <form class="container" method="POST">
        <div class="row">
            <div class="col-md-12">                
                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                <input type="hidden" name="batch_number" value="<?= $batch_number ?>">
                <input type="hidden" name="stock_id" value="<?= $stock['stock_id'] ?>">

                <div class="form-group mb-3">
                    <label for="quantity">Quantity to Move</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" 
                           min="1" max="<?= $stock['quantity'] ?>" value="<?= $stock['quantity'] ?>" required>
                    <small class="form-text text-muted">
                        Available: <?= $stock['quantity'] ?>. If moving less than the total quantity, 
                        a new batch will be created for the moved quantity.
                    </small>
                </div>

                <div class="form-group mb-3">
                    <label for="warehouse_id">Warehouse</label>
                    <select name="warehouse_id" id="warehouse_id" required>
                        <option value="" disabled selected>Select Warehouse</option>
                        <?php foreach ($warehouses as $warehouse) {
                            $selected = ($warehouse['warehouse_id'] == $current_warehouse_id) ? 'selected' : '';
                        ?>
                            <option value="<?= $warehouse['warehouse_id'] ?>" <?= $selected ?>><?= html_entity_decode($warehouse['warehouse_name']) ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group mb-3">
                    <label for="location_id">Location</label>
                    <select name="location_id" id="location_id" required>
                        <option value="" disabled selected>Select Location</option>
                        <?php foreach ($warehouse_locations as $location) {
                            $selected = ($location['location_id'] == $current_location_id) ? 'selected' : '';
                            $location_label = $location['name'] . ' (' . $location['type'] . ')';
                        ?>
                            <option value="<?= $location['location_id'] ?>" <?= $selected ?>><?= $location_label ?></option>
                        <?php } ?>
                    </select>
                    <small class="form-text text-muted">First select a warehouse to see available locations</small>
                </div>

                <div class="form-group mb-3">
                    <label for="notes">Notes (Optional)</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Add notes about this move"></textarea>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary" id="move_to_location" name="move_to_location">Move to Location</button>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(document).ready(function() {
            // When warehouse changes, load locations for that warehouse
            $('#warehouse_id').change(function() {
                var warehouseId = $(this).val();
                if (warehouseId) {
                    $.ajax({
                        url: 'ajax.php',
                        type: 'POST',
                        data: {
                            action: 'get_locations_by_warehouse',
                            warehouse_id: warehouseId
                        },
                        success: function(response) {
                            if ($('#location_id').data('selectize')) {
                                $('#location_id')[0].selectize.destroy();
                            }
                            $('#location_id').html(response);
                            $('#location_id').selectize({
                                create: false,
                                sortField: 'text',
                                placeholder: 'Select Location',
                                dropdownParent: 'body'
                            });
                        }
                    });
                } else {
                    $('#location_id').html('<option value="" disabled selected>Select Location</option>');
                }
            });
        });
    </script>
<?php
    exit;
}

if (isset($_POST['move_to_location'])) {
    $product_id = filtervar($_POST['product_id']);
    $batch_number = filtervar($_POST['batch_number']);
    $stock_id = filtervar($_POST['stock_id']);
    $warehouse_id = filtervar($_POST['warehouse_id']);
    $location_id = filtervar($_POST['location_id']);
    $notes = filtervar($_POST['notes']);
    $quantity = intval(filtervar($_POST['quantity']));

    try {
        // Verify location exists in the selected warehouse
        $location = getWarehouseLocationById($location_id);
        if (!$location) {
            throw new Exception('Invalid location selected');
        }

        // Check if stock exists
        $stock = getStockByProductAndBatch($product_id, $batch_number);
        if (!$stock) {
            throw new Exception('Stock not found');
        }

        // Validate quantity
        if ($quantity <= 0 || $quantity > $stock['quantity']) {
            throw new Exception('Invalid quantity. Must be between 1 and ' . $stock['quantity']);
        }

        // If moving partial quantity, create a new batch and move only that portion
        if ($quantity < $stock['quantity']) {
            $result = movePartialStockToLocation($stock_id, $warehouse_id, $location_id, $quantity, $notes, $userdata['customer_id']);
            $message = 'Partial stock quantity moved to new location successfully';
        } else {
            // Move entire stock to the new location
            $result = moveStockToLocation($stock_id, $warehouse_id, $location_id, $notes, $userdata['customer_id']);
            $message = 'Stock moved to new location successfully';
        }

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => $message, 'function' => 'reloadPage']);
        } else {
            throw new Exception('Failed to move stock to the selected location');
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
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
                            <div class="card-header border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0"><?= $title ?></h5>
                                <a href="location-history" class="btn btn-info">
                                    <i class="bx bx-history me-1"></i> View Location History
                                </a>
                            </div>
                            <div class="card-body pt-3">
                                <div class="row justify-content-center align-items-end mb-3">
                                    <div class="col-md-3">
                                        <label for="category">Category</label>
                                        <select name="category" id="category">
                                            <option value="">Select Category</option>
                                            <?php
                                            $categories = getAllProductCategories();
                                            foreach ($categories as $category) {
                                            ?>
                                                <option value="<?= $category['category_id'] ?>"><?= html_entity_decode($category['category_name']) ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="product">Product</label>
                                        <select name="product" id="product">
                                            <option value="">Select Product</option>
                                            <?php
                                            $products = getAllProducts();
                                            foreach ($products as $product) {
                                            ?>
                                                <option value="<?= $product['product_id'] ?>"><?= html_entity_decode($product['product_name']) ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-none">
                                        <label for="batch_number">Batch Number</label>
                                        <input type="text" name="batch_number" id="batch_number" class="form-control"
                                            placeholder="Batch Number" list="batch_number">
                                        <datalist id="batch_number">
                                            <?php
                                            $stmt = $conn->prepare("SELECT DISTINCT batch_number FROM `tbl_stock`");
                                            $stmt->execute();
                                            $batches = $stmt->fetchAll();
                                            foreach (
                                                $batches

                                                as $batch
                                            ) {
                                            ?>
                                                <option value="<?= $batch['batch_number'] ?>">
                                                <?php
                                            }
                                                ?>
                                        </datalist>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-primary" id="search">Search</button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="stock-list">
                                                <thead class="bg-primary text-white">
                                                </thead>
                                                <tfoot>
                                                    <tr>
                                                        <th colspan="5" style="text-align:right">Total:</th>
                                                        <th id="total-stock"></th>
                                                        <th></th>
                                                        <th id="total-value"></th>
                                                        <th></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php include './includes/layouts/dash-footer.php'; ?>
                    <div class="content-backdrop fade"></div>
                </div>
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <?php include './includes/layouts/scripts.php'; ?>
    <script>
        $(function() {
            let table = $('#stock-list').DataTable({
                "processing": true,
                "serverSide": true,
                "searching": false,
                "responsive": true,
                "ajax": {
                    url: 'stock-list',
                    type: 'POST',
                    data: function(d) {
                        d.stock_list = true;
                        d.product = $('#product').val();
                        d.category = $('#category').val();
                        d.batch_number = $('#batch_number').val();
                    }
                },
                "columns": [{
                        "data": "product_id",
                        "visible": false,
                        "searchable": false
                    },
                    {
                        "data": "sl_no",
                        "title": "Sl. No.",
                        "orderable": false
                    },
                    {
                        "data": "batch_number",
                        "title": "Batch Number",
                        "orderable": false
                    },
                    {
                        "data": "product_name",
                        "title": "Product Name",
                        "orderable": false
                    },
                    {
                        "data": "supplier_name",
                        "title": "Supplier",
                        "orderable": false
                    },
                    {
                        "data": "warehouse_name",
                        "title": "Warehouse",
                        "orderable": false,
                        "className": "text-center"
                    },
                    {
                        "data": "quantity",
                        "title": "Stock",
                        "orderable": false
                    },
                    {
                        "data": "unit_cost_price",
                        "title": "Unit Price",
                        "orderable": false,
                        "render": function(data) {
                            return rupee(data);
                        }
                    },
                    {
                        "data": "total_value",
                        "title": "Total Value",
                        "orderable": false,
                        "render": function(data) {
                            return rupee(data);
                        }
                    },
                    {
                        "data": "added_date",
                        "title": "Added Date",
                        "orderable": false
                    },
                    {
                        "data": "action",
                        "title": "Action",
                        "orderable": false
                    }
                ],
                "footerCallback": function(row, data, start, end, display) {
                    let totalStock = 0;
                    let totalValue = 0;

                    for (let i = 0; i < data.length; i++) {
                        totalStock += parseFloat(data[i].stock) || 0;
                        totalValue += parseFloat(data[i].total_value) || 0;
                    }

                    $('#total-stock').html(totalStock);
                    $('#total-value').html(rupee(totalValue));
                }
            });

            $('#search').on('click', function() {
                table.ajax.reload();
            });

            $("#category").selectize({
                create: false,
                sortField: 'text',
                dropdownParent: 'body',
                onChange: function(value) {
                    table.ajax.reload();
                    $.ajax({
                        url: 'stock-list',
                        type: 'POST',
                        data: {
                            category: value,
                            getProducts: true
                        },
                        success: function(response) {
                            try {
                                let data = JSON.parse(response);
                                let options = '<option value="">Select Product</option>';
                                $('#product').selectize()[0].selectize.destroy();
                                data.forEach(function(product) {
                                    options += `<option value="${product.product_id}">${product.product_name}</option>`;
                                });
                                $('#product').html(options);
                                $('#product').selectize({
                                    create: false,
                                    sortField: 'text',
                                    dropdownParent: 'body',
                                    onChange: function(value) {
                                        table.ajax.reload();
                                    }
                                });
                            } catch (e) {
                                console.error(e);
                            }
                        }
                    })
                }
            });

            $('#product').selectize({
                create: false,
                sortField: 'text',
                dropdownParent: 'body',
                onChange: function(value) {
                    table.ajax.reload();
                }
            });

            $('#search').on('click', function() {
                table.ajax.reload();
            });

            window.reloadPage = function() {
                if (window.currentDialog) {
                    window.currentDialog.close();
                }
                table.ajax.reload();
            }
        });

        function stockReport(batch_number, product_id, product_name) {
            window.currentDialog = $.dialog({
                title: 'Stock Report for ' + product_name + ' - ' + batch_number,
                columnClass: 'xl',
                content: `<div class="card" id="stock-report-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="hidden" name="stock_report_product_id" id="stock_report_product_id">
                                        <input type="hidden" name="stock_report_batch_number" id="stock_report_batch_number">
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="stock-report">
                                                <thead class="bg-primary text-white">
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`,
                onContentReady: function() {
                    $('#stock_report_product_id').val(product_id);
                    $('#stock_report_batch_number').val(batch_number);
                    $('#stock-report').DataTable({
                        "processing": true,
                        "serverSide": true,
                        "searching": false,
                        "responsive": true,
                        "ajax": {
                            url: 'stock-list',
                            type: 'POST',
                            data: function(d) {
                                d.stock_report = true;
                                d.product_id = product_id;
                                d.batch_number = batch_number;
                            }
                        },
                        "columns": [{
                                "data": "sl_no",
                                "title": "Sl. No.",
                                "orderable": false,
                                "width": '7%'
                            },
                            {
                                "data": "last_updated",
                                "title": "Updated",
                                "orderable": false,
                                "width": '18%'
                            },
                            {
                                "data": "quantity_change",
                                "title": "Quantity Change",
                                "orderable": false
                            },
                            {
                                "data": "transaction_type",
                                "title": "Transaction Type",
                                "orderable": false
                            },
                            {
                                "data": "order_reference",
                                "title": "Order Reference",
                                "orderable": false
                            },
                            {
                                "data": "notes",
                                "title": "Notes",
                                "orderable": false
                            }
                        ]
                    });
                }
            });
        }

        function manageStock(product_id, batch_number, product_name) {
            window.currentDialog = $.dialog({
                title: 'Manage Stock for Product: ' + product_name + ' (' + batch_number + ')',
                columnClass: 'm',
                content: `url:stock-list?manageStock=true&product_id=${product_id}&batch_number=${batch_number}`,
            });
        }

        function assignWarehouse(product_id, batch_number, product_name) {
            window.currentDialog = $.dialog({
                title: 'Assign Warehouse & Location for Product: ' + product_name + ' (' + batch_number + ')',
                columnClass: 'm',
                content: `url:stock-list?assignWarehouse=true&product_id=${product_id}&batch_number=${batch_number}`,
                onContentReady: function() {
                    // Initialize selectize after content loads
                    $('#warehouse_id, #location_id').selectize({
                        create: false,
                        sortField: 'text',
                        dropdownParent: 'body'
                    });
                }
            });
        }

        function moveToLocation(product_id, batch_number, product_name) {
            window.currentDialog = $.dialog({
                title: 'Move to Location for Product: ' + product_name + ' (' + batch_number + ')',
                columnClass: 'm',
                content: `url:stock-list?moveToLocation=true&product_id=${product_id}&batch_number=${batch_number}`,
                onContentReady: function() {
                    // Initialize selectize after content loads
                    $('#warehouse_id, #location_id').selectize({
                        create: false,
                        sortField: 'text',
                        dropdownParent: 'body'
                    });
                }
            });
        }
    </script>
</body>

</html>