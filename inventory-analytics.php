<?php
require_once 'includes/config/after-login.php';
$title = 'Inventory Analytics Dashboard';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'valuation_comparison':
                $product_id = intval($_POST['product_id']);
                $quantity = intval($_POST['quantity']) ?: null;
                $result = compareValuationMethods($product_id, $quantity);
                echo json_encode(['status' => 'success', 'data' => $result]);
                break;
                
            case 'inventory_control':
                $product_id = intval($_POST['product_id']);
                $params = [
                    'lead_time' => intval($_POST['lead_time'] ?? 7),
                    'ordering_cost' => floatval($_POST['ordering_cost'] ?? 50),
                    'holding_cost_percentage' => floatval($_POST['holding_cost_percentage'] ?? 0.2),
                    'safety_stock' => floatval($_POST['safety_stock'] ?? 0)
                ];
                $result = getInventoryControlAnalysis($product_id, $params);
                echo json_encode(['status' => 'success', 'data' => $result]);
                break;
                
            case 'financial_dashboard':
                $product_id = !empty($_POST['product_id']) ? intval($_POST['product_id']) : null;
                $start_date = $_POST['start_date'] ?? date('Y-m-d', strtotime('-12 months'));
                $end_date = $_POST['end_date'] ?? date('Y-m-d');
                $result = getFinancialDashboard($product_id, $start_date, $end_date);
                echo json_encode(['status' => 'success', 'data' => $result]);
                break;
                
            case 'demand_forecast':
                $product_id = intval($_POST['product_id']);
                $result = getDemandForecastDashboard($product_id);
                echo json_encode(['status' => 'success', 'data' => $result]);
                break;
                  case 'abc_analysis':
                $result = performABCAnalysis();
                echo json_encode(['status' => 'success', 'data' => $result]);
                break;
                
            case 'quick_stats':
                $result = getQuickDashboardStats();
                echo json_encode(['status' => 'success', 'data' => $result]);
                break;
                
            default:
                echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// Get products for dropdowns
$products = getAllProducts();
?>
<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="assets/"
    data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <?php include './includes/layouts/styles.php'; ?>
    <style>
        .analytics-card {
            transition: transform 0.2s ease-in-out;
        }
        .analytics-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #5f61e6;
        }
        .metric-label {
            color: #6c757d;
            font-size: 0.875rem;
        }
        .method-comparison {
            border-left: 4px solid #28a745;
            padding-left: 15px;
            margin: 10px 0;
        }
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .analysis-section {
            margin-bottom: 30px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <?php include './includes/layouts/sidebar.php'; ?>
            <div class="layout-page">
                <?php include './includes/layouts/navbar.php'; ?>
                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        
                        <!-- Page Header -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0">
                                            <i class="bx bx-line-chart me-2"></i>
                                            <?= $title ?>
                                        </h4>
                                        <p class="text-muted mb-0">Comprehensive inventory analysis and forecasting tools</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Analytics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card analytics-card h-100">
                                    <div class="card-body text-center">
                                        <i class="bx bx-package text-primary" style="font-size: 2rem;"></i>
                                        <div class="metric-value" id="total-products">-</div>
                                        <div class="metric-label">Total Products</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card analytics-card h-100">
                                    <div class="card-body text-center">
                                        <i class="bx bx-money text-success" style="font-size: 2rem;"></i>
                                        <div class="metric-value" id="total-value">-</div>
                                        <div class="metric-label">Total Inventory Value</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card analytics-card h-100">
                                    <div class="card-body text-center">
                                        <i class="bx bx-trending-up text-info" style="font-size: 2rem;"></i>
                                        <div class="metric-value" id="turnover-ratio">-</div>
                                        <div class="metric-label">Avg Turnover Ratio</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card analytics-card h-100">
                                    <div class="card-body text-center">
                                        <i class="bx bx-calendar text-warning" style="font-size: 2rem;"></i>
                                        <div class="metric-value" id="days-inventory">-</div>
                                        <div class="metric-label">Avg Days in Inventory</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Analysis Sections -->
                        <div class="row">
                            <!-- Inventory Valuation Methods -->
                            <div class="col-lg-6">
                                <div class="card analysis-section">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="bx bx-calculator me-2"></i>
                                            Inventory Valuation Methods
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="valuationForm" class="not-form-js">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <label class="form-label">Select Product</label>
                                                    <select class="form-select" name="product_id" required>
                                                        <option value="">Choose Product...</option>
                                                        <?php foreach ($products as $product): ?>
                                                            <option value="<?= $product['product_id'] ?>">
                                                                <?= htmlspecialchars($product['product_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Quantity (Optional)</label>
                                                    <input type="number" class="form-control" name="quantity" placeholder="All stock">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary mt-3">
                                                <i class="bx bx-search"></i> Compare Methods
                                            </button>
                                        </form>
                                        
                                        <div class="loading-spinner" id="valuation-loading">
                                            <div class="spinner-border text-primary" role="status"></div>
                                        </div>
                                        
                                        <div id="valuation-results" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Inventory Control Models -->
                            <div class="col-lg-6">
                                <div class="card analysis-section">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="bx bx-cog me-2"></i>
                                            Inventory Control Models
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="controlForm" class="not-form-js">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Select Product</label>
                                                    <select class="form-select" name="product_id" required>
                                                        <option value="">Choose Product...</option>
                                                        <?php foreach ($products as $product): ?>
                                                            <option value="<?= $product['product_id'] ?>">
                                                                <?= htmlspecialchars($product['product_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Lead Time (Days)</label>
                                                    <input type="number" class="form-control" name="lead_time" value="7">
                                                </div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Ordering Cost (₹)</label>
                                                    <input type="number" class="form-control" name="ordering_cost" value="50" step="0.01">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Holding Cost (%)</label>
                                                    <input type="number" class="form-control" name="holding_cost_percentage" value="20" step="0.1">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-success mt-3">
                                                <i class="bx bx-calculator"></i> Calculate EOQ & ROP
                                            </button>
                                        </form>
                                        
                                        <div class="loading-spinner" id="control-loading">
                                            <div class="spinner-border text-success" role="status"></div>
                                        </div>
                                        
                                        <div id="control-results" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Financial Calculations -->
                            <div class="col-lg-8">
                                <div class="card analysis-section">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="bx bx-line-chart me-2"></i>
                                            Financial Calculations Dashboard
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="financialForm" class="not-form-js">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-label">Product (Optional)</label>
                                                    <select class="form-select" name="product_id">
                                                        <option value="">All Products</option>
                                                        <?php foreach ($products as $product): ?>
                                                            <option value="<?= $product['product_id'] ?>">
                                                                <?= htmlspecialchars($product['product_name']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Start Date</label>
                                                    <input type="date" class="form-control" name="start_date" 
                                                           value="<?= date('Y-m-d', strtotime('-12 months')) ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">End Date</label>
                                                    <input type="date" class="form-control" name="end_date" 
                                                           value="<?= date('Y-m-d') ?>">
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-info mt-3">
                                                <i class="bx bx-chart"></i> Generate Financial Report
                                            </button>
                                        </form>
                                        
                                        <div class="loading-spinner" id="financial-loading">
                                            <div class="spinner-border text-info" role="status"></div>
                                        </div>
                                        
                                        <div id="financial-results" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Demand Forecasting -->
                            <div class="col-lg-4">
                                <div class="card analysis-section">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="bx bx-trending-up me-2"></i>
                                            Demand Forecasting
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form id="forecastForm" class="not-form-js">
                                            <div class="mb-3">
                                                <label class="form-label">Select Product</label>
                                                <select class="form-select" name="product_id" required>
                                                    <option value="">Choose Product...</option>
                                                    <?php foreach ($products as $product): ?>
                                                        <option value="<?= $product['product_id'] ?>">
                                                            <?= htmlspecialchars($product['product_name']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="bx bx-trending-up"></i> Generate Forecast
                                            </button>
                                        </form>
                                        
                                        <div class="loading-spinner" id="forecast-loading">
                                            <div class="spinner-border text-warning" role="status"></div>
                                        </div>
                                        
                                        <div id="forecast-results" class="mt-4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ABC Analysis -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card analysis-section">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">
                                            <i class="bx bx-category me-2"></i>
                                            ABC Analysis
                                        </h5>
                                        <button class="btn btn-primary" id="runABCAnalysis">
                                            <i class="bx bx-play"></i> Run ABC Analysis
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="loading-spinner" id="abc-loading">
                                            <div class="spinner-border text-primary" role="status"></div>
                                        </div>
                                        
                                        <div id="abc-results"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <?php include './includes/layouts/dash-footer.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include './includes/layouts/scripts.php'; ?>
    
    <script>
        $(document).ready(function() {
            // Load quick metrics on page load
            loadQuickMetrics();
            
            // Valuation Methods Form
            $('#valuationForm').on('submit', function(e) {
                e.preventDefault();
                
                $('#valuation-loading').show();
                $('#valuation-results').html('');
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $(this).serialize() + '&action=valuation_comparison',
                    dataType: 'json',
                    success: function(response) {
                        $('#valuation-loading').hide();
                        if (response.status === 'success') {
                            displayValuationResults(response.data);
                        } else {
                            showError('Valuation Error', response.message);
                        }
                    },
                    error: function() {
                        $('#valuation-loading').hide();
                        showError('Error', 'Failed to perform valuation analysis');
                    }
                });
            });
            
            // Inventory Control Form
            $('#controlForm').on('submit', function(e) {
                e.preventDefault();
                
                $('#control-loading').show();
                $('#control-results').html('');
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $(this).serialize() + '&action=inventory_control',
                    dataType: 'json',
                    success: function(response) {
                        $('#control-loading').hide();
                        if (response.status === 'success') {
                            displayControlResults(response.data);
                        } else {
                            showError('Control Analysis Error', response.message);
                        }
                    },
                    error: function() {
                        $('#control-loading').hide();
                        showError('Error', 'Failed to perform control analysis');
                    }
                });
            });
            
            // Financial Dashboard Form
            $('#financialForm').on('submit', function(e) {
                e.preventDefault();
                
                $('#financial-loading').show();
                $('#financial-results').html('');
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $(this).serialize() + '&action=financial_dashboard',
                    dataType: 'json',
                    success: function(response) {
                        $('#financial-loading').hide();
                        if (response.status === 'success') {
                            displayFinancialResults(response.data);
                        } else {
                            showError('Financial Analysis Error', response.message);
                        }
                    },
                    error: function() {
                        $('#financial-loading').hide();
                        showError('Error', 'Failed to perform financial analysis');
                    }
                });
            });
            
            // Demand Forecasting Form
            $('#forecastForm').on('submit', function(e) {
                e.preventDefault();
                
                $('#forecast-loading').show();
                $('#forecast-results').html('');
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: $(this).serialize() + '&action=demand_forecast',
                    dataType: 'json',
                    success: function(response) {
                        $('#forecast-loading').hide();
                        if (response.status === 'success') {
                            displayForecastResults(response.data);
                        } else {
                            showError('Forecast Error', response.message);
                        }
                    },
                    error: function() {
                        $('#forecast-loading').hide();
                        showError('Error', 'Failed to generate forecast');
                    }
                });
            });
            
            // ABC Analysis Button
            $('#runABCAnalysis').on('click', function() {
                $('#abc-loading').show();
                $('#abc-results').html('');
                
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: { action: 'abc_analysis' },
                    dataType: 'json',
                    success: function(response) {
                        $('#abc-loading').hide();
                        if (response.status === 'success') {
                            displayABCResults(response.data);
                        } else {
                            showError('ABC Analysis Error', response.message);
                        }
                    },
                    error: function() {
                        $('#abc-loading').hide();
                        showError('Error', 'Failed to perform ABC analysis');
                    }
                });
            });
        });
          function loadQuickMetrics() {
            $.ajax({
                url: '',
                method: 'POST',
                data: { action: 'quick_stats' },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#total-products').text(response.data.total_products);
                        $('#total-value').text(rupee(response.data.total_value));
                        $('#turnover-ratio').text(response.data.turnover_ratio);
                        $('#days-inventory').text(response.data.days_inventory + ' days');
                    } else {
                        // Fallback to basic count if AJAX fails
                        $('#total-products').text('<?= count($products) ?>');
                        $('#total-value').text('Error loading');
                        $('#turnover-ratio').text('Error loading');
                        $('#days-inventory').text('Error loading');
                    }
                },
                error: function() {
                    // Fallback to basic count if AJAX fails
                    $('#total-products').text('<?= count($products) ?>');
                    $('#total-value').text('Error loading');
                    $('#turnover-ratio').text('Error loading');
                    $('#days-inventory').text('Error loading');
                }
            });
        }
        
        function displayValuationResults(data) {
            if (data.error) {
                $('#valuation-results').html(`<div class="alert alert-danger">${data.error}</div>`);
                return;
            }
            
            let html = `
                <h6>Valuation Comparison Results</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="method-comparison">
                            <strong>FIFO Method</strong><br>                            <span class="text-success">${rupee(data.fifo.total_value)}</span><br>
                            <small>Cost per unit: ${rupee(data.fifo.cost_per_unit)}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="method-comparison">
                            <strong>LIFO Method</strong><br>                            <span class="text-info">${rupee(data.lifo.total_value)}</span><br>
                            <small>Cost per unit: ${rupee(data.lifo.cost_per_unit)}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="method-comparison">
                            <strong>WAC Method</strong><br>                            <span class="text-warning">${rupee(data.wac.total_value)}</span><br>
                            <small>Cost per unit: ${rupee(data.wac.cost_per_unit)}</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        Quantity Valued: ${data.quantity_valued || 'All available stock'} units<br>
                        Value Difference: ${rupee(data.comparison.value_difference.toFixed(2))}
                    </small>
                </div>
            `;
            $('#valuation-results').html(html);
        }
        
        function displayControlResults(data) {
            if (data.error || data.analysis.error) {
                $('#control-results').html(`<div class="alert alert-danger">${data.error || data.analysis.error}</div>`);
                return;
            }
            
            let html = `
                <h6>${data.product_name} - Control Analysis</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="method-comparison">
                            <strong>Economic Order Quantity</strong><br>
                            <span class="text-primary">${data.analysis.eoq?.eoq || 'N/A'} units</span><br>
                            <small>Orders per year: ${data.analysis.eoq?.number_of_orders_per_year || 'N/A'}</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="method-comparison">
                            <strong>Reorder Point</strong><br>
                            <span class="text-success">${data.analysis.rop?.reorder_point || 'N/A'} units</span><br>
                            <small>Lead time: ${data.analysis.rop?.lead_time_days || 'N/A'} days</small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <strong>Current Status:</strong><br>
                    <span class="badge ${data.analysis.current_status?.reorder_needed ? 'bg-danger' : 'bg-success'}">
                        ${data.analysis.current_status?.reorder_needed ? 'Reorder Needed' : 'Stock OK'}
                    </span>
                    <small class="d-block mt-1">
                        Current Stock: ${data.analysis.current_status?.current_stock || 0} units<br>
                        Days Remaining: ${data.analysis.current_status?.stock_days_remaining || 'N/A'}
                    </small>
                </div>
            `;
            $('#control-results').html(html);
        }
        
        function displayFinancialResults(data) {
            if (data.error) {
                $('#financial-results').html(`<div class="alert alert-danger">${data.error}</div>`);
                return;
            }
            
            let html = `
                <h6>Financial Metrics Dashboard</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="metric-value text-primary">${data.summary_metrics.turnover_ratio}</div>
                            <div class="metric-label">Turnover Ratio</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="metric-value text-success">${data.summary_metrics.days_in_inventory}</div>
                            <div class="metric-label">Days in Inventory</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="metric-value text-info">${data.summary_metrics.gross_margin_percent}%</div>
                            <div class="metric-label">Gross Margin</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="metric-value text-warning">${data.summary_metrics.roi_percent}%</div>
                            <div class="metric-label">ROI</div>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        Period: ${data.period}<br>
                        Sales Revenue: ${rupee(data.gross_profit_margin.sales_revenue)}<br>
                        COGS: ${rupee(data.gross_profit_margin.cost_of_goods_sold)}<br>
                        Gross Profit: ${rupee(data.gross_profit_margin.gross_profit)}
                    </small>
                </div>
            `;
            $('#financial-results').html(html);
        }
        
        function displayForecastResults(data) {
            if (data.error) {
                $('#forecast-results').html(`<div class="alert alert-danger">${data.error}</div>`);
                return;
            }
            
            let html = `
                <h6>${data.product_info.product_name}</h6>
                <div class="mb-3">
                    <strong>Current Status:</strong><br>
                    Stock: ${data.product_info.current_stock} units<br>
                    Annual Demand: ${data.product_info.annual_demand} units<br>
                    Daily Avg: ${data.product_info.daily_average_demand} units
                </div>
            `;
            
            if (data.forecasting_analysis.best_method) {
                const bestMethod = data.forecasting_analysis.forecasting_methods[data.forecasting_analysis.best_method];
                html += `
                    <div class="method-comparison">
                        <strong>Best Forecast Method: ${data.forecasting_analysis.best_method.replace('_', ' ').toUpperCase()}</strong><br>
                        <span class="text-primary">Next Period: ${bestMethod.next_period_forecast} units</span><br>
                        <small>MAE: ${bestMethod.accuracy_metrics.mean_absolute_error}</small>
                    </div>
                `;
            }
            
            if (data.recommendations && data.recommendations.length > 0) {
                html += `<div class="mt-3"><strong>Recommendations:</strong>`;
                data.recommendations.forEach(rec => {
                    const badgeClass = rec.type === 'urgent' ? 'bg-danger' : 
                                     rec.type === 'warning' ? 'bg-warning' : 'bg-info';
                    html += `<div class="d-block mt-1"><span class="badge ${badgeClass}">${rec.message}</span></div>`;
                });
                html += `</div>`;
            }
            
            $('#forecast-results').html(html);
        }
        
        function displayABCResults(data) {
            if (data.error) {
                $('#abc-results').html(`<div class="alert alert-danger">${data.error}</div>`);
                return;
            }
            
            let html = `
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="metric-value text-danger">${data.summary.a_items.count}</div>
                            <div class="metric-label">A Items (${data.summary.a_items.percentage}%)</div>
                            <small>Value: ${rupee(data.summary.a_items.value)} (${data.summary.a_items.value_percentage}%)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="metric-value text-warning">${data.summary.b_items.count}</div>
                            <div class="metric-label">B Items (${data.summary.b_items.percentage}%)</div>
                            <small>Value: ${rupee(data.summary.b_items.value)} (${data.summary.b_items.value_percentage}%)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="metric-value text-success">${data.summary.c_items.count}</div>
                            <div class="metric-label">C Items (${data.summary.c_items.percentage}%)</div>
                            <small>Value: ${rupee(data.summary.c_items.value)} (${data.summary.c_items.value_percentage}%)</small>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Classification</th>
                                <th>Annual Value</th>
                                <th>Cumulative %</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.products.slice(0, 10).forEach(product => {
                const badgeClass = product.classification === 'A' ? 'bg-danger' : 
                                 product.classification === 'B' ? 'bg-warning' : 'bg-success';
                html += `
                    <tr>
                        <td>${product.product_name}</td>
                        <td><span class="badge ${badgeClass}">${product.classification}</span></td>
                        <td>${rupee(Number(product.annual_value).toFixed(2))}</td>
                        <td>${product.cumulative_percentage}%</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Showing top 10 products. Total: ${data.total_products} products analyzed.</small>
            `;
            
            $('#abc-results').html(html);
        }
        
        function showError(title, message) {
            $.dialog({
                title: title,
                content: message,
                type: 'red'
            });
        }
    </script>
</body>
</html>
