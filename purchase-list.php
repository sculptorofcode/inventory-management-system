<?php
require_once 'includes/config/after-login.php';
$title = 'Purchase List';
if (isset($_REQUEST['draw'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];

    $query = "SELECT po.*, s.supplier_name, (SELECT COUNT(*) FROM `tbl_supplier_payments` WHERE `tbl_supplier_payments`.`purchase_order_id` = po.`order_id` ) AS total_payments FROM `tbl_purchase_order` po
    LEFT JOIN `tbl_suppliers` s ON po.supplier_id = s.supplier_id
    WHERE 1 = 1";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $total = $stmt->rowCount();

    if (!empty($search)) {
        $query .= " AND (po.added_date LIKE '%$search%' OR s.supplier_name LIKE '%$search%')";
    }

    if (!empty($_REQUEST['supplier'])) {
        $query .= " AND po.supplier_id = " . $_REQUEST['supplier'];
    }

    if (!empty($_REQUEST['product'])) {
        $query .= " AND po.order_id IN (SELECT purchase_order_id FROM tbl_purchase_order_details WHERE product_id = " . $_REQUEST['product'] . ")";
    }

    if (!empty($_REQUEST['status'])) {
        $query .= " AND po.status = '" . $_REQUEST['status'] . "'";
    }

    $query .= " ORDER BY " . $columns[$order]['data'] . " DESC LIMIT $start, $length";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $data = [];
    $sl_no = $start + 1;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $inv_number = "<a href='javascript:void(0)' class='text-nowrap' onclick='showOrder(" . $row['order_id'] . ")'>" . $row['inv_number'] . "</a>";
        $status = '<a href="javascript:void(0)" onclick="changeStatus(' . $row['order_id'] . ')">';
        if ($row['status'] === 'pending')
            $status .= '<span class="badge badge-warning bg-warning">Pending</span>';
        elseif ($row['status'] === 'confirmed')
            $status .= '<span class="badge bg-info">Confirmed</span>';
        elseif ($row['status'] === 'shipped')
            $status .= '<span class="badge bg-primary">Shipped</span>';
        elseif ($row['status'] === 'delivered')
            $status .= '<span class="badge bg-success">Delivered</span>';
        elseif ($row['status'] === 'cancelled')
            $status .= '<span class="badge bg-danger">Cancelled</span>';
        else
            $status .= '<span class="badge bg-secondary">Unknown</span>';
        $status .= '</a>';

        $due_amount = rupee($row['due_amount'], 2);
        if ($row['due_amount'] > 0) {
            $due_amount = '<span class="text-danger">' . $due_amount . '</span>';
            $due_amount .= ' <a href="javascript:void(0)" onclick="payDue(' . $row['order_id'] . ')" title="Pay Now" class="btn btn-primary btn-sm ms-2 px-1 py-1"><i class="bx bx-plus"></i></a>';
        } else if ($row['total_payments'] > 0) {
            $due_amount = ' <a href="javascript:void(0)" title="Payment List" onclick="payDue(' . $row['order_id'] . ')" class="btn btn-info btn-sm ms-2 px-1 py-1"><i class="bx bx-list-check"></i></a>';
        }

        $action = '';
        # $action .= '<a href="javascript:void(0)" onclick="changeStatus(' . $row['order_id'] . ')" class="btn btn-primary btn-sm px-2 py-1"><i class="bx bx-edit"></i></a>';
        $action .= '<a href="purchase-print?order_id=' . $row['order_id'] . '" target="_blank" class="btn btn-info btn-sm px-2 py-1"><i class="bx bx-printer"></i></a>';


        $data[] = [
            'sl_no' => $sl_no++,
            'order_id' => $row['order_id'],
            'inv_number' => $inv_number,
            'supplier_name' => $row['supplier_name'],
            'total_products' => $row['total_products'],
            'total_amount' => rupee($row['total_amount'], 2),
            'total_quantity' => $row['total_quantity'],
            'total_cost_price' => rupee($row['total_cost_price'], 2),
            'total_gst' => rupee($row['total_gst'], 2),
            'discount' => rupee($row['discount'], 2),
            'paid_amount' => rupee($row['paid_amount'], 2),
            'due_amount' => $due_amount,
            'status' => $status,
            'action' => $action
        ];
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

if (isset($_REQUEST['order-details'])) {
    $order_id = $_REQUEST['order_id'];
    $stmt = $conn->prepare("SELECT po.*, s.supplier_name FROM `tbl_purchase_order` po
            LEFT JOIN `tbl_suppliers` s ON po.supplier_id = s.supplier_id
            WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt = $conn->prepare("SELECT * FROM tbl_purchase_order_details WHERE purchase_order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="container">
        <?php order_details($order, 'purchase'); ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="bg-primary text-white">
                <tr>
                    <th>Sl. No.</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Cost Price</th>
                    <th>Amount</th>
                    <th>GST</th>
                    <th>Grand Total</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sl_no = 1;
                foreach ($order_details as $order_detail) {
                    $product = getProductById($order_detail['product_id']);
                    $amount = $order_detail['quantity'] * $order_detail['unit_cost_price'];
                    $gst = ($amount * $order_detail['gst_rate']) / 100; ?>
                    <tr>
                        <td><?= $sl_no++ ?></td>
                        <td><?= html_entity_decode($product['product_name']) ?></td>
                        <td><?= $order_detail['quantity'] ?></td>
                        <td><?= rupee($order_detail['unit_cost_price'], 2) ?></td>
                        <td><?= rupee($amount, 2) ?></td>
                        <td><?= rupee($gst, 2) ?> (<?= round($order_detail['gst_rate'], 2) ?>%)</td>
                        <td><?= rupee($amount + $gst, 2) ?></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td colspan="6" class="text-end"><b>Total Cost Price</b></td>
                    <td><?= rupee($order['total_cost_price'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-end"><b>Total GST</b></td>
                    <td><?= rupee($order['total_gst'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-end"><b>Sub Total</b></td>
                    <td><?= rupee($order['total_amount'] + $order['discount'], 2) ?></td>
                </tr>
                <?php if ($order['discount'] > 0) { ?>
                    <tr>
                        <td colspan="6" class="text-end"><b>Discount</b></td>
                        <td><?= rupee($order['discount'], 2) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="6" class="text-end"><b>Total Amount</b></td>
                    <td><?= rupee($order['total_amount'], 2) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    exit;
}

if (isset($_REQUEST['change-status'])) {
    $order_id = $_REQUEST['order_id'];
    $stmt = $conn->prepare("SELECT po.*, s.supplier_name FROM `tbl_purchase_order` po
            LEFT JOIN `tbl_suppliers` s ON po.supplier_id = s.supplier_id
            WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <div class="container">
        <div class="row">
            <?php
            if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled') { ?>
                <div class="col-md-12">
                    <form action="purchase-list" method="post" class="form">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        <?php order_details($order, 'purchase'); ?>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Status</label>
                            <div class="col-sm-9">
                                <select name="status" class="form-select order-status">
                                    <option value="">Select Status</option>
                                    <?php
                                    foreach ($order_status as $status) {
                                        ?>
                                        <option value="<?= $status ?>" <?= $order['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row d-none delivery-group">
                            <label class="col-sm-3 col-form-label">Delivery Date</label>
                            <div class="col-sm-9">
                                <input type="text" name="delivery_date" class="form-control datepicker"
                                       placeholder="Select Date"
                                       value="<?= ($order['delivery_date'] != '0000-00-00' ? $order['delivery_date'] : '') ?>">
                            </div>
                        </div>
                        <!-- Removed shipping_address field -->
                        <div class="form-group row d-none delivery-group">
                            <label class="col-sm-3 col-form-label">Warehouse</label>
                            <div class="col-sm-9">
                                <select name="warehouse_id" class="form-select">
                                    <option value="">Select Warehouse</option>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM tbl_warehouse WHERE is_deleted = 0");
                                    $stmt->execute();
                                    while ($warehouse = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                        <option value="<?= $warehouse['warehouse_id'] ?>"><?= $warehouse['warehouse_name'] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label">Remarks</label>
                            <div class="col-sm-9">
                                <textarea name="remarks" class="form-control"
                                          placeholder="Enter Remarks"></textarea>
                            </div>
                        </div>
                        <div class="form-group row justify-content-center">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" name="change_status">Change Status
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php } ?>
            <div class="col-md-12">
                <h5>Change Log</h5>
            </div>
            <div class="col-md-12">
                <div class="table-responsive force">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white">
                        <tr>
                            <th>Sl. No.</th>
                            <th>Old Status</th>
                            <th>New Status</th>
                            <th>Remarks</th>
                            <th>Changed By</th>
                            <th>Changed At</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM `tbl_purchase_order_status_log` WHERE order_id = :order_id ORDER BY `changed_at` DESC");
                        $stmt->bindParam(':order_id', $order_id);
                        $stmt->execute();
                        $sl_no = 1;
                        while ($log = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <tr>
                                <td><?= $sl_no++ ?></td>
                                <td><?= ucfirst($log['old_status']) ?></td>
                                <td><?= ucfirst($log['new_status']) ?></td>
                                <td><?= html_entity_decode($log['remarks']) ?></td>
                                <td><?= getCustomerById($log['changed_by'])['full_name'] ?></td>
                                <td><?= date('d-m-Y h:i A', strtotime($log['changed_at'])) ?></td>
                            </tr>
                            <?php
                        }
                        if ($sl_no === 1) {
                            ?>
                            <tr>
                                <td colspan="6" class="text-center">No logs found</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function () {
            $('.datepicker').flatpickr({
                altInput: true,
                altFormat: 'd-m-Y',
                dateFormat: 'Y-m-d',
                maxDate: 'today',
                disableMobile: false,
            });
            $(document).on('change', '.order-status', function () {
                if ($(this).val() === 'delivered') {
                    $('.delivery-group').removeClass('d-none').find('input, textarea, select').prop('required', true);
                } else {
                    $('.delivery-group').addClass('d-none').find('input, textarea, select').prop('required', false);
                }
            })
        })
    </script>
    <?php
    exit;
}

if (isset($_POST['change_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $delivery_date = filtervar($_POST['delivery_date']);
    $remarks = filtervar($_POST['remarks']);
    $warehouse_id = filtervar($_POST['warehouse_id']);
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("SELECT * FROM `tbl_purchase_order` WHERE order_id = :order_id");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        $sql = "UPDATE `tbl_purchase_order` SET status = :status";
        if ($status === 'delivered') {
            $sql .= ", delivery_date = :delivery_date";
        }
        $sql .= " WHERE order_id = :order_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $status);
        if ($status === 'delivered') {
            $stmt->bindParam(':delivery_date', $delivery_date);
        }
        $stmt->bindParam(':order_id', $order_id);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("INSERT INTO `tbl_purchase_order_status_log` SET order_id = :order_id, old_status = :old_status, new_status = :new_status, changed_by = :changed_by, remarks = :remarks");
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':old_status', $order['status']);
            $stmt->bindParam(':new_status', $status);
            $stmt->bindParam(':changed_by', $user_id);
            $stmt->bindParam(':remarks', $remarks);
            if ($stmt->execute()) {
                if ($status === 'delivered') {
                    $order_details_query = $conn->prepare("SELECT * FROM tbl_purchase_order_details WHERE purchase_order_id = :order_id");
                    $order_details_query->bindParam(':order_id', $order_id);
                    $order_details_query->execute();
                    while ($order_detail = $order_details_query->fetch(PDO::FETCH_ASSOC)) {
                        $product = getProductById($order_detail['product_id']);
                        $stmt = $conn->prepare("UPDATE `tbl_products` SET stock = stock + :quantity WHERE product_id = :product_id");
                        $stmt->bindParam(':quantity', $order_detail['quantity']);
                        $stmt->bindParam(':product_id', $order_detail['product_id']);
                        $stmt->execute();
                        $batch_number = generateUniqueInvoiceNumber('B', 'tbl_stock', 'stock_id', 4, ['product_id' => $order_detail['product_id']]);
                        $stmt = $conn->prepare("INSERT INTO `tbl_stock` SET product_id = :product_id, batch_number = :batch_number, quantity = :quantity, supplier_id = :supplier_id, unit_cost_price = :unit_cost_price, warehouse_id = :warehouse_id");
                        $stmt->bindParam(':product_id', $order_detail['product_id']);
                        $stmt->bindParam(':batch_number', $batch_number);
                        $stmt->bindParam(':quantity', $order_detail['quantity']);
                        $stmt->bindParam(':supplier_id', $order['supplier_id']);
                        $stmt->bindParam(':unit_cost_price', $order_detail['unit_cost_price']);
                        $stmt->bindParam(':warehouse_id', $warehouse_id);
                        if ($stmt->execute()) {
                            $stock_id = $conn->lastInsertId();
                            $stmt = $conn->prepare("INSERT INTO `tbl_stock_transactions` SET product_id = :product_id, stock_id = :stock_id, quantity_change = :quantity_change, previous_quantity = :previous_quantity, transaction_type = :transaction_type, transaction_date = :transaction_date, notes = :notes, user_id = :user_id, order_reference = :order_reference");
                            $transaction_type = 'in';
                            $notes = 'Stock added for order #' . $order['inv_number'];
                            $stmt->bindParam(':product_id', $order_detail['product_id']);
                            $stmt->bindParam(':stock_id', $stock_id);
                            $stmt->bindParam(':quantity_change', $order_detail['quantity']);
                            $stmt->bindParam(':previous_quantity', $product['stock']);
                            $stmt->bindParam(':transaction_type', $transaction_type);
                            $stmt->bindParam(':transaction_date', $delivery_date);
                            $stmt->bindParam(':notes', $notes);
                            $stmt->bindParam(':user_id', $user_id);
                            $stmt->bindParam(':order_reference', $order['inv_number']);
                            $stmt->execute();
                        }

                    }
                }
                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'Status changed successfully', 'function' => 'reload']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to log status change']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to change status']);
        }
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

if (isset($_REQUEST['pay-due'])) {
    $order_id = $_REQUEST['order_id'];
    $stmt = $conn->prepare("SELECT po.*, s.supplier_name FROM `tbl_purchase_order` po
            LEFT JOIN `tbl_suppliers` s ON po.supplier_id = s.supplier_id
            WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
    <div class="container">
        <?php order_details($order, 'purchase'); ?>
        <div class="row">
            <div class="col-md-12">
                <?php if ($order['due_amount'] > 0) { ?>
                    <form action="purchase-list" method="post" class="form">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        <input type="hidden" name="due_amount" id="due_amount" value="<?= $order['due_amount'] ?>">
                        <div class="row form-group">
                            <div class="col-sm-4">
                                <label for="pay_amount" class="form-label">Pay Amount</label>
                                <input type="text" name="pay_amount" id="pay_amount" class="form-control numInput"
                                       placeholder="Enter Amount"
                                       max="<?= $order['due_amount'] ?>" required>
                            </div>
                            <div class="col-sm-4">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Select Payment Method</option>
                                    <?php
                                    foreach ($payment_mode as $key => $mode) {
                                        ?>
                                        <option value="<?= $key ?>"><?= $mode ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label for="payment_date" class="form-label">Payment Date</label>
                                <input type="text" name="payment_date" class="form-control datepicker"
                                       placeholder="Select Date"
                                       required>
                            </div>
                            <div class="col-sm-12 mt-2">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control"
                                          placeholder="Enter Remarks"></textarea>
                            </div>
                        </div>
                        <div class="form-group row justify-content-center">
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary" name="pay_due">Pay Due Amount</button>
                            </div>
                        </div>
                    </form>
                    <hr>
                <?php } ?>
            </div>
            <div class="col-12">
                <h4>Payment History</h4>
            </div>
            <div class="col-12">
                <div class="table-responsive force">
                    <table class="table table-bordered">
                        <thead class="bg-primary text-white text-nowrap">
                        <tr>
                            <th>Sl. No.</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $stmt = $conn->prepare("SELECT * FROM `tbl_supplier_payments` WHERE purchase_order_id = :order_id ORDER BY `created_at` DESC");
                        $stmt->bindParam(':order_id', $order_id);
                        $stmt->execute();
                        $sl_no = 1;
                        while ($payment = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            ?>
                            <tr>
                                <td><?= $sl_no++ ?></td>
                                <td><?= date('d-m-Y', strtotime($payment['payment_date'])) ?></td>
                                <td><?= $payment_mode[$payment['payment_method']] ?></td>
                                <td><?= rupee($payment['amount'], 2) ?></td>
                                <td><?= !empty($payment['notes']) ? html_entity_decode($payment['notes']) : '' ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(function () {
            $('.datepicker').flatpickr({
                altInput: true,
                altFormat: 'd-m-Y',
                dateFormat: 'Y-m-d',
                maxDate: 'today',
                disableMobile: false,
            });
            $("#pay_amount").on('input', function () {
                let due_amount = parseInt($("#due_amount").val());
                let pay_amount = parseInt($(this).val());
                if (pay_amount > due_amount) {
                    $(this).val('');
                    $('.due-amount').html('<?= rupee($order['due_amount'], 2) ?>');
                    toastr && toastr.error("Amount should be less than due amount", "Error", {
                        positionClass: 'toast-top-right',
                        timeOut: 5000
                    });
                } else {
                    $('.due-amount').html('₹ ' + (due_amount - pay_amount).toLocaleString('en-IN', {maximumFractionDigits: 2}));
                }
            })
        })
    </script>
    <?php
    exit;
}

if (isset($_POST['pay_due'])) {
    $order_id = $_POST['order_id'];
    $pay_amount = $_POST['pay_amount'];
    $payment_method = $_POST['payment_method'];
    $remarks = filtervar($_POST['remarks']);
    $payment_date = date('Y-m-d', strtotime($_POST['payment_date']));
    if (intval($pay_amount) <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid amount']);
        exit;
    } else {
        $stmt = $conn->prepare("SELECT * FROM `tbl_purchase_order` WHERE order_id = :order_id");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        $due_amount = $order['due_amount'] - intval($pay_amount);
        $total_paid = $order['paid_amount'] + intval($pay_amount);
        $stmt = $conn->prepare("INSERT INTO `tbl_supplier_payments` SET supplier_id = :supplier_id, purchase_order_id = :order_id, payment_date = :payment_date, payment_method = :payment_method, amount = :amount, notes = :notes, payment_status = :payment_status");
        $payment_status = "completed";
        $stmt->bindParam(':supplier_id', $order['supplier_id']);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->bindParam(':payment_date', $payment_date);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':amount', $pay_amount);
        $stmt->bindParam(':notes', $remarks);
        $stmt->bindParam(':payment_status', $payment_status);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("UPDATE `tbl_purchase_order` SET due_amount = :due_amount, paid_amount = :total_paid WHERE order_id = :order_id");
            $stmt->bindParam(':due_amount', $due_amount);
            $stmt->bindParam(':total_paid', $total_paid);
            $stmt->bindParam(':order_id', $order_id);
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Payment added successfully', 'function' => 'reload']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to update due amount']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add payment']);
        }
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
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group row justify-content-center align-items-end gap-3">
                                        <div class="col-sm">
                                            <label class="form-label">Search:</label>
                                            <input type="text" name="search" id="search" class="form-control"
                                                   placeholder="Search">
                                        </div>
                                        <div class="col-sm">
                                            <label class="form-label">Supplier</label>
                                            <select name="supplier" id="supplier" class="form-select">
                                                <option value="">Select Supplier</option>
                                                <?php
                                                $stmt = $conn->prepare("SELECT * FROM `tbl_suppliers`");
                                                $stmt->execute();
                                                while ($supplier = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>
                                                    <option value="<?= $supplier['supplier_id'] ?>"><?= $supplier['supplier_name'] ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-sm">
                                            <label class="form-label">Product</label>
                                            <select name="product" id="product" class="form-select">
                                                <option value="">Select Product</option>
                                                <?php
                                                $stmt = $conn->prepare("SELECT * FROM `tbl_products`");
                                                $stmt->execute();
                                                while ($product = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                    ?>
                                                    <option value="<?= $product['product_id'] ?>"><?= html_entity_decode($product['product_name']) ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-sm">
                                            <label class="form-label">Status</label>
                                            <select name="status" id="status" class="form-select">
                                                <option value="">Select Status</option>
                                                <?php
                                                foreach ($order_status as $status) {
                                                    ?>
                                                    <option value="<?= $status ?>"><?= ucfirst($status) ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-auto">
                                            <button type="button" class="btn btn-primary"
                                                    onclick="table.api().ajax.reload()">Search
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable">
                                            <thead class="bg-primary text-white">

                                            </thead>
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
    let table = currentModal = '';
    $(function () {
        table = $("#dataTable").dataTable({
            processing: true,
            serverSide: true,
            searching: false,
            lengthChange: false,
            responsive: true,
            ajax: {
                url: "purchase-list",
                type: "POST",
                data: function (d) {
                    d.search.value = $('#search').val();
                    d.supplier = $('#supplier').val();
                    d.product = $('#product').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                {"data": "order_id", "visible": false},
                {"data": "sl_no", title: "Sl. No.", orderable: false},
                {"data": "inv_number", title: "Order ID", orderable: false},
                {"data": "supplier_name", title: "Supplier", orderable: false},
                {"data": "total_products", title: "Total Products", orderable: false},
                {"data": "total_quantity", title: "Total Quantity", orderable: false},
                {"data": "total_cost_price", title: "Total Cost Price", orderable: false},
                {"data": "total_gst", title: "Total GST", orderable: false},
                {"data": "discount", title: "Discount", orderable: false},
                {"data": "total_amount", title: "Total Amount", orderable: false},
                {"data": "paid_amount", title: "Paid Amount", orderable: false},
                {"data": "due_amount", title: "Due Amount", orderable: false},
                {"data": 'status', title: 'Status', orderable: false},
                {"data": "action", title: "Action", orderable: false}
            ]
        });
    })

    function showOrder(order_id) {
        currentModal = $.dialog({
            title: 'Order Details',
            content: 'url:purchase-list?order-details&order_id=' + order_id,
            columnClass: 'l',
            closeIcon: true,
            closeIconClass: 'fa fa-close',
            onContentReady: function () {
                var self = this;
                self.$content.find('.btn').click(function () {
                    self.close();
                });
            }
        })
    }

    function changeStatus(order_id) {
        currentModal = $.dialog({
            title: 'Change Status',
            content: 'url:purchase-list?change-status&order_id=' + order_id,
            columnClass: 'l',
            closeIcon: true,
            closeIconClass: 'fa fa-close',
            onContentReady: function () {
                var self = this;
                self.$content.find('.btn').click(function () {
                    self.close();
                });
            }
        })
    }

    function reload() {
        if (currentModal) {
            currentModal.close();
        }
        table.api().ajax.reload();
    }

    function payDue(id) {
        currentModal = $.dialog({
            title: 'Pay Due Amount',
            content: 'url:purchase-list?pay-due&order_id=' + id,
            columnClass: 'l',
            closeIcon: true,
            closeIconClass: 'fa fa-close',
            onContentReady: function () {
                var self = this;
                self.$content.find('.btn').click(function () {
                    self.close();
                });
            }
        })
    }
</script>
</body>

</html>