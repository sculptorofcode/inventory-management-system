<?php
require_once 'includes/config/after-login.php';
$title = 'Customer List';

if (isset($_REQUEST['draw'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];

    $total = getCustomersCount();

    $sql = "SELECT customer_id, first_name, last_name, full_name, email, phone, registration_date, preferred_contact_method
            FROM tbl_customers WHERE user_type = 'customer'";

    if (!empty($search)) {
        $sql .= " AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    }

    $sql .= " ORDER BY customer_id DESC LIMIT :start, :length";

    $stmt = $conn->prepare($sql);

    // Bind parameters for pagination and search
    if (!empty($search)) {
        $searchParam = '%' . $search . '%';
        $stmt->bindParam(':search', $searchParam);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process data for DataTables display
    $i = 0;
    foreach ($data as $key => $row) {
        $data[$key]['sl_no'] = ++$i;
        $data[$key]['full_name'] = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
        $data[$key]['registration_date'] = date('d M Y', strtotime($row['registration_date']));
        $data[$key]['action'] = '<a href="customer?id=' . $row['customer_id'] . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>';
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
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="customerTable">
                                        <thead class="bg-primary text-white"></thead>
                                    </table>
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
        $("#customerTable").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "customer-list",
                type: "POST"
            },
            "columns": [
                { data: "sl_no", title: "SL No.", orderable: false },
                { data: "full_name", title: "Full Name", orderable: false },
                { data: "email", title: "Email", orderable: false },
                { data: "phone", title: "Phone", orderable: false },
                { data: "registration_date", title: "Registration Date", orderable: false },
                { data: "action", title: "Action", orderable: false }
            ]
        });
    </script>
</body>

</html>