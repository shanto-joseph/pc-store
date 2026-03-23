<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$category_id = $_GET['id'];

// Get category details
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$category_id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $result = $stmt->execute([$name, $category_id]);
        
        if ($result) {
            $_SESSION['success_message'] = "Category updated successfully.";
            header('Location: dashboard.php');
            exit();
        } else {
            $error_message = "Failed to update category. Please try again.";
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
    <title>Edit Category - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/edit_category.css">

</head>
<body>
    <header>
        <h1>Edit Category</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="edit-category-form">
            <h2>Edit Category</h2>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            
            <form action="edit_category.php?id=<?php echo $category_id; ?>" method="post">
                <label for="name">Category Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                
                <button type="submit">Update Category</button>
            </form>
        </section>
    </main>

    <footer>
        <p>&copy; 2026 PC STORE. All rights reserved.</p>
    </footer>
</body>
</html>