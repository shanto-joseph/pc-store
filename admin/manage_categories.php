<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Get all categories
$categories = get_categories($conn);

// Get product count for each category
$category_stats = [];
foreach ($categories as $category) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as product_count 
        FROM products 
        WHERE category_id = ? AND deleted = 0 AND status = 'approved'
    ");
    $stmt->execute([$category['id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $category_stats[$category['id']] = $result['product_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Dashboard</title>
    <link rel="stylesheet" href="../css/manage_categories.css">
    <style>
        html, body {
    overflow: auto;
    
}

        h1 {
           position: relative;
            margin-top: 0px;
            left:7%;
        }
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .category-card {
            background: #1c1c1ca1;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .category-stats {
            color: white;
            font-size: 0.9em;
            margin: 10px 0;
        }
        .category-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .add-category-btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .add-category-btn:hover {
            background: #45a049;
        }
        .edit-btn {
            background: #2196F3;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .delete-btn {
            background: #f44336;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .edit-btn:hover {
            background: #1976D2;
        }
        .delete-btn:hover {
            background: #d32f2f;
        }
        .disabled-btn {
            background: #ccc;
            cursor: not-allowed;

            
        }
    </style>
</head>
<body>
    <header>
        <br>
        <h1>Manage Categories</h1>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section id="manage-categories">
            <div class="section-header">
                <h2>Categories</h2>
                <a href="add_category.php" class="add-category-btn">Add New Category</a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <p class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            <?php endif; ?>

            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-header">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        </div>
                        <div class="category-stats">
                            <p>Products: <?php echo $category_stats[$category['id']]; ?></p>
                            <p>Created: <?php echo date('F j, Y', strtotime($category['created_at'])); ?></p>
                        </div>
                        <div class="category-actions">
                            <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="edit-btn">Edit</a>
                            <form action="delete_category.php" method="post" style="display: inline;">
                                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                                <?php if ($category_stats[$category['id']] > 0): ?>
                                    <button type="button" class="delete-btn disabled-btn" 
                                            title="Cannot delete category with existing products" disabled>
                                        Delete
                                    </button>
                                <?php else: ?>
                                    <button type="submit" class="delete-btn" 
                                            onclick="return confirm('Are you sure you want to delete this category?')">
                                        Delete
                                    </button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

     <footer>
        <p>&copy; 2026 PC STORE. All rights reserved.</p>
    </footer> 
</body>
</html>