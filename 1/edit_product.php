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

$product_id = $_GET['id'];
$product = get_product_by_id($conn, $product_id);

if (!$product) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    // Handle image upload
    $image_path = $product['image']; // Keep existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_dir = "../uploads/products/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $unique_filename = uniqid() . '.' . $file_extension;
        $target_file = $upload_dir . $unique_filename;
        
        // Check if image file is a valid image
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Delete old image if it exists
                if (!empty($product['image']) && file_exists("../" . $product['image'])) {
                    unlink("../" . $product['image']);
                }
                $image_path = 'uploads/products/' . $unique_filename;
            }
        }
    }

    // Update product with new image path
    $stmt = $conn->prepare("
        UPDATE products 
        SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?
        WHERE id = ?
    ");
    $result = $stmt->execute([$name, $description, $price, $stock, $category_id, $image_path, $product_id]);

    if ($result) {
        $_SESSION['success_message'] = "Product updated successfully.";
        header('Location: dashboard.php');
        exit();
    } else {
        $error_message = "Failed to update the product. Please try again.";
    }
}

$categories = get_categories($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Vendor Dashboard</title>
    <link rel="stylesheet" href="../css/vendor.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/product-form.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <a href="#" class="nav__logo">
                    <img src="assets/icons/logo.svg" alt="" class="nav__logo-icon">
                    <span class="nav__logo-text">       </span>
                </a>

                <div class="nav__toggle" id="nav-toggle">
                    <i class='bx bx-chevron-right'></i>
                </div>

                <ul class="nav__list">
                    <a href="dashboard.php" class="nav__link">
                        <i class='bx bx-grid-alt nav__icon'></i>
                        <span class="nav__text">Dashboard</span>
                    </a>
                    <a href="#" class="nav__link active">
                        <i class='bx bx-package nav__icon'></i>
                        <span class="nav__text">Edit </span>
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
        <div class="product-form-container">
            <h1>Edit Product</h1>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
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
                            <option value="<?php echo $category['id']; ?>" <?php echo ($category['id'] == $product['category_id']) ? 'selected' : ''; ?>>
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
                            <p>Current image</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Update Product</button>
                    <a href="dashboard.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>

    <script src="../js/nav.js"></script>
</body>
</html>