<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "products";
$page_title   = "Edit Product";

$error = '';
$product = null;

/* ================= PRODUCT ID ================= */
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    header("Location: index.php");
    exit;
}

/* ================= ADD CATEGORY (NO AJAX) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {

    $new_category = trim($_POST['new_category'] ?? '');

    if ($new_category !== '') {
        $check = $pdo->prepare("SELECT category_id FROM categories WHERE name = ?");
        $check->execute([$new_category]);

        if (!$check->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$new_category]);
            $_SESSION['selected_category'] = $pdo->lastInsertId();
        }
    }

    header("Location: edit.php?id=" . $product_id);
    exit;
}

/* ================= FETCH CATEGORIES ================= */
$categories = $pdo->query(
    "SELECT category_id, name FROM categories ORDER BY name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

/* ================= FETCH PRODUCT ================= */
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit;
}

/* ================= UPDATE PRODUCT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {

    $name        = trim($_POST['name']);
    $price       = trim($_POST['price']);
    $category_id = $_POST['category_id'] ?? null;
    $discount    = $_POST['discount'] ?? 0;
    $stock       = $_POST['stock'] ?? 0;
    $status      = $_POST['status'] ?? 'active';
    $description = trim($_POST['description']);
    $image_path  = $product['image'];

    if ($name === '' || $price === '' || !$category_id) {
        $error = "Please fill all required fields.";
    } else {

        if (!empty($_FILES['image']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            $file = 'product_' . time() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file);

            if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                unlink(__DIR__ . '/../' . $image_path);
            }

            $image_path = 'admin/uploads/products/' . $file;
        }

        $stmt = $pdo->prepare("
            UPDATE products SET
                name = ?, description = ?, price = ?, discount = ?, stock = ?,
                status = ?, category_id = ?, image = ?
            WHERE product_id = ?
        ");

        $stmt->execute([
            $name, $description, $price, $discount,
            $stock, $status, $category_id, $image_path, $product_id
        ]);

        quickLog($pdo, 'update', 'product', $product_id, "Updated product: {$name}");
        unset($_SESSION['selected_category']);

        header("Location: index.php?updated=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="../assets/css/prody-admin.css">

<style>
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem}
@media(max-width:768px){.grid-3{grid-template-columns:1fr}}

.category-row{
    display:flex;
    align-items:center;
    gap:.5rem;
}
.category-plus{
    width:38px;height:38px;
    border:1px dashed var(--border-medium);
    background:none;
    border-radius:8px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.2s;
}
.category-plus:hover{
    background:var(--bg-soft);
    color:var(--blue);
    border-color:var(--blue);
}
.category-add-box{display:none;margin-top:.75rem}
.category-add-form{display:flex;gap:.5rem}
.image-preview{
    width:80px;height:80px;
    object-fit:cover;
    border-radius:6px;
    margin-bottom:.5rem;
}
</style>
</head>

<body>
<div class="container">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<div class="header">
    <div class="breadcrumb">
        <i class='bx bx-home'></i> Products / Edit
    </div>
    <div class="header-actions">
        <a href="index.php" class="header-btn">
            <i class='bx bx-arrow-back'></i> Back
        </a>
    </div>
</div>

<div class="content-area">

<?php if ($error): ?>
<div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="table-card">
<div class="table-header">
    <div class="table-title">
        Editing: <?= htmlspecialchars($product['name']) ?>
    </div>
</div>

<div style="padding:2rem">
<form method="POST" enctype="multipart/form-data" class="form-modern">

<div class="grid-3">
    <div class="form-group">
        <label>Product Name *</label>
        <input type="text" name="name" class="form-input"
               value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>

    <!-- CATEGORY WITH + ICON -->
    <div class="form-group">
        <label>Category *</label>

        <div class="category-row">
            <select name="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['category_id'] ?>"
                        <?= (
                            ($_SESSION['selected_category'] ?? $product['category_id'])
                            == $cat['category_id']
                        ) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="button" class="category-plus" onclick="toggleCategoryAdd()">
                <i class='bx bx-plus'></i>
            </button>
        </div>

        <div id="addCategoryInline" class="category-add-box">
            <form method="POST" class="category-add-form">
                <input type="text" name="new_category" class="form-input"
                       placeholder="New category name" required>
                <button type="submit" name="add_category" class="table-btn">Add</button>
            </form>
        </div>
    </div>

    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-select">
            <option value="active"   <?= $product['status']=='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= $product['status']=='inactive'?'selected':'' ?>>Inactive</option>
        </select>
    </div>
</div>

<div class="grid-3">
    <div class="form-group">
        <label>Price (₹) *</label>
        <input type="number" step="0.01" name="price" class="form-input"
               value="<?= $product['price'] ?>" required>
    </div>

    <div class="form-group">
        <label>Discount (%)</label>
        <input type="number" step="0.01" name="discount" class="form-input"
               value="<?= $product['discount'] ?>">
    </div>

    <div class="form-group">
        <label>Stock Quantity</label>
        <input type="number" name="stock" class="form-input"
               value="<?= $product['stock'] ?>">
    </div>
</div>

<div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-textarea" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
</div>

<div class="form-group">
    <label>Product Image</label>
    <?php if ($product['image']): ?>
        <img src="../../<?= htmlspecialchars($product['image']) ?>" class="image-preview">
    <?php endif; ?>
    <input type="file" name="image" class="form-input">
</div>

<div class="form-actions">
    <button type="submit" name="update_product" class="btn-action btn-edit">
        Update Product
    </button>
    <a href="index.php" class="table-btn" style="margin-left:1rem">Cancel</a>
</div>

</form>
</div>
</div>
</div>
</div>
</div>

<script>
function toggleCategoryAdd(){
    const box=document.getElementById('addCategoryInline');
    box.style.display = box.style.display==='block'?'none':'block';
}
</script>

</body>
</html>
