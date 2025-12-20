<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "products";
$page_title   = "Add Product";

$error = '';

/* ================= STORE PRODUCT FORM TEMP ================= */
function storeProductDraft() {
    $_SESSION['product_draft'] = $_POST;
}

function getDraft($key, $default = '') {
    return $_SESSION['product_draft'][$key] ?? $default;
}

/* ================= ADD CATEGORY (SMOOTH) ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category_only'])) {

    storeProductDraft();
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

    header("Location: add.php");
    exit;
}

/* ================= FETCH CATEGORIES ================= */
$categories = $pdo->query(
    "SELECT category_id, name FROM categories ORDER BY name ASC"
)->fetchAll(PDO::FETCH_ASSOC);

/* ================= SAVE PRODUCT ================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {

    $name        = trim($_POST['name']);
    $price       = trim($_POST['price']);
    $category_id = $_POST['category_id'] ?? null;
    $discount    = $_POST['discount'] ?? 0;
    $stock       = $_POST['stock'] ?? 0;
    $status      = $_POST['status'] ?? 'active';
    $description = trim($_POST['description']);
    $image_path  = null;

    if ($name === '' || $price === '' || !$category_id) {
        $error = "Please fill all required fields.";
        storeProductDraft();
    } else {
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/products/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $file = 'product_' . time() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $file);
            $image_path = 'admin/uploads/products/' . $file;
        }

        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, discount, stock, status, category_id, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $discount, $stock, $status, $category_id, $image_path]);

        quickLog($pdo, 'create', 'product', $pdo->lastInsertId(), "Added product: {$name}");
        unset($_SESSION['product_draft'], $_SESSION['selected_category']);
        header("Location: index.php?added=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Product</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="../assets/css/prody-admin.css">

<style>
.grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem}
@media(max-width:768px){.grid-3{grid-template-columns:1fr}}

.category-row{display:flex;align-items:center;gap:.5rem}
.category-plus{
    width:42px;height:42px;
    border:1px dashed var(--border-medium);
    background:none;border-radius:8px;
    cursor:pointer; display:flex; align-items:center; justify-content:center;
}
.category-plus:hover{background:var(--bg-soft);color:var(--blue);border-color:var(--blue)}

/* Modern Inline Box */
.category-add-inline {
    display: none;
    margin-top: 0.75rem;
    padding: 1rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
}
.inline-flex { display: flex; gap: 0.5rem; }
</style>
</head>

<body>
<div class="container">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<div class="content-area">

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="table-card">
    <div class="table-header"><div class="table-title">Add New Product</div></div>
    <div style="padding:2rem">

    <form method="POST" id="mainProductForm" enctype="multipart/form-data" class="form-modern">
        <div class="grid-3">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" class="form-input" value="<?= getDraft('name') ?>" required>
            </div>

            <div class="form-group">
                <label>Category *</label>
                <div class="category-row">
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"
                                <?= (($_SESSION['selected_category'] ?? getDraft('category_id')) == $cat['category_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="category-plus" onclick="toggleCategoryAdd()">
                        <i class='bx bx-plus' id="plusIcon"></i>
                    </button>
                </div>

                <div id="categoryInlineBox" class="category-add-inline">
                    <label style="font-size: 0.75rem; color: #64748b; display:block; margin-bottom:5px;">New Category Name</label>
                    <div class="inline-flex">
                        <input type="text" id="temp_cat_name" class="form-input" placeholder="Enter name...">
                        <button type="button" onclick="submitQuickCategory()" class="btn-action btn-add" style="padding:0 15px; height:42px">Add</button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="active" <?= getDraft('status','active')=='active'?'selected':'' ?>>Active</option>
                    <option value="inactive" <?= getDraft('status')=='inactive'?'selected':'' ?>>Inactive</option>
                </select>
            </div>
        </div>

        <div class="grid-3">
            <div class="form-group">
                <label>Price (₹) *</label>
                <input type="number" name="price" class="form-input" value="<?= getDraft('price') ?>" required>
            </div>
            <div class="form-group">
                <label>Discount (%)</label>
                <input type="number" name="discount" class="form-input" value="<?= getDraft('discount',0) ?>">
            </div>
            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" class="form-input" value="<?= getDraft('stock',0) ?>">
            </div>
        </div>

        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-textarea" rows="4"><?= getDraft('description') ?></textarea>
        </div>

        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" class="form-input">
        </div>

        <div class="form-actions">
            <button type="submit" name="save_product" class="btn-action btn-add">Save Product</button>
        </div>
    </form>

    <form method="POST" id="hiddenCategoryForm" style="display:none;">
        <input type="hidden" name="add_category_only" value="1">
        <input type="hidden" name="new_category" id="real_cat_input">
        <?php foreach (['name', 'price', 'discount', 'stock', 'status', 'description'] as $f): ?>
            <input type="hidden" name="<?= $f ?>" id="draft_<?= $f ?>">
        <?php endforeach; ?>
    </form>

    </div>
    </div>
</div>
</div>
</div>

<script>
function toggleCategoryAdd(){
    const box = document.getElementById('categoryInlineBox');
    const icon = document.getElementById('plusIcon');
    const isHidden = box.style.display === 'none' || box.style.display === '';
    box.style.display = isHidden ? 'block' : 'none';
    icon.className = isHidden ? 'bx bx-x' : 'bx bx-plus';
}

function submitQuickCategory() {
    const catName = document.getElementById('temp_cat_name').value.trim();
    if(catName === "") {
        alert("Please enter a category name");
        return;
    }

    // Copy the category name to the hidden form
    document.getElementById('real_cat_input').value = catName;

    // Copy current product field values so you don't lose typed text
    const mainForm = document.getElementById('mainProductForm');
    document.getElementById('draft_name').value = mainForm.name.value;
    document.getElementById('draft_price').value = mainForm.price.value;
    document.getElementById('draft_discount').value = mainForm.discount.value;
    document.getElementById('draft_stock').value = mainForm.stock.value;
    document.getElementById('draft_status').value = mainForm.status.value;
    document.getElementById('draft_description').value = mainForm.description.value;

    // Submit the hidden form
    document.getElementById('hiddenCategoryForm').submit();
}
</script>

</body>
</html>