<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get monthly sales data
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') as month,
        SUM(oi.price * oi.quantity) as total_sales,
        COUNT(DISTINCT o.id) as order_count
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
    ORDER BY month ASC
");
$stmt->execute();
$monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get top selling products
$stmt = $conn->prepare("
    SELECT 
        p.name,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.price * oi.quantity) as total_revenue
    FROM products p
    JOIN order_items oi ON p.id = oi.product_id
    WHERE p.deleted = 0
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 5
");
$stmt->execute();
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get category performance
$stmt = $conn->prepare("
    SELECT 
        c.name,
        COUNT(DISTINCT o.id) as order_count,
        SUM(oi.price * oi.quantity) as revenue
    FROM categories c
    JOIN products p ON c.id = p.category_id
    JOIN order_items oi ON p.id = oi.product_id
    JOIN orders o ON oi.order_id = o.id
    WHERE p.deleted = 0
    GROUP BY c.id
    ORDER BY revenue DESC
");
$stmt->execute();
$category_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admindash.css">
    <link rel="stylesheet" href="../css/nav.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .chart-container {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            backdrop-filter: blur(10px);
        }
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(min(400px, 100%), 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        canvas {
            max-width: 100%;
            max-height: 320px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 10px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .metric-card {
            background: rgba(31, 187, 31, 0.1);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #1fbb1f;
        }
    </style>
</head>
<body>
<div class="l-navbar" id="navbar">
            <nav class="nav">
                <div>
                    <a href="#" class="nav__logo">
                        <img src="../icon/logo.svg" alt="" class="nav__logo-icon">
                    </a>
    
                    <div class="nav__toggle" id="nav-toggle">
                        <i class='bx bx-chevron-right'></i>
                    </div>
    
                    <ul class="nav__list">
                    <a href="dashboard.php" class="nav__link">
                        <i class='bx bx-home nav__icon'></i>
                            <span class="nav__text">Dashboard</span>
                        </a>
                        <a href="manage_categories.php" class="nav__link">
                        <i class='bx bx-grid-alt nav__icon'></i>
                            <span class="nav__text">Categories</span>

                            </a>
                            <a href="order_manage.php" class="nav__link ">
                        <i class='bx bxs-cart-alt nav__icon'></i>
                        <span class="nav__text">Orders</span>
                        </a> 
                       
                       
                        <a href="support.php" class="nav__link">
                            <i class='bx bx-message-rounded nav__icon' ></i>
                            <span class="nav__text">Support</span>
                        </a>   
                        <a href="report.php" class="nav__link active">
                        <i class='bx bx-bar-chart-alt-2 nav__icon'></i>
                        <span class="nav__text">Reports</span>
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
        <h1>Advanced Analytics</h1>

        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card">
                <h3>Total Revenue</h3>
                <div class="metric-value">
                    ₹<?php 
                        $total_revenue = array_sum(array_column($monthly_data, 'total_sales'));
                        echo number_format($total_revenue, 2);
                    ?>
                </div>
            </div>
            <div class="metric-card">
                <h3>Total Orders</h3>
                <div class="metric-value">
                    <?php 
                        $total_orders = array_sum(array_column($monthly_data, 'order_count'));
                        echo number_format($total_orders);
                    ?>
                </div>
            </div>
            <div class="metric-card">
                <h3>Average Order Value</h3>
                <div class="metric-value">
                    ₹<?php 
                        $avg_order = $total_orders > 0 ? $total_revenue / $total_orders : 0;
                        echo number_format($avg_order, 2);
                    ?>
                </div>
            </div>
        </div>

        <div class="chart-grid">
            <!-- Monthly Sales Trend -->
            <div class="chart-container">
                <h2>Monthly Sales Trend</h2>
                <canvas id="salesTrendChart"></canvas>
            </div>

            <!-- Top Products -->
            <div class="chart-container">
                <h2>Top Selling Products</h2>
                <canvas id="topProductsChart"></canvas>
            </div>

            <!-- Category Performance -->
            <div class="chart-container">
                <h2>Category Performance</h2>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </main>

    <script>
        // Prepare data for charts
        const monthlyLabels = <?php echo json_encode(array_column($monthly_data, 'month')); ?>;
        const monthlySales = <?php echo json_encode(array_column($monthly_data, 'total_sales')); ?>;
        const monthlyOrders = <?php echo json_encode(array_column($monthly_data, 'order_count')); ?>;

        // Sales Trend Chart
        new Chart(document.getElementById('salesTrendChart'), {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Sales (₹)',
                    data: monthlySales,
                    borderColor: '#1fbb1f',
                    tension: 0.1
                }, {
                    label: 'Orders',
                    data: monthlyOrders,
                    borderColor: '#4CAF50',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: '#fff' }
                    }
                },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#fff' } },
                    x: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#fff' } }
                }
            }
        });

        // Top Products Chart
        new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($top_products, 'name')); ?>,
                datasets: [{
                    label: 'Units Sold',
                    data: <?php echo json_encode(array_column($top_products, 'total_quantity')); ?>,
                    backgroundColor: '#1fbb1f'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: '#fff' }
                    }
                },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#fff' } },
                    x: { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#fff' } }
                }
            }
        });

        // Category Performance Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($category_data, 'name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($category_data, 'revenue')); ?>,
                    backgroundColor: [
                        '#1fbb1f', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: '#fff' }
                    }
                }
            }
        });
    </script>
    <script src="../js/admindash.js"></script>
</body>
</html>