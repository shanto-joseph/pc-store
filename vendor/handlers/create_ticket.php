<?php
session_start();
include '../../config.php';
include '../../functions.php';
include '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($subject) || empty($message)) {
        $_SESSION['error_message'] = "Both subject and message are required.";
        header('Location: ../support.php');
        exit();
    }
    
    $stmt = $conn->prepare("
        INSERT INTO support_tickets (user_id, subject, message) 
        VALUES (?, ?, ?)
    ");
    
    if ($stmt->execute([$user_id, $subject, $message])) {
        $_SESSION['success_message'] = "Support ticket created successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to create support ticket.";
    }
}

header('Location: ../support.php');
exit();
?>