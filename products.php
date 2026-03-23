<?php
session_start();
include 'config.php';
include 'functions.php';

$category_id  = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search_term  = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$sort         = isset($_GET['sort'])     ? $_GET['sort']           : 'newest';
$min_price    = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
$max_price    = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;

// Build query
$sql = "SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'approved' AND p.deleted = 0";
$params = [];

if ($category_id) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
}
if ($search_term) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_term%";
    $params[] = "%$search_term%";
}
if ($min_price !== null) {
    $sql .= " AND p.price >= ?";
    $params[] = $min_price;
}
if ($max_price !== null) {
    $sql .= " AND p.price <= ?";
    $params[] = $max_price;
}

$order_map = [
    'newest'    => 'p.created_at DESC',
    'oldest'    => 'p.created_at ASC',
    'price_asc' => 'p.price ASC',
    'price_desc'=> 'p.price DESC',
    'name_asc'  => 'p.name ASC',
];
$sql .= " ORDER BY " . ($order_map[$sort] ?? 'p.created_at DESC');

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = get_categories($conn);

// Active category name for heading
$active_category_name = 'All Products';
if ($category_id) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $category_id) {
            $active_category_name = htmlspecialchars($cat['name']);
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $active_category_name; ?> - PC STORE</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/products.css">
    <link rel="icon" href="icon/monitor.svg" type="image/x-icon">
</head>
<body>
    <!-- ── Nav (same as index.php) ── -->
    <nav class="main-nav">
        <div class="logo">
            <a href="index.php">PC STORE</a>
        </div>
        <button class="nav-hamburger" id="navHamburger" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
        <div class="nav-center">
            <div class="search-container">
                <div class="search-icon" id="searchIcon">
                    <img src="icon/search.svg" alt="Search">
                </div>
                <div class="search-bar" id="searchBar">
                    <form action="products.php" method="get">
                        <?php if ($category_id): ?>
                            <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="Search products..."
                               value="<?php echo htmlspecialchars($search_term); ?>">
                    </form>
                </div>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">All Products</a>
                <div class="category-dropdown">
                    <a href="#" class="category-btn">Category ▼</a>
                    <div class="category-content">
                        <a href="products.php">All</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="products.php?category=<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="about.html">About</a>
            </div>
        </div>
        <div class="nav-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-menu">
                    <a href="cart.php" class="cart-icon">
                        <img src="icon/cart1.svg" alt="Cart">
                    </a>
                    <div class="profile-dropdown">
                        <span class="profile-icon">
                            <img src="icon/user.svg" alt="Profile">
                            <?php
                            $user = get_user_by_id($conn, $_SESSION['user_id']);
                            echo htmlspecialchars($user['name']);
                            ?>
                        </span>
                        <div class="profile-content">
                            <a href="profile.php">Profile</a>
                            <a href="logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="login-btn">Login/Signup</a>
            <?php endif; ?>
        </div>
    </nav>

    <main class="products-page">

        <!-- ── Sidebar filters ── -->
        <aside class="filters-sidebar">
            <h3>Filters</h3>
            <form action="products.php" method="get" id="filterForm">

                <!-- Search -->
                <div class="filter-group">
                    <label>Search</label>
                    <input type="text" name="search" placeholder="Search..."
                           value="<?php echo htmlspecialchars($search_term); ?>">
                </div>

                <!-- Categories -->
                <div class="filter-group">
                    <label>Category</label>
                    <ul class="category-list">
                        <li>
                            <a href="products.php<?php echo $search_term ? '?search='.urlencode($search_term) : ''; ?>"
                               class="<?php echo !$category_id ? 'active' : ''; ?>">
                                All
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <li>
                                <a href="products.php?category=<?php echo $cat['id']; ?><?php echo $search_term ? '&search='.urlencode($search_term) : ''; ?>"
                                   class="<?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Price range -->
                <div class="filter-group">
                    <label>Price Range (₹)</label>
                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="Min"
                               value="<?php echo $min_price !== null ? $min_price : ''; ?>" min="0">
                        <span>–</span>
                        <input type="number" name="max_price" placeholder="Max"
                               value="<?php echo $max_price !== null ? $max_price : ''; ?>" min="0">
                    </div>
                </div>

                <?php if ($category_id): ?>
                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                <?php endif; ?>
                <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">

                <button type="submit" class="filter-btn">Apply</button>
                <a href="products.php" class="clear-btn">Clear All</a>
            </form>
        </aside>

        <!-- ── Products area ── -->
        <div class="products-area">

            <!-- Toolbar -->
            <div class="products-toolbar">
                <h2><?php echo $active_category_name; ?>
                    <span class="count">(<?php echo count($products); ?>)</span>
                </h2>
                <div class="sort-bar">
                    <label for="sortSelect">Sort:</label>
                    <select id="sortSelect" onchange="applySort(this.value)">
                        <option value="newest"     <?php echo $sort==='newest'     ? 'selected':'' ?>>Newest</option>
                        <option value="price_asc"  <?php echo $sort==='price_asc'  ? 'selected':'' ?>>Price: Low → High</option>
                        <option value="price_desc" <?php echo $sort==='price_desc' ? 'selected':'' ?>>Price: High → Low</option>
                        <option value="name_asc"   <?php echo $sort==='name_asc'   ? 'selected':'' ?>>Name A–Z</option>
                    </select>
                </div>
            </div>

            <!-- Active filters chips -->
            <?php if ($category_id || $search_term || $min_price !== null || $max_price !== null): ?>
            <div class="active-filters">
                <?php if ($search_term): ?>
                    <span class="chip">Search: "<?php echo htmlspecialchars($search_term); ?>"
                        <a href="<?php echo 'products.php' . ($category_id ? '?category='.$category_id : ''); ?>">×</a>
                    </span>
                <?php endif; ?>
                <?php if ($category_id): ?>
                    <span class="chip">Category: <?php echo $active_category_name; ?>
                        <a href="products.php<?php echo $search_term ? '?search='.urlencode($search_term) : ''; ?>">×</a>
                    </span>
                <?php endif; ?>
                <?php if ($min_price !== null || $max_price !== null): ?>
                    <span class="chip">Price: ₹<?php echo $min_price ?? '0'; ?> – ₹<?php echo $max_price ?? '∞'; ?>
                        <a href="products.php<?php
                            $q = [];
                            if ($category_id) $q[] = 'category='.$category_id;
                            if ($search_term) $q[] = 'search='.urlencode($search_term);
                            echo $q ? '?'.implode('&',$q) : '';
                        ?>">×</a>
                    </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Grid -->
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <p>No products found.</p>
                    <a href="products.php" class="btn-back">View all products</a>
                </div>
            <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-link">
                            <div class="product-image">
                                <?php if (!empty($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <?php else: ?>
                                    <div class="no-img"><i>📦</i></div>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></span>
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
                                <?php if ($product['stock'] < 5 && $product['stock'] > 0): ?>
                                    <span class="low-stock">Only <?php echo $product['stock']; ?> left</span>
                                <?php elseif ($product['stock'] == 0): ?>
                                    <span class="out-of-stock">Out of stock</span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="product-actions">
                            <button onclick="addToCart(<?php echo $product['id']; ?>, event)"
                                    class="add-to-cart-btn"
                                    <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                Add to Cart
                            </button>
                            <a href="product_details.php?id=<?php echo $product['id']; ?>" class="view-btn">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <div class="footer-links">
            <a href="#">Terms and Conditions</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Return Policy</a>
            <a href="#">Contact Us</a>
        </div>
        <div class="footer-copyright">© 2026 PC STORE. All rights reserved.</div>
    </footer>

    <script>
        // Search icon toggle
        document.getElementById('searchIcon').addEventListener('click', function () {
            document.getElementById('searchBar').classList.toggle('active');
        });

        // Hamburger
        document.getElementById('navHamburger').addEventListener('click', function () {
            document.querySelector('.nav-center').classList.toggle('open');
        });

        // Sort — rebuild URL preserving other params
        function applySort(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', value);
            window.location.href = url.toString();
        }

        // Add to cart
        function addToCart(productId, event) {
            event.preventDefault();
            event.stopPropagation();
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${productId}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showToast('Added to cart!');
                } else {
                    showToast(data.message || 'Failed to add to cart', true);
                }
            })
            .catch(() => showToast('An error occurred', true));
        }

        function showToast(msg, isError = false) {
            const t = document.createElement('div');
            t.className = 'toast' + (isError ? ' toast-error' : '');
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => t.classList.add('show'), 10);
            setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 2500);
        }
    </script>
</body>
</html>
