<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$product_id = $_GET['id'];
$vendor_id = $_SESSION['user_id'];

// Get product details and verify ownership
$stmt = $conn->prepare("
    SELECT * FROM products 
    WHERE id = ? AND vendor_id = ? AND deleted = 0
");
$stmt->execute([$product_id, $vendor_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header('Location: dashboard.php');
    exit();
}

$categories = get_categories($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    
    // Handle image upload
    $image_path = $product['image'];
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
        UPDATE products 
        SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?, status = 'pending'
        WHERE id = ? AND vendor_id = ?
    ");
    
    if ($stmt->execute([$name, $description, $price, $stock, $category_id, $image_path, $product_id, $vendor_id])) {
        $_SESSION['success_message'] = "Product updated successfully and sent for approval.";
        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = "Failed to update product. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Vendor Dashboard</title>
    <link rel="stylesheet" href="../css/product-form.css">
</head>
<body>
    <div class="product-form-container">
        <h1>Edit Product</h1>
        
        <?php if (isset($error_message)): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post" enctype="multipart/form-data" class="product-form">
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" value="<?php echo $product['stock']; ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" 
                            <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="image">Product Image</label>
                <input type="file" id="image" name="image" accept="image/*" class="file-input">
                <?php if (!empty($product['image'])): ?>
                    <div class="current-image">
                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Current product image">
                        <p>Current image will be kept if no new image is uploaded</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Update Product</button>
                <a href="dashboard.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>