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
    <title>Order Details</title>
    <link rel="stylesheet" href="css/orders.css">
    
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
        <section id="order-details">
            <h2>Order #<?php echo $order['id']; ?></h2>
            
            <h3>Order Information</h3>
            <p><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
            <p><strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
            
            <h3>Shipping Address</h3>
            <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
            
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
            
            <div class="action-buttons">
                <a href="profile.php" class="button">Back to Profile</a>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 PC STORE. All rights reserved.</p>
    </footer>
</body>
</html>