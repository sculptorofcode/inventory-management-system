<?php
require_once 'includes/config/after-login.php';
$title = 'Product List';
if (isset($_REQUEST['draw'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];
    $total = getProductsCount();
    $sql = "SELECT p.*, s.supplier_name, c.category_name
        FROM `tbl_products` p
        JOIN `tbl_suppliers` s ON p.supplier_id = s.supplier_id
        JOIN `tbl_product_categories` c ON p.category = c.category_id";

    if (!empty($search)) {
        $sql .= " WHERE (p.product_name LIKE :search OR s.email LIKE :search OR s.phone LIKE :search)";
    }

    $sql .= " ORDER BY p.product_id DESC LIMIT $start, $length";

    $stmt = $conn->prepare($sql);

    // Bind parameters for pagination and search
    if (!empty($search)) {
        $searchParam = '%' . $search . '%';
        $stmt->bindParam(':search', $searchParam);
    }

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $i = 0;
    foreach ($data as $key => $row) {
        $data[$key]['sl_no'] = ++$i;
        foreach ($row as $k => $v) {
            $data[$key][$k] = html_entity_decode($v);
        }
        $data[$key]['added_date'] = !empty($row['added_date']) ? date('d M Y', strtotime($row['added_date'])) : '';
        $data[$key]['action'] = '
            <div class="d-flex gap-1">
                <a href="product?id=' . $row['product_id'] . '" class="btn btn-sm btn-primary" title="Edit Product"><i class="fa fa-edit"></i></a>
                <a href="location-history.php?product_id=' . $row['product_id'] . '" class="btn btn-sm btn-info" title="View Location History"><i class="bx bx-history"></i></a>
            </div>';
        if ($row['gst_type'] > 0) {
            $data[$key]['gst_type'] = $row['gst_type'] == 1 ? 'CGST/SGST' : ($row['gst_type'] == 2 ? 'IGST' : '');
            $data[$key]['gst_type'] .= ' - ' . round($row['gst_rate'], 2) . '%';
        } else {
            $data[$key]['gst_type'] = '';
        }
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
                url: "product-list",
                type: "POST"
            },
            "columns": [{
                    data: "product_id",
                    title: "ID",
                    orderable: false,
                    visible: false
                }, {
                    data: "sl_no",
                    title: "SL No.",
                    orderable: false,
                },
                {
                    "data": "product_name",
                    title: "Product Name",
                    orderable: false
                },
                {
                    data: 'supplier_name',
                    title: 'Supplier Name',
                    orderable: false
                },
                {
                    data: 'category_name',
                    title: 'Category',
                    orderable: false
                },
                {
                    data: 'gst_type',
                    title: 'GST Type & Rate',
                    orderable: false
                },
                {
                    "data": "added_date",
                    title: "Added Date",
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