<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    
    // Don't allow admin to delete themselves
    if ($user_id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "You cannot delete your own admin account.";
        header('Location: dashboard.php');
        exit();
    }

    // Begin transaction
    $conn->beginTransaction();

    try {
        // Delete user's cart items
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Delete user's reviews
        $stmt = $conn->prepare("DELETE FROM product_reviews WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Delete user's support tickets and replies
        $stmt = $conn->prepare("DELETE FROM support_replies WHERE ticket_id IN (SELECT id FROM support_tickets WHERE user_id = ?)");
        $stmt->execute([$user_id]);
        
        $stmt = $conn->prepare("DELETE FROM support_tickets WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // For vendors, handle their products
        $stmt = $conn->prepare("SELECT user_type FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['user_type'] === 'vendor') {
            // Soft delete vendor's products
            $stmt = $conn->prepare("UPDATE products SET deleted = 1 WHERE vendor_id = ?");
            $stmt->execute([$user_id]);
        }

        // Delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = "User deleted successfully.";

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error_message'] = "Failed to delete user. Please try again.";
    }
}

header('Location: dashboard.php');
exit();
?>