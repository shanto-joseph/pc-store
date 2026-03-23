<?php
session_start();
include 'config.php';
include 'functions.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$products = get_products($conn, $category_id, $search_term);
$categories = get_categories($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PC STORE</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="icon" href="icon/monitor.svg" type="image/x-icon">
</head>
<body>
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
                    <form action="index.php" method="get">
                        <input type="text" name="search" placeholder="Search products...">
                        <!-- <button type="submit">Search</button> -->
                    </form>
                </div>
            </div>
            
            <div class="nav-links">
                <a href="index.php">Home</a>
                <div class="category-dropdown">
                    <a href="#" class="category-btn">Category ▼</a>
                    <div class="category-content">
                        <a href="products.php">All</a>
                        <?php foreach ($categories as $category): ?>
                            <a href="products.php?category=<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="products.php">Products</a>
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

    <div class="carousel">
        <div class="carousel-container">
            <div class="carousel-slide">
                <img src="image/1.png" alt="Slide 1">
            </div>
            <div class="carousel-slide">
                <img src="image/2.png" alt="Slide 2">
            </div>
            <div class="carousel-slide">
                <img src="image/3.png" alt="Slide 3">
            </div>
        </div>
        <div class="carousel-dots">
            <span class="dot active"></span>
            <span class="dot"></span>
            <span class="dot"></span>
        </div>
    </div>

    <div class="category-boxes">
        <?php foreach ($categories as $cat): ?>
        <div class="category-box" data-category="<?php echo $cat['id']; ?>">
            <?php
            // Map category name to icon
            $name_lower = strtolower($cat['name']);
            if (str_contains($name_lower, 'laptop'))      $icon = 'laptop';
            elseif (str_contains($name_lower, 'desktop')) $icon = 'desktop';
            elseif (str_contains($name_lower, 'component') || str_contains($name_lower, 'part')) $icon = 'component';
            elseif (str_contains($name_lower, 'peripher')) $icon = 'peripheral';
            elseif (str_contains($name_lower, 'accessor')) $icon = 'accessory';
            elseif (str_contains($name_lower, 'monitor') || str_contains($name_lower, 'display')) $icon = 'monitor';
            else $icon = 'package';
            ?>
            <img src="icon/<?php echo $icon; ?>.svg" alt="<?php echo htmlspecialchars($cat['name']); ?>">
            <span><?php echo htmlspecialchars($cat['name']); ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <main>
        <section id="products">
            <h2>Products <a href="products.php" class="view-all-link">View All →</a></h2>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="product-link">
                            <?php if (!empty($product['image'])): ?>
                                <div class="product-image">
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="price">₹<?php echo number_format($product['price'], 2); ?></p>
                            </div>
                        </a>
                        <div class="product-actions">
                            <button onclick="addToCart(<?php echo $product['id']; ?>, event)" class="add-to-cart-btn">
                                Add to Cart
                            </button>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-links">
            <a href="#">Terms and Conditions</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Return Policy</a>
            <a href="#">Contact Us</a>
        </div>
        <div class="footer-copyright">
            © 2026 PC STORE. All rights reserved.
        </div>
    </footer>

    <script>
        // Search functionality
        document.getElementById('searchIcon').addEventListener('click', function() {
            document.getElementById('searchBar').classList.toggle('active');
        });

        // Hamburger nav toggle
        document.getElementById('navHamburger').addEventListener('click', function() {
            document.querySelector('.nav-center').classList.toggle('open');
        });

        // Carousel functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.dot');

        function showSlide(n) {
            slides.forEach(slide => slide.style.display = 'none');
            dots.forEach(dot => dot.classList.remove('active'));
            
            currentSlide = (n + slides.length) % slides.length;
            slides[currentSlide].style.display = 'block';
            dots[currentSlide].classList.add('active');
        }

        function nextSlide() {
            showSlide(currentSlide + 1);
        }

        // Initialize carousel
        showSlide(0);
        setInterval(nextSlide, 5000);

        // Dot navigation
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => showSlide(index));
        });

        // Category box click → products page
        document.querySelectorAll('.category-box').forEach(box => {
            box.addEventListener('click', function() {
                const category = this.dataset.category;
                window.location.href = `products.php?category=${category}`;
            });
        });

        // Cart functionality
        function addToCart(productId, event) {
            event.preventDefault();
            event.stopPropagation();
            
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Added to cart!');
                } else {
                    showToast(data.message || 'Failed to add to cart', true);
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
</body>
</html>