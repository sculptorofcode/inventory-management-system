<?php
require_once 'includes/config/after-login.php';
$title = 'Product Category';
$form_action = 'add';
if (isset($_POST['submit_product_category'])) {
    $form_action = isset($_POST['form_action']) ? $_POST['form_action'] : 'add';
    $id = filtervar($_POST['id']);
    $category_name = filtervar($_POST['category_name']);
    $description = filtervar($_POST['description']);

    if ($form_action == 'add') {
        $res = addProductCategory($category_name, $description);
    } else {
        $res = updateProductCategory($id, $category_name, $description);
    }

    if ($res) {
        if ($form_action == 'add') {
            $res = ['status' => 'success', 'message' => 'Product category added successfully', 'redirect' => 'product-category'];
        } else {
            $res = ['status' => 'success', 'message' => 'Product category updated successfully', 'redirect' => 'product-category'];
        }
    } else {
        $res = ['status' => 'error', 'message' => 'An error occurred while adding product category'];
    }

    echo json_encode($res);
    exit;
}


if (isset($_REQUEST['draw'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];
    $total = getCount('tbl_product_categories', []);
    $sql = "SELECT * FROM `tbl_product_categories`";

    if (!empty($search)) {
        $sql .= " WHERE (category_name LIKE '%$search%' OR description LIKE '%$search%')";
    }

    $sql .= " ORDER BY " . $columns[$order]['data'] . " $order_dir LIMIT $start, $length";

    $stmt = $conn->prepare($sql);

    $stmt->execute();

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $i = 0;
    foreach ($data as $key => $row) {
        $data[$key]['sl_no'] = ++$i;
        $data[$key]['category_name'] = html_entity_decode($row['category_name']);
        $data[$key]['description'] = html_entity_decode($row['description']);
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

if (isset($_GET['id'])) {
    $id = filtervar($_GET['id']);
    $row = getProductCategoryById($id);
    $title = 'Edit Supplier';
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
                        <div class="row">
                            <div class="col-md-6">
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
                                                <!-- Product Category -->
                                                <div class="form-group col-md-12">
                                                    <label for="category_name">Product Category</label>
                                                    <input type="text" class="form-control" id="category_name"
                                                        name="category_name" placeholder="Enter Product Category"
                                                        value="<?= isset($row['category_name']) ? special_echo($row['category_name']) : '' ?>"
                                                        required>
                                                </div>

                                                <!-- Description -->
                                                <div class="form-group col-md-12">
                                                    <label for="description">Description</label>
                                                    <textarea class="form-control" id="description" name="description"
                                                        placeholder="Enter Product Description" rows="3"
                                                        required><?= isset($row['description']) ? special_echo($row['description']) : '' ?></textarea>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <button type="submit" name="submit_product_category"
                                                        class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header p-3 border-bottom">
                                        <h4 class="card-title mb-0"><?= $title ?> List</h4>
                                    </div>
                                    <div class="card-body p-3">
                                        <table class="table table-bordered" id="dataTable">
                                            <thead class="bg-primary text-white"></thead>
                                        </table>
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
    $(function() {
        <?php
            if (isset($row['postal_code'])) {
                echo '$("#postal_code").trigger("input");';
            }
            ?>
        $("#dataTable").DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "product-category",
                type: "POST"
            },
            "columns": [{
                    "data": "category_id",
                    "title": "ID",
                    "orderable": false,
                    "visible": false
                },
                {
                    "data": "sl_no",
                    "title": "SL No.",
                    "orderable": false,
                },
                {
                    "data": "category_name",
                    "title": "Product Category",
                    "orderable": false
                },
                {
                    "data": "description",
                    "title": "Description",
                    "orderable": false
                },
                {
                    "data": "category_id",
                    "render": function(data, type, row) {
                        return `<a href="product-category?id=${data}" class="btn btn-sm btn-primary">Edit</a>`;
                    },
                    "title": "Action",
                    "orderable": false
                }
            ]
        })
    })
    </script>
</body>

</html>