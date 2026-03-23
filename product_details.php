<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$product_id = $_GET['id'];
$product = get_product_by_id($conn, $product_id);

if (!$product || $product['deleted'] == 1 || $product['status'] !== 'approved') {
    header('Location: index.php');
    exit();
}

// Get vendor details
$vendor = get_user_by_id($conn, $product['vendor_id']);

// Get category details if available
$category = null;
if ($product['category_id']) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$product['category_id']]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if the product is in the user's cart
$in_cart = false;
$cart_quantity = 0;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user_id'], $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($cart_item) {
        $in_cart = true;
        $cart_quantity = $cart_item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Product Details</title>
    <link rel="stylesheet" href="css/details.css">

    <style>
        
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="cart.php">Cart</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <main>
        <section class="product-details">
            <div class="product-grid">
                <div class="product-image">
                    <?php if (!empty($product['image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php else: ?>
                        <img src="images/placeholder.jpg" alt="No image available">
                    <?php endif; ?>
                </div>

                <div class="product-info">
                    <h2 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h2>
                    
                    <div class="product-price">
                    ₹<?php echo number_format($product['price'], 2); ?>
                    </div>

                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
<!-- ___________________________________________________________________________________________________________________________________________________________________________________ -->
<div class="product-meta">
    <?php if ($category): ?>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($category['name']); ?></p>
    <?php endif; ?>
    <p><strong>Vendor:</strong> <?php echo htmlspecialchars($vendor['name']); ?></p>
    
    <?php
    // Get average rating and review count
    $stmt = $conn->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
        FROM product_reviews 
        WHERE product_id = ?
    ");
    $stmt->execute([$product_id]);
    $rating_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
    $review_count = $rating_data['review_count'];
    ?>
    
    <div class="product-rating">
        <div class="star-rating">
            <?php
            $full_stars = floor($avg_rating);
            $half_star = $avg_rating - $full_stars >= 0.5;
            
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $full_stars) {
                    echo '★';
                } elseif ($i == $full_stars + 1 && $half_star) {
                    echo '★';
                } else {
                    echo '☆';
                }
            }
            ?>
            <span>(<?php echo $avg_rating; ?> / 5)</span>
        </div>
        <p><?php echo $review_count; ?> customer reviews</p>
    </div>
    
    <?php
    $stock_class = '';
    $stock_text = '';
    if ($product['stock'] > 10) {
        $stock_class = 'in-stock';
        $stock_text = 'In Stock';
    } elseif ($product['stock'] > 0) {
        $stock_class = 'low-stock';
        $stock_text = 'Low Stock - Only ' . $product['stock'] . ' left';
    } else {
        $stock_class = 'out-of-stock';
        $stock_text = 'Out of Stock';
    }
    ?>
    <div class="stock-status <?php echo $stock_class; ?>">
        <?php echo $stock_text; ?>
    </div>
</div>

                    <?php if ($product['stock'] > 0): ?>
                        <div class="quantity-controls">
                            <button class="quantity-btn minus" onclick="updateQuantity(-1)">-</button>
                            <input type="number" class="quantity-input" value="<?php echo max(1, $cart_quantity); ?>" 
                                   min="1" max="<?php echo $product['stock']; ?>" id="quantity">
                            <button class="quantity-btn plus" onclick="updateQuantity(1)">+</button>
                        </div>

                        <div class="action-buttons">
                            <button onclick="addToCart()" class="add-to-cart-btn">
                                <?php echo $in_cart ? 'Update Cart' : 'Add to Cart'; ?>
                            </button>
                            <form action="buy_now.php" method="post" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" id="buy-now-quantity" value="1">
                                <button type="submit" class="buy-now-btn">Buy Now</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <script>
    function updateQuantity(change) {
        const input = document.querySelector('.quantity-input');
        let newValue = parseInt(input.value) + change;
        const maxStock = <?php echo $product['stock']; ?>;
        
        if (newValue < 1) newValue = 1;
        if (newValue > maxStock) newValue = maxStock;
        
        input.value = newValue;
        document.getElementById('buy-now-quantity').value = newValue;
    }

    function addToCart() {
        const quantity = document.querySelector('.quantity-input').value;
        
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=<?php echo $product_id; ?>&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Added to cart!');
                location.reload();
            } else {
                showToast(data.message || 'Failed to add product to cart.', true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', true);
        });
    }

    function showToast(msg, isError = false) {
        let t = document.getElementById('cart-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'cart-toast';
            t.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:12px 22px;border-radius:8px;font-size:15px;z-index:9999;transition:opacity .4s;';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.style.background = isError ? '#c0392b' : '#1fbb1f';
        t.style.color = '#fff';
        t.style.opacity = '1';
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.style.opacity = '0', 2500);
    }
    </script>

    <footer>
        <p>&copy; 2026 PC STORE. All rights reserved.</p>
    </footer>
</body>
</html>