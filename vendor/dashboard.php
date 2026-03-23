<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../login.php');
    exit();
}

$vendor_id = $_SESSION['user_id'];
$vendor = get_user_by_id($conn, $vendor_id);
$vendor_products = get_vendor_products($conn, $vendor_id);
$vendor_sales = get_vendor_sales($conn, $vendor_id);
$vendor_orders = get_vendor_orders($conn, $vendor_id);

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM products WHERE vendor_id = ? AND status = 'pending' AND deleted = 0");
$stmt->execute([$vendor_id]);
$pending_count = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM products WHERE vendor_id = ? AND stock < 5 AND deleted = 0");
$stmt->execute([$vendor_id]);
$low_stock_count = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM products WHERE vendor_id = ? AND deleted = 0");
$stmt->execute([$vendor_id]);
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM support_tickets WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$vendor_id]);
$ticket_count = $stmt->fetch(PDO::FETCH_ASSOC)['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Dashboard - PC STORE</title>
    <link rel="stylesheet" href="../css/vendor.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
</head>
<body id="body">

<div class="l-navbar" id="navbar">
    <nav class="nav">
        <div>
            <div class="nav__toggle" id="nav-toggle">
                <i class='bx bx-chevron-right'></i>
            </div>
            <ul class="nav__list">
                <a href="#" class="nav__link active" data-section="dashboard-stats">
                    <i class='bx bx-grid-alt nav__icon'></i>
                    <span class="nav__text">Dashboard</span>
                </a>
                <a href="#" class="nav__link" data-section="manage-products">
                    <i class='bx bx-package nav__icon'></i>
                    <span class="nav__text">Products</span>
                </a>
                <a href="add_product.php" class="nav__link">
                    <i class='bx bx-plus-circle nav__icon'></i>
                    <span class="nav__text">Add Product</span>
                </a>
                <a href="support.php" class="nav__link">
                    <i class='bx bx-message-rounded nav__icon'></i>
                    <span class="nav__text">Support
                        <?php if ($ticket_count > 0): ?>
                            <span class="badge"><?php echo $ticket_count; ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <a href="report.php" class="nav__link">
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
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <!-- Dashboard Stats -->
    <div id="dashboard-stats" class="section active">
        <h2>Welcome, <?php echo htmlspecialchars($vendor['name']); ?></h2>
        <div class="dashboard-grid">
            <div class="stat-card">
                <h3>Total Sales</h3>
                <p class="stat-number">₹<?php echo number_format($vendor_sales, 2); ?></p>
                <p>Revenue Generated</p>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p class="stat-number"><?php echo $vendor_orders; ?></p>
                <p>Orders Processed</p>
            </div>
            <div class="stat-card">
                <h3>Products</h3>
                <p class="stat-number"><?php echo $total_products; ?></p>
                <p>Active Listings</p>
            </div>
            <div class="stat-card">
                <h3>Pending Approvals</h3>
                <p class="stat-number"><?php echo $pending_count; ?></p>
                <p>Awaiting Admin Review</p>
            </div>
        </div>

        <?php if ($low_stock_count > 0): ?>
        <div class="alert-box">
            <i class='bx bx-error-circle'></i>
            <?php echo $low_stock_count; ?> product(s) have low stock (less than 5 units).
            <a href="#" data-section="manage-products" class="nav__link alert-link">View Products</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Manage Products -->
    <div id="manage-products" class="section">
        <div class="section-header">
            <h2>My Products</h2>
            <a href="add_product.php" class="btn-primary">+ Add Product</a>
        </div>

        <div class="filter-bar">
            <select id="statusFilter" onchange="filterProducts(this.value)">
                <option value="all">All</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
            <input type="text" id="searchProducts" placeholder="Search products..." oninput="searchProducts(this.value)">
        </div>

        <?php if (empty($vendor_products)): ?>
            <p class="empty-msg">No products yet. <a href="add_product.php">Add your first product</a></p>
        <?php else: ?>
        <div class="vendor-products">
            <?php foreach ($vendor_products as $product): ?>
                <div class="vendor-product-card"
                     data-status="<?php echo $product['status']; ?>"
                     data-stock="<?php echo $product['stock']; ?>">
                    <?php if (!empty($product['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($product['image']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <div class="no-image"><i class='bx bx-image'></i></div>
                    <?php endif; ?>
                    <div class="vendor-product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                        <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
                        <p class="stock-info <?php echo $product['stock'] < 5 ? 'low' : ''; ?>">
                            Stock: <?php echo $product['stock']; ?>
                            <?php if ($product['stock'] < 5): ?><span class="stock-warning"> ⚠ Low</span><?php endif; ?>
                        </p>
                        <span class="status-badge status-<?php echo $product['status']; ?>">
                            <?php echo ucfirst($product['status']); ?>
                        </span>
                    </div>
                    <div class="vendor-product-actions">
                        <a href="edit_product.php?id=<?php echo $product['id']; ?>">
                            <i class='bx bx-edit'></i> Edit
                        </a>
                        <form action="delete_product.php" method="post">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" onclick="return confirm('Delete this product?')">
                                <i class='bx bx-trash'></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<script src="../js/vendor.js"></script>
</body>
</html>
