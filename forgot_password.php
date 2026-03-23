<?php
session_start();
include 'config.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // First check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error_message = "No account found with this email address.";
    } else {
        // Validate password
        $password_errors = validatePassword($new_password, $confirm_password);
        
        if (!empty($password_errors)) {
            $error_message = implode("<br>", $password_errors);
        } else {
            // Update password with hash
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            if ($stmt->execute([$hashed_password, $email])) {
                $_SESSION['success_message'] = "Password updated successfully!";
                header('Location: login.php');
                exit();
            } else {
                $error_message = "Failed to update password. Please try again.";
            }
        }
    }
}

function validatePassword($password, $confirmPassword = null) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter"; 
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = "Password must contain at least one special character";
    }
    if ($confirmPassword !== null && $password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    return $errors;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PC STORE</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="logo">
    <img src="icon/cart.svg" alt="PC STORE Logo" class="logo-icon">
    <span class="logo-text">PC STORE</span>
</div>
<!-- <script>
    document.querySelector('.logo').addEventListener('click', function() {
        window.location.href = 'index.php';
    });

    function validatePasswordClient(password, confirmPassword) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        const errors = [];
        
        if (password.length < minLength) {
            errors.push('Password must be at least 8 characters long');
        }
        if (!hasUpperCase) {
            errors.push('Password must contain at least one uppercase letter');
        }
        if (!hasLowerCase) {
            errors.push('Password must contain at least one lowercase letter');
        }
        if (!hasNumbers) {
            errors.push('Password must contain at least one number');
        }
        if (!hasSpecialChar) {
            errors.push('Password must contain at least one special character');
        }
        if (confirmPassword && password !== confirmPassword) {
            errors.push('Passwords do not match');
        }
        
        return errors;
    }

    function handleFormSubmit(event) {
        const password = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        const errors = validatePasswordClient(password, confirmPassword);
    
        if (errors.length > 0) {
            event.preventDefault();
            alert(errors.join('\n'));
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.addEventListener('submit', handleFormSubmit);
    });
</script> -->
<div class="up">
    <div class="menu-button" onclick="toggleNav()"> 
        <span></span>
        <span></span>
        <span></span>
    </div>
    <div class="bar">
        <div class="item"><a href="index.php">HOME</a></div>
        <div class="item"><a href="about.html">ABOUT</a></div>
        <div class="item"><a href="contact.html">CONTACT</a></div> 
    </div>
</div>

<main>
    <div class="login-box">
        <h2>Reset Password</h2>
        <?php if (isset($error_message)): ?>
            <div class="login-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="forgot_password.php" method="post">
            <div class="user-box">
                <input type="email" id="email" name="email" required placeholder=" ">
                <label for="email">Email Address</label>
            </div>
            <div class="user-box">
                <input type="password" id="new_password" name="new_password" required placeholder=" ">
                <label for="new_password">New Password</label>
            </div>
            <div class="user-box">
                <input type="password" id="confirm_password" name="confirm_password" required placeholder=" ">
                <label for="confirm_password">Confirm Password</label>
            </div>
            <button type="submit" class="login-button">Update Password</button>
            <div class="links-container">
                Remember your password? <a href="login.php" class="signup-link">Login</a>
            </div>
        </form>
    </div>
</main>

<div class="side-nav" id="sideNav">
    <a href="login.php">Customer Login</a>
    <a href="vendor/login.php">Vendor Login</a>
    <a href="admin/login.php">Admin Login</a>
</div>

<script src="login.js"></script>
</body>
</html>