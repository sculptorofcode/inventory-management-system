<?php
require_once 'includes/config/after-login.php';
$title = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
      data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/>
    <?php include './includes/layouts/styles.php'; ?>
    <link rel="stylesheet" href="assets/libs/apex-charts/apex-charts.css">
</head>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <?php include './includes/layouts/sidebar.php'; ?>
        <div class="layout-page">
            <?php include './includes/layouts/navbar.php'; ?>
            <div class="content-wrapper">

                <div class="container-fluid flex-grow-1 container-p-y">
                    <h1 class="mb-4">Dashboard</h1>

                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card text-white bg-success">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-box-open fa-2x me-3"></i>
                                        <div>
                                            <h5 class="card-title">Total Items</h5>
                                            <p class="card-text" id="total-items">0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card text-white bg-warning">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                                        <div>
                                            <h5 class="card-title">Low Stock Alerts</h5>
                                            <p class="card-text" id="low-stock">0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card text-white bg-primary">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-dollar-sign fa-2x me-3"></i>
                                        <div>
                                            <h5 class="card-title">Total Sales</h5>
                                            <p class="card-text" id="total-sales">0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="card text-white bg-info">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-shopping-cart fa-2x me-3"></i>
                                        <div>
                                            <h5 class="card-title">New Transactions</h5>
                                            <p class="card-text" id="new-transactions">0</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- New Widget for Top Selling Items -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Top Selling Items</h5>
                                    <ul id="top-selling-items" class="list-group">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Item A
                                            <span class="badge bg-primary rounded-pill">100</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Item B
                                            <span class="badge bg-primary rounded-pill">80</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Item C
                                            <span class="badge bg-primary rounded-pill">60</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="row mb-4">
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Inventory Levels</h5>
                                    <div id="inventoryChart"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Sales Distribution</h5>
                                    <div id="salesChart"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Recent Activity</h5>
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Item</th>
                                            <th>Action</th>
                                            <th>Quantity</th>
                                        </tr>
                                        </thead>
                                        <tbody id="recent-activity-table">
                                        <!-- Rows of recent activity will be populated here -->
                                        </tbody>
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
<script src="assets/libs/apex-charts/apexcharts.js"></script>
<script>
    // Sample data for total counts
    document.getElementById('total-items').innerText = 100; // Replace with dynamic data
    document.getElementById('low-stock').innerText = 5; // Replace with dynamic data
    document.getElementById('total-sales').innerText = 3000; // Replace with dynamic data
    document.getElementById('new-transactions').innerText = 15; // Replace with dynamic data

    // Sample recent activity data
    const recentActivities = [
        {date: '2024-11-01', item: 'Item A', action: 'Added', quantity: 10},
        {date: '2024-11-02', item: 'Item B', action: 'Sold', quantity: 5},
        {date: '2024-11-03', item: 'Item C', action: 'Updated', quantity: 3},
    ];

    const recentActivityTable = document.getElementById('recent-activity-table');
    recentActivities.forEach(activity => {
        const row = `<tr>
                                        <td>${activity.date}</td>
                                        <td>${activity.item}</td>
                                        <td>${activity.action}</td>
                                        <td>${activity.quantity}</td>
                                    </tr>`;
        recentActivityTable.innerHTML += row;
    });

    // Sample Data for Charts using ApexCharts
    var optionsInventory = {
        chart: {
            type: 'bar',
            height: 350
        },
        series: [{
            name: 'Inventory Levels',
            data: [10, 20, 5, 30] // Replace with dynamic data
        }],
        xaxis: {
            categories: ['Item A', 'Item B', 'Item C', 'Item D']
        }
    };

    var inventoryChart = new ApexCharts(document.querySelector("#inventoryChart"), optionsInventory);
    inventoryChart.render();

    var optionsSales = {
        chart: {
            type: 'line',
            height: 350
        },
        series: [{
            name: 'Sales',
            data: [50, 70, 60, 90] // Replace with dynamic data
        }],
        xaxis: {
            categories: ['January', 'February', 'March', 'April']
        }
    };

    var salesChart = new ApexCharts(document.querySelector("#salesChart"), optionsSales);
    salesChart.render();
</script>
</body>

</html>
