<?php
require_once 'includes/config/after-login.php';
$title = 'Stock List';
if (isset($_REQUEST['draw']) && isset($_REQUEST['stock_list'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];

    $total = getCount($table_products, [], 'stock > 0');

    $sql = "SELECT p.*,sp.supplier_name,c.category_name,s.* FROM $table_stock s
            JOIN $table_products p ON s.product_id = p.product_id
            JOIN $table_product_categories c ON p.category = c.category_id
            JOIN $table_suppliers sp ON p.supplier_id = sp.supplier_id
            WHERE p.stock > 0";

    if ($search) {
        $sql .= " AND (p.product_name LIKE '%$search%' OR c.category_name LIKE '%$search%' OR sp.supplier_name LIKE '%$search%')";
    }

    if (isset($_REQUEST['product']) && $_REQUEST['product']) {
        $sql .= " AND p.product_id = {$_REQUEST['product']}";
    }

    if (isset($_REQUEST['category']) && $_REQUEST['category']) {
        $sql .= " AND p.category = {$_REQUEST['category']}";
    }

    if (isset($_REQUEST['batch_number']) && $_REQUEST['batch_number']) {
        $sql .= " AND s.batch_number LIKE '%{$_REQUEST['batch_number']}%'";
    }

    $sql .= " ORDER BY p.{$columns[$order]['data']} $order_dir LIMIT $start, $length";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = [];
    while($row = $stmt->fetch()) {
        $row['sl_no'] = $start + 1;
        $row['stock'] = $row['quantity'];
        $row['category_name'] = html_entity_decode($row['category_name']);
        $row['product_name'] = html_entity_decode($row['product_name']);
        $row['purchase_price'] = rupee($row['purchase_price'], 2);
        $row['added_date'] = !empty($row['added_on']) ? date('d M Y', strtotime($row['added_on'])) : '';
        $row['batch_number'] = '<a href="javascript:void(0)" onclick="stockReport(\'' . $row['batch_number'] . '\',' . $row['product_id'] . ')">' . $row['batch_number'] . '</a>';
        $data[] = $row;
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

if (isset($_REQUEST['draw']) && isset($_REQUEST['stock_report'])) {
    $draw = $_REQUEST['draw'];
    $start = $_REQUEST['start'];
    $length = $_REQUEST['length'];
    $search = $_REQUEST['search']['value'];
    $order = $_REQUEST['order'][0]['column'];
    $order_dir = $_REQUEST['order'][0]['dir'];
    $columns = $_REQUEST['columns'];

    $product_id = $_REQUEST['product_id'];
    $batch_number = $_REQUEST['batch_number'];

    $sql = "SELECT *,$table_products.product_name FROM $table_stock_transactions 
            LEFT JOIN $table_stock ON $table_stock.stock_id = $table_stock_transactions.stock_id
            LEFT JOIN $table_products ON $table_products.product_id = $table_stock.product_id
            WHERE $table_stock_transactions.product_id = :product_id AND $table_stock.batch_number = :batch_number";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':batch_number', $batch_number);
    $stmt->execute();

    $total = $stmt->rowCount();

    $sql .= " ORDER BY $table_stock_transactions.created_at DESC LIMIT $start, $length";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':batch_number', $batch_number);

    $stmt->execute();

    $data = [];

    while ($row = $stmt->fetch()) {
        $row['sl_no'] = $start + 1;
        $row['added_on'] = date('d M Y', strtotime($row['created_at']));
        $row['last_updated'] = $row['updated_at'] ? date('d M Y h:i A', strtotime($row['updated_at'])) : '';
        $row['product_name'] = html_entity_decode($row['product_name']);
        $data[] = $row;
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

if (isset($_REQUEST['stock-report'])) {
    $product_id = $_REQUEST['product_id'];
    $batch_number = $_REQUEST['batch_number'];
    $product = getProductById($product_id);
    $stmt = $conn->prepare("SELECT * FROM $table_stock WHERE product_id = :product_id");
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $stocks = $stmt->fetchAll();
    if (!$stocks) { ?>
        <div class="alert alert-danger">No stock found for this product</div>
        <?php
        exit;
    }
    ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h4 class="text-center">Stock Report of <?= $product['product_name'] ?></h4>
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Sl. No.</th>
                        <th>Date</th>
                        <th>Quantity</th>
                        <th>Batch Number</th>
                        <th>Updated</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 0;
                    foreach ($stocks as $stock) {
                        $i++;
                        ?>
                        <tr>
                            <td><?= $i ?></td>
                            <td><?= date('d M Y', strtotime($stock['added_on'])) ?></td>
                            <td><?= $stock['quantity'] ?></td>
                            <td><?= $stock['batch_number'] ?></td>
                            <td><?= $stock['last_updated'] ? date('d M Y h:i A', strtotime($stock['last_updated'])) : '' ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    exit;
}

if (isset($_REQUEST['getProducts'])) {
    $category_id = $_REQUEST['category'];
    $products = getAllProducts($category_id);
    echo json_encode($products);
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
                        <div class="card-header border-bottom py-3">
                            <h5 class="card-title mb-0"><?= $title ?></h5>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row justify-content-center align-items-end mb-3">
                                <div class="col-md-3">
                                    <label for="category">Category</label>
                                    <select name="category" id="category">
                                        <option value="">Select Category</option>
                                        <?php
                                        $categories = getAllProductCategories();
                                        foreach ($categories as $category) {
                                            ?>
                                            <option value="<?= $category['category_id'] ?>"><?= html_entity_decode($category['category_name']) ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="product">Product</label>
                                    <select name="product" id="product">
                                        <option value="">Select Product</option>
                                        <?php
                                        $products = getAllProducts();
                                        foreach ($products as $product) {
                                            ?>
                                            <option value="<?= $product['product_id'] ?>"><?= html_entity_decode($product['product_name']) ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 d-none">
                                    <label for="batch_number">Batch Number</label>
                                    <input type="text" name="batch_number" id="batch_number" class="form-control"
                                           placeholder="Batch Number" list="batch_number">
                                    <datalist id="batch_number">
                                        <?php
                                        $stmt = $conn->prepare("SELECT DISTINCT batch_number FROM $table_stock");
                                        $stmt->execute();
                                        $batches = $stmt->fetchAll();
                                        foreach ($batches

                                        as $batch) {
                                        ?>
                                        <option value="<?= $batch['batch_number'] ?>">
                                            <?php
                                            }
                                            ?>
                                    </datalist>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-primary" id="search">Search</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="stock-list">
                                            <thead class="bg-primary text-white">
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th colspan="4" style="text-align:right">Total:</th>
                                                <th id="total-stock"></th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header border-bottom py-3">
                            <h5 class="card-title mb-0">Stock Report</h5>
                        </div>
                        <div class="card-body pt-3">
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="hidden" name="stock_report_product_id" id="stock_report_product_id">
                                    <input type="hidden" name="stock_report_batch_number" id="stock_report_batch_number">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="stock-report">
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
    $(function () {
        let table = $('#stock-list').DataTable({
            "processing": true,
            "serverSide": true,
            "searching": false,
            "responsive": true,
            "ajax": {
                url: 'stock-list',
                type: 'POST',
                data: function (d) {
                    d.stock_list = true;
                    d.product = $('#product').val();
                    d.category = $('#category').val();
                    d.batch_number = $('#batch_number').val();
                }
            },
            "columns": [
                {"data": "product_id", "visible": false, "searchable": false},
                {"data": "sl_no", "title": "Sl. No.", "orderable": false},
                {"data": "batch_number", "title": "Batch Number", "orderable": false},
                {"data": "product_name", "title": "Product Name", "orderable": false},
                {"data": "category_name", "title": "Category", "orderable": false},
                {"data": "stock", "title": "Stock", "orderable": false},
                {"data": "purchase_price", "title": "Unit Price", "orderable": false},
                {"data": "added_date", "title": "Added Date", "orderable": false},
            ],
            "footerCallback": function (row, data, start, end, display) {
                let totalStock = 0;

                for (let i = 0; i < data.length; i++) {
                    totalStock += parseFloat(data[i].stock) || 0;
                }

                $('#total-stock').html(totalStock);
            }
        });

        $('#search').on('click', function () {
            table.ajax.reload();
        });

        $("#category").selectize({
            create: false,
            sortField: 'text',
            dropdownParent: 'body',
            onChange: function (value) {
                table.ajax.reload();
                $.ajax({
                    url: 'stock-list',
                    type: 'POST',
                    data: {category: value, getProducts: true},
                    success: function (response) {
                        try {
                            let data = JSON.parse(response);
                            let options = '<option value="">Select Product</option>';
                            $('#product').selectize()[0].selectize.destroy();
                            data.forEach(function (product) {
                                options += `<option value="${product.product_id}">${product.product_name}</option>`;
                            });
                            $('#product').html(options);
                            $('#product').selectize({
                                create: false,
                                sortField: 'text',
                                dropdownParent: 'body',
                                onChange: function (value) {
                                    table.ajax.reload();
                                }
                            });
                        } catch (e) {
                            console.error(e);
                        }
                    }
                })
            }
        });

        $('#product').selectize({
            create: false,
            sortField: 'text',
            dropdownParent: 'body',
            onChange: function (value) {
                table.ajax.reload();
            }
        });

        window.stockReportTable = $('#stock-report').DataTable({
            "processing": true,
            "serverSide": true,
            "searching": false,
            "responsive": true,
            "ajax": {
                url: 'stock-list',
                type: 'POST',
                data: function (d) {
                    d.stock_report = true;
                    d.product_id = $('#stock_report_product_id').val();
                    d.batch_number = $('#stock_report_batch_number').val();
                }
            },
            "columns": [
                {"data": "sl_no", "title": "Sl. No.", "orderable": false, width: '7%'},
                {"data": "product_name", "title": "Product", "orderable": false, width: '12%'},
                {"data": "batch_number", "title": "Batch Number", "orderable": false, width: '12%'},
                {"data": "added_on", "title": "Date", "orderable": false, width: '12%'},
                {"data": "quantity_change", "title": "Quantity Change", "orderable": false},
                {"data": "transaction_type", "title": "Transaction Type", "orderable": false},
                {"data": "order_reference", "title": "Order Reference", "orderable": false},
                {"data": "last_updated", "title": "Updated", "orderable": false, width: '12%'},
            ]
        });
    });

    function stockReport(batch_number, product_id) {
        $('#stock_report_product_id').val(product_id);
        $('#stock_report_batch_number').val(batch_number);
        stockReportTable.ajax.reload();
    }
</script>
</body>

</html>