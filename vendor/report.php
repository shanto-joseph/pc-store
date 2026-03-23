<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../login.php');
    exit();
}

$vendor_id = $_SESSION['user_id'];

// Get total sales
$stmt = $conn->prepare("
    SELECT 
        SUM(oi.price * oi.quantity) as total_sales,
        COUNT(DISTINCT o.id) as total_orders,
        COUNT(DISTINCT oi.product_id) as products_sold
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = ? AND p.deleted = 0
");
$stmt->execute([$vendor_id]);
$sales_data = $stmt->fetch(PDO::FETCH_ASSOC);

// Get top selling products
$stmt = $conn->prepare("
    SELECT 
        p.name,
        p.price,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.price * oi.quantity) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = ? AND p.deleted = 0
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 5
");
$stmt->execute([$vendor_id]);
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get low stock products
$stmt = $conn->prepare("
    SELECT name, stock
    FROM products 
    WHERE vendor_id = ? AND deleted = 0 AND stock < 5
    ORDER BY stock ASC
");
$stmt->execute([$vendor_id]);
$low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get monthly sales for the last 6 months
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') as month,
        SUM(oi.price * oi.quantity) as monthly_sales
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    WHERE p.vendor_id = ? AND p.deleted = 0
    AND o.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
    ORDER BY month DESC
");
$stmt->execute([$vendor_id]);
$monthly_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Reports</title>
    <link rel="stylesheet" href="../css/vendor.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .report-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .report-card {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }

        .report-card h3 {
            color: #1fbb1f;
            margin-bottom: 15px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .report-table th,
        .report-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .report-table th {
            background: rgba(31, 187, 31, 0.1);
            color: #1fbb1f;
        }

        .low-stock-alert {
            color: #ff4444;
            font-weight: bold;
        }

        .monthly-chart {
            height: 300px;
            margin-top: 20px;
        }

        .report-card { overflow-x: auto; }

        @media (max-width: 768px) {
            .report-grid { grid-template-columns: 1fr; }
            .report-table th, .report-table td { padding: 8px; font-size: 0.88rem; }
        }
    </style>
</head>
<body id="body">
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <div class="nav__toggle" id="nav-toggle">
                    <i class='bx bx-chevron-right'></i>
                </div>

                <ul class="nav__list">
                    <a href="dashboard.php" class="nav__link">
                        <i class='bx bx-grid-alt nav__icon'></i>
                        <span class="nav__text">Dashboard</span>
                    </a>
                   
                    <a href="add_product.php" class="nav__link">
                        <i class='bx bx-plus-circle nav__icon'></i>
                        <span class="nav__text">Add </span>
                    </a>
                    <a href="support.php" class="nav__link">
                        <i class='bx bx-message-rounded nav__icon'></i>
                        <span class="nav__text">Support </span>
                    </a> 
                    <a href="report.php" class="nav__link active">
                        <i class='bx bx-bar-chart-alt-2 nav__icon'></i>
                        <span class="nav__text">Reports</span>
                    </a>
                    <a href="profile.php" class="nav__link">
                        <i class='bx bx-user-circle nav__icon'></i>
                        <span class="nav__text">Profile</span>
                    </a>
                </ul>
            </div>
            <a href="../logout.php" class="nav__link">           
                <i class='bx bx-log-out-circle nav__icon'></i>
                <span class="nav__text">Logout</span>
            </a>
        </nav>
    </div>

    <main>
        <h1>Sales Reports & Analytics</h1>

        <div class="report-grid">
            <div class="report-card">
                <h3>Sales Overview</h3>
                <p>Total Sales: ₹<?php echo number_format($sales_data['total_sales'] ?? 0, 2); ?></p>
                <p>Total Orders: <?php echo $sales_data['total_orders'] ?? 0; ?></p>
                <p>Products Sold: <?php echo $sales_data['products_sold'] ?? 0; ?></p>
            </div>

            <div class="report-card">
                <h3>Top Selling Products</h3>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo $product['total_quantity']; ?></td>
                                <td>₹<?php echo number_format($product['total_revenue'] ?? 0, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="report-card">
                <h3>Low Stock Alert</h3>
                <?php if (empty($low_stock)): ?>
                    <p>No products with low stock.</p>
                <?php else: ?>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td class="low-stock-alert"><?php echo $product['stock']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="report-card">
            <h3>Monthly Sales (Last 6 Months)</h3>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthly_sales as $month): ?>
                        <tr>
                            <td><?php echo date('F Y', strtotime($month['month'] . '-01')); ?></td>
                            <td>₹<?php echo number_format($month['monthly_sales'] ?? 0, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="../js/vendor.js"></script>
</body>
</html>