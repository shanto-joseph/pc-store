<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];
    
    // Check if category has any products
    $stmt = $conn->prepare("SELECT COUNT(*) as product_count FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['product_count'] > 0) {
        $_SESSION['error_message'] = "Cannot delete category: There are products associated with this category.";
    } else {
        // Delete the category
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $result = $stmt->execute([$category_id]);
        
        if ($result) {
            $_SESSION['success_message'] = "Category deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete category. Please try again.";
        }
    }
}

header('Location: dashboard.php');
exit();
?>