<?php
session_start();
include '../config.php';
include '../functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Update the product status to 'approved'
    $stmt = $conn->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
    $result = $stmt->execute([$product_id]);

    if ($result) {
        $_SESSION['success_message'] = "Product approved successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to approve the product. Please try again.";
    }
}

// Redirect back to the admin dashboard
header('Location: dashboard.php');
exit();
?>