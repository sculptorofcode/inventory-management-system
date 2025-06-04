<?php
// Table schema for warehouse_location
/*
Hierarchical structure Warehouse → Zone → Aisle → Rack → Shelf → Bin.
Warehouse can have multiple Zones.
Zone can have multiple Aisles.
Aisle can have multiple Racks.
Rack can have multiple Shelves.
Shelf can have multiple Bins.

CREATE TABLE `tbl_warehouse_location` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `warehouse_id` int(11) NOT NULL,
  `parent_location_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL CHECK (`type` IN ('Zone', 'Aisle', 'Rack', 'Shelf', 'Bin')),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) NOT NULL,
  `deleted_by` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`location_id`),
  FOREIGN KEY (`warehouse_id`) REFERENCES `tbl_warehouse`(`warehouse_id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_location_id`) REFERENCES `tbl_warehouse_location`(`location_id`) ON DELETE SET NULL
);*/

require_once 'includes/config/after-login.php';
$title = 'Warehouse Location';
$form_action = 'add';

if (isset($_POST['submit_warehouse_location'])) {
    $form_action = isset($_POST['form_action']) ? $_POST['form_action'] : 'add';
    $id = filtervar($_POST['id']);
    $warehouse_id = filtervar($_POST['warehouse_id']);
    $parent_location_id = isset($_POST['parent_location_id']) && !empty($_POST['parent_location_id']) ? filtervar($_POST['parent_location_id']) : NULL;
    $name = filtervar($_POST['name']);
    $type = filtervar($_POST['type']);

    if ($form_action == 'add') {
        $query = $conn->prepare("SELECT * FROM `tbl_warehouse_location` WHERE warehouse_id = :warehouse_id AND name = :name AND type = :type AND is_deleted = 0");
        $query->bindParam(':warehouse_id', $warehouse_id);
        $query->bindParam(':name', $name);
        $query->bindParam(':type', $type);
        $query->execute();

        if ($query->rowCount() > 0) {
            $res = ['status' => 'error', 'message' => 'Warehouse location already exists'];
            echo json_encode($res);
            exit;
        }

        $query = $conn->prepare("INSERT INTO `tbl_warehouse_location` (warehouse_id, parent_location_id, name, type, created_by, updated_by) VALUES (:warehouse_id, :parent_location_id, :name, :type, :created_by, :updated_by)");
        $query->bindParam(':warehouse_id', $warehouse_id);
        $query->bindParam(':parent_location_id', $parent_location_id);
        $query->bindParam(':name', $name);
        $query->bindParam(':type', $type);
        $query->bindParam(':created_by', $user_id);
        $query->bindParam(':updated_by', $user_id);
        $res = $query->execute();
    } else {
        $stmt = $conn->prepare("SELECT * FROM `tbl_warehouse_location` WHERE warehouse_id = :warehouse_id AND is_deleted = 0");
        $stmt->bindParam(':warehouse_id', $warehouse_id);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $res = ['status' => 'error', 'message' => 'Warehouse location not exists'];
            echo json_encode($res);
            exit;
        }

        $stmt = $conn->prepare("UPDATE `tbl_warehouse_location` SET warehouse_id = :warehouse_id, parent_location_id = :parent_location_id, name = :name, type = :type, updated_by = :updated_by WHERE location_id = :location_id");
        $stmt->bindParam(':warehouse_id', $warehouse_id);
        $stmt->bindParam(':parent_location_id', $parent_location_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':updated_by', $user_id);
        $stmt->bindParam(':location_id', $id);
        $res = $stmt->execute();
    }

    if ($res) {
        if ($form_action == 'add') {
            $res = ['status' => 'success', 'message' => 'Warehouse location added successfully', 'function' => 'reloadPage'];
        } else {
            $res = ['status' => 'success', 'message' => 'Warehouse location updated successfully', 'redirect' => 'warehouse-location'];
        }
    } else {
        $res = ['status' => 'error', 'message' => 'An error occurred while adding warehouse location'];
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
    $total = getCount('tbl_warehouse_location', ['is_deleted' => 0]);
    $sql = "SELECT * 
            FROM `tbl_warehouse_location`
            LEFT JOIN `tbl_warehouse` ON `tbl_warehouse_location`.`warehouse_id` = `tbl_warehouse`.`warehouse_id`
            WHERE tbl_warehouse_location.is_deleted = 0";

    if (!empty($search)) {
        $sql .= " AND (name LIKE :search OR type LIKE :search)";
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
        $data[$key]['name'] = html_entity_decode($row['name']);
        $data[$key]['type'] = html_entity_decode($row['type']);
        $data[$key]['warehouse'] = html_entity_decode($row['warehouse_name']);
        $data[$key]['parent_location'] = isset($row['parent_location_id']) ? html_entity_decode(getValue('tbl_warehouse_location', 'name', ['location_id' => $row['parent_location_id']])) : '';
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
    $stmt = $conn->prepare("SELECT * FROM `tbl_warehouse_location` WHERE location_id = :location_id AND is_deleted = 0");
    $stmt->bindParam(':location_id', $id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $title = 'Edit Warehouse Location';
    $form_action = 'edit';

    if (!$row) {
        echo '<script>alert("Warehouse location not found");window.location.assign("warehouse-location")</script>';
        exit;
    }
}

if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'get_parent_location') {
    $type = $_POST['type'];
    $warehouse_id = $_POST['warehouse_id'];

    $hierarchy = [
        'Zone' => [],
        'Aisle' => ['Zone'],
        'Rack' => ['Aisle'],
        'Shelf' => ['Rack'],
        'Bin' => ['Shelf']
    ];

    $parent_types = $hierarchy[$type];
    $placeholders = implode(',', array_fill(0, count($parent_types), '?'));

    $sql = "SELECT * FROM `tbl_warehouse_location` WHERE ";
    if (count($parent_types) > 0) {
        $sql .= "type IN ($placeholders) AND ";
    }
    $sql .= "warehouse_id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($sql);
    $params = array_merge($parent_types, [$warehouse_id]);
    $stmt->execute($params);
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $options = '<option value="" selected disabled>Select Parent Location</option>';
    foreach ($locations as $location) {
        $options .= '<option value="' . $location['location_id'] . '">' . $location['name'] . '</option>';
    }

    echo $options;
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
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header p-3 border-bottom">
                                        <h4 class="card-title mb-0"><?= $title ?></h4>
                                    </div>
                                    <div class="card-body py-3">
                                        <form action="warehouse-location" method="post" class="form">
                                            <input type="hidden" name="form_action" value="<?= $form_action ?>">
                                            <input type="hidden" name="id"
                                                value="<?= isset($row['location_id']) ? $row['location_id'] : '' ?>">

                                            <div class="row">
                                                <!-- Warehouse ID -->
                                                <div class="form-group col-md-12">
                                                    <label for="warehouse_id">Warehouse</label>
                                                    <select class="form-select" id="warehouse_id" name="warehouse_id" required>
                                                        <option value="" selected disabled>Select Warehouse</option>
                                                        <?php
                                                        $warehouses = getTable('tbl_warehouse', ['is_deleted' => 0, 'status' => 'active']);
                                                        foreach ($warehouses as $warehouse) {
                                                            $selected = isset($row['warehouse_id']) && $row['warehouse_id'] == $warehouse['warehouse_id'] ? 'selected' : '';
                                                            echo '<option value="' . $warehouse['warehouse_id'] . '" ' . $selected . '>' . $warehouse['warehouse_name'] . '</option>';
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <!-- Type -->
                                                <div class="form-group col-md-12">
                                                    <label for="type">Type</label>
                                                    <select class="form-select" id="type" name="type" required>
                                                        <option value="" selected disabled>Select Type</option>
                                                        <option value="Zone" <?= isset($row['type']) && $row['type'] == 'Zone' ? 'selected' : '' ?>>Zone</option>
                                                        <option value="Aisle" <?= isset($row['type']) && $row['type'] == 'Aisle' ? 'selected' : '' ?>>Aisle</option>
                                                        <option value="Rack" <?= isset($row['type']) && $row['type'] == 'Rack' ? 'selected' : '' ?>>Rack</option>
                                                        <option value="Shelf" <?= isset($row['type']) && $row['type'] == 'Shelf' ? 'selected' : '' ?>>Shelf</option>
                                                        <option value="Bin" <?= isset($row['type']) && $row['type'] == 'Bin' ? 'selected' : '' ?>>Bin</option>
                                                    </select>
                                                </div>

                                                <!-- Parent Location ID -->
                                                <div class="form-group col-md-12">
                                                    <label for="parent_location_id">Parent Location</label>
                                                    <select class="form-select" id="parent_location_id" name="parent_location_id">
                                                        <option value="" selected disabled>Select Parent Location</option>
                                                        <?php
                                                        if ($form_action == 'edit') {
                                                            $filters = ['is_deleted' => 0];
                                                            if ($row['type'] == 'Zone') {
                                                                $filters['warehouse_id'] = $row['warehouse_id'];
                                                            }else{
                                                                $filters['location_id'] = $row['parent_location_id'];
                                                            }
                                                            $locations = getTable('tbl_warehouse_location', $filters);
                                                            foreach ($locations as $location) {
                                                                $selected = isset($row['parent_location_id']) && $row['parent_location_id'] == $location['location_id'] ? 'selected' : '';
                                                                echo '<option value="' . $location['location_id'] . '" ' . $selected . '>' . $location['name'] . '</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>

                                                <!-- Name -->
                                                <div class="form-group col-md-12">
                                                    <label for="name">Name</label>
                                                    <input type="text" class="form-control" id="name"
                                                        name="name" placeholder="Enter Name"
                                                        value="<?= isset($row['name']) ? special_echo($row['name']) : '' ?>"
                                                        required>
                                                </div>

                                            </div>

                                            <div class="row">
                                                <div class="form-group col-md-12">
                                                    <button type="submit" name="submit_warehouse_location"
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
            let table = $("#dataTable").DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "warehouse-location",
                    type: "POST"
                },
                "order": [
                    [0, "desc"]
                ],
                "columns": [{
                        "data": "location_id",
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
                        "data": "warehouse",
                        "title": "Warehouse",
                        "orderable": false
                    },
                    {
                        "data": "parent_location",
                        "title": "Parent Location",
                        "orderable": false
                    },
                    {
                        "data": "name",
                        "title": "Name",
                        "orderable": false
                    },
                    {
                        "data": "type",
                        "title": "Type",
                        "orderable": false
                    },
                    {
                        "data": "location_id",
                        "render": function(data, type, row) {
                            return `<a href="warehouse-location?id=${data}" class="btn btn-sm btn-primary">Edit</a>`;
                        },
                        "title": "Action",
                        "orderable": false
                    }
                ]
            });

            // Load parent locations based on selected type
            $(document).on('change', '#type,#warehouse_id', function() {
                var type = $('#type').val();
                var warehouse_id = $('#warehouse_id').val();

                if (!warehouse_id) {
                    toastr.error('Please select warehouse');
                    $('#type').val('');
                    return;
                }

                if (!type) {
                    $('#parent_location_id').html('<option value="" selected disabled>Select Parent Location</option>');
                    return;
                }

                if (type == 'Zone') {
                    $('#parent_location_id').html('<option value="" selected disabled>Select Parent Location</option>');
                    return;
                }

                $.ajax({
                    url: 'warehouse-location',
                    type: 'POST',
                    data: {
                        type: type,
                        warehouse_id: warehouse_id,
                        action: 'get_parent_location'
                    },
                    success: function(response) {
                        $('#parent_location_id').html(response);
                    }
                });
            });

            window.reloadPage = function() {
                table.ajax.reload();
                $("#name").val('');
            }
        });
    </script>
</body>

</html>