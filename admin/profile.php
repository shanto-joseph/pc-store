<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin = get_user_by_id($conn, $admin_id);

$total_sales   = get_total_sales($conn);
$total_orders  = get_total_orders($conn);

$stmt = $conn->query("SELECT COUNT(*) as c FROM products WHERE deleted = 0");
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$stmt = $conn->query("SELECT COUNT(*) as c FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM support_tickets WHERE status = 'pending'");
$stmt->execute();
$pending_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['c'];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name  = trim($_POST['name']);
        $email = trim($_POST['email']);
        if (empty($name) || empty($email)) {
            $error = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            $chk = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $chk->execute([$email, $admin_id]);
            if ($chk->fetch()) {
                $error = 'That email is already in use.';
            } else {
                update_user_profile($conn, $admin_id, $name, $email);
                $_SESSION['success_message'] = 'Profile updated.';
                header('Location: profile.php');
                exit();
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new     = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if (!password_verify($current, $admin['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $conn->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $admin_id]);
            $_SESSION['success_message'] = 'Password changed.';
            header('Location: profile.php');
            exit();
        }
    }
}

if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - PC STORE</title>
    <link rel="stylesheet" href="../css/admindash.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .profile-wrap { max-width: 700px; margin: 0 auto; }
        .profile-card {
            background: rgba(0,0,0,0.45);
            border-radius: 12px;
            padding: 28px;
            margin-bottom: 24px;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .profile-card h3 { color: #1fbb1f; margin-bottom: 20px; font-size: 1.05rem; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; color: rgba(255,255,255,0.7); font-size: 0.9rem; }
        .form-group input {
            width: 100%; padding: 10px 14px;
            background: rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 6px; color: #fff; font-size: 0.95rem;
        }
        .form-group input:focus { outline: none; border-color: #1fbb1f; }
        .stats-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 24px; }
        .mini-stat {
            background: rgba(0,0,0,0.45);
            border-radius: 10px; padding: 16px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .mini-stat .val { font-size: 1.5rem; font-weight: 700; color: #1fbb1f; }
        .mini-stat .lbl { font-size: 0.78rem; color: rgba(255,255,255,0.5); margin-top: 4px; }
        .btn-save {
            display: inline-block; padding: 10px 24px;
            background: #1fbb1f; color: #000;
            border: none; border-radius: 6px;
            font-weight: 600; cursor: pointer;
            transition: background 0.2s;
        }
        .btn-save:hover { background: #17a317; }
        .alert-success { background: rgba(31,187,31,0.12); border: 1px solid rgba(31,187,31,0.3); color: #1fbb1f; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
        .alert-error   { background: rgba(244,67,54,0.12);  border: 1px solid rgba(244,67,54,0.3);  color: #f44336; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; }
        @media(max-width:600px){ .stats-row{ grid-template-columns: repeat(2,1fr); } }
    </style>
</head>
<body id="body">

<div class="l-navbar" id="navbar">
    <nav class="nav">
        <div>
            <div class="nav__toggle" id="nav-toggle">
                <i class='bx bx-chevron-right'></i>
            </div>
            <ul class="nav__list">
                <a href="dashboard.php" class="nav__link">
                    <i class='bx bx-home nav__icon'></i>
                    <span class="nav__text">Dashboard</span>
                </a>
                <a href="manage_categories.php" class="nav__link">
                    <i class='bx bx-grid-alt nav__icon'></i>
                    <span class="nav__text">Categories</span>
                </a>
                <a href="dashboard.php" class="nav__link">
                    <i class='bx bx-package nav__icon'></i>
                    <span class="nav__text">Products</span>
                </a>
                <a href="order_manage.php" class="nav__link">
                    <i class='bx bxs-cart-alt nav__icon'></i>
                    <span class="nav__text">Orders</span>
                </a>
                <a href="support.php" class="nav__link">
                    <i class='bx bx-message-rounded nav__icon'></i>
                    <span class="nav__text">Support</span>
                </a>
                <a href="report.php" class="nav__link">
                    <i class='bx bx-bar-chart-alt-2 nav__icon'></i>
                    <span class="nav__text">Reports</span>
                </a>
                <a href="profile.php" class="nav__link active">
                    <i class='bx bx-user-circle nav__icon'></i>
                    <span class="nav__text">Profile</span>
                </a>
            </ul>
        </div>
        <a href="../logout.php" class="nav__link">
            <i class='bx bx-log-out-circle nav__icon'></i>
            <span class="nav__text">Logout</span>
        </a>
    </nav>
</div>

<main>
    <div class="profile-wrap">
        <h2>Admin Profile</h2>

        <?php if ($success): ?><div class="alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

        <!-- Quick Stats -->
        <div class="stats-row">
            <div class="mini-stat">
                <div class="val">₹<?php echo number_format($total_sales, 0); ?></div>
                <div class="lbl">Total Sales</div>
            </div>
            <div class="mini-stat">
                <div class="val"><?php echo $total_orders; ?></div>
                <div class="lbl">Orders</div>
            </div>
            <div class="mini-stat">
                <div class="val"><?php echo $total_products; ?></div>
                <div class="lbl">Products</div>
            </div>
            <div class="mini-stat">
                <div class="val"><?php echo $total_users; ?></div>
                <div class="lbl">Users</div>
            </div>
        </div>

        <!-- Edit Profile -->
        <div class="profile-card">
            <h3><i class='bx bx-edit'></i> Edit Profile</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn-save">Save Changes</button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="profile-card">
            <h3><i class='bx bx-lock-alt'></i> Change Password</h3>
            <form method="POST">
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn-save">Change Password</button>
            </form>
        </div>
    </div>
</main>

<script src="../js/admindash.js"></script>
</body>
</html>
