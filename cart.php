<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = get_cart_items($conn, $user_id);
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - PC STORE</title>
    <link rel="stylesheet" href="css/cart.css">
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2>Your Shopping Cart</h2>
        
        <?php if (empty($cart_items)): ?>
            <p >Your cart is empty. <a href="index.php" style="color: green;">Continue shopping</a></p>

        <?php else: ?>
            <div class="cart-grid">
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <?php if (!empty($item['image'])): ?>
                            <div class="cart-item-image">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="cart-item-details">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="price">₹<?php echo number_format($item['price'], 2); ?></p>
                            
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                <input type="number" class="quantity-input" value="<?php echo $item['quantity']; ?>" 
                                       min="1" onchange="updateQuantity(<?php echo $item['product_id']; ?>, 0, this.value)">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, 1)">+</button>
                            </div>
                            
                            <p class="subtotal">
                                Subtotal: ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </p>
                            
                            <button class="remove-btn" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                Remove
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-total">
                <h3>Total: ₹<?php echo number_format($total_amount, 2); ?></h3>
                <a href="checkout.php" class="button">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function updateQuantity(productId, change, newValue = null) {
            let quantity;
            if (newValue !== null) {
                quantity = parseInt(newValue);
            } else {
                const input = event.target.parentElement.querySelector('.quantity-input');
                quantity = parseInt(input.value) + change;
                if (quantity < 1) quantity = 1;
                input.value = quantity;
            }

            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to update cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        function removeFromCart(productId) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to remove item from cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }
    </script>
</body>
</html>