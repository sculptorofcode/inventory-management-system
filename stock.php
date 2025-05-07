<?php
require_once 'includes/config/after-login.php';
$title = 'Add Stock';
$form_action = 'add';

include '404.php';
die();

if (isset($_POST['stock'])) {
    $form_action = filtervar($_POST['form_action']);
    $id = filtervar($_POST['id']);

    $productId = filtervar($_POST['product_id']);
    $batch_number = filtervar($_POST['batch_number']);
    $quantity = filtervar($_POST['quantity']);
    $location = filtervar($_POST['location']);
    $supplierId = filtervar($_POST['supplier_id']);
    $unitCostPrice = filtervar($_POST['unit_cost_price']);

    if ($form_action == 'add') {
        $sql = "SELECT * FROM `tbl_stock` WHERE product_id = :product_id AND batch_number = :batch_number";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
        $stmt->bindParam(':batch_number', $batch_number, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $res = ['status' => 'error', 'message' => 'Stock already exists'];
            echo json_encode($res);
            exit;
        }

        $sql = "INSERT INTO `tbl_stock` SET product_id = :product_id, batch_number = :batch_number, quantity = :quantity, location = :location, supplier_id = :supplier_id, unit_cost_price = :unit_cost_price";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
        $stmt->bindParam(':batch_number', $batch_number, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':location', $location, PDO::PARAM_STR);
        $stmt->bindParam(':supplier_id', $supplierId, PDO::PARAM_STR);
        $stmt->bindParam(':unit_cost_price', $unitCostPrice, PDO::PARAM_STR);
        $result = $stmt->execute();
    }
    if ($result === true) {

        $prev_stock = getProductById($productId);
        $prev_quantity = $prev_stock['quantity'];
        $user_id = $userdata['customer_id'];

        $sql = "INSERT `tbl_stock_transactions` SET `product_id`=':product_id',`quantity_change`=':quantity',`previous_quantity`=':previous_quantity',`transaction_type`=':transaction_type',`notes`=':notes',`user_id`=':user_id',`transaction_location`=':transaction_location',`order_reference`=':order_reference'";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':previous_quantity', $prev_quantity, PDO::PARAM_INT);
        $stmt->bindParam(':transaction_type', 'in', PDO::PARAM_STR);
        $stmt->bindParam(':notes', 'Stock added', PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':transaction_location', $location, PDO::PARAM_STR);
        $stmt->bindParam(':order_reference', $batch_number, PDO::PARAM_STR);
        $result = $stmt->execute();

        $sql = "UPDATE `tbl_products` SET quantity = quantity + :quantity WHERE product_id = :product_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $productId, PDO::PARAM_STR);
        $res = $stmt->execute();
        if ($res === true) {
            $res = ['status' => 'success', 'message' => 'Stock added successfully', 'redirect' => 'stock'];
        } else {
            $res = ['status' => 'error', 'message' => $res];
        }
    } else {
        $res = ['status' => 'error', 'message' => $result];
    }
    echo json_encode($res);
    exit;
}

if (isset($_GET['id'])) {
    $id = filtervar($_GET['id']);
    $sql = "SELECT * FROM `tbl_stock` WHERE stock_id = :stock_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':stock_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $title = 'Edit Supplier';
    $form_action = 'edit';
}

if (isset($_POST['getUnitCostPrice'])) {
    $productId = filtervar($_POST['productId']);
    if ($productId) {

        $product = getProductById($productId);
        $stock_count = getCount('tbl_stock', ['product_id' => $productId]);
        $batch_number = generateUniqueBatchNumber('B', intval($stock_count) + 1);
        echo json_encode(['unit_cost_price' => round($product['purchase_price'], 2), 'batch_number' => $batch_number, 'supplier_id' => $product['supplier_id']]);
    } else {
        echo json_encode(['unit_cost_price' => 0, 'batch_number' => '']);
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
                            <div class="card-header p-3 border-bottom">
                                <h4 class="card-title mb-0"><?= $title ?></h4>
                            </div>
                            <div class="card-body py-3">
                                <form action="" method="post" class="form">
                                    <input type="hidden" name="form_action" value="<?= $form_action ?>">
                                    <input type="hidden" name="id"
                                        value="<?= isset($row['stock_id']) ? $row['stock_id'] : '' ?>">

                                    <div class="row">
                                        <!-- Product ID -->
                                        <div class="form-group col-md-3">
                                            <label for="product_id">Product ID</label>
                                            <?php
                                            if (isset($row['product_id'])) {
                                                $product = getProductById($row['product_id']);
                                                echo '<input type="text" class="form-control" id="product_id" name="product_id" value="' . $product['product_id'] . '" placeholder="Enter Product ID" required readonly>';
                                            } else {
                                                $products = getAllProducts();
                                                echo '<select class="form-select" id="product_id" name="product_id" required>';
                                                echo '<option value="">Select Product</option>';
                                                foreach ($products as $product) {
                                                    echo '<option value="' . $product['product_id'] . '">' . html_entity_decode($product['product_name']) . '</option>';
                                                }
                                                echo '</select>';
                                            }
                                            ?>
                                        </div>

                                        <!-- Batch Number -->
                                        <div class="form-group col-md-3">
                                            <label for="batch_number">Batch Number</label>
                                            <input type="text" class="form-control" id="batch_number"
                                                name="batch_number"
                                                value="<?= isset($row['batch_number']) ? $row['batch_number'] : '' ?>"
                                                placeholder="Enter Batch Number" required>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="form-group col-md-3">
                                            <label for="quantity">Quantity</label>
                                            <input type="number" class="form-control numInput" id="quantity"
                                                name="quantity"
                                                value="<?= isset($row['quantity']) ? $row['quantity'] : '' ?>"
                                                placeholder="Enter Quantity" required>
                                        </div>

                                        <!-- Location -->
                                        <div class="form-group col-md-3">
                                            <label for="location">Location</label>
                                            <input type="text" class="form-control" id="location" name="location"
                                                value="<?= isset($row['location']) ? $row['location'] : '' ?>"
                                                placeholder="Enter Location">
                                        </div>

                                        <!-- Supplier ID -->
                                        <div class="form-group col-md-3">
                                            <label for="supplier_id">Supplier</label>
                                            <?php
                                            $suppliers = getSuppliers();
                                            echo '<select class="form-select" id="supplier_id" name="supplier_id" required>';
                                            echo '<option value="">Select Supplier</option>';
                                            foreach ($suppliers as $supplier) {
                                                echo '<option value="' . $supplier['supplier_id'] . '">' . html_entity_decode($supplier['supplier_name']) . '</option>';
                                            }
                                            echo '</select>';
                                            ?>
                                        </div>

                                        <!-- Unit Cost Price -->
                                        <div class="form-group col-md-3">
                                            <label for="unit_cost_price">Unit Cost Price</label>
                                            <input type="text" class="form-control" id="unit_cost_price"
                                                name="unit_cost_price"
                                                value="<?= isset($row['unit_cost_price']) ? $row['unit_cost_price'] : '' ?>"
                                                placeholder="Enter Unit Cost Price" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <button type="submit" name="stock" class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </form>
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
            $("#product_id").change(function() {
                let productId = $(this).val();
                $.ajax({
                    url: 'stock',
                    type: 'POST',
                    data: {
                        getUnitCostPrice: true,
                        productId: productId
                    },
                    success: function(response) {
                        try {
                            let data = JSON.parse(response);
                            $("#unit_cost_price").val(data.unit_cost_price);
                            $("#batch_number").val(data.batch_number).attr('readonly', true)
                                .addClass('valid');
                            $("#supplier_id").val(data.supplier_id);
                        } catch (error) {
                            console.error(error);
                            toastr && toastr.error('Error getting unit cost price');
                        }
                    }
                });
            });
        })
    </script>
</body>

</html>