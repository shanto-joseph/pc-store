<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1;

    // Add the product to the cart
    add_to_cart($conn, $user_id, $product_id, $quantity);

    // Redirect to checkout
    header('Location: checkout.php');
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>