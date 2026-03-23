<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get statistics and counts
$vendor_id = isset($_GET['vendor_id']) ? $_GET['vendor_id'] : null;
$vendors = get_all_vendors($conn);
$all_products = get_filtered_products($conn, $vendor_id);
$pending_products = get_pending_products($conn);
$users = get_users($conn);
$total_sales = get_total_sales($conn);
$total_orders = get_total_orders($conn);
$categories = get_categories($conn);

// Get pending support tickets count
$stmt = $conn->prepare("SELECT COUNT(*) as ticket_count FROM support_tickets WHERE status = 'pending'");
$stmt->execute();
$pending_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['ticket_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admindash.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>


    
</head>
<body>
<div class="l-navbar" id="navbar">
            <nav class="nav">
                <div>
                    <a href="#" class="nav__logo">
                        <img src="../icon/logo.svg" alt="" class="nav__logo-icon">
                        <span class="nav__logo-text">  </span>
                    </a>
    
                    <div class="nav__toggle" id="nav-toggle">
                        <i class='bx bx-chevron-right'></i>
                    </div>
    
                    <ul class="nav__list">
                    <a href="#" class="nav__link active" data-section="dashboard-overview">
                        <i class='bx bx-home nav__icon'></i>
                            <span class="nav__text">Dashboard</span>
                        </a>
                        <a href="manage_categories.php" class="nav__link">
                        <i class='bx bx-grid-alt nav__icon'></i>
                            <span class="nav__text">Categories</span>
                        </a>
                        <a href="#" class="nav__link"data-section="all-products">
                            <i class='bx bx-package nav__icon'></i>
                            <span class="nav__text">Products</span>
                        </a>
                        <a href="#" class="nav__link"data-section="pending-products">
                        <i class='bx bxs-inbox nav__icon'></i>
                            <span class="nav__text">Requests</span>
                        </a>
                        <a href="order_manage.php" class="nav__link ">
                        <i class='bx bxs-cart-alt nav__icon'></i>
                        <span class="nav__text">Orders</span>
                        </a>  
                        <a href="#" class="nav__link"data-section="manage-users">
                        <i class='bx bx-user-circle nav__icon'></i>
                            <span class="nav__text">Users</span>
                        </a>
                        <a href="support.php" class="nav__link">
                            <i class='bx bx-message-rounded nav__icon' ></i>
                            <span class="nav__text">Support</span>
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
            <div class="success-message">
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="error-message">
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        <div id="dashboard-overview" class="section active">
        <h2>Admin Dashboard</h2>
            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>Support Tickets</h3>
                    <p class="stat-number"><?php echo $pending_tickets; ?></p>
                    <p>Pending Tickets</p>
                    <a href="support.php" class="button">View Tickets</a>
                </div>
                <div class="stat-card">
                    <h3>Total Sales</h3>
                    <p class="stat-number">₹<?php echo number_format($total_sales, 2); ?></p>
                    <p>Revenue Generated</p>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <p class="stat-number"><?php echo $total_orders; ?></p>
                    <p>Orders Processed</p>
                </div>
                <div class="stat-card">
                    <h3>Products</h3>
                    <p class="stat-number"><?php echo count($all_products); ?></p>
                    <p>Active Products</p>
                </div>
            </div>
        </div>
        <div id="all-products" class="section">
            <div class="section-header">
                <h2>All Products</h2>
                <div class="action-buttons">
                    <a href="manage_categories.php" class="button">Manage Categories</a>
                    <a href="add_product.php" class="button">Add Product</a>
                </div>
            </div>
            
            <div class="filter-section">
                <form class="filter-form" action="dashboard.php" method="get">
                    <select name="vendor_id">
                        <option value="">All Vendors</option>
                        <?php foreach ($vendors as $vendor): ?>
                            <option value="<?php echo $vendor['id']; ?>" 
                                <?php echo ($vendor_id == $vendor['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($vendor['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Apply Filter</button>
                    <?php if ($vendor_id): ?>
                        <a href="dashboard.php" class="clear-filter">Clear Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($all_products)): ?>
                <p>No products available.</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($all_products as $product): ?>
                        <div class="vendor-product-card">
                            <?php if (!empty($product['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p>Price: ₹<?php echo number_format($product['price'], 2); ?></p>
                                <p>Stock: <?php echo $product['stock']; ?></p>
                                <p>Status: <?php echo ucfirst($product['status']); ?></p>
                                <p>Vendor: <?php echo htmlspecialchars($product['vendor_name']); ?></p>
                                <div class="product-actions">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                       class="button edit-button">Edit</a>
                                    <form action="delete_product.php" method="post" class="delete-form">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="button delete-button" 
                                                onclick="return confirm('Are you sure you want to delete this product?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
                            </div>
                            <div id="pending-products" class="section">
            <h2>Pending Product Requests</h2>
            <?php if (empty($pending_products)): ?>
                <p>No pending product requests.</p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($pending_products as $product): ?>
                        <div class="vendor-product-card">
                            <?php if (!empty($product['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p><?php echo htmlspecialchars($product['description']); ?></p>
                                <p>Price: ₹<?php echo number_format($product['price'], 2); ?></p>
                                <p>Stock: <?php echo $product['stock']; ?></p>
                                <div class="product-actions">
                                    <form action="approve_product.php" method="post" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="button approve-button">Approve</button>
                                    </form>
                                    <form action="reject_product.php" method="post" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" class="button reject-button">Reject</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
                    </div>

        <div id="manage-users" class="section">
        <div class="tab">
            <h2>Manage Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo ucfirst($user['user_type']); ?></td>
                            <td>
                                <form action="delete_user.php" method="post">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="button delete-button"
                                            onclick="return confirm('Are you sure you want to delete this user?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div>
    </main>
    <script src="../js/admindash.js"></script>
</body>
</html>