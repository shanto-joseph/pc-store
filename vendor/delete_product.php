<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../login.php');
    exit();
}

$vendor_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Check if the product belongs to the vendor
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$product_id, $vendor_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $result = delete_product($conn, $product_id);
        // Alternatively, use soft_delete_product($conn, $product_id);

        if ($result) {
            $_SESSION['success_message'] = "Product deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to delete the product. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "You don't have permission to delete this product.";
    }
}

header('Location: dashboard.php');
exit();
?>