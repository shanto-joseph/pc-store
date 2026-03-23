<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header('Location: profile.php');
    exit();
}

$review_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get review details
$stmt = $conn->prepare("
    SELECT r.*, p.name as product_name, u.name as user_name 
    FROM product_reviews r
    JOIN products p ON r.product_id = p.id
    JOIN users u ON r.user_id = u.id
    WHERE r.id = ? AND r.user_id = ?
");
$stmt->execute([$review_id, $user_id]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Review</title>
    <link rel="stylesheet" href="css/review.css">
    <style>
        
    </style>
</head>
<body>
    <header>

        <h1>Your Review</h1>
        <div class="nav-center">
             <nav>
                <ul>
                    <li><a href="profile.php">Back to Profile</a></li>
                    <li><a href="index.php">Home</a></li>
                </ul>
             </nav>
        </div>
    </header>

    <main>
        <div class="review-container">
            <h2><?php echo htmlspecialchars($review['product_name']); ?></h2>
            <div class="review-meta">
                <p>Reviewed on: <?php echo date('F j, Y', strtotime($review['created_at'])); ?></p>
            </div>
            <div class="star-rating">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    echo ($i <= $review['rating']) ? '★' : '☆';
                }
                ?>
            </div>
            <div class="review-text">
                <?php echo nl2br(htmlspecialchars($review['review'])); ?>
            </div>
            <a href="profile.php" class="button">Back to Profile</a>
        </div>
    </main>
</body>
</html>