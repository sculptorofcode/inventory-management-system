<?php
/**
 * Demand Forecasting Functions
 * 
 * This file contains functions for demand forecasting methods:
 * - Moving Average
 * - Exponential Smoothing  
 * - Trend Analysis
 * - Seasonal Analysis
 */

/**
 * Calculate Moving Average forecast
 * 
 * @param array $historical_data Array of historical demand data
 * @param int $periods Number of periods to use for moving average
 * @return array Moving average calculation and forecast
 */
function calculateMovingAverage($historical_data, $periods = 3): array
{
    if (count($historical_data) < $periods) {
        throw new Exception("Insufficient data. Need at least $periods periods for moving average.");
    }
    
    $moving_averages = [];
    $forecasts = [];
    
    // Calculate moving averages for historical data
    for ($i = $periods - 1; $i < count($historical_data); $i++) {
        $sum = 0;
        for ($j = 0; $j < $periods; $j++) {
            $sum += $historical_data[$i - $j]['demand'];
        }
        $moving_avg = $sum / $periods;
        
        $moving_averages[] = [
            'period' => $historical_data[$i]['period'],
            'actual_demand' => $historical_data[$i]['demand'],
            'moving_average' => round($moving_avg, 2),
            'error' => $historical_data[$i]['demand'] - $moving_avg,
            'absolute_error' => abs($historical_data[$i]['demand'] - $moving_avg)
        ];
    }
    
    // Calculate next period forecast
    $last_periods = array_slice($historical_data, -$periods);
    $next_forecast = array_sum(array_column($last_periods, 'demand')) / $periods;
    
    // Calculate accuracy metrics
    $mae = count($moving_averages) > 0 ? 
        array_sum(array_column($moving_averages, 'absolute_error')) / count($moving_averages) : 0;
    
    $mse = count($moving_averages) > 0 ? 
        array_sum(array_map(function($item) { return pow($item['error'], 2); }, $moving_averages)) / count($moving_averages) : 0;
    
    return [
        'method' => 'Moving Average',
        'periods_used' => $periods,
        'historical_analysis' => $moving_averages,
        'next_period_forecast' => round($next_forecast, 2),
        'accuracy_metrics' => [
            'mean_absolute_error' => round($mae, 2),
            'mean_squared_error' => round($mse, 2),
            'root_mean_squared_error' => round(sqrt($mse), 2)
        ]
    ];
}

/**
 * Calculate Exponential Smoothing forecast
 * 
 * @param array $historical_data Array of historical demand data
 * @param float $alpha Smoothing constant (0 < alpha < 1)
 * @return array Exponential smoothing calculation and forecast
 */
function calculateExponentialSmoothing($historical_data, $alpha = 0.3): array
{
    if (empty($historical_data)) {
        throw new Exception("No historical data provided for exponential smoothing.");
    }
    
    if ($alpha <= 0 || $alpha >= 1) {
        throw new Exception("Alpha must be between 0 and 1.");
    }
    
    $smoothed_values = [];
    $forecast = $historical_data[0]['demand']; // Initial forecast = first period actual
    
    foreach ($historical_data as $index => $data) {
        if ($index > 0) {
            // Ft = α × At-1 + (1-α) × Ft-1
            $forecast = $alpha * $historical_data[$index - 1]['demand'] + (1 - $alpha) * $forecast;
        }
        
        $error = $data['demand'] - $forecast;
        $smoothed_values[] = [
            'period' => $data['period'],
            'actual_demand' => $data['demand'],
            'forecast' => round($forecast, 2),
            'error' => round($error, 2),
            'absolute_error' => abs($error)
        ];
    }
    
    // Next period forecast
    $next_forecast = $alpha * end($historical_data)['demand'] + (1 - $alpha) * $forecast;
    
    // Calculate accuracy metrics
    $mae = count($smoothed_values) > 0 ? 
        array_sum(array_column($smoothed_values, 'absolute_error')) / count($smoothed_values) : 0;
    
    $mse = count($smoothed_values) > 0 ? 
        array_sum(array_map(function($item) { return pow($item['error'], 2); }, $smoothed_values)) / count($smoothed_values) : 0;
    
    return [
        'method' => 'Exponential Smoothing',
        'alpha' => $alpha,
        'historical_analysis' => $smoothed_values,
        'next_period_forecast' => round($next_forecast, 2),
        'accuracy_metrics' => [
            'mean_absolute_error' => round($mae, 2),
            'mean_squared_error' => round($mse, 2),
            'root_mean_squared_error' => round(sqrt($mse), 2)
        ]
    ];
}

/**
 * Calculate Linear Trend Analysis
 * 
 * @param array $historical_data Array of historical demand data
 * @param int $forecast_periods Number of future periods to forecast
 * @return array Trend analysis and forecasts
 */
function calculateLinearTrend($historical_data, $forecast_periods = 1): array
{
    if (count($historical_data) < 2) {
        throw new Exception("Need at least 2 data points for trend analysis.");
    }
    
    $n = count($historical_data);
    $sum_x = 0;
    $sum_y = 0;
    $sum_xy = 0;
    $sum_x2 = 0;
    
    // Calculate sums for linear regression
    foreach ($historical_data as $index => $data) {
        $x = $index + 1; // Period number
        $y = $data['demand'];
        
        $sum_x += $x;
        $sum_y += $y;
        $sum_xy += $x * $y;
        $sum_x2 += $x * $x;
    }
    
    // Calculate slope (b) and intercept (a)
    // b = (n*Σxy - Σx*Σy) / (n*Σx² - (Σx)²)
    $slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
    
    // a = (Σy - b*Σx) / n
    $intercept = ($sum_y - $slope * $sum_x) / $n;
    
    // Calculate fitted values and errors
    $trend_analysis = [];
    $total_error = 0;
    
    foreach ($historical_data as $index => $data) {
        $x = $index + 1;
        $fitted_value = $intercept + $slope * $x;
        $error = $data['demand'] - $fitted_value;
        
        $trend_analysis[] = [
            'period' => $data['period'],
            'actual_demand' => $data['demand'],
            'trend_forecast' => round($fitted_value, 2),
            'error' => round($error, 2),
            'absolute_error' => abs($error)
        ];
        
        $total_error += $error * $error;
    }
    
    // Generate future forecasts
    $future_forecasts = [];
    for ($i = 1; $i <= $forecast_periods; $i++) {
        $future_period = $n + $i;
        $forecast = $intercept + $slope * $future_period;
        
        $future_forecasts[] = [
            'period' => $future_period,
            'forecast' => round($forecast, 2)
        ];
    }
    
    // Calculate R-squared
    $mean_y = $sum_y / $n;
    $ss_tot = array_sum(array_map(function($data) use ($mean_y) {
        return pow($data['demand'] - $mean_y, 2);
    }, $historical_data));
    
    $r_squared = $ss_tot > 0 ? 1 - ($total_error / $ss_tot) : 0;
    
    // Calculate accuracy metrics
    $mae = array_sum(array_column($trend_analysis, 'absolute_error')) / count($trend_analysis);
    $mse = $total_error / $n;
    
    return [
        'method' => 'Linear Trend Analysis',
        'trend_equation' => [
            'slope' => round($slope, 4),
            'intercept' => round($intercept, 2),
            'equation' => "Y = " . round($intercept, 2) . " + " . round($slope, 4) . " * X"
        ],
        'historical_analysis' => $trend_analysis,
        'future_forecasts' => $future_forecasts,
        'accuracy_metrics' => [
            'r_squared' => round($r_squared, 4),
            'mean_absolute_error' => round($mae, 2),
            'mean_squared_error' => round($mse, 2),
            'root_mean_squared_error' => round(sqrt($mse), 2)
        ]
    ];
}

/**
 * Calculate Weighted Moving Average
 * 
 * @param array $historical_data Array of historical demand data
 * @param array $weights Array of weights (most recent period should have highest weight)
 * @return array Weighted moving average calculation
 */
function calculateWeightedMovingAverage($historical_data, $weights = [0.5, 0.3, 0.2]): array
{
    $periods = count($weights);
    
    if (count($historical_data) < $periods) {
        throw new Exception("Insufficient data. Need at least $periods periods for weighted moving average.");
    }
    
    // Normalize weights to sum to 1
    $weight_sum = array_sum($weights);
    $normalized_weights = array_map(function($w) use ($weight_sum) {
        return $w / $weight_sum;
    }, $weights);
    
    $wma_results = [];
    
    // Calculate weighted moving averages
    for ($i = $periods - 1; $i < count($historical_data); $i++) {
        $weighted_sum = 0;
        for ($j = 0; $j < $periods; $j++) {
            $weighted_sum += $historical_data[$i - $j]['demand'] * $normalized_weights[$j];
        }
        
        $error = $historical_data[$i]['demand'] - $weighted_sum;
        $wma_results[] = [
            'period' => $historical_data[$i]['period'],
            'actual_demand' => $historical_data[$i]['demand'],
            'weighted_ma' => round($weighted_sum, 2),
            'error' => round($error, 2),
            'absolute_error' => abs($error)
        ];
    }
    
    // Calculate next period forecast
    $last_periods = array_slice($historical_data, -$periods);
    $next_forecast = 0;
    for ($i = 0; $i < $periods; $i++) {
        $next_forecast += $last_periods[$i]['demand'] * $normalized_weights[$periods - 1 - $i];
    }
    
    // Calculate accuracy metrics
    $mae = count($wma_results) > 0 ? 
        array_sum(array_column($wma_results, 'absolute_error')) / count($wma_results) : 0;
    
    $mse = count($wma_results) > 0 ? 
        array_sum(array_map(function($item) { return pow($item['error'], 2); }, $wma_results)) / count($wma_results) : 0;
    
    return [
        'method' => 'Weighted Moving Average',
        'weights' => $normalized_weights,
        'periods_used' => $periods,
        'historical_analysis' => $wma_results,
        'next_period_forecast' => round($next_forecast, 2),
        'accuracy_metrics' => [
            'mean_absolute_error' => round($mae, 2),
            'mean_squared_error' => round($mse, 2),
            'root_mean_squared_error' => round(sqrt($mse), 2)
        ]
    ];
}

/**
 * Get historical demand data for a product
 * 
 * @param int $product_id Product ID
 * @param int $months Number of months of historical data
 * @param string $period_type Type of period (daily, weekly, monthly)
 * @return array Historical demand data
 */
function getHistoricalDemandData($product_id, $months = 12, $period_type = 'monthly'): array
{
    global $conn;
    
    $date_format = '%Y-%m'; // Monthly by default
    $date_interval = 'MONTH';
    
    switch (strtolower($period_type)) {
        case 'daily':
            $date_format = '%Y-%m-%d';
            $date_interval = 'DAY';
            break;
        case 'weekly':
            $date_format = '%Y-%u'; // Year-week
            $date_interval = 'WEEK';
            break;
        case 'monthly':
        default:
            $date_format = '%Y-%m';
            $date_interval = 'MONTH';
            break;
    }
    
    // Use tbl_sale_order and tbl_sale_order_details for demand (sales) data
    $sql = "SELECT 
                DATE_FORMAT(o.order_date, '$date_format') as period,
                SUM(od.quantity) as demand
            FROM tbl_sale_order_details od
            INNER JOIN tbl_sale_order o ON od.sale_order_id = o.order_id
            WHERE od.product_id = :product_id
            AND o.status IN ('confirmed', 'shipped', 'delivered')
            AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL :months $date_interval)
            GROUP BY DATE_FORMAT(o.order_date, '$date_format')
            ORDER BY period ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':months', $months);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Compare multiple forecasting methods
 * 
 * @param int $product_id Product ID
 * @param int $historical_months Months of historical data to use
 * @return array Comparison of different forecasting methods
 */
function compareForecastingMethods($product_id, $historical_months = 12): array
{
    $historical_data = getHistoricalDemandData($product_id, $historical_months, 'monthly');
    
    if (empty($historical_data)) {
        return [
            'error' => 'No historical data available for forecasting',
            'product_id' => $product_id
        ];
    }
    
    $results = [];
    
    try {
        // Moving Average (3 periods)
        if (count($historical_data) >= 3) {
            $results['moving_average_3'] = calculateMovingAverage($historical_data, 3);
        }
        
        // Exponential Smoothing
        $results['exponential_smoothing'] = calculateExponentialSmoothing($historical_data, 0.3);
        
        // Linear Trend
        if (count($historical_data) >= 2) {
            $results['linear_trend'] = calculateLinearTrend($historical_data, 3);
        }
        
        // Weighted Moving Average
        if (count($historical_data) >= 3) {
            $results['weighted_moving_average'] = calculateWeightedMovingAverage($historical_data);
        }
        
    } catch (Exception $e) {
        $results['error'] = $e->getMessage();
    }
    
    // Find best method based on lowest MAE
    $best_method = null;
    $lowest_mae = PHP_FLOAT_MAX;
    
    foreach ($results as $method_name => $method_data) {
        if (isset($method_data['accuracy_metrics']['mean_absolute_error'])) {
            $mae = $method_data['accuracy_metrics']['mean_absolute_error'];
            if ($mae < $lowest_mae) {
                $lowest_mae = $mae;
                $best_method = $method_name;
            }
        }
    }
    
    return [
        'product_id' => $product_id,
        'historical_periods' => count($historical_data),
        'forecasting_methods' => $results,
        'best_method' => $best_method,
        'best_method_mae' => $lowest_mae !== PHP_FLOAT_MAX ? $lowest_mae : null
    ];
}

/**
 * Generate demand forecast dashboard for a product
 * 
 * @param int $product_id Product ID
 * @return array Complete demand forecasting dashboard
 */
function getDemandForecastDashboard($product_id): array
{
    global $conn;
    
    // Get product details
    $product = getProductById($product_id);
    if (!$product) {
        return ['error' => 'Product not found'];
    }
    
    // Get forecasting comparison
    $forecast_comparison = compareForecastingMethods($product_id);
    
    // Get current inventory levels
    $current_stock = getCurrentStock($product_id);
    
    // Calculate demand statistics
    $annual_demand = getAnnualDemand($product_id);
    $monthly_avg = $annual_demand / 12;
    $daily_avg = $annual_demand / 365;
    
    return [
        'product_info' => [
            'product_id' => $product_id,
            'product_name' => $product['product_name'],
            'current_stock' => $current_stock,
            'annual_demand' => $annual_demand,
            'monthly_average_demand' => round($monthly_avg, 2),
            'daily_average_demand' => round($daily_avg, 2)
        ],
        'forecasting_analysis' => $forecast_comparison,
        'recommendations' => generateForecastRecommendations($forecast_comparison, $current_stock, $daily_avg)
    ];
}

/**
 * Generate recommendations based on forecast analysis
 * 
 * @param array $forecast_data Forecasting analysis results
 * @param float $current_stock Current stock level
 * @param float $daily_demand Average daily demand
 * @return array Recommendations
 */
function generateForecastRecommendations($forecast_data, $current_stock, $daily_demand): array
{
    $recommendations = [];
    
    if (isset($forecast_data['best_method']) && $forecast_data['best_method']) {
        $best_forecast = $forecast_data['forecasting_methods'][$forecast_data['best_method']];
        $next_forecast = $best_forecast['next_period_forecast'] ?? 0;
        
        // Stock-out risk assessment
        $days_of_stock = $daily_demand > 0 ? $current_stock / $daily_demand : 999;
        
        if ($days_of_stock < 7) {
            $recommendations[] = [
                'type' => 'urgent',
                'message' => 'Critical: Only ' . round($days_of_stock, 1) . ' days of stock remaining',
                'action' => 'Place emergency order immediately'
            ];
        } elseif ($days_of_stock < 14) {
            $recommendations[] = [
                'type' => 'warning',
                'message' => 'Low stock: ' . round($days_of_stock, 1) . ' days of stock remaining',
                'action' => 'Consider placing order soon'
            ];
        }
        
        // Forecast accuracy assessment
        $mae = $best_forecast['accuracy_metrics']['mean_absolute_error'] ?? 0;
        if ($mae > ($next_forecast * 0.3)) {
            $recommendations[] = [
                'type' => 'info',
                'message' => 'High forecast variability detected',
                'action' => 'Consider increasing safety stock levels'
            ];
        }
        
        // Next period recommendation
        $recommendations[] = [
            'type' => 'forecast',
            'message' => "Predicted demand for next period: " . $next_forecast . " units",
            'action' => "Based on " . $forecast_data['best_method'] . " method"
        ];
    }
    
    return $recommendations;
}
