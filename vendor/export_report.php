<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../login.php');
    exit();
}

$vendor_id = $_SESSION['user_id'];
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get sales data
$stmt = $conn->prepare("
    SELECT 
        DATE(o.created_at) as sale_date,
        p.name as product_name,
        oi.quantity,
        oi.price,
        (oi.price * oi.quantity) as total
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = ? 
    AND DATE(o.created_at) BETWEEN ? AND ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$vendor_id, $start_date, $end_date]);
$sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate CSV content
$csv_content = "Date,Product,Quantity,Price,Total\n";
foreach ($sales_data as $sale) {
    $csv_content .= sprintf(
        "%s,%s,%d,%.2f,%.2f\n",
        $sale['sale_date'],
        str_replace(',', ' ', $sale['product_name']), // Remove commas from product names
        $sale['quantity'],
        $sale['price'],
        $sale['total']
    );
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d') . '.csv"');

// Output CSV content
echo $csv_content;
exit();
?>