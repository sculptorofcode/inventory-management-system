<?php
require_once 'includes/config/after-login.php';

$title = 'Sales List';

if (isset($_REQUEST['draw'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'] ?? '';
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];

    $query = "SELECT so.*, c.full_name AS customer_name, 
              (SELECT COUNT(*) FROM `tbl_customer_payments` WHERE `tbl_customer_payments`.`sale_order_id` = so.`order_id`) AS total_payments 
              FROM `tbl_sale_order` so
              LEFT JOIN `tbl_customers` c ON so.customer_id = c.customer_id 
              WHERE 1 = 1";

    if (!empty($search)) {
        $query .= " AND (so.sale_date LIKE '%$search%' OR c.customer_name LIKE '%$search%')";
    }

    if (!empty($_REQUEST['status'])) {
        $query .= " AND so.status = '" . $_REQUEST['status'] . "'";
    }

    $query .= " ORDER BY " . $columns[$order]['data'] . " " . $order_dir . " LIMIT $start, $length";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $total = $stmt->rowCount();

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
        }

        $action = '<a href="javascript:void(0)" data-url="sale-print?order_id=' . $row['order_id'] . '" class="btn btn-info btn-sm print-btn px-2 py-1"><i class="bx bx-printer"></i></a>';

        $data[] = [
            'sl_no' => $sl_no++,
            'order_id' => $row['order_id'],
            'inv_number' => $inv_number,
            'customer_name' => $row['customer_name'],
            'total_products' => $row['total_products'],
            'total_quantity' => $row['total_quantity'],
            'total_cost_price' => rupee($row['total_cost_price'], 2),
            'total_gst' => rupee($row['total_gst'], 2),
            'discount' => rupee($row['discount'], 2),
            'total_amount' => rupee($row['total_amount'], 2),
            'paid_amount' => rupee($row['paid_amount'], 2),
            'due_amount' => $due_amount,
            'status' => $status,
            'action' => $action,
        ];
    }

    $response = [
        'draw' => $draw,
        'recordsTotal' => $total,
        'recordsFiltered' => $total,
        'data' => $data,
    ];

    echo json_encode($response);
    exit;
}

if (isset($_REQUEST['order-details'])) {
    $order_id = $_REQUEST['order_id'];
    $stmt = $conn->prepare("SELECT so.*, c.full_name AS customer_name FROM `tbl_sale_order` so
                LEFT JOIN `tbl_customers` c ON so.customer_id = c.customer_id
                WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch order details
    $stmt = $conn->prepare("SELECT sod.*, p.product_name FROM `tbl_sale_order_details` sod LEFT JOIN `tbl_products` p ON p.`product_id` = sod.`product_id` WHERE sale_order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="container">
        <?php order_details($order, 'sale'); ?>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="bg-primary text-white text-nowrap">
                <tr>
                    <th style="width: 50px;">Sl. No.</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Amount</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sl_no = 1;
                foreach ($order_details as $order_detail) {
                    $amount = $order_detail['quantity'] * $order_detail['unit_cost_price'];
                    ?>
                    <tr>
                        <td><?= $sl_no++ ?></td>
                        <td><?= html_entity_decode($order_detail['product_name']) ?></td>
                        <td><?= $order_detail['quantity'] ?></td>
                        <td><?= rupee($order_detail['unit_cost_price'], 2) ?></td>
                        <td><?= rupee($amount, 2) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="4" class="text-end"><b>Total Cost Price</b></td>
                    <td><?= rupee($order['total_cost_price'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><b>Total GST</b></td>
                    <td><?= rupee($order['total_gst'], 2) ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="text-end"><b>Sub Total</b></td>
                    <td><?= rupee($order['total_amount'] + $order['discount'], 2) ?></td>
                </tr>
                <?php if ($order['discount'] > 0) { ?>
                    <tr>
                        <td colspan="4" class="text-end"><b>Discount</b></td>
                        <td><?= rupee($order['discount'], 2) ?></td>
                    </tr>
                <?php } ?>
                <tr>
                    <td colspan="4" class="text-end"><b>Total Amount</b></td>
                    <td><?= rupee($order['total_amount'], 2) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php
    exit;
}

if (isset($_REQUEST['pay-due'])) {
    $order_id = $_REQUEST['order_id'];
    $stmt = $conn->prepare("SELECT so.*, c.full_name AS customer_name FROM `tbl_sale_order` so
            LEFT JOIN `tbl_customers` c ON so.customer_id = c.customer_id
            WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC); ?>
    <div class="container">
        <?php order_details($order, 'sale'); ?>
        <div class="row">
            <div class="col-md-12">
                <?php if ($order['due_amount'] > 0) { ?>
                    <form action="sale-list" method="post" class="form">
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
                        $stmt = $conn->prepare("SELECT * FROM `tbl_customer_payments` WHERE sale_order_id = :order_id ORDER BY `created_at` DESC");
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
    $remarks = filter_var($_POST['remarks']);
    $payment_date = date('Y-m-d', strtotime($_POST['payment_date']));

    if (intval($pay_amount) <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid amount']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM `tbl_sale_order` WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    $due_amount = $order['due_amount'] - intval($pay_amount);
    $total_paid = $order['paid_amount'] + intval($pay_amount);

    $stmt = $conn->prepare("INSERT INTO `tbl_customer_payments` SET customer_id = :customer_id, sale_order_id = :order_id, payment_date = :payment_date, payment_method = :payment_method, amount = :amount, notes = :notes");
    $stmt->bindParam(':customer_id', $order['customer_id']);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':payment_date', $payment_date);
    $stmt->bindParam(':payment_method', $payment_method);
    $stmt->bindParam(':amount', $pay_amount);
    $stmt->bindParam(':notes', $remarks);

    if ($stmt->execute()) {
        $stmt = $conn->prepare("UPDATE `tbl_sale_order` SET due_amount = :due_amount, paid_amount = :total_paid WHERE order_id = :order_id");
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
    exit;
}

if (isset($_REQUEST['change-status'])) {
    $order_id = $_REQUEST['order_id'];
    $stmt = $conn->prepare("SELECT so.*, c.full_name as customer_name FROM `tbl_sale_order` so
            LEFT JOIN `tbl_customers` c ON so.customer_id = c.customer_id
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
                    <form action="sale-list" method="post" class="form">
                        <input type="hidden" name="order_id" value="<?= $order_id ?>">
                        <?php order_details($order, 'sale'); ?>
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
                        <div class="form-group row d-none delivery-group">
                            <label class="col-sm-3 col-form-label">Delivered At</label>
                            <div class="col-sm-9">
                    <textarea name="shipping_address" class="form-control"
                              placeholder="Enter Delivery Address"><?= $order['shipping_address'] ?></textarea>
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
                        $stmt = $conn->prepare("SELECT * FROM `tbl_sale_order_status_log` WHERE order_id = :order_id ORDER BY `changed_at` DESC");
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
                    $('.delivery-group').removeClass('d-none').find('input, textarea').prop('required', true);
                } else {
                    $('.delivery-group').addClass('d-none').find('input, textarea').prop('required', false);
                }
            })
        })
    </script>
    <?php
    exit;
}

if (isset($_REQUEST['change_status'])) {
    $order_id = filtervar($_REQUEST['order_id']);
    $status = filtervar($_REQUEST['status']);
    $remarks = filtervar($_REQUEST['remarks']);
    $now = date('Y-m-d H:i:s');
    $user_id = $userdata['customer_id'];

    $stmt = $conn->prepare("SELECT * FROM `tbl_sale_order` WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($order)) {
        if($status === $order['status']){
            echo json_encode(['status' => 'error', 'message' => 'Status not changed']);
            exit;
        }
        
        // Get shipping address
        $shipping_address = $order['shipping_address'];
        if (empty($shipping_address)) {
            $customer = getCustomerById($order['customer_id']);
            $shipping_address = $customer['address'] ?? 'Customer address not available';
        }
        
        try {
            $conn->beginTransaction();
            
            // If cancelling a confirmed or delivered order, restore stock
            if ($status === 'cancelled' && ($order['status'] === 'confirmed' || $order['status'] === 'delivered')) {
                $stmt = $conn->prepare("SELECT * FROM `tbl_sale_order_details` WHERE sale_order_id = :order_id");
                $stmt->bindParam(':order_id', $order_id);
                $stmt->execute();
                $order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                foreach ($order_details as $order_detail) {
                    // If the order detail has a batch number, we need to restore stock
                    if (!empty($order_detail['batch_number'])) {
                        // Use the new function to restore stock
                        restoreStockFromCancelledOrder(
                            $order_detail['product_id'],
                            $order_detail['quantity'],
                            $order_detail['batch_number'],
                            $order['inv_number'],
                            $remarks,
                            $user_id
                        );
                    }
                }
            }
            // If changing from pending to delivered/confirmed, we might need to handle stock deduction
            // This branch handles legacy orders that didn't deduct stock at creation time
            else if (($status === 'delivered' || $status === 'confirmed') && $order['status'] === 'pending') {
                $stmt = $conn->prepare("SELECT * FROM `tbl_sale_order_details` WHERE sale_order_id = :order_id");
                $stmt->bindParam(':order_id', $order_id);
                $stmt->execute();
                $order_details = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($order_details as $order_detail) {
                    // Only process items without batch numbers assigned (legacy orders)
                    if (empty($order_detail['batch_number'])) {
                        // Use the new stock deduction function
                        $stockResult = deductStockForSale(
                            $order_detail['product_id'],
                            $order_detail['quantity'],
                            $order['inv_number'],
                            $remarks,
                            $user_id,
                            $shipping_address
                        );
                        
                        // Update sale order detail with batch number
                        $stmt = $conn->prepare("UPDATE `tbl_sale_order_details` 
                                              SET batch_number = :batch_number 
                                              WHERE sale_order_id = :order_id AND product_id = :product_id");
                        $stmt->bindParam(':batch_number', $stockResult['batch_number']);
                        $stmt->bindParam(':order_id', $order_id);
                        $stmt->bindParam(':product_id', $order_detail['product_id']);
                        $stmt->execute();
                    }
                }
            }

            // Record status change in status log table
            $stmt = $conn->prepare("INSERT INTO `tbl_sale_order_status_log` SET order_id = :order_id, old_status = :old_status, new_status = :new_status, remarks = :remarks, changed_by = :changed_by, changed_at = :changed_at");
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':old_status', $order['status']);
            $stmt->bindParam(':new_status', $status);
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':changed_by', $user_id);
            $stmt->bindParam(':changed_at', $now);
            $stmt->execute();

            // Update order status
            $stmt = $conn->prepare("UPDATE `tbl_sale_order` SET status = :status WHERE order_id = :order_id");
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->execute();

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Status updated successfully!', 'function' => 'reload']);
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
                            <div class="table-responsive">
                                <table class="table table-bordered" id="salesList">
                                    <thead class="bg-primary text-white">
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php include './includes/layouts/dash-footer.php'; ?>
        </div>
    </div>
</div>
<?php include './includes/layouts/scripts.php'; ?>
<script>
    $(document).ready(function () {
        let salesListTable = $('#salesList').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'sale-list.php',
                type: 'POST',
            },
            order: [[0, 'desc']],
            columns: [
                {"data": "order_id", "visible": false},
                {"data": "sl_no", title: "Sl. No.", orderable: false},
                {"data": "inv_number", title: "Order ID", orderable: false},
                {"data": "customer_name", title: "Customer", orderable: false},
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
            ],
        });

        window.reload = function () {
            if (window.currentDialog) {
                window.currentDialog.close();
            }
            $('#salesList').DataTable().ajax.reload();
        }

        $(document).on('click', '.print-btn', function (e) {
            e.preventDefault();
            let url = $(this).data('url');
            let w =window.open(url, '_blank', 'location=yes,height=800,width=1200,scrollbars=yes,status=yes');
            w.print();
            setTimeout(function () {
                w.close();
            }, 1000);
        });
    });

    function showOrder(order_id) {
        window.currentDialog = $.dialog({
            title: 'Order Details',
            content: 'url:sale-list?order-details=true&order_id=' + order_id,
            columnClass: 'l',
            type: 'blue',
        })
    }

    function payDue(order_id) {
        window.currentDialog = $.dialog({
            title: "Pay Due Amount",
            content: `url:sale-list?pay-due=true&order_id=${order_id}`,
            columnClass: 'l',
            type: 'blue',
        });
    }

    function changeStatus(order_id) {
        window.currentDialog = $.dialog({
            title: 'Change Order Status',
            content: `url:sale-list?change-status=true&order_id=${order_id}`,
            columnClass: 'l',
            type: 'blue',
        });
    }
</script>
</body>
</html>
