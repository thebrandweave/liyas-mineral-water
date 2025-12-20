<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "categories";
$id = (int)($_GET['id'] ?? 0);
$error = '';

// Fetch Category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    
    if ($name) {
        try {
            $update = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
            $update->execute([$name, $desc, $id]);
            quickLog($pdo, 'update', 'category', $id, "Updated: $name");
            header("Location: index.php?updated=1");
            exit;
        } catch (PDOException $e) { $error = "Duplicate category name!"; }
    } else { $error = "Name is required."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <title>Edit Category</title>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb"><span>Categories</span> / <span>Edit</span></div>
                <div class="header-actions"><a href="index.php" class="header-btn"><i class='bx bx-arrow-back'></i> Back</a></div>
            </div>
            <div class="content-area">
                <?php if($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
                <div class="table-card">
                    <div class="table-header"><div class="table-title">Edit Category: <?= htmlspecialchars($category['name']) ?></div></div>
                    <form method="POST" class="form-modern" style="padding: 2rem;">
                        <div class="form-group">
                            <label>Category Name *</label>
                            <input type="text" name="name" class="form-input" value="<?= htmlspecialchars($category['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-textarea" rows="5"><?= htmlspecialchars($category['description']) ?></textarea>
                        </div>
                        <div class="form-actions" style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 2rem;">
                            <button type="submit" class="btn-action btn-edit noselect" style="width: auto; padding: 0.8rem 3rem;">
                                <span class="text">Save Changes</span>
                                <span class="icon"><i class='bx bx-check'></i></span>
                            </button>
                            <a href="index.php" class="table-btn" style="margin-left: 10px;">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>