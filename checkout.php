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
$user = get_user_by_id($conn, $user_id);

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

$total_amount = 0;
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required_fields = ['full_name', 'address_line1', 'city', 'postal_code', 'country', 'phone'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }

    if (!empty($_POST['phone']) && !preg_match('/^\+[0-9]{1,4}[0-9]{10}$/', $_POST['phone'])) {
        $errors[] = 'Please enter a valid phone number with country code (e.g., +911234567890)';
    }

    if (empty($errors)) {
        $shipping_address = $_POST['full_name'] . "\n" .
                          $_POST['address_line1'] . "\n" .
                          (!empty($_POST['address_line2']) ? $_POST['address_line2'] . "\n" : '') .
                          $_POST['city'] . "\n" .
                          $_POST['postal_code'] . "\n" .
                          $_POST['country'] . "\n" .
                          $_POST['phone'];
        
        if (!empty($_POST['razorpay_payment_id'])) {
            $order_id = create_order_with_shipping(
                $conn,
                $user_id,
                $shipping_address,
                'razorpay',
                $total_amount
            );

            if ($order_id) {
                $razorpayPaymentId = $_POST['razorpay_payment_id'];
                save_payment_details($conn, $order_id, $razorpayPaymentId);

                foreach ($cart_items as $item) {
                    add_order_item($conn, $order_id, $item['product_id'], $item['quantity'], $item['price']);
                }

                clear_cart($conn, $user_id);

                $_SESSION['success_message'] = "Order placed successfully!";
                header('Location: order_confirmation.php?order_id=' . $order_id);
                exit();
            } else {
                $error_message = "Failed to place the order. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/cart.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            min-height: calc(100vh - 200px);
        }
        .order-summary {
            background: rgba(0, 0, 0, 0.5);
            padding: 2rem;
            border-radius: 10px;
            height: fit-content;
        }
        .shipping-form {
            background: rgba(0, 0, 0, 0.5);
            padding: 2rem;
            border-radius: 10px;
            height: fit-content;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-full-width { grid-column: 1 / -1; }
        .checkout-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .checkout-item:last-child { border-bottom: none; }
        .item-details { display: flex; align-items: center; gap: 1rem; }
        .item-image { width: 60px; height: 60px; border-radius: 5px; overflow: hidden; }
        .item-image img { width: 100%; height: 100%; object-fit: cover; }
        .total-section {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .place-order-btn { width: 100%; margin-top: 1rem; }
        nav { margin-bottom: 0; }
        main { padding-top: 1rem; }
        @media (max-width: 768px) {
            .checkout-container { grid-template-columns: 1fr; padding: 1rem; }
            .form-grid { grid-template-columns: 1fr; }
            .form-full-width { grid-column: 1; }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="cart.php">Cart</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="checkout-container">
            <div class="order-summary">
                <h2>Order Summary</h2>
                <?php foreach ($cart_items as $item): ?>
                    <div class="checkout-item">
                        <div class="item-details">
                            <?php if (!empty($item['image'])): ?>
                                <div class="item-image">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                            <?php endif; ?>
                            <div>
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>Quantity: <?php echo $item['quantity']; ?></p>
                            </div>
                        </div>
                        <div class="item-price">
                            ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="total-section">
                    <h3>Total: ₹<?php echo number_format($total_amount, 2); ?></h3>
                </div>
            </div>

            <div class="shipping-form">
                <h2>Shipping Details</h2>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form action="checkout.php" method="post" class="checkout-form">
                    <div class="form-grid">
                        <div class="form-full-width">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required 
                                   value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                        </div>
                        <div class="form-full-width">
                            <label for="address_line1">Address Line 1</label>
                            <input type="text" id="address_line1" name="address_line1" required
                                   value="<?php echo isset($_POST['address_line1']) ? htmlspecialchars($_POST['address_line1']) : ''; ?>">
                        </div>
                        <div class="form-full-width">
                            <label for="address_line2">Address Line 2 (Optional)</label>
                            <input type="text" id="address_line2" name="address_line2"
                                   value="<?php echo isset($_POST['address_line2']) ? htmlspecialchars($_POST['address_line2']) : ''; ?>">
                        </div>
                        <div class="form-fu">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required
                                   value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                        </div>
                        <div class="form-fu">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" required
                                   value="<?php echo isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : ''; ?>">
                        </div>
                        <div class="form-fu">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" required
                                   value="<?php echo isset($_POST['country']) ? htmlspecialchars($_POST['country']) : ''; ?>">
                        </div>
                        <div class="form-fu">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required placeholder="+91"
                                   pattern="\+[0-9]{1,4}[0-9]{10}"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                    </div>

                    <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                    <button type="button" id="rzp-button1" class="place-order-btn">Place Order</button>
                </form>
            </div>
        </div>
    </main>

    <script>
        var options = {
            "key": "<?php echo htmlspecialchars(RAZORPAY_KEY_ID); ?>",
            "amount": "<?php echo $total_amount * 100; ?>",
            "currency": "INR",
            "name": "PC STORE",
            "description": "Order Payment",
            "handler": function (response) {
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.querySelector('.checkout-form').submit();
            },
            "prefill": {
                "name": "<?php echo htmlspecialchars($user['name']); ?>",
                "email": "<?php echo htmlspecialchars($user['email']); ?>"
            },
            "theme": {
                "color": "#1fbb1f"
            }
        };
        var rzp1 = new Razorpay(options);
        document.getElementById('rzp-button1').onclick = function(e) {
            var form = document.querySelector('.checkout-form');
            if (form.checkValidity()) {
                rzp1.open();
                e.preventDefault();
            } else {
                form.reportValidity();
            }
        };
    </script>
</body>
</html>