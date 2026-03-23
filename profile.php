<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($conn, $user_id);
$orders = get_user_orders($conn, $user_id);

// Get user's support tickets count
$stmt = $conn->prepare("SELECT COUNT(*) as ticket_count FROM support_tickets WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$user_id]);
$ticket_count = $stmt->fetch(PDO::FETCH_ASSOC)['ticket_count'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    update_user_profile($conn, $user_id, $name, $email);
    $user = get_user_by_id($conn, $user_id);
    $success_message = "Profile updated successfully.";
}

// Function to check if a product has been reviewed by the user
function has_user_reviewed($conn, $user_id, $product_id) {
    $stmt = $conn->prepare("SELECT * FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/nav.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
</head>
<body id="body">
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <a href="index.php" class="nav__logo">
                    <img src="icon/logo.svg" alt="" class="nav__logo-icon">
                </a>
                <div class="nav__toggle" id="nav-toggle">
                    <i class='bx bx-chevron-right'></i>
                </div>
                <ul class="nav__list">
                    <a href="#" class="nav__link active" data-section="profile-section">
                        <i class='bx bx-user-circle nav__icon'></i>
                        <span class="nav__text">Profile</span>
                    </a>
                    <a href="#" class="nav__link" data-section="orders-section">
                        <i class='bx bx-package nav__icon'></i>
                        <span class="nav__text">Orders</span>
                    </a>
                    <a href="index.php" class="nav__link">
                        <i class='bx bx-home nav__icon'></i>
                        <span class="nav__text">Home</span>
                    </a>
                    <a href="cart.php" class="nav__link">
                        <i class='bx bx-cart-alt nav__icon'></i>
                        <span class="nav__text">Cart</span>
                    </a>
                    <a href="support.php" class="nav__link">
                        <i class='bx bx-message-rounded nav__icon'></i>
                        <span class="nav__text">Support</span>
                    </a>
                </ul>
            </div>
            <a href="logout.php" class="nav__link">           
                <i class='bx bx-log-out-circle nav__icon'></i>
                <span class="nav__text">Logout</span>
            </a>
        </nav>
    </div>

    <main>
        <div id="profile-section" class="section active">
            <h2>Your Profile</h2>
            <?php if (isset($success_message)): ?>
                <p class="success"><?php echo $success_message; ?></p>
            <?php endif; ?>
            <form action="profile.php" method="post">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                <button type="submit">Update Profile</button>
            </form>
        </div>

        <div id="orders-section" class="section">
            <h2>Your Orders</h2>
            <?php if (empty($orders)): ?>
                <p>You haven't placed any orders yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Review</th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php 
                            // Get order items for review
                            $order_items = get_order_items($conn, $order['id']);
                            ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo date('F j, Y', strtotime($order['created_at'])); ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo ucfirst($order['status']); ?></td>
                                <td>
                                    <a href="order_details.php?order_id=<?php echo $order['id']; ?>">View Details</a>
                                </td>
                                <td>
                                    <?php foreach ($order_items as $item): ?>
                                        <?php $review = has_user_reviewed($conn, $user_id, $item['product_id']); ?>
                                        <div style="margin-bottom: 10px;">
                                            <?php if ($review): ?>
                                                <button onclick="viewReview(<?php echo $review['id']; ?>)" class="button">
                                                    View Review
                                                </button>
                                            <?php else: ?>
                                                <button onclick="openReviewModal(<?php echo $item['product_id']; ?>, '<?php echo htmlspecialchars($item['product_name']); ?>')" class="button">
                                                    Add Review
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
    
    <!-- Review Modal -->
    <div id="reviewModal" class="review-modal">
        <div class="review-modal-content">
            <span class="close-modal" onclick="closeReviewModal()">&times;</span>
            <h2>Write a Review</h2>
            <form id="reviewForm" action="submit_review.php" method="post">
                <input type="hidden" id="product_id" name="product_id">
                <p id="productName"></p>
                
                <div class="stars-container">
                    <?php for($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" class="star-input">
                        <label for="star<?php echo $i; ?>" class="star-label">★</label>
                    <?php endfor; ?>
                </div>
                
                <label for="review">Your Review:</label>
                <textarea id="review" name="review" required></textarea>
                
                <button type="submit">Submit Review</button>
            </form>
        </div>
    </div>
    <script src="js/nav.js"></script>
</body>
</html>