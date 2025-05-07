<?php
// Table schema for warehouse
/*
CREATE TABLE `tbl_warehouse` (
  `warehouse_id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`warehouse_id`)
);*/

require_once 'includes/config/after-login.php';
$title = 'Warehouse';
$form_action = 'add';

if (isset($_POST['submit_warehouse'])) {
    $form_action = isset($_POST['form_action']) ? $_POST['form_action'] : 'add';
    $id = filter_var($_POST['id']);
    $warehouse_name = filter_var($_POST['warehouse_name']);
    $location = filter_var($_POST['location']);
    $description = filter_var($_POST['description']);

    if ($form_action == 'add') {
        $query = $conn->prepare("SELECT * FROM `tbl_warehouse` WHERE warehouse_name = :warehouse_name AND is_deleted = 0");
        $query->bindParam(':warehouse_name', $warehouse_name);
        $query->execute();

        if ($query->rowCount() > 0) {
            $res = ['status' => 'error', 'message' => 'Warehouse already exists'];
            echo json_encode($res);
            exit;
        }

        $query = $conn->prepare("INSERT INTO `tbl_warehouse` (warehouse_name, location, description, created_by, updated_by) VALUES (:warehouse_name, :location, :description, :created_by, :updated_by)");
        $query->bindParam(':warehouse_name', $warehouse_name);
        $query->bindParam(':location', $location);
        $query->bindParam(':description', $description);
        $query->bindParam(':created_by', $user_id);
        $query->bindParam(':updated_by', $user_id);
        $res = $query->execute();
    } else {
        $stmt = $conn->prepare("SELECT * FROM `tbl_warehouse` WHERE warehouse_id = :warehouse_id AND is_deleted = 0");
        $stmt->bindParam(':warehouse_id', $id);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $res = ['status' => 'error', 'message' => 'Warehouse not exists'];
            echo json_encode($res);
            exit;
        }

        $stmt = $conn->prepare("UPDATE `tbl_warehouse` SET warehouse_name = :warehouse_name, location = :location, description = :description, updated_by = :updated_by WHERE warehouse_id = :warehouse_id");
        $stmt->bindParam(':warehouse_name', $warehouse_name);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':updated_by', $user_id);
        $stmt->bindParam(':warehouse_id', $id);
        $res = $stmt->execute();
    }

    if ($res) {
        if ($form_action == 'add') {
            $res = ['status' => 'success', 'message' => 'Warehouse added successfully', 'redirect' => 'warehouse'];
        } else {
            $res = ['status' => 'success', 'message' => 'Warehouse updated successfully', 'redirect' => 'warehouse'];
        }
    } else {
        $res = ['status' => 'error', 'message' => 'An error occurred while adding warehouse'];
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
    $total = getCount('tbl_warehouse', ['is_deleted' => 0]);
    $sql = "SELECT * FROM `tbl_warehouse` WHERE is_deleted = 0";

    if (!empty($search)) {
        $sql .= " AND (warehouse_name LIKE :search OR location LIKE :search OR description LIKE :search)";
    }

    $sql .= " ORDER BY " . $columns[$order]['data'] . " $order_dir LIMIT :start, :length";

    $stmt = $conn->prepare($sql);
    if (!empty($search)) {
        $search = "%$search%";
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $i = $start;
    foreach ($data as $key => $row) {
        $data[$key]['sl_no'] = ++$i;
        $data[$key]['warehouse_name'] = html_entity_decode($row['warehouse_name']);
        $data[$key]['location'] = html_entity_decode($row['location']);
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
    $id = filter_var($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM `tbl_warehouse` WHERE warehouse_id = :warehouse_id AND is_deleted = 0");
    $stmt->bindParam(':warehouse_id', $id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $title = 'Edit Warehouse';
    $form_action = 'edit';

    if (!$row) {
        echo '<script>alert("Warehouse not found");window.location.assign("warehouse")</script>';
        exit;
    }
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
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header p-3 border-bottom">
                                        <h4 class="card-title mb-0"><?= $title ?></h4>
                                    </div>
                                    <div class="card-body py-3">
                                        <form action="warehouse" method="post" class="form">
                                            <input type="hidden" name="form_action" value="<?= $form_action ?>">
                                            <input type="hidden" name="id"
                                                value="<?= isset($row['warehouse_id']) ? $row['warehouse_id'] : '' ?>">

                                            <div class="row">
                                                <!-- Warehouse Name -->
                                                <div class="form-group col-md-12">
                                                    <label for="warehouse_name">Warehouse Name</label>
                                                    <input type="text" class="form-control" id="warehouse_name"
                                                        name="warehouse_name" placeholder="Enter Warehouse Name"
                                                        value="<?= isset($row['warehouse_name']) ? special_echo($row['warehouse_name']) : '' ?>"
                                                        required>
                                                </div>

                                                <!-- Location -->
                                                <div class="form-group col-md-12">
                                                    <label for="location">Location</label>
                                                    <input type="text" class="form-control" id="location"
                                                        name="location" placeholder="Enter Location"
                                                        value="<?= isset($row['location']) ? special_echo($row['location']) : '' ?>"
                                                        required>
                                                </div>

                                                <!-- Description -->
                                                <div class="form-group col-md-12">
                                                    <label for="description">Description</label>
                                                    <textarea class="form-control" id="description" name="description"
                                                        placeholder="Enter Description" rows="3"
                                                        required><?= isset($row['description']) ? special_echo($row['description']) : '' ?></textarea>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <button type="submit" name="submit_warehouse"
                                                        class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8 <?= ($form_action == 'edit') ? 'd-none' : '' ?>">
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
                url: "warehouse",
                type: "POST"
            },
            "columns": [{
                    "data": "warehouse_id",
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
                    "data": "warehouse_name",
                    "title": "Warehouse Name",
                    "orderable": false
                },
                {
                    "data": "location",
                    "title": "Location",
                    "orderable": false
                },
                {
                    "data": "description",
                    "title": "Description",
                    "orderable": false
                },
                {
                    "data": "warehouse_id",
                    "render": function(data, type, row) {
                        return `<a href="warehouse?id=${data}" class="btn btn-sm btn-primary">Edit</a>`;
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
