<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "products";
$page_title = "Products";

// Check for error message from redirect
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Handle edit action - if page=edit is requested, redirect to edit.php with proper query string
if (isset($_GET['page']) && $_GET['page'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    header("Location: edit.php?id=" . $edit_id);
    exit;
}

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    try {
        // Check if product exists and get image path
        $checkStmt = $pdo->prepare("SELECT product_id, image FROM products WHERE product_id = ?");
        $checkStmt->execute([$product_id]);
        $product_data = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product_data) {
            // Delete product
            $deleteStmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
            $deleteStmt->execute([$product_id]);
            
            // Delete associated image file if it exists
            if (!empty($product_data['image']) && file_exists(__DIR__ . '/../' . $product_data['image'])) {
                unlink(__DIR__ . '/../' . $product_data['image']);
            }
            
            $success_message = "Product deleted successfully!";
        } else {
            $error_message = "Product not found!";
        }
    } catch (PDOException $e) {
        $error_message = "Error deleting product: " . $e->getMessage();
    }
}

// Search functionality
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
try {
    if (!empty($search)) {
        $count_query = "SELECT COUNT(*) FROM products WHERE name LIKE :search OR description LIKE :search";
        $count_stmt = $pdo->prepare($count_query);
        $count_stmt->bindValue(':search', "%$search%");
    } else {
        $count_query = "SELECT COUNT(*) FROM products";
        $count_stmt = $pdo->prepare($count_query);
    }
    
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
    error_log("Products count error: " . $e->getMessage());
}

// Fetch products
try {
    // Check if image column exists
    try {
        $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'image'");
        $hasImageColumn = $checkCol->rowCount() > 0;
    } catch (Exception $e) {
        $hasImageColumn = false;
    }
    
    // Build the base query
    if (!empty($search)) {
        if ($hasImageColumn) {
            $query = "SELECT p.product_id, p.name, p.description, p.price, p.created_at, p.image
                      FROM products p 
                      WHERE p.name LIKE :search OR p.description LIKE :search
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT p.product_id, p.name, p.description, p.price, p.created_at, NULL as image
                      FROM products p 
                      WHERE p.name LIKE :search OR p.description LIKE :search
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        }
    } else {
        if ($hasImageColumn) {
            $query = "SELECT p.product_id, p.name, p.description, p.price, p.created_at, p.image
                      FROM products p 
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT p.product_id, p.name, p.description, p.price, p.created_at, NULL as image
                      FROM products p 
                      ORDER BY p.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        }
    }
    
    $stmt = $pdo->prepare($query);
    
    // Only bind search parameters if they exist
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
    }
    
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
    if (!isset($error_message)) {
        $error_message = "Error loading products: " . $e->getMessage();
    }
    error_log("Products fetch error: " . $e->getMessage());
}

// Get statistics
try {
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
} catch (PDOException $e) {
    $total_products = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- Google Font: Poppins -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/prody-admin.css">
	
	<!-- Favicon -->
	<link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="shortcut icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="apple-touch-icon" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="32x32" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="16x16" href="../../assets/images/logo/logo-bg.jpg">
	
    <title>Products - Liyas Admin</title>
</head>
<body>
	<div class="container">
		<?php include '../includes/sidebar.php'; ?>
		
		<div class="main-content">
			<div class="header">
				<div class="breadcrumb">
					<i class='bx bx-home'></i>
					<span>Products</span>
				</div>
				<div class="header-actions">
					<form action="index.php" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
						<input type="search" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border-light); border-radius: 6px; font-size: 14px; font-family: inherit;">
						<button type="submit" class="header-btn" style="padding: 0.5rem;">
							<i class='bx bx-search'></i>
						</button>
					</form>
				</div>
			</div>
			
			<div class="content-area">
				<?php if (isset($success_message)): ?>
					<div class="alert alert-success">
						<?= htmlspecialchars($success_message) ?>
					</div>
				<?php endif; ?>
				
				<?php if (isset($error_message)): ?>
					<div class="alert alert-error">
						<?= htmlspecialchars($error_message) ?>
					</div>
				<?php endif; ?>
				
				<div class="table-card">
					<div class="table-header">
						<div class="table-title">
							All Products
							<i class='bx bx-chevron-down'></i>
						</div>
						<div class="table-actions">
							<button class="table-btn">
								<i class='bx bx-filter'></i>
								<span>Filter</span>
							</button>
							<button class="table-btn">
								<i class='bx bx-search'></i>
								<span>Search</span>
							</button>
							<button class="table-btn">
								<i class='bx bx-download'></i>
								<span>Export</span>
							</button>
							<a href="add.php" class="btn-action btn-add noselect">
								<span class="text">Add</span>
								<span class="icon">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
								</span>
							</a>
						</div>
					</div>
					
					<?php if (empty($products)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bx-shopping-bag' style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
							<p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 0.5rem;">No products found</p>
							<p style="color: var(--text-muted); font-size: 0.9rem;">Get started by <a href="add.php" style="color: var(--blue);">adding your first product</a></p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
									<th>ID</th>
									<th>Product</th>
									<th>Description</th>
									<th>Price</th>
									<th>Created</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($products as $product): ?>
									<tr>
										<td><?= str_pad($product['product_id'], 2, '0', STR_PAD_LEFT) ?></td>
										<td>
											<?php if (!empty($product['image'])): ?>
												<img src="../../<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="table-img">
											<?php endif; ?>
											<strong><?= htmlspecialchars($product['name']) ?></strong>
										</td>
										<td>
											<span style="color: var(--text-secondary); max-width: 300px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
												<?= htmlspecialchars($product['description'] ?? 'No description') ?>
											</span>
										</td>
										<td><strong>₹<?= number_format($product['price'], 2) ?></strong></td>
										<td><?= date('d-m-Y', strtotime($product['created_at'])) ?></td>
										<td>
											<a href="edit.php?id=<?= $product['product_id'] ?>" class="btn-action btn-edit noselect">
												<span class="text">Edit</span>
												<span class="icon">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
												</span>
											</a>
											<a href="javascript:void(0);" onclick="confirmDelete(<?= $product['product_id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>')" class="btn-action btn-delete noselect">
												<span class="text">Delete</span>
												<span class="icon">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg>
												</span>
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						
						<!-- Pagination -->
						<?php if ($total_pages > 1): ?>
							<div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-light); display: flex; justify-content: center; gap: 0.75rem; align-items: center;">
								<?php if ($page > 1): ?>
									<a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="table-btn">
										<i class='bx bx-chevron-left'></i> Previous
									</a>
								<?php endif; ?>
								
								<span style="padding: 0.5rem 1rem; color: var(--text-secondary);">
									Page <?= $page ?> of <?= $total_pages ?>
								</span>
								
								<?php if ($page < $total_pages): ?>
									<a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="table-btn">
										Next <i class='bx bx-chevron-right'></i>
									</a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	
	<?php include '../includes/delete_confirm_modal.php'; ?>
	
	<script src="../assets/js/delete-confirm.js"></script>
	<script>
	// Override confirmDelete for products
	function confirmDelete(productId, productName) {
		window.confirmDelete(productId, productName, function(id) {
			window.location.href = `index.php?delete=${id}`;
		});
	}
	</script>
</body>
</html>

