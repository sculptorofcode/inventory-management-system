<?php
// filepath: e:\SGP\5th Sem\Major Project\ims-project\location-history-pdf.php
/**
 * Location History PDF Export
 * 
 * This script generates a PDF report of product location history
 */

include 'includes/config/database.php';
require_once 'includes/functions/functions.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

// Check if we have required parameters
$selectedCategory = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$selectedProduct = isset($_GET['product_id']) ? $_GET['product_id'] : null;
$selectedStock = isset($_GET['stock_id']) ? $_GET['stock_id'] : null;

if (empty($selectedProduct) && empty($selectedStock)) {
    header('Location: location-history.php' . ($selectedCategory ? '?category_id=' . $selectedCategory : ''));
    exit;
}

// Get history data based on parameters
$history = [];
$stockInfo = null;
$productInfo = null;

if ($selectedStock) {
    // Get stock item details
    $sql = "SELECT s.stock_id, s.product_id, s.batch_number, p.product_name
            FROM tbl_stock s
            JOIN tbl_products p ON s.product_id = p.product_id
            WHERE s.stock_id = :stock_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':stock_id', $selectedStock);
    $stmt->execute();
    $stockInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($stockInfo) {
        // Get location history for this stock item
        $history = getStockLocationHistory($selectedStock);
    } else {
        header('Location: location-history.php');
        exit;
    }
} else if ($selectedProduct) {
    // Get product details
    $sql = "SELECT product_id, product_name FROM tbl_products WHERE product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $selectedProduct);
    $stmt->execute();
    $productInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($productInfo) {
        // Get location history for this product
        $history = getProductLocationHistory($selectedProduct);
    } else {
        header('Location: location-history.php');
        exit;
    }
}

// Create new PDF document
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document metadata
$pdf->SetCreator(APP_NAME);
$pdf->SetAuthor(APP_NAME);

$pdf->SetPageOrientation('L');

if ($stockInfo) {
    $title = 'Location History - ' . $stockInfo['product_name'] . ' (Batch: ' . ($stockInfo['batch_number'] ?: 'N/A') . ')';
    $pdf->SetTitle($title);
    $pdf->SetSubject($title);
} else if ($productInfo) {
    $title = 'Location History - ' . $productInfo['product_name'];
    $pdf->SetTitle($title);
    $pdf->SetSubject($title);
} else {
    $pdf->SetTitle('Product Location History');
    $pdf->SetSubject('Product Location History');
}

// Remove header and footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(8, 8, 8);
$pdf->SetAutoPageBreak(true, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 10);

// Add logo
if (file_exists('assets/images/logo/transparent/logo.png')) {
    // Get page width and calculate X to center the image
    $pageWidth = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    $imgWidth = 50;
    $x = $pdf->getMargins()['left'] + ($pageWidth - $imgWidth) / 2;
    $pdf->Image('assets/images/logo/transparent/logo.png', $x, 15, $imgWidth, 0, 'PNG');
}

// Title
$pdf->SetFont('helvetica', 'B', 18);
$pdf->Cell(0,23, '', 0, 1); // Add space after logo
$pdf->Cell(0, 10, 'Location History Report', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);

if ($stockInfo) {
    $productText = 'Product: ' . $stockInfo['product_name'];
    $batchText = 'Batch Number: ' . ($stockInfo['batch_number'] ?: 'N/A');
    $pdf->Cell(0, 10, $productText . '    |    ' . $batchText, 0, 1);
} else if ($productInfo) {
    $pdf->Cell(0, 10, 'Product: ' . $productInfo['product_name'], 0, 1);
}

// Show title left, generated right in same row
$pdf->SetFont('helvetica', 'B', 18);

// Use multicell for left and right alignment in same row
$left = 'Location History Report';
$right = 'Generated: ' . date('d M Y H:i:s');
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 0, '', 0, 1); // move to new line
$y = $pdf->GetY();
$pdf->SetXY($pdf->GetX(), $y);
$pdf->Cell(140, 10, $left, 0, 0, 'L');
$pdf->Cell(0, 10, $right, 0, 1, 'R');
$pdf->SetFont('helvetica', '', 10);
$pdf->Ln(5);

if (!empty($history)) {
    // Format the history data into a table
    $html = '<table border="1" cellpadding="5" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background-color: #f5f5f5; font-weight: bold;">
                <th align="center">Date & Time</th>';
                
    if (!$selectedStock) {
        $html .= '<th align="center">Batch</th>';
    }
    
    $html .= '<th align="center">From</th>
              <th align="center">To</th>
              <th align="center">Moved By</th>
              <th align="center">Notes</th>
            </tr>
        </thead>
        <tbody>';
        
    foreach ($history as $record) {
        $html .= '<tr>
            <td>' . date('M d, Y h:i A', strtotime($record['created_at'])) . '</td>';
            
        if (!$selectedStock) {
            $html .= '<td>' . ($record['batch_number'] ? htmlspecialchars($record['batch_number']) : 'N/A') . '</td>';
        }
        
        // From location
        $html .= '<td>';
        if ($record['old_warehouse_id']) {
            $html .= htmlspecialchars($record['old_warehouse_name']);
            if ($record['old_location_id']) {
                $html .= '<br/><small>' . htmlspecialchars($record['old_location_type'] . ': ' . $record['old_location_name']) . '</small>';
            }
        } else {
            $html .= '<span style="color: #0d6efd;">Initial Assignment</span>';
        }
        $html .= '</td>';
        
        // To location
        $html .= '<td>';
        $html .= htmlspecialchars($record['new_warehouse_name'] ?? 'N/A');
        if ($record['new_location_id']) {
            $html .= '<br/><small>' . htmlspecialchars($record['new_location_type'] . ': ' . $record['new_location_name']) . '</small>';
        }
        $html .= '</td>';
        
        $html .= '<td>' . htmlspecialchars($record['moved_by_name']) . '</td>
            <td>' . ($record['notes'] ? htmlspecialchars($record['notes']) : '<em>No notes</em>') . '</td>
        </tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Output the HTML as a table
    $pdf->writeHTML($html, true, false, true, false, '');
} else {
    $pdf->Cell(0, 10, 'No location history found.', 0, 1, 'C');
}

// Output the PDF
$filename = 'location_history_' . date('YmdHis') . '.pdf';
$pdf->Output($filename, 'I');
exit;
