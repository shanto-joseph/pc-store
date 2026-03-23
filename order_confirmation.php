<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_GET['order_id'];

// Fetch order details
$order = get_order_by_id($conn, $order_id);

if (!$order || $order['user_id'] != $user_id) {
    header('Location: index.php');
    exit();
}

// Fetch order items and user details
$order_items = get_order_items($conn, $order_id);
$user = get_user_by_id($conn, $user_id);

// Handle invoice download
if (isset($_POST['download_invoice'])) {
    $invoice_content = generate_pdf_invoice($order, $order_items, $user);
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="invoice_' . $order_id . '.pdf"');
    header('Content-Length: ' . strlen($invoice_content));
    echo $invoice_content;
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link rel="stylesheet" href="css/cart.css">
    <style>
        .download-invoice {
            margin-top: 20px;
            text-align: center;
        }
        .download-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        .download-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="profile.php">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <section id="order-confirmation">
            <h2>Thank you for your order!</h2>
            <?php if (isset($_SESSION['success_message'])): ?>
                <p class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
            <?php endif; ?>
            
            <h3>Order Details</h3>
            <p><strong>Order ID:</strong> <?php echo $order['id']; ?></p>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
            <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></p>
            
            <h3>Order Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td>₹<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="download-invoice">
                <form method="post">
                    <button type="submit" name="download_invoice" class="download-btn">
                        Download Invoice
                    </button>
                </form>
            </div>
            
            <p>An email confirmation has been sent to your registered email address.</p>
            <p>You can view your order details in your <a href="profile.php">profile</a>.</p>
            
            <a href="index.php" class="button">Continue Shopping</a>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 PC STORE. All rights reserved.</p>
    </footer>
</body>
</html>