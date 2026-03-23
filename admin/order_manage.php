<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get all orders with user and product details
$stmt = $conn->prepare("
    SELECT 
        o.id,
        o.user_id,
        o.total_amount,
        o.status,
        o.created_at,
        o.shipping_address,
        o.payment_status,
        u.name as customer_name,
        u.email as customer_email,
        COUNT(oi.id) as item_count,
        GROUP_CONCAT(p.name SEPARATOR ', ') as products
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE p.deleted = 0
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        $_SESSION['success_message'] = "Order status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to update order status.";
    }
    
    header('Location: order_manage.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admindash.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .order-filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .order-filters select,
        .order-filters input {
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
        }

        .order-grid {
            display: grid;
            gap: 20px;
        }

        .order-card {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 8px;
            padding: 20px;
            backdrop-filter: blur(10px);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .order-id {
            font-size: 1.2em;
            color: #1fbb1f;
        }

        .order-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.9em;
        }

        .status-pending { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
        .status-processing { background: rgba(33, 150, 243, 0.2); color: #2196f3; }
        .status-completed { background: rgba(76, 175, 80, 0.2); color: #4caf50; }
        .status-cancelled { background: rgba(244, 67, 54, 0.2); color: #f44336; }

        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-group {
            background: rgba(255, 255, 255, 0.05);
            padding: 10px;
            border-radius: 4px;
        }

        .detail-group h4 {
            color: #1fbb1f;
            margin-bottom: 5px;
        }

        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .order-actions form {
            flex: 1;
        }

        .order-actions select {
            width: 100%;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            background: rgba(0, 0, 0, 0.3);
            color: #fff;
            margin-bottom: 10px;
        }

        .update-btn {
            width: 100%;
            padding: 8px;
            background: #1fbb1f;
            color: #000;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .update-btn:hover {
            background: #00ff00;
        }

        .view-details-btn {
            display: inline-block;
            padding: 8px 15px;
            background: rgba(31, 187, 31, 0.2);
            color: #1fbb1f;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .view-details-btn:hover {
            background: rgba(31, 187, 31, 0.3);
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
                        <a href="order_manage.php" class="nav__link active ">
                        <i class='bx bxs-cart-alt nav__icon'></i>
                        <span class="nav__text">Orders</span>
                        </a> 
                        <a href="support.php" class="nav__link">
                            <i class='bx bx-message-rounded nav__icon' ></i>
                            <span class="nav__text">Support</span>
                        </a>   
                        <a href="report.php" class="nav__link">
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
        <h2>Order Management</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="order-filters">
            <select id="statusFilter" onchange="filterOrders()">
                <option value="all">All Orders</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <input type="text" id="searchOrders" placeholder="Search orders..." onkeyup="searchOrders(this.value)">
        </div>

        <div class="order-grid">
            <?php foreach ($orders as $order): ?>
                <div class="order-card" data-order-status="<?php echo $order['status']; ?>">
                    <div class="order-header">
                        <span class="order-id">Order #<?php echo $order['id']; ?></span>
                        <span class="order-status status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>

                    <div class="order-details">
                        <div class="detail-group">
                            <h4>Customer</h4>
                            <p><?php echo htmlspecialchars($order['customer_name']); ?></p>
                            <p><?php echo htmlspecialchars($order['customer_email']); ?></p>
                        </div>

                        <div class="detail-group">
                            <h4>Order Info</h4>
                            <p>Items: <?php echo $order['item_count']; ?></p>
                            <p>Total: ₹<?php echo number_format($order['total_amount'], 2); ?></p>
                            <p>Date: <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                        </div>

                        <div class="detail-group">
                            <h4>Payment</h4>
                            <p>Status: <?php echo ucfirst($order['payment_status']); ?></p>
                        </div>
                    </div>

                    <div class="order-actions">
                        <form action="order_manage.php" method="post">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" class="update-btn">Update Status</button>
                        </form>
                        <a href="order_details.php?order_id=<?php echo $order['id']; ?>" class="view-details-btn">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
    function filterOrders() {
        const status = document.getElementById('statusFilter').value;
        const orders = document.querySelectorAll('.order-card');
        
        orders.forEach(order => {
            if (status === 'all' || order.dataset.orderStatus === status) {
                order.style.display = 'block';
            } else {
                order.style.display = 'none';
            }
        });
    }

    function searchOrders(query) {
        const orders = document.querySelectorAll('.order-card');
        query = query.toLowerCase();
        
        orders.forEach(order => {
            const text = order.textContent.toLowerCase();
            order.style.display = text.includes(query) ? 'block' : 'none';
        });
    }
    </script>
    <script src="../js/admindash.js"></script>
</body>
</html>