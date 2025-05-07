<?php
require_once 'includes/config/after-login.php';
$title = 'Supplier List';
if (isset($_REQUEST['draw'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];
    $total = getSupplierCount();
    $sql = "SELECT * FROM `tbl_suppliers`";

    if (!empty($search)) {
        $sql .= " WHERE (supplier_name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
    }

    $sql .= " ORDER BY " . $columns[$order]['data'] . " $order_dir LIMIT $start, $length";

    $stmt = $conn->prepare($sql);

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $i = 0;
    foreach ($data as $key => $row) {
        $data[$key]['sl_no'] = ++$i;
        $data[$key]['registration_date'] = !empty($row['registration_date']) ? date('d M Y', strtotime($row['registration_date'])) : '';
        $data[$key]['action'] = '<a href="supplier?id=' . $row['supplier_id'] . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>';
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
                                    <table class="table table-bordered" id="dataTable">
                                        <thead class="bg-primary text-white">

                                        </thead>
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
    $("#dataTable").dataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: "supplier-list",
            type: "POST"
        },
        "columns": [{
                data: "supplier_id",
                title: "ID",
                orderable: false,
                visible: false
            }, {
                data: "sl_no",
                title: "SL No.",
                orderable: false,
            },
            {
                "data": "supplier_name",
                title: "Supplier Name",
                orderable: false
            },
            {
                "data": "email",
                title: "Email",
                orderable: false
            },
            {
                "data": "phone",
                title: "Phone",
                orderable: false
            },
            {
                "data": "registration_date",
                title: "Registration Date",
                orderable: false
            },
            {
                "data": "action",
                title: "Action",
                orderable: false
            }
        ]
    })
    </script>
</body>

</html>