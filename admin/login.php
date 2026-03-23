<?php
session_start();
include '../config.php';
include '../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = authenticate_user($conn, $email, $password);

    if ($user && $user['user_type'] === 'admin') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = 'admin';
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - PC STORE</title>
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
            <h2>Admin Login</h2>
            <?php if (isset($error)): ?>
                <div class="login-error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="login.php" method="post">
            <div class="user-box">
                <input type="email" id="email" name="email" required placeholder=" ">
                <label for="email">Email</label>
            </div>
            <div class="user-box">
                <input type="password" id="password" name="password" required placeholder=" ">
                <label for="password">Password</label>
            </div>
            <button type="submit" class="login-button">Login</button>
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