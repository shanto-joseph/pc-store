<?php
session_start();
include 'config.php';
include 'functions.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password
    $password_errors = validatePassword($password, $confirm_password);
    
    if (!empty($password_errors)) {
        $error = implode("<br>", $password_errors);
    } else {
        $result = register_user($conn, $name, $email, $password, 'customer');
        if ($result) {
            $_SESSION['user_id'] = $result;
            $_SESSION['user_type'] = 'customer';
            header('Location: index.php');
            exit();
        } else {
            $error = "Registration failed. Please try again.";
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
    <title>Register - PC STORE</title>
    <link rel="stylesheet" href="css/login.css">
    
</head>
<body>
<div class="logo">
    <img src="icon/cart.svg" alt="PC STORE Logo" class="logo-icon">
    <span class="logo-text">PC STORE</span>
</div>
<script>
    document.querySelector('.logo').addEventListener('click', function() {
        window.location.href = 'index.php';
    });
</script>
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
            <h2>Register</h2>
            <?php if (isset($error)): ?>
                <div class="login-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="register.php" method="post">
            <div class="user-box">
                <input type="text" id="name" name="name" required placeholder=" ">
                <label for="name">Name</label>
            </div>
            <div class="user-box">
                <input type="email" id="email" name="email" required placeholder=" ">
                <label for="email">Email</label>
            </div>
            <div class="user-box">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Password</label>
            </div>
            <div class="user-box">
                <input type="password" id="confirm_password" name="confirm_password" required placeholder=" ">
                <label for="confirm_password">Confirm Password</label>
            </div>
            <button type="submit" class="login-button">Register</button>
            <div class="links-container">
                Already have an account? <a href="login.php" class="signup-link">Login</a>
            </div>
            </form>
        </div>
    </main>
    <div class="side-nav" id="sideNav">
        <a href="login.php">Customer Login</a>
        <a href="vendor/login.php">Vendor Login</a>
    </div>



    <!-- <script>
        function validatePassword(password, confirmPassword) {
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

function handlePasswordValidation(event, passwordId, confirmPasswordId) {
    const password = document.getElementById(passwordId).value;
    const confirmPassword = document.getElementById(confirmPasswordId).value;
    
    const errors = validatePassword(password, confirmPassword);
    
    if (errors.length > 0) {
        event.preventDefault();
        alert(errors.join('\n'));
    }
}
</script> -->

    <script src="login.js"></script>

</body>
</html>