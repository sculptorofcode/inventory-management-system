<?php
require_once 'includes/config/after-login.php';
$title = 'Add Purchase Order';
$form_action = 'add';

if (isset($_POST['purchase'])) {
    $form_action = filtervar($_POST['form_action']);
    $id = filtervar($_POST['id']);

    $supplier_id = filtervar($_POST['supplier_id']);
    $order_date = filtervar($_POST['order_date']);
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

    $product_id_arr = array_filter($_POST['product_id']);
    $quantity_arr = $_POST['quantity'];
    $unit_cost_price_arr = $_POST['unit_cost_price'];
    $gst_type_arr = $_POST['gst_type'];
    $gst_rate_arr = $_POST['gst_rate'];
    $hsn_code_arr = $_POST['hsn_code'];
    $sub_total_arr = $_POST['sub_total'];
    $total_arr = $_POST['total'];

    if (count($product_id_arr) > 0) {
        try {
            $inv_number = generateUniqueInvoiceNumber('PO', 'tbl_purchase_order', 'order_id', 4);

            $conn->beginTransaction();

            $stmt = $conn->prepare("INSERT INTO `tbl_purchase_order` SET supplier_id = :supplier_id, inv_number = :inv_number, order_date = :order_date, total_products = :total_products, total_quantity = :total_quantity, total_cost_price = :total_cost_price, total_gst = :total_gst, discount = :discount, total_amount = :total_amount, paid_amount = :paid_amount, due_amount = :due_amount, pay_mode = :pay_mode, notes = :notes");
            $stmt->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT);
            $stmt->bindParam(':inv_number', $inv_number, PDO::PARAM_STR);
            $stmt->bindParam(':order_date', $order_date, PDO::PARAM_STR);
            $stmt->bindParam(':total_products', $total_products, PDO::PARAM_INT);
            $stmt->bindParam(':total_quantity', $total_quantity, PDO::PARAM_INT);
            $stmt->bindParam(':total_cost_price', $total_cost_price, PDO::PARAM_STR);
            $stmt->bindParam(':total_gst', $total_gst, PDO::PARAM_STR);
            $stmt->bindParam(':discount', $discount, PDO::PARAM_STR);
            $stmt->bindParam(':total_amount', $total_amount, PDO::PARAM_STR);
            $stmt->bindParam(':paid_amount', $paid_amount, PDO::PARAM_STR);
            $stmt->bindParam(':due_amount', $due_amount, PDO::PARAM_STR);
            $stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
            $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);

            $result = $stmt->execute();

            if ($result) {
                $purchase_order_id = $conn->lastInsertId();

                $stmt = $conn->prepare("INSERT INTO `tbl_supplier_payments` SET supplier_id = :supplier_id, purchase_order_id = :purchase_order_id, amount = :amount, payment_method = :pay_mode, notes = :notes, payment_date = :order_date, payment_status = 'completed'");
                $stmt->bindParam(':supplier_id', $supplier_id, PDO::PARAM_INT);
                $stmt->bindParam(':purchase_order_id', $purchase_order_id, PDO::PARAM_INT);
                $stmt->bindParam(':amount', $paid_amount, PDO::PARAM_STR);
                $stmt->bindParam(':pay_mode', $pay_mode, PDO::PARAM_STR);
                $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
                $stmt->bindParam(':order_date', $order_date, PDO::PARAM_STR);
                $result = $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO `tbl_purchase_order_details` SET purchase_order_id = :purchase_order_id, product_id = :product_id, quantity = :quantity, unit_cost_price = :unit_cost_price, gst_type = :gst_type, gst_rate = :gst_rate, hsn_code = :hsn_code, sub_total = :sub_total, total = :total");

                foreach ($product_id_arr as $key => $product_id) {
                    $stmt->bindParam(':purchase_order_id', $purchase_order_id, PDO::PARAM_INT);
                    $stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
                    $stmt->bindParam(':quantity', $quantity_arr[$key], PDO::PARAM_INT);
                    $stmt->bindParam(':unit_cost_price', $unit_cost_price_arr[$key], PDO::PARAM_STR);
                    $stmt->bindParam(':gst_type', $gst_type_arr[$key], PDO::PARAM_INT);
                    $stmt->bindParam(':gst_rate', $gst_rate_arr[$key], PDO::PARAM_STR);
                    $stmt->bindParam(':hsn_code', $hsn_code_arr[$key], PDO::PARAM_STR);
                    $stmt->bindParam(':sub_total', $sub_total_arr[$key], PDO::PARAM_STR);
                    $stmt->bindParam(':total', $total_arr[$key], PDO::PARAM_STR);

                    $result = $stmt->execute();

                    if (!$result) {
                        throw new Exception("Error adding purchase order details");
                    }
                }

                $conn->commit();
                $res = ['status' => 'success', 'message' => 'Purchase order added successfully', 'redirect' => 'purchase'];

            } else {
                throw new Exception("Error adding purchase order");
            }

        } catch (Exception $e) {
            $conn->rollBack();
            $res = ['status' => 'error', 'message' => $e->getMessage()];
        }
    } else {
        $res = ['status' => 'error', 'message' => 'Please add product details'];
    }

    echo json_encode($res);
    exit;
}

if (isset($_GET['id'])) {
    $id = filtervar($_GET['id']);
    $sql = "SELECT * FROM `tbl_purchase_order` WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        header('Location: purchase');
        exit;
    }
    $title = 'Edit Purchase Order';
    $form_action = 'edit';
}

if (isset($_POST['getUnitCostPrice'])) {
    $productId = filtervar($_POST['productId']);
    if ($productId) {
        $product = getProductById($productId);
        $stock_count = getCount('tbl_stock', ['product_id' => $productId]);
        $batch_number = generateUniqueBatchNumber('B', intval($stock_count) + 1);
        $product['batch_number'] = $batch_number;
        $product['purchase_price'] = round($product['purchase_price'], 2);
        $product['gst_rate'] = round($product['gst_rate'], 2);
        echo json_encode(['status' => 'success', 'data' => $product]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
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
                                        <div class="form-group col-md-3">
                                            <label for="supplier_id">Supplier</label>
                                            <select name="supplier_id" id="supplier_id" class="selectize" required>
                                                <option value="">Select Supplier</option>
                                                <?php
                                                $suppliers = getSuppliers();
                                                foreach ($suppliers as $supplier) {
                                                    $selected = isset($row) && $row['supplier_id'] == $supplier['supplier_id'] ? 'selected' : '';
                                                    echo "<option value='{$supplier['supplier_id']}' $selected>{$supplier['supplier_name']}</option>";
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
                                                    <thead class="text-nowrap">
                                                    <tr>
                                                        <th style="width: 18%;">Product</th>
                                                        <th>Quantity</th>
                                                        <th>Unit Cost Price</th>
                                                        <th>GST Type</th>
                                                        <th>GST Rate</th>
                                                        <th>HSN Code</th>
                                                        <th>Sub Total</th>
                                                        <th>Total</th>
                                                        <th style="width: 7%;">Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="product_details">
                                                    <?php
                                                    $entries = isset($row) ? getTable('tbl_purchase_order_details',['purchase_order_id' => $row['order_id']]) : [];
                                                    $i = 0;
                                                    do {
                                                        $entry = $entries[$i] ?? null;
                                                        ?>

                                                        <tr class="clone_row">
                                                            <td>
                                                                <select name="product_id[]" id="product_id_1"
                                                                        class="selectize product_id" required>
                                                                    <option value="">Select Product</option>
                                                                    <?php
                                                                    $products = getAllProducts();
                                                                    foreach ($products as $product) {
                                                                        $product['product_name'] = html_entity_decode($product['product_name']);
                                                                        $selected = $entry && $entry['product_id'] == $product['product_id'] ? 'selected' : '';
                                                                        echo "<option value='{$product['product_id']}' $selected>{$product['product_name']}</option>";
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </td>
                                                            <td>
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
                                                                       class="form-control unit_cost_price decimalInput" required>
                                                            </td>
                                                            <td>
                                                                <select name="gst_type[]" id="gst_type_1"
                                                                        class="form-select gst_type" required>
                                                                    <option value="">Select GST Type</option>
                                                                    <option value="1"
                                                                        <?= isset($entry['gst_type']) && $entry['gst_type'] == 1 ? 'selected' : '' ?>>
                                                                        CGST/SGST</option>
                                                                    <option value="2"
                                                                        <?= isset($entry['gst_type']) && $entry['gst_type'] == 2 ? 'selected' : '' ?>>
                                                                        IGST</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="gst_rate[]" id="gst_rate_1"
                                                                       placeholder="Enter GST Rate"
                                                                       value="<?= $entry ? $entry['gst_rate'] : '' ?>"
                                                                       class="form-control gst_rate decimalInput" required>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="hsn_code[]" id="hsn_code_1"
                                                                       placeholder="Enter HSN Code"
                                                                       value="<?= $entry ? $entry['hsn_code'] : '' ?>"
                                                                       class="form-control hsn_code" required>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="sub_total[]" id="sub_total_1"
                                                                       placeholder="Sub total"
                                                                       value="<?= $entry ? $entry['sub_total'] : '' ?>"
                                                                       class="form-control sub_total" required readonly>
                                                            </td>
                                                            <td>
                                                                <input type="text" name="total[]" id="total_1" placeholder="Total"
                                                                       value="<?= isset($entry['total']) ? $entry['total'] : '' ?>"
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
                                                                                class="btn btn-warning btn-sm px-2 button-add remove-row"><i
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
                                                class="form-control numInput calculateTotal" placeholder="Enter paid amount"
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
                                                placeholder="Enter notes" rows="1"><?= isset($row) ? $row['notes'] : '' ?></textarea>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-md-12">
                                            <button type="submit" name="purchase" class="btn btn-primary">Submit</button>
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
            let dynamicRow = new DynamicRow($("#product_details"), calculateTotal, calculateTotal);

            let selectedValues = {};

            $(document).on('change', 'select.product_id', function() {
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
                        url: 'purchase',
                        type: 'POST',
                        data: {
                            getUnitCostPrice: true,
                            productId: productId
                        },
                        success: function(response) {
                            try {
                                let data = JSON.parse(response);
                                if (data['status'] === 'success') {
                                    data = data['data'];

                                    let $row = $this.closest('.clone_row');
                                    $row.find('.unit_cost_price').val(data[
                                        'purchase_price']);
                                    $row.find('.gst_type').val(data['gst_type']);
                                    $row.find('.gst_rate').val(data['gst_rate']);
                                    $row.find('.hsn_code').val(data['hsn_code']);
                                    calculateTotal();
                                } else {
                                    toastr && toastr.error('Error getting unit cost price');
                                }
                            } catch (error) {
                                console.error(error);
                                toastr && toastr.error('Error getting unit cost price');
                            }
                        }
                    });
                }
            });

            $("#supplier_id").change(function() {
                let supplierId = $(this).val();
                $.ajax({
                    url: 'purchase',
                    type: 'POST',
                    data: {
                        getProducts: true,
                        supplierId: supplierId
                    },
                    success: function(response) {
                        try {
                            let data = JSON.parse(response);
                            let options = '<option value="">Select Product</option>';
                            data.forEach(product => {
                                options +=
                                    `<option value="${product.product_id}">${htmlDecode(product.product_name)}</option>`;
                            });
                            if (data.length === 0) {
                                toastr && toastr.error('No products found for this supplier');
                            }
                            $('.product_id').each(function() {
                                $(this).selectize()[0].selectize.destroy();
                            });
                            $('.product_id').html(options);
                            $('.product_id').selectize();
                            selectedValues = {};
                            $('.clone_row').find('input,select').val('');
                        } catch (error) {
                            console.error(error);
                            toastr && toastr.error('Error getting supplier details');
                        }
                    }
                });
            });
        });

        $(document).on('input', '.clone_row, .calculateTotal', function() {
            calculateTotal();
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

            $('.clone_row').each(function() {
                let $this = $(this);
                if ($this.find('.product_id').val() === '') {
                    return;
                }
                let quantity = $this.find('.quantity').val();
                let unitCostPrice = $this.find('.unit_cost_price').val();
                let gstType = $this.find('.gst_type').val();
                let gstRate = $this.find('.gst_rate').val();
                let hsnCode = $this.find('.hsn_code').val();
                let subTotal = quantity * unitCostPrice;
                let gst = (subTotal * gstRate) / 100;
                let total = subTotal + gst;
                totalProducts++;
                totalQuantity += parseInt(quantity) || 0;
                totalCostPrice += parseFloat(subTotal);
                totalGST += parseFloat(gst);
                totalAmount += parseFloat(total);
                $this.find('.sub_total').val(subTotal);
                $this.find('.total').val(total);
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
            if(paidAmount > totalAmount) {
                $('#paid_amount').val(0);
                paidAmount = 0;
            }
            dueAmount = totalAmount - paidAmount;
            if(dueAmount < 0) {
                dueAmount = 0;
            }
            $('#due_amount').val(dueAmount);
        }
    </script>
</body>

</html>