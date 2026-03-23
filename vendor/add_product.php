<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../login.php');
    exit();
}

$vendor_id = $_SESSION['user_id'];
$categories = get_categories($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $error_message = "Only JPG, PNG, GIF, and WEBP images are allowed.";
        } else {
            $target_dir = "../uploads/";
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/" . $new_filename;
            }
        }
    }
    
    $stmt = $conn->prepare("
        INSERT INTO products (name, description, price, stock, category_id, image, vendor_id, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    
    if ($stmt->execute([$name, $description, $price, $stock, $category_id, $image_path, $vendor_id])) {
        $_SESSION['success_message'] = "Product added successfully and sent for approval.";
        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = "Failed to add product. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Vendor Dashboard</title>
    <link rel="stylesheet" href="../css/product-form.css">
</head>
<body>
    <div class="product-form-container">
        <h1>Add New Product</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="add_product.php" method="post" enctype="multipart/form-data" class="product-form">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" required class="file-input">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Add Product</button>
                <a href="dashboard.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>