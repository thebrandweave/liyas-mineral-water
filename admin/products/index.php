<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Products";

// --- MESSAGES ---
if (isset($_SESSION['error_message'])) { $error_message = $_SESSION['error_message']; unset($_SESSION['error_message']); }
if (isset($_GET['added']) && $_GET['added'] == '1') { $success_message = "Product added successfully!"; }
if (isset($_GET['updated']) && $_GET['updated'] == '1') { $success_message = "Product updated successfully!"; }

// --- DELETE LOGIC ---
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    try {
        $checkStmt = $pdo->prepare("SELECT name, image FROM products WHERE product_id = ?");
        $checkStmt->execute([$product_id]);
        $product_data = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product_data) {
            if (!empty($product_data['image'])) {
                $full_path = __DIR__ . '/../uploads/products/' . $product_data['image'];
                if (file_exists($full_path)) unlink($full_path);
            }
            $pdo->prepare("DELETE FROM products WHERE product_id = ?")->execute([$product_id]);
            quickLog($pdo, 'delete', 'product', $product_id, "Deleted product: {$product_data['name']}");
            $success_message = "Product deleted successfully!";
        }
    } catch (PDOException $e) { $error_message = "Error: " . $e->getMessage(); }
}

// --- SEARCH & PAGINATION (Bulletproof) ---
$search = $_GET['search'] ?? '';
$searchTerm = "%$search%";
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE name LIKE ? OR description LIKE ?");
$count_stmt->execute([$searchTerm, $searchTerm]);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $per_page);

$stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
$stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
$stmt->bindValue(3, (int)$per_page, PDO::PARAM_INT);
$stmt->bindValue(4, (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <title>Products - Liyas Admin</title>
    <style>
        /* This block ensures the fonts and sizes MATCH Categories exactly */
        body { font-family: 'Poppins', sans-serif; }
        .table-card table tbody td { font-size: 14px; color: #334155; }
        .text-muted-custom { font-size: 13px; color: #94a3b8; }
        .price-bold { color: #3b82f6; font-weight: 600; font-size: 15px; }
        .img-thumb-fixed { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb"><i class='bx bx-home'></i> <span>Products</span></div>
                <div class="header-actions">
                    <form action="index.php" method="GET" style="display: flex;text-align:center; gap: 0.5rem;">
                        <input type="search" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" class="form-input" style="width: 200px;">
                        <button type="submit" class="header-btn" style="padding: 0.5rem;"><i class='bx bx-search'></i></button>
                    </form>
                </div>
            </div>
            
            <div class="content-area">
                <?php if (isset($success_message)): ?><div class="alert alert-success"><?= $success_message ?></div><?php endif; ?>
                <?php if (isset($error_message)): ?><div class="alert alert-error"><?= $error_message ?></div><?php endif; ?>

                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">All Products</div>
                        <div class="table-actions">
                            <a href="add.php" class="btn-action btn-add noselect">
                                <span class="text">Add</span>
                                <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg></span>
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <?php 
                                            $img_path = "../uploads/products/" . $product['image'];
                                            if (!empty($product['image']) && file_exists(__DIR__ . "/../uploads/products/" . $product['image'])): ?>
                                                <img src="<?= $img_path ?>" class="img-thumb-fixed">
                                            <?php else: ?>
                                                <div class="img-thumb-fixed" style="background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #cbd5e1;"><i class='bx bx-image'></i></div>
                                            <?php endif; ?>
                                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        </div>
                                    </td>
                                    <td><span class="text-muted-custom"><?= htmlspecialchars(substr($product['description'] ?? 'No description', 0, 40)) ?>...</span></td>
                                    <td><strong class="price-bold">₹<?= number_format($product['price'], 2) ?></strong></td>
                                    <td><?= date('d-m-Y', strtotime($product['created_at'])) ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $product['product_id'] ?>" class="btn-action btn-edit noselect">
                                            <span class="text">Edit</span>
                                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></span>
                                        </a>
                                        <a href="javascript:void(0);" onclick="confirmDelete(<?= $product['product_id'] ?>, '<?= addslashes($product['name']) ?>')" class="btn-action btn-delete noselect">
                                            <span class="text">Delete</span>
                                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg></span>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/delete_confirm_modal.php'; ?>
    <script src="../assets/js/delete-confirm.js"></script>
    <script>
    function confirmDelete(id, name) {
        if (typeof window.showDeleteConfirm === 'function') {
            window.showDeleteConfirm(id, name, (id) => window.location.href = `index.php?delete=${id}`);
        } else if (confirm(`Delete "${name}"?`)) {
            window.location.href = `index.php?delete=${id}`;
        }
    }
    </script>
</body>
</html>