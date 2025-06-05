<?php
include 'auth_functions.php';
include 'order_functions.php';
include 'payment_functions.php';
include 'product_functions.php';
include 'supplier_functions.php';
include 'warehouse_functions.php';
include 'inventory_valuation_functions.php';
include 'inventory_control_functions.php';
include 'financial_calculations_functions.php';
include 'demand_forecasting_functions.php';
include 'analytics_dashboard_functions.php';
include 'stock_functions.php';

function filtervar($var)
{
    $var = str_replace('&', 'and', $var);
    $var = trim($var);
    $var = stripslashes($var);
    $var = htmlspecialchars($var);
    $var = strip_tags($var);
    $var = htmlentities($var);
    return filter_var($var);
}

function generateOTP($length = 6)
{
    $numbers = '0123456789';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $numbers[rand(0, strlen($numbers) - 1)];
    }
    return $otp;
}

function password_changed_email_template()
{
    return file_get_contents(SITE_URL . '/includes/layouts/password-change-email.php');
}

function email_otp_template($otp)
{
    ob_start();
    include 'includes/layouts/email-otp.php';
    $body = ob_get_contents();
    ob_end_clean();
    return $body;
}

function welcome_email_template($full_name, $email, $creation_date)
{
    ob_start();
    include 'includes/layouts/welcome-email.php';
    $body = ob_get_contents();
    ob_end_clean();
    return $body;
}

function get_countries(): array
{
    global $conn;
    $sql = "SELECT * FROM `tbl_countries`";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function extracted($filters, $sql, $conn, $type, $extraConditions = [])
{
    $params = [];
    foreach ($filters as $key => $value) {
        if (!empty($value)) {
            $sql .= " AND $key = :$key";
            $params[":$key"] = $value;
        }
    }

    // Add additional conditions if provided
    if (!empty($extraConditions)) {
        if (gettype($extraConditions) == 'string') {
            $sql .= " AND $extraConditions";
        } else {
            foreach ($extraConditions as $key => $value) {
                if ($key == 'order') {
                    $sql .= " ORDER BY $value";
                } elseif ($key == 'limit') {
                    $sql .= " LIMIT $value";
                } elseif ($key == 'group') {
                    $sql .= " GROUP BY $value";
                } elseif ($key == 'compare') {
                    $sql .= " AND $value";
                } else {
                    $sql .= " AND $key = :$key";
                    $params[":$key"] = $value;
                }
            }
        }
    }

    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    if ($type == 'count') {
        return $stmt->fetchColumn();
    } else {
        return $stmt->fetchAll();
    }
}

function getCount($table_name, $filters = [], $extraConditions = '')
{
    global $conn;
    $sql = "SELECT COUNT(*) FROM $table_name WHERE 1=1";

    return extracted($filters, $sql, $conn, 'count', $extraConditions);
}


function getTable($table_name, $filters = [], $extraConditions = [])
{
    global $conn;
    $sql = "SELECT * FROM $table_name WHERE 1=1";
    return extracted($filters, $sql, $conn, 'table', $extraConditions);
}

// getValue('tbl_warehouse_location', 'name', ['location_id' => $row['parent_location_id']]);
function getValue($table_name, $column, $filters = [], $extraConditions = [])
{
    global $conn;
    $sql = "SELECT $column FROM $table_name WHERE 1=1";
    $result = extracted($filters, $sql, $conn, 'table', $extraConditions);
    return $result[0][$column];
}


function special_echo($data)
{
    echo html_entity_decode($data);
}

function generateUniqueBatchNumber($prefix, $value, $length = 4): string
{
    return $prefix . '-' . str_pad($value, $length, '0', STR_PAD_LEFT);
}

function generateUniqueInvoiceNumber($prefix, $table_name, $column = '', $length = 4, $extraConditions = []): string
{
    global $conn;
    if (!empty($column)) {
        $sql = "SELECT MAX($column) AS `total` FROM $table_name";
    } else {
        $sql = "SELECT COUNT(*) AS `total` FROM $table_name";
    }

    if (!empty($extraConditions)) {
        $sql .= " WHERE 1=1";
        foreach ($extraConditions as $key => $value) {
            $sql .= " AND $key = '$value'";
        }
    }

    $stmt = $conn->prepare($sql);

    if (!empty($exraConditions)) {
        foreach ($extraConditions as $key => $value) {
            $stmt->bindParam(":$key", $value);
        }
    }

    $stmt->execute();
    $result = $stmt->fetch();
    $value = intval($result['total']) + 1;
    return $prefix . '-' . str_pad($value, $length, '0', STR_PAD_LEFT);
}

function rupee($amount, $precision = 2, $currency = '₹'): string
{
    if (round($amount, $precision) == round($amount, 0)) {
        return $currency . ' ' . number_format($amount, 0);
    } else {
        return $currency . ' ' . number_format($amount, $precision);
    }
}

// Specially for this software
function order_details($order, $order_type): void
{
    global $conn;
?>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-3 shadow-none">
                <div class="card-header px-0 pb-1 pt-3 mb-2 border-bottom">
                    <h5 class="card-title mb-0">Order Details</h5>
                </div>
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-auto col-sm-4 mb-2 d-flex gap-2 justify-content-start align-items-center">
                            <h6 class="mb-0">Order ID:</h6>
                            <p class="text-muted mb-0"><?= $order['inv_number'] ?></p>
                        </div>
                        <div class="col-auto col-sm-4 mb-2 d-flex gap-2 justify-content-start align-items-center">
                            <h6 class="mb-0"><?= isset($order['customer_name']) ? "Customer" : '' ?><?= isset($order['supplier_name']) ? "Supplier" : '' ?> :</h6>
                            <p class="text-muted mb-0"><?= $order['supplier_name'] ?? $order['customer_name'] ?></p>
                        </div>
                        <div class="col-auto col-sm-4 mb-2 d-flex gap-2 justify-content-start align-items-center">
                            <h6 class="mb-0">Order Date:</h6>
                            <p class="text-muted mb-0"><?= date('d-m-Y', strtotime($order['order_date'])) ?></p>
                        </div>
                        <div class="col-auto col-sm-4 mb-2 d-flex gap-2 justify-content-start align-items-center">
                            <h6 class="mb-0">Total Amount:</h6>
                            <p class="text-muted mb-0"><span
                                    class="badge bg-primary"><?= rupee($order['total_amount'], 2) ?></span>
                            </p>
                        </div>
                        <div class="col-auto col-sm-4 mb-2 d-flex gap-2 justify-content-start align-items-center">
                            <h6 class="mb-0">Paid Amount:</h6>
                            <p class="text-muted mb-0"><span
                                    class="badge bg-success"><?= rupee($order['paid_amount'], 2) ?></span>
                            </p>
                        </div>
                        <div class="col-auto col-sm-4 mb-2 d-flex gap-2 justify-content-start align-items-center">
                            <h6 class="mb-0">Due Amount:</h6>
                            <p class="text-muted mb-0"><span
                                    class="badge bg-danger due-amount"><?= rupee($order['due_amount'], 2) ?></span>
                            </p>
                        </div>
                    </div>
                    <?php
                    if ($order['status'] === 'delivered') {
                        if($order_type === 'purchase'){
                            $sql = "SELECT `tbl_warehouse`.warehouse_name 
                                FROM `tbl_stock_transactions`
                                LEFT JOIN `tbl_stock` ON `tbl_stock_transactions`.stock_id = `tbl_stock`.stock_id
                                LEFT JOIN `tbl_warehouse` ON `tbl_stock`.warehouse_id = `tbl_warehouse`.warehouse_id
                                WHERE `order_reference` = :order_id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':order_id', $order['inv_number']);
                            $stmt->execute();
                            $warehouse = $stmt->fetch();
                            $shippingAddress = $warehouse['warehouse_name'];
                        }else{
                            $shippingAddress = $order['shipping_address'];
                        }
                    ?>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6 class="mb-0">Order Delivered</h6>
                                <p class="text-muted mb-0">Order delivered on <?= date('d-m-Y', strtotime($order['delivery_date'])) ?> at <?= $shippingAddress ?></p>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php
}

function getRow(string $string, string $string1, $warehouse_id)
{
    global $conn;
    $sql = "SELECT * FROM $string WHERE $string1 = :warehouse_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':warehouse_id', $warehouse_id);
    $stmt->execute();
    return $stmt->fetch();
}

function getPurchaseOrderById($order_id)
{
    global $conn;
    $sql = "SELECT * FROM `tbl_purchase_order` WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    return $stmt->fetch();
}

function getPurchaseOrderDetails($order_id): array
{
    global $conn;
    $sql = "SELECT `tbl_purchase_order_details`.*, `tbl_products`.`product_name` 
            FROM `tbl_purchase_order_details`
            LEFT JOIN `tbl_products` ON `tbl_purchase_order_details`.product_id = `tbl_products`.product_id
            WHERE purchase_order_id = :order_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getSupplierPayments($order_id)
{
    global $conn;
    $sql = "SELECT * FROM `tbl_supplier_payments` WHERE purchase_order_id = :order_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getSaleOrderById($order_id)
{
    global $conn;
    $sql = "SELECT * FROM `tbl_sale_order` WHERE order_id = :order_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    return $stmt->fetch();
}

function getSaleOrderDetails($order_id): array
{
    global $conn;
    $sql = "SELECT `tbl_sale_order_details`.*, `tbl_products`.`product_name` 
            FROM `tbl_sale_order_details`
            LEFT JOIN `tbl_products` ON `tbl_sale_order_details`.product_id = `tbl_products`.product_id
            WHERE sale_order_id = :order_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCustomerPayments($order_id): array
{
    global $conn;
    $sql = "SELECT * FROM `tbl_customer_payments` WHERE sale_order_id = :order_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getStockByProductAndBatch($product_id, $batch_number)
{
    global $conn;
    $sql = "SELECT * FROM `tbl_stock` WHERE product_id = :product_id AND batch_number = :batch_number";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':batch_number', $batch_number);
    $stmt->execute();
    return $stmt->fetch();
}

function getAllWarehouses(): array
{
    global $conn;
    $sql = "SELECT * FROM `tbl_warehouse`";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getLocationsByTypeAndParent($type, $parent_id): array
{
    global $conn;
    $sql = "SELECT * FROM `tbl_warehouse_location` WHERE `type` = :type";
    if($type == "Zone"){
        $sql .= " AND warehouse_id = :parent_id";
    }else{
        $sql .= " AND parent_location_id = :parent_id";
    }
    $sql .= " ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':parent_id', $parent_id);
    $stmt->execute();
    return $stmt->fetchAll();
}