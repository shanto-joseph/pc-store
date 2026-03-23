<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];

    // Check if user actually purchased this product
    $stmt = $conn->prepare("
        SELECT oi.id FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ? AND oi.product_id = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id, $product_id]);
    if (!$stmt->fetch()) {
        $_SESSION['error_message'] = "You can only review products you have purchased.";
        header('Location: profile.php');
        exit();
    }

    // Check if user has already reviewed this product
    $stmt = $conn->prepare("SELECT id FROM product_reviews WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    if ($stmt->fetch()) {
        $_SESSION['error_message'] = "You have already reviewed this product.";
        header('Location: profile.php');
        exit();
    }

    // Insert the review
    $stmt = $conn->prepare("
        INSERT INTO product_reviews (user_id, product_id, rating, review, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    if ($stmt->execute([$user_id, $product_id, $rating, $review])) {
        // Update the average rating in products table
        $stmt = $conn->prepare("
            UPDATE products 
            SET average_rating = (
                SELECT AVG(rating) 
                FROM product_reviews 
                WHERE product_id = ?
            )
            WHERE id = ?
        ");
        $stmt->execute([$product_id, $product_id]);
        
        $_SESSION['success_message'] = "Review submitted successfully!";
    } else {
        $_SESSION['error_message'] = "Failed to submit review. Please try again.";
    }
}

header('Location: profile.php');
exit();
?>