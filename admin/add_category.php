<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $result = $stmt->execute([$name]);
        
        if ($result) {
            $_SESSION['success_message'] = "Category created successfully.";
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = "Failed to create category. Please try again.";
        }
    } else {
        $error_message = "Category name cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/add_category.css">
</head>
<body>
    <header>
        <h1>Add New Category</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="add-category-form">
            <h2>Create New Category</h2>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            
            <form action="add_category.php" method="post">
                <label for="name">Category Name:</label>
                <input type="text" id="name" name="name" required>
                
                <button type="submit">Create Category</button>
            </form>
        </section>
    </main>

    <!-- <footer>
        <p>&copy; 2023 E-commerce Website. All rights reserved.</p>
    </footer> -->
</body>
</html>