<?php
session_start();
include '../config.php';
include '../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Password must contain an uppercase letter";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "Password must contain a lowercase letter";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain a number";
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) $errors[] = "Password must contain a special character";

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        $result = register_user($conn, $name, $email, $password, 'vendor');
        if ($result) {
            $_SESSION['user_id'] = $result;
            $_SESSION['user_type'] = 'vendor';
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Registration failed. Email may already be in use.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration - PC STORE</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
<div class="logo">
    <img src="../icon/cart.svg" alt="PC STORE Logo" class="logo-icon">
    <span class="logo-text">PC STORE</span>
</div>
<script>
    document.querySelector('.logo').addEventListener('click', function() {
    window.location.href = '../index.php';
});
</script>

<div class="up">
    <div class="menu-button" onclick="toggleNav()"> 
        <span></span>
        <span></span>
        <span></span>
    </div>
<div class="bar">
    <div class="item"><a href="../index.php">HOME</a></div>
    <div class="item"><a href="../about.html">ABOUT</a></div>
    <div class="item"><a href="../contact.html">CONTACT</a></div> 
</div>
</div>
    <main>
        <div class="login-box">
            <h2>Vendor Registration</h2>
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
            <button type="submit"class="login-button">Register</button>
            <div class="links-container">
                Already have an account? <a href="login.php" class="signup-link">Login</a>
            </div>
            </form>
        </div>
    </main>
    <div class="side-nav" id="sideNav">
    <a href="../login.php">Customer Login</a>
    <a href="../vendor/login.php">Vendor Login</a>
</div>
<script src="../login.js"></script>

</body>
</html>