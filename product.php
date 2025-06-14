<?php
require_once 'includes/config/after-login.php';
$title = 'Add Product';
$form_action = 'add';
if (isset($_POST['submit_product'])) {
    $form_action = filtervar($_POST['form_action']);
    $id = filtervar($_POST['id']);
    $product_name = filtervar($_POST['product_name']);
    $supplier_id = filtervar($_POST['supplier_id']);
    $category = filtervar($_POST['category']);
    $purchase_price = filtervar($_POST['purchase_price']);
    $selling_price = filtervar($_POST['selling_price']);
    $quantity = filtervar($_POST['quantity']);
    $added_date = filtervar($_POST['added_date']);
    $description = filtervar($_POST['description']);
    $gst_type = filtervar($_POST['gst_type']);
    $gst_rate = filtervar($_POST['gst_rate']);
    $hsn_code = filtervar($_POST['hsn_code']);

    if ($form_action == 'add') {
        $res = addProduct($supplier_id, $product_name, $category, $purchase_price, $selling_price, $quantity, $description, $gst_type, $gst_rate, $hsn_code);
    } else {
        $res = updateProduct($id, $supplier_id, $product_name, $category, $purchase_price, $selling_price, $quantity, $description, $gst_type, $gst_rate, $hsn_code);
    }

    if ($res) {
        if ($form_action == 'add') {
            $res = ['status' => 'success', 'message' => 'Product added successfully', 'redirect' => 'product'];
        } else {
            $res = ['status' => 'success', 'message' => 'Product updated successfully', 'redirect' => 'product-list'];
        }
    } else {
        $res = ['status' => 'error', 'message' => 'Failed to add supplier'];
    }

    echo json_encode($res);
    exit;
}
if (isset($_GET['id'])) {
    $id = filtervar($_GET['id']);
    $row = getProductById($id);
    $title = 'Edit Product';
    $form_action = 'edit';
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
                                        value="<?= isset($row['product_id']) ? $row['product_id'] : '' ?>">

                                    <div class="row">
                                        <!-- Product Name -->
                                        <div class="form-group col-md-3">
                                            <label for="product_name">Product Name</label>
                                            <input type="text" class="form-control" id="product_name"
                                                name="product_name" placeholder="Enter Product Name"
                                                value="<?php isset($row['product_name']) ? special_echo($row['product_name']) : '' ?>"
                                                required>
                                        </div>

                                        <!-- Supplier ID -->
                                        <div class="form-group col-md-3">
                                            <label for="supplier_id">Supplier</label>
                                            <select name="supplier_id" id="supplier_id" class="form-select" required>
                                                <option value="">Select Supplier</option>
                                                <?php
                                                $suppliers = getSuppliers();
                                                foreach ($suppliers as $supplier) {
                                                    $selected = isset($row['supplier_id']) && $row['supplier_id'] == $supplier['supplier_id'] ? 'selected' : '';
                                                    echo '<option value="' . $supplier['supplier_id'] . '" ' . $selected . '>' . html_entity_decode($supplier['supplier_name']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Category -->
                                        <div class="form-group col-md-3">
                                            <label for="category">Category</label>
                                            <select name="category" id="category" class="form-select" required>
                                                <option value="">Select Category</option>
                                                <?php
                                                $categories = getAllProductCategories();
                                                foreach ($categories as $category) {
                                                    $selected = isset($row['category']) && $row['category'] == $category['category_id'] ? 'selected' : '';
                                                    echo '<option value="' . $category['category_id'] . '" ' . $selected . '>' . html_entity_decode($category['category_name']) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <!-- Purchase Price -->
                                        <div class="form-group col-md-3">
                                            <label for="purchase_price">Purchase Price</label>
                                            <input type="number" class="form-control" id="purchase_price"
                                                name="purchase_price" placeholder="Enter Purchase Price"
                                                value="<?= isset($row['purchase_price']) ? round($row['purchase_price'], 2) : '' ?>"
                                                required>
                                        </div>

                                        <!-- Selling Price -->
                                        <div class="form-group col-md-3">
                                            <label for="selling_price">Selling Price</label>
                                            <input type="number" class="form-control" id="selling_price"
                                                name="selling_price" placeholder="Enter Selling Price"
                                                value="<?= isset($row['selling_price']) ? round($row['selling_price'], 2) : '' ?>"
                                                step="0.01" required>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="form-group col-md-3 d-none">
                                            <label for="quantity">Quantity</label>
                                            <input type="number" class="form-control" id="quantity" name="quantity"
                                                placeholder="Enter Quantity"
                                                value="<?= isset($row['quantity']) ? special_echo($row['quantity']) : '' ?>">
                                        </div>

                                        <!-- Added Date -->
                                        <div class="form-group col-md-3">
                                            <label for="added_date">Added Date</label>
                                            <input type="text" class="form-control datepicker" id="added_date"
                                                name="added_date"
                                                value="<?= isset($row['added_date']) ? special_echo($row['added_date']) : '' ?>"
                                                placeholder="Select Date" required>
                                        </div>

                                        <!-- Description -->
                                        <div class="form-group col-md-12">
                                            <label for="description">Description</label>
                                            <textarea class="form-control" id="description" name="description"
                                                placeholder="Enter Product Description" rows="3"
                                                required><?= isset($row['description']) ? special_echo($row['description']) : '' ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row gst-group">
                                        <div class="col-md-12">
                                            <h5>GST Details</h5>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label for="gst_type">GST Type</label>
                                            <select name="gst_type" id="gst_type" class="form-select" required>
                                                <option value="">Select GST Type</option>
                                                <option value="1"
                                                    <?= isset($row['gst_type']) && $row['gst_type'] == 1 ? 'selected' : '' ?>>
                                                    CGST/SGST</option>
                                                <option value="2"
                                                    <?= isset($row['gst_type']) && $row['gst_type'] == 2 ? 'selected' : '' ?>>
                                                    IGST</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label for="gst_rate">GST Rate (%)</label>
                                            <input type="text" class="form-control decimalInput" id="gst_rate"
                                                name="gst_rate" placeholder="Enter GST Rate" step="0.01"
                                                value="<?= isset($row['gst_rate']) ? round($row['gst_rate'], 2) : '' ?>"
                                                required>
                                        </div>

                                        <div class="col-md-3 form-group">
                                            <label for="hsn_code">HSN/SAC Code</label>
                                            <input type="text" class="form-control" id="hsn_code" name="hsn_code"
                                                placeholder="Enter HSN/SAC Code"
                                                value="<?= isset($row['hsn_code']) ? special_echo($row['hsn_code']) : '' ?>"
                                                required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <button type="submit" name="submit_product"
                                                class="btn btn-primary">Submit</button>
                                        </div>
                                    </div>
                                </form>

                                <?php if (isset($row) && $form_action === 'edit'): 
                                    $stock_items = getStockByProductId($row['product_id']);
                                    if (!empty($stock_items)):
                                ?>                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-header p-3 border-bottom d-flex justify-content-between align-items-center">
                                                <h5 class="card-title mb-0">Product Location Information</h5>
                                                <a href="location-history.php?product_id=<?= $row['product_id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="bx bx-history me-1"></i> View Location History
                                                </a>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="bg-light">
                                                            <tr>
                                                                <th>Batch Number</th>
                                                                <th>Quantity</th>
                                                                <th>Warehouse</th>
                                                                <th>Location</th>
                                                                <th>Added Date</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach($stock_items as $item): ?>
                                                                <tr>
                                                                    <td><?= $item['batch_number'] ?></td>
                                                                    <td><?= $item['quantity'] ?></td>
                                                                    <td><?= !empty($item['warehouse_name']) ? html_entity_decode($item['warehouse_name']) : 'Not assigned' ?></td>
                                                                    <td>
                                                                        <?php 
                                                                        if (!empty($item['location_name']) && !empty($item['location_type'])) {
                                                                            echo $item['location_name'] . ' (' . $item['location_type'] . ')';
                                                                        } else {
                                                                            echo 'Not assigned';
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td><?= date('d M Y', strtotime($item['added_on'])) ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; endif; ?>
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
            <?php
            if (isset($row['postal_code'])) {
                echo '$("#postal_code").trigger("input");';
            }
            ?>
        })
    </script>
</body>

</html>