<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Edit Product";

$error = '';
$product = null;

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header("Location: index.php");
    exit;
}

// Fetch categories
$categories = $pdo->query("SELECT category_id, name FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch product
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $error = $e->getMessage(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category_id = $_POST['category_id'] ?? null;
    $stock = trim($_POST['stock'] ?? 0);
    $discount = trim($_POST['discount'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    $image_path = $product['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/products/';
        $file_name = 'product_' . time() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file_name)) {
            if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) unlink(__DIR__ . '/../' . $image_path);
            $image_path = 'admin/uploads/products/' . $file_name;
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, discount = ?, stock = ?, status = ?, category_id = ?, image = ? WHERE product_id = ?");
        $stmt->execute([$name, $description, $price, $discount, $stock, $status, $category_id, $image_path, $product_id]);
        quickLog($pdo, 'update', 'product', $product_id, "Updated product: {$name}");
        header("Location: index.php?updated=1");
        exit;
    } catch (PDOException $e) { $error = $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <title>Edit Product - Liyas Admin</title>
    <style>
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; }
        .image-preview-area { display: flex; align-items: center; gap: 1rem; background: #f8fafc; padding: 1rem; border-radius: 8px; }
        @media (max-width: 768px) { .grid-3 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb"><i class='bx bx-home'></i> <span>Products</span> / <span>Edit</span></div>
                <div class="header-actions"><a href="index.php" class="header-btn"><i class='bx bx-arrow-back'></i> Back</a></div>
            </div>
            
            <div class="content-area">
                <div class="table-card">
                    <div class="table-header"><div class="table-title">Settings for: <?= htmlspecialchars($product['name'] ?? '') ?></div></div>
                    <div style="padding: 2rem;">
                        <form method="POST" enctype="multipart/form-data" class="form-modern">
                            <div class="grid-3">
                                <div class="form-group">
                                    <label>Product Name</label>
                                    <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($product['name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_id" class="form-select" required>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['category_id'] ?>" <?= ($product['category_id'] == $cat['category_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-select">
                                        <option value="active" <?= $product['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $product['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid-3">
                                <div class="form-group">
                                    <label>Price (₹)</label>
                                    <input type="number" name="price" step="0.01" class="form-input" value="<?= $product['price'] ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Discount (%)</label>
                                    <input type="number" name="discount" step="0.01" class="form-input" value="<?= $product['discount'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Stock Quantity</label>
                                    <input type="number" name="stock" class="form-input" value="<?= $product['stock'] ?>">
                                </div>
                            </div>

                            <div class="form-group"><label>Description</label>
                                <textarea name="description" class="form-textarea" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Image Management</label>
                                <div class="image-preview-area">
                                    <?php if ($product['image']): ?>
                                        <img src="../../<?= htmlspecialchars($product['image']) ?>" style="width:80px; height:80px; object-fit:cover; border-radius:4px;">
                                    <?php endif; ?>
                                    <input type="file" name="image" class="form-input">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="update" class="btn-action btn-edit noselect">
                                    <span class="text">Update Product</span>
                                    <span class="icon"><i class='bx bx-save'></i></span>
                                </button>
                                <a href="index.php" class="table-btn" style="margin-left:1rem;">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>