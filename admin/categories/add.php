<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "categories";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    
    if ($name) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $desc]);
            quickLog($pdo, 'create', 'category', $pdo->lastInsertId(), "Created: $name");
            header("Location: index.php?added=1");
            exit;
        } catch (PDOException $e) { $error = "Category name already exists!"; }
    } else { $error = "Name is required."; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <title>Add Category</title>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb"><span>Categories</span> / <span>Add New</span></div>
                <div class="header-actions"><a href="index.php" class="header-btn"><i class='bx bx-arrow-back'></i> Back</a></div>
            </div>
            <div class="content-area">
                <div class="table-card">
                    <div class="table-header"><div class="table-title">New Category</div></div>
                    <form method="POST" class="form-modern" style="padding: 2rem;">
                        <div class="form-group">
                            <label>Category Name *</label>
                            <input type="text" name="name" class="form-input" placeholder="e.g. Sparkling Water" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-textarea" rows="5" placeholder="Details about this category..."></textarea>
                        </div>
                        <div class="form-actions" style="margin-top: 2rem; border-top: 1px solid #eee; padding-top: 2rem;">
                            <button type="submit" class="btn-action btn-add noselect" style="width: auto; padding: 0.8rem 3rem;">
                                <span class="text">Create Category</span>
                                <span class="icon"><i class='bx bx-plus'></i></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>