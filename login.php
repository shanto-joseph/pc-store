<?php
session_start();
include 'config.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = authenticate_user($conn, $email, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];

        if ($user['user_type'] === 'admin') {
            header('Location: admin/dashboard.php');
            exit();
        } elseif ($user['user_type'] === 'vendor') {
            header('Location: vendor/dashboard.php');
            exit();
        } else {
            header('Location: index.php');
            exit();
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!-- //Authorized// -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PC STORE</title>
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
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your PC STORE account</p>
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="login-error" style="background:rgba(31,187,31,0.15);border-color:#1fbb1f;color:#1fbb1f;"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="login.php" method="post">
            <div class="user-box">
                <input type="email" id="email" name="email" required placeholder=" ">
                <label for="email">Email</label>
                <span class="field-icon">&#9993;</span>
            </div>
            <div class="user-box">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Password</label>
                <span class="field-icon">&#128274;</span>
            </div>
                <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                <button type="submit" class="login-button">Login</button>
            <div class="links-container">
                Don't have an account? <a href="register.php" class="signup-link">Sign up</a>
            </div>
            </form>
        </div>
    </main>
    

    <div class="side-nav" id="sideNav">
    <a href="admin/login.php">Admin Login</a>
    <a href="vendor/login.php">Vendor Login</a>
</div>

<script src="login.js"></script>

</body>
</html>