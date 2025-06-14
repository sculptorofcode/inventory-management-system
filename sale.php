<?php
require_once 'includes/config/after-login.php';
$title = 'Add Sale Order';
$form_action = 'add';

if (isset($_POST['sale'])) {
    // Retrieve form data using custom filtervar function
    $customer_id = filtervar($_POST['customer_id']);
    $order_date = filtervar($_POST['order_date']);
    $shipping_address = filtervar($_POST['shipping_address']);
    $total_products = filtervar($_POST['total_products']);
    $total_quantity = filtervar($_POST['total_quantity']);
    $total_cost_price = filtervar($_POST['total_cost_price']);
    $total_gst = filtervar($_POST['total_gst']);
    $discount = filtervar($_POST['discount']);
    $total_amount = filtervar($_POST['total_amount']);
    $paid_amount = filtervar($_POST['paid_amount']);
    $due_amount = filtervar($_POST['due_amount']);
    $pay_mode = filtervar($_POST['pay_mode']);
    $notes = filtervar($_POST['notes']);
    $user_id = $userdata['customer_id']; // Get the current user ID

    $conn->beginTransaction();
    try {
        $inv_number = generateUniqueInvoiceNumber('SO', 'tbl_sale_order', 'order_id', 4);
        $stmt = $conn->prepare("INSERT INTO `tbl_sale_order` SET 
            customer_id = :customer_id, 
            inv_number = :inv_number,
            order_date = :order_date, 
            shipping_address = :shipping_address, 
            total_products = :total_products, 
            total_quantity = :total_quantity, 
            total_cost_price = :total_cost_price, 
            total_gst = :total_gst, 
            discount = :discount, 
            total_amount = :total_amount, 
            paid_amount = :paid_amount, 
            due_amount = :due_amount, 
            pay_mode = :pay_mode, 
            status = 'confirmed',
            notes = :notes");

        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->bindParam(':inv_number', $inv_number);
        $stmt->bindParam(':order_date', $order_date);
        $stmt->bindParam(':shipping_address', $shipping_address);
        $stmt->bindParam(':total_products', $total_products);
        $stmt->bindParam(':total_quantity', $total_quantity);
        $stmt->bindParam(':total_cost_price', $total_cost_price);
        $stmt->bindParam(':total_gst', $total_gst);
        $stmt->bindParam(':discount', $discount);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':paid_amount', $paid_amount);
        $stmt->bindParam(':due_amount', $due_amount);
        $stmt->bindParam(':pay_mode', $pay_mode);
        $stmt->bindParam(':notes', $notes);

        $result = $stmt->execute();

        if($result) {
            $sale_order_id = $conn->lastInsertId();

            $stmt = $conn->prepare("INSERT INTO `tbl_customer_payments` 
                                            SET 
                                                customer_id = :customer_id, 
                                                sale_order_id = :sale_order_id, 
                                                amount = :amount, 
                                                payment_method = :pay_mode, 
                                                notes = :notes, 
                                                payment_date = :order_date, 
                                                payment_status = 'completed'");

            $stmt->bindParam(':customer_id', $customer_id);
            $stmt->bindParam(':sale_order_id', $sale_order_id);
            $stmt->bindParam(':amount', $paid_amount);
            $stmt->bindParam(':pay_mode', $pay_mode);
            $stmt->bindParam(':notes', $notes);
            $stmt->bindParam(':order_date', $order_date);

            $stmt->execute();
            
            // Get customer info for shipping address
            $customer = getCustomerById($customer_id);
            if ($shipping_address == '') {
                $shipping_address = $customer['address'] ?? 'Customer address not available';
            }
            
            // Record status log
            $stmt = $conn->prepare("INSERT INTO `tbl_sale_order_status_log` SET 
                                    order_id = :order_id, 
                                    old_status = :old_status, 
                                    new_status = :new_status, 
                                    remarks = :remarks, 
                                    changed_by = :changed_by, 
                                    changed_at = NOW()");
            $stmt->bindParam(':order_id', $sale_order_id);
            $stmt->bindValue(':old_status', 'pending');
            $stmt->bindValue(':new_status', 'confirmed');
            $stmt->bindValue(':remarks', 'Order created and stock allocated');
            $stmt->bindParam(':changed_by', $user_id);
            $stmt->execute();
            
            $now = date('Y-m-d H:i:s');

            foreach ($_POST['product_id'] as $index => $product_id) {
                $quantity = filtervar($_POST['quantity'][$index]);
                $unit_cost_price = filtervar($_POST['unit_cost_price'][$index]);
                $sale_price = filtervar($_POST['sale_price'][$index]);
                $gst_type = filtervar($_POST['gst_type'][$index]);
                $gst_rate = filtervar($_POST['gst_rate'][$index]);
                $gst_amount = filtervar($_POST['gst_amount'][$index]);
                $sub_total = filtervar($_POST['sub_total'][$index]);
                $total = filtervar($_POST['total'][$index]);
                
                // Use the new stock deduction function to handle all stock operations
                $stockResult = deductStockForSale(
                    $product_id,
                    $quantity,
                    $inv_number,
                    $notes,
                    $user_id,
                    $shipping_address
                );
                
                $batch_number = $stockResult['batch_number'];

                $stmt = $conn->prepare("INSERT INTO `tbl_sale_order_details` SET 
                    sale_order_id = :sale_order_id, 
                    product_id = :product_id, 
                    batch_number = :batch_number,
                    quantity = :quantity, 
                    unit_cost_price = :unit_cost_price, 
                    sale_price = :sale_price, 
                    gst_type = :gst_type,
                    gst_rate = :gst_rate, 
                    gst_amount = :gst_amount, 
                    sub_total = :sub_total, 
                    total = :total");

                $stmt->bindParam(':sale_order_id', $sale_order_id);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->bindParam(':batch_number', $batch_number);
                $stmt->bindParam(':quantity', $quantity);
                $stmt->bindParam(':unit_cost_price', $unit_cost_price);
                $stmt->bindParam(':sale_price', $sale_price);
                $stmt->bindParam(':gst_type', $gst_type);
                $stmt->bindParam(':gst_rate', $gst_rate);
                $stmt->bindParam(':gst_amount', $gst_amount);
                $stmt->bindParam(':sub_total', $sub_total);
                $stmt->bindParam(':total', $total);
                $stmt->execute();
            }
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Sale order added successfully with stock deduction', 'redirect' => 'sale']);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Failed to add sale order: ' . $e->getMessage()]);
    }
    exit;
}

if (isset($_POST['getUnitCostPrice'])) {
    $productId = filtervar($_POST['productId']);
    if ($productId) {
        $product = getProductById($productId);
        $stmt = $conn->prepare("SELECT * FROM `tbl_stock` WHERE product_id = :product_id AND quantity > 0 ORDER BY added_on ASC LIMIT 1");
        $stmt->execute(['product_id' => $productId]);
        if ($stmt->rowCount() === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Product not in stock']);
            exit;
        }
        $stock_count = $stmt->fetch();
        $product['batch_number'] = $stock_count['batch_number'];
        $product['sale_price'] = round($stock_count['unit_cost_price'], 2);
        $product['gst_rate'] = round($product['gst_rate'], 2);
        $product['selling_price'] = round($product['selling_price'], 2);
        // Also return the available stock quantity for validation
        $product['available_stock'] = $stock_count['quantity'];
        echo json_encode(['status' => 'success', 'data' => $product]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    }
    exit;
}

if (isset($_POST['checkStockAvailability'])) {
    $productId = filtervar($_POST['productId']);
    $quantity = filtervar($_POST['quantity']);
    
    if ($productId && $quantity) {
        $stockInfo = checkSufficientStock($productId, $quantity);
        
        if ($stockInfo) {
            // Product has sufficient stock
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'available' => true,
                    'product_name' => $stockInfo['product_name'] ?? '',
                    'available_quantity' => $stockInfo['quantity'] ?? $stockInfo['available_quantity'],
                    'needs_multiple_batches' => isset($stockInfo['needs_multiple_batches']) ? $stockInfo['needs_multiple_batches'] : false
                ]
            ]);
        } else {
            // Get available quantity
            $stmt = $conn->prepare("SELECT SUM(s.quantity) as total_available, p.product_name 
                                   FROM tbl_stock s
                                   JOIN tbl_products p ON s.product_id = p.product_id
                                   WHERE s.product_id = :product_id");
            $stmt->bindParam(':product_id', $productId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'error',
                'data' => [
                    'available' => false,
                    'product_name' => $result['product_name'] ?? 'Unknown product',
                    'available_quantity' => $result['total_available'] ?? 0,
                    'required_quantity' => $quantity,
                    'message' => 'Insufficient stock for ' . ($result['product_name'] ?? 'this product') . 
                               '. Available: ' . ($result['total_available'] ?? 0) . ', Required: ' . $quantity
                ]
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product or quantity']);
    }
    exit;
}

if (isset($_POST['getProducts'])) {
    $supplierId = filtervar($_POST['supplierId']);
    if ($supplierId) {
        $products = getProductsBySupplierId($supplierId);
        echo json_encode($products);
    } else {
        echo json_encode([]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
      data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
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
                                    <div class="form-group col-md-3">
                                        <label for="customer_id">Customer</label>
                                        <select name="customer_id" id="customer_id" class="selectize" required>
                                            <option value="">Select Customer</option>
                                            <?php
                                            $customers = getAllCustomers();
                                            foreach ($customers as $customer) {
                                                $selected = isset($row) && $row['customer_id'] == $customer['customer_id'] ? 'selected' : '';
                                                echo "<option value='{$customer['customer_id']}' $selected>{$customer['full_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="order_date">Order Date</label>
                                        <input type="date" name="order_date" id="order_date"
                                               class="form-control datepicker"
                                               value="<?= isset($row['order_date']) ? $row['order_date'] : date('Y-m-d') ?>"
                                               required>
                                    </div>
                                    <div class="form-group col-md-6 d-none">
                                        <label for="shipping_address">Shipping Address</label>
                                        <input type="text" name="shipping_address" id="shipping_address"
                                               class="form-control"
                                               value="<?= isset($row['shipping_address']) ? $row['shipping_address'] : '' ?>"
                                               placeholder="Enter shipping address">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <h5>Product Details</h5>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="table-responsive-xxl">
                                            <table class="table table-bordered">
                                                <thead class="text-nowrap bg-primary text-white text-center">
                                                <tr>
                                                    <th style="width: 18%;">Product & Quantity</th>
                                                    <th>Price</th>
                                                    <th>GST</th>
                                                    <th>HSN Code & GST Amount</th>
                                                    <th>Sub Total & Total</th>
                                                    <th style="width: 7%;">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody id="product_details">
                                                <?php
                                                $entries = isset($row) ? getTable($table_sale_orders_details, ['sale_order_id' => $row['order_id']]) : [];
                                                $i = 0;
                                                do {
                                                    $entry = $entries[$i] ?? null;
                                                    ?>
                                                    <tr class="clone_row">
                                                        <td>
                                                            <select name="product_id[]" id="product_id_1"
                                                                    class="selectize product_id mb-3" required>
                                                                <option value="">Select Product</option>
                                                                <?php
                                                                $sql = 'SELECT * 
                                                                        FROM `tbl_stock`
                                                                        LEFT JOIN `tbl_products` ON `tbl_stock`.`product_id` = `tbl_products`.`product_id` 
                                                                        WHERE tbl_stock.quantity > 0
                                                                        ORDER BY `tbl_stock`.`stock_id` DESC';
                                                                $query = $conn->prepare($sql);
                                                                $query->execute();
                                                                $products = $query->fetchAll(PDO::FETCH_ASSOC);
                                                                foreach ($products as $product) {
                                                                    $product['product_name'] = html_entity_decode($product['product_name']);
                                                                    $selected = $entry && $entry['product_id'] == $product['product_id'] ? 'selected' : '';
                                                                    $cost_price = rupee($product['unit_cost_price']);
                                                                    echo "<option value='{$product['product_id']}' $selected>{$product['product_name']} - $cost_price</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <input type="text" name="quantity[]" id="quantity_1"
                                                                   placeholder="Enter quantity"
                                                                   class="form-control quantity numInput"
                                                                   value="<?= $entry ? $entry['quantity'] : '' ?>"
                                                                   required>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="unit_cost_price[]"
                                                                   id="unit_cost_price_1"
                                                                   placeholder="Enter unit cost price"
                                                                   value="<?= $entry ? $entry['unit_cost_price'] : '' ?>"
                                                                   class="form-control unit_cost_price decimalInput mb-3"
                                                                   required readonly>
                                                            <input type="text" name="sale_price[]"
                                                                   id="sale_price_1"
                                                                   placeholder="Enter sale price"
                                                                   value="<?= $entry ? $entry['sale_price'] : '' ?>"
                                                                   class="form-control sale_price decimalInput"
                                                                   required>
                                                        </td>
                                                        <td>
                                                            <select name="gst_type[]" id="gst_type_1"
                                                                    class="form-select gst_type mb-3" required>
                                                                <option value="">Select GST Type</option>
                                                                <option value="1"
                                                                    <?= isset($entry['gst_type']) && $entry['gst_type'] == 1 ? 'selected' : '' ?>>
                                                                    CGST/SGST
                                                                </option>
                                                                <option value="2"
                                                                    <?= isset($entry['gst_type']) && $entry['gst_type'] == 2 ? 'selected' : '' ?>>
                                                                    IGST
                                                                </option>
                                                            </select>

                                                            <input type="text" name="gst_rate[]" id="gst_rate_1"
                                                                   placeholder="Enter GST Rate"
                                                                   value="<?= $entry ? $entry['gst_rate'] : '' ?>"
                                                                   class="form-control gst_rate decimalInput" required>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="hsn_code[]" id="hsn_code_1"
                                                                   placeholder="Enter HSN Code"
                                                                   value="<?= $entry ? $entry['hsn_code'] : '' ?>"
                                                                   class="form-control hsn_code mb-3" required>
                                                            <input type="text" name="gst_amount[]" id="gst_amount_1"
                                                                   placeholder="GST Amount"
                                                                   value="<?= $entry ? $entry['gst_amount'] : '' ?>"
                                                                   class="form-control gst_amount decimalInput" required
                                                                   readonly>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="sub_total[]" id="sub_total_1"
                                                                   placeholder="Sub total"
                                                                   value="<?= $entry ? $entry['sub_total'] : '' ?>"
                                                                   class="form-control sub_total mb-3" required
                                                                   readonly>

                                                            <input type="text" name="total[]" id="total_1"
                                                                   placeholder="Total"
                                                                   value="<?= $entry['total'] ?? '' ?>"
                                                                   class="form-control total" required readonly>

                                                        </td>
                                                        <td>
                                                            <div class="row">
                                                                <div class="col-md-12 text-nowrap">
                                                                    <button type="button"
                                                                            class="btn btn-primary btn-sm px-2 button-add add-row"
                                                                            id="add_product">
                                                                        <i class="fas fa-plus"></i>
                                                                    </button>
                                                                    <button type="button"
                                                                            class="btn btn-warning btn-sm px-2 button-add remove-row">
                                                                        <i
                                                                                class="fas fa-minus"></i></button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $i++;
                                                } while ($i < count($entries));
                                                ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md form-group">
                                        <label for="total_products">Total Products</label>
                                        <input type="text" name="total_products" id="total_products"
                                               class="form-control" placeholder="Enter total products"
                                               value="<?= isset($row) ? $row['total_products'] : '' ?>" required
                                               readonly>
                                    </div>
                                    <div class="col-md form-group">
                                        <label for="total_quantity">Total Quantity</label>
                                        <input type="text" name="total_quantity" id="total_quantity"
                                               class="form-control" placeholder="Enter total quantity"
                                               value="<?= isset($row) ? $row['total_quantity'] : '' ?>" required
                                               readonly>
                                    </div>
                                    <div class="col-md form-group">
                                        <label for="total_cost_price">Total Cost Price</label>
                                        <input type="text" name="total_cost_price" id="total_cost_price"
                                               class="form-control" placeholder="Enter total cost price"
                                               value="<?= isset($row) ? $row['total_cost_price'] : '' ?>" required
                                               readonly>
                                    </div>
                                    <div class="col-md form-group">
                                        <label for="total_gst">Total GST</label>
                                        <input type="text" name="total_gst" id="total_gst" class="form-control"
                                               placeholder="Enter total gst"
                                               value="<?= isset($row) ? $row['total_gst'] : '' ?>" required readonly>
                                    </div>
                                    <div class="col-md form-group">
                                        <label for="discount">Discount</label>
                                        <input type="text" name="discount" id="discount"
                                               class="form-control numInput calculateTotal"
                                               placeholder="Enter discount"
                                               value="<?= isset($row) ? $row['discount'] : '0' ?>" required>
                                    </div>
                                    <div class="col-md form-group">
                                        <label for="total_amount">Total Amount</label>
                                        <input type="text" name="total_amount" id="total_amount"
                                               class="form-control" placeholder="Enter total amount"
                                               value="<?= isset($row) ? $row['total_amount'] : '' ?>" required
                                               readonly>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label for="paid_amount">Paid Amount</label>
                                        <input type="text" name="paid_amount" id="paid_amount"
                                               class="form-control numInput calculateTotal"
                                               placeholder="Enter paid amount"
                                               value="<?= isset($row) ? $row['paid_amount'] : '' ?>">
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="due_amount">Due Amount</label>
                                        <input type="text" name="due_amount" id="due_amount"
                                               class="form-control numInput" placeholder="Enter due amount"
                                               value="<?= isset($row) ? $row['due_amount'] : '' ?>" readonly>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="pay_mode">Payment Mode</label>
                                        <select name="pay_mode" id="pay_mode" class="form-select">
                                            <option value="">Select Payment Mode</option>
                                            <?php
                                            foreach ($payment_mode as $value => $mode) {
                                                $selected = isset($row) && $row['pay_mode'] == $value ? 'selected' : '';
                                                echo "<option value='$value' $selected>$mode</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label for="notes">Notes</label>
                                        <textarea name="notes" id="notes" class="form-control"
                                                  placeholder="Enter notes"
                                                  rows="1"><?= isset($row) ? $row['notes'] : '' ?></textarea>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <button type="button" id="validate-stock-btn" class="btn btn-info me-2">Validate Stock</button>
                                        <button type="submit" name="sale" class="btn btn-primary" id="submit-btn" disabled>Submit</button>
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
    $(function () {
        new DynamicRow($("#product_details"), calculateTotal, calculateTotal);

        let selectedValues = {};
        // Store available stock for each product
        let availableStock = {};
        
        // Handle stock validation button
        $('#validate-stock-btn').on('click', function() {
            let isValid = true;
            let promises = [];
            
            // Check each product row
            $('.clone_row').each(function() {
                let $row = $(this);
                let productId = $row.find('.product_id').val();
                let quantity = parseInt($row.find('.quantity').val()) || 0;
                
                if (productId && quantity > 0) {
                    // Create a promise for this row's validation
                    let promise = new Promise((resolve, reject) => {
                        $.ajax({
                            url: 'sale',
                            type: 'POST',
                            data: {
                                checkStockAvailability: true,
                                productId: productId,
                                quantity: quantity
                            },
                            success: function(response) {
                                try {
                                    let result = JSON.parse(response);
                                    if (result.status === 'error') {
                                        toastr.error(result.data.message);
                                        $row.find('.quantity').addClass('is-invalid');
                                        isValid = false;
                                    } else {
                                        $row.find('.quantity').removeClass('is-invalid');
                                        if (result.data.needs_multiple_batches) {
                                            toastr.warning('Product "' + result.data.product_name + '" will use multiple batches for this quantity.');
                                        }
                                    }
                                    resolve();
                                } catch (error) {
                                    console.error(error);
                                    reject(error);
                                }
                            },
                            error: function(error) {
                                reject(error);
                            }
                        });
                    });
                    
                    promises.push(promise);
                }
            });
            
            // Wait for all validations to complete
            Promise.all(promises).then(() => {
                if (isValid) {
                    toastr.success('Stock validation successful! You can now submit the form.');
                    $('#submit-btn').prop('disabled', false);
                } else {
                    toastr.error('Please fix the stock quantity issues before submitting.');
                    $('#submit-btn').prop('disabled', true);
                }
            }).catch(() => {
                toastr.error('Error validating stock. Please try again.');
                $('#submit-btn').prop('disabled', true);
            });
        });

        $(document).on('change', 'select.product_id', function () {
            let $this = $(this);
            let productId = $(this).val();
            if (productId.length > 0) {
                if (Object.values(selectedValues).filter(value => value === productId).length > 0) {
                    toastr && toastr.error('Product already selected');
                    $this.val('');
                    $this.selectize()[0].selectize.setValue('');
                    return;
                }
                selectedValues[$this.attr('id')] = productId;
                $.ajax({
                    url: 'sale',
                    type: 'POST',
                    data: {
                        getUnitCostPrice: true,
                        productId: productId
                    },
                    success: function (response) {
                        try {
                            let data = JSON.parse(response);
                            if (data['status'] === 'success') {
                                data = data['data'];

                                let $row = $this.closest('.clone_row');
                                $row.find('.unit_cost_price').val(data['sale_price']);
                                $row.find('.sale_price').val(data['selling_price']);
                                $row.find('.gst_type').val(data['gst_type']);
                                $row.find('.gst_rate').val(data['gst_rate']);
                                $row.find('.hsn_code').val(data['hsn_code']);
                                
                                // Store available stock for this product and row
                                let rowIndex = $row.index();
                                availableStock[rowIndex] = {
                                    productId: productId,
                                    stock: data['available_stock']
                                };
                                
                                // Set max attribute on quantity input
                                $row.find('.quantity').attr('max', data['available_stock']);
                                $row.find('.quantity').attr('data-original-title', 'Available: ' + data['available_stock']);
                                $row.find('.quantity').attr('title', 'Available: ' + data['available_stock']);
                                $row.find('.quantity').tooltip({
                                    trigger: 'hover'
                                });
                                
                                // Reset validation state
                                $('#submit-btn').prop('disabled', true);
                                
                                calculateTotal();
                            } else {
                                toastr && toastr.error(data['message'] ?? 'Error getting unit cost price');
                            }
                        } catch (error) {
                            console.error(error);
                            toastr && toastr.error('Error getting unit cost price');
                        }
                    }
                });
            }
        });
    });

    $(document).on('input', '.clone_row, .calculateTotal', function () {
        // When any input changes, require re-validation
        $('#submit-btn').prop('disabled', true);
        calculateTotal();
    });
    
    // Validate quantity against available stock
    $(document).on('input', '.quantity', function() {
        let $row = $(this).closest('.clone_row');
        let rowIndex = $row.index();
        let quantity = parseInt($(this).val()) || 0;
        const maxQuantity = parseInt($(this).attr('max')) || 0;
        
        // Check if we have stock information for this row
        if (maxQuantity) {
            let max = maxQuantity;
            
            if (quantity > max) {
                toastr && toastr.error('Cannot exceed available stock of ' + max + ' units');
                $(this).val(max);
                calculateTotal();
            }
        }
    });

    function calculateTotal() {
        let totalProducts = 0;
        let totalQuantity = 0;
        let totalCostPrice = 0;
        let totalGST = 0;
        let totalAmount = 0;
        let discount = parseFloat($('#discount').val()) || 0;
        let paidAmount = parseFloat($('#paid_amount').val()) || 0;
        let dueAmount = 0;

        $('.clone_row').each(function () {
            let $this = $(this);
            if ($this.find('.product_id').val() === '') {
                return;
            }
            let quantity = $this.find('.quantity').val();
            let unitCostPrice = $this.find('.sale_price').val();
            let gstRate = $this.find('.gst_rate').val();
            let subTotal = quantity * unitCostPrice;
            let gst = (subTotal * gstRate) / 100;
            let total = subTotal + gst;
            totalProducts++;
            totalQuantity += parseInt(quantity) || 0;
            totalCostPrice += subTotal;
            totalGST += gst;
            totalAmount += total;
            $this.find('.sub_total').val(subTotal);
            $this.find('.total').val(total);
            $this.find('.gst_amount').val(gst);
        });

        if (discount > totalAmount) {
            $('#discount').val(0);
        } else {
            totalAmount -= discount;
        }

        totalAmount = Math.ceil(totalAmount);

        $('#total_products').val(totalProducts);
        $('#total_quantity').val(totalQuantity);
        $('#total_cost_price').val(totalCostPrice);
        $('#total_gst').val(totalGST);
        $('#total_amount').val(totalAmount);
        if (paidAmount > totalAmount) {
            $('#paid_amount').val(0);
            paidAmount = 0;
        }
        dueAmount = totalAmount - paidAmount;
        if (dueAmount < 0) {
            dueAmount = 0;
        }
        $('#due_amount').val(dueAmount);
    }
</script>
</body>

</html>