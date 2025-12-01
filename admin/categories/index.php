<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "categories";
$page_title = "Categories";

// Check for error message from redirect
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    try {
        // Check if category exists
        $checkStmt = $pdo->prepare("SELECT category_id FROM categories WHERE category_id = ?");
        $checkStmt->execute([$category_id]);
        $category_data = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($category_data) {
            // Check if category is used by any products (if category_id column exists in products table)
            $product_count = 0;
            try {
                $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'category_id'");
                if ($checkCol->rowCount() > 0) {
                    $productsStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                    $productsStmt->execute([$category_id]);
                    $product_count = $productsStmt->fetchColumn();
                }
            } catch (PDOException $e) {
                // Column doesn't exist, so no products use categories
                $product_count = 0;
            }
            
            if ($product_count > 0) {
                $error_message = "Cannot delete category. It is being used by $product_count product(s).";
            } else {
                // Delete category
                $deleteStmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
                $deleteStmt->execute([$category_id]);
                $success_message = "Category deleted successfully!";
            }
        } else {
            $error_message = "Category not found!";
        }
    } catch (PDOException $e) {
        $error_message = "Error deleting category: " . $e->getMessage();
    }
}

// Handle add/edit form submission
$form_error = '';
$form_success = '';
$editing_category = null;
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

if ($edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
        $stmt->execute([$edit_id]);
        $editing_category = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $form_error = "Error loading category: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    // Validation
    if (empty($name)) {
        $form_error = "Category name is required.";
    } else {
        try {
            if ($category_id > 0) {
                // Update existing category
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
                $stmt->execute([$name, $description ?: null, $category_id]);
                $form_success = "Category updated successfully!";
                $editing_category = null;
                $edit_id = 0;
            } else {
                // Insert new category
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description ?: null]);
                $form_success = "Category added successfully!";
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $form_error = "A category with this name already exists.";
            } else {
                $form_error = "Error saving category: " . $e->getMessage();
            }
        }
    }
}

// Determine if category modal should be visible on load
$show_modal = ($editing_category || $form_error || $form_success);

// Search functionality
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
try {
    if (!empty($search)) {
        $count_query = "SELECT COUNT(*) FROM categories WHERE name LIKE :search OR description LIKE :search";
        $count_stmt = $pdo->prepare($count_query);
        $count_stmt->bindValue(':search', "%$search%");
    } else {
        $count_query = "SELECT COUNT(*) FROM categories";
        $count_stmt = $pdo->prepare($count_query);
    }
    
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
    error_log("Categories count error: " . $e->getMessage());
}

// Fetch categories
try {
    // Check if category_id column exists in products table
    $hasCategoryColumn = false;
    try {
        $checkCol = $pdo->query("SHOW COLUMNS FROM products LIKE 'category_id'");
        $hasCategoryColumn = $checkCol->rowCount() > 0;
    } catch (PDOException $e) {
        $hasCategoryColumn = false;
    }
    
    if (!empty($search)) {
        if ($hasCategoryColumn) {
            $query = "SELECT c.category_id, c.name, c.description, c.created_at,
                             (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count
                      FROM categories c 
                      WHERE c.name LIKE :search OR c.description LIKE :search
                      ORDER BY c.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT c.category_id, c.name, c.description, c.created_at, 0 as product_count
                      FROM categories c 
                      WHERE c.name LIKE :search OR c.description LIKE :search
                      ORDER BY c.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        }
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search', "%$search%");
    } else {
        if ($hasCategoryColumn) {
            $query = "SELECT c.category_id, c.name, c.description, c.created_at,
                             (SELECT COUNT(*) FROM products WHERE category_id = c.category_id) as product_count
                      FROM categories c 
                      ORDER BY c.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT c.category_id, c.name, c.description, c.created_at, 0 as product_count
                      FROM categories c 
                      ORDER BY c.created_at DESC 
                      LIMIT :limit OFFSET :offset";
        }
        $stmt = $pdo->prepare($query);
    }
    
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
    error_log("Categories fetch error: " . $e->getMessage());
}

// Get statistics
try {
    $total_categories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
} catch (PDOException $e) {
    $total_categories = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<!-- Favicon -->
	<link rel="icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="shortcut icon" type="image/jpeg" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="apple-touch-icon" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="32x32" href="../../assets/images/logo/logo-bg.jpg">
	<link rel="icon" type="image/jpeg" sizes="16x16" href="../../assets/images/logo/logo-bg.jpg">
	
	<link rel="preload" href="https://cal.com/fonts/CalSans-SemiBold.woff2" as="font" type="font/woff2" crossorigin>
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/prody-admin.css">
	<title>Categories - Liyas Admin</title>

	<style>
		/* Modal overlay for Add/Edit Category */
		.modal-overlay {
			position: fixed;
			inset: 0;
			background: rgba(15, 23, 42, 0.35);
			backdrop-filter: blur(4px);
			display: none;
			align-items: center;
			justify-content: center;
			z-index: 999;
			padding: 1.5rem;
		}

		.modal-card {
			max-width: 720px;
			width: 100%;
		}

		@media (max-width: 768px) {
			.modal-overlay {
				align-items: flex-start;
				padding-top: 4rem;
			}
		}
	</style>
</head>
<body>
	<div class="container">
		<?php include '../includes/sidebar.php'; ?>
		
		<div class="main-content">
			<div class="header">
				<div class="breadcrumb">
					<i class='bx bx-home'></i>
					<span>Categories</span>
				</div>
				<div class="header-actions">
					<form action="index.php" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
						<input type="search" name="search" placeholder="Search categories..." value="<?= htmlspecialchars($search) ?>" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border-light); border-radius: 6px; font-size: 14px; font-family: inherit;">
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

				<!-- Add/Edit Category Modal -->
				<div 
					id="categoryModal" 
					class="modal-overlay" 
					style="<?= $show_modal ? 'display:flex;' : 'display:none;' ?>"
				>
					<div class="form-card modal-card" id="categoryForm">
						<div class="form-header" style="display:flex;justify-content:space-between;align-items:center;">
							<h2><?= $editing_category ? 'Edit Category' : 'Add New Category' ?></h2>
							<button type="button" class="btn btn-secondary" style="padding:0.25rem 0.75rem;font-size:12px;" onclick="closeCategoryModal()">
								<i class='bx bx-x'></i>
							</button>
						</div>

						<?php if ($form_error): ?>
							<div class="alert alert-error">
								<?= htmlspecialchars($form_error) ?>
							</div>
						<?php endif; ?>

						<?php if ($form_success): ?>
							<div class="alert alert-success">
								<?= htmlspecialchars($form_success) ?>
							</div>
						<?php endif; ?>

						<form method="POST" action="" class="form-modern">
							<?php if ($editing_category): ?>
								<input type="hidden" name="category_id" value="<?= $editing_category['category_id'] ?>">
							<?php endif; ?>
							
							<div class="form-group">
								<label for="name">Category Name <span style="color: var(--red);">*</span></label>
								<input 
									type="text" 
									name="name" 
									id="name" 
									class="form-input"
									value="<?= htmlspecialchars($editing_category['name'] ?? '') ?>" 
									required
									placeholder="e.g., Mineral Water, Sparkling Water"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Enter a unique category name</small>
							</div>

							<div class="form-group">
								<label for="description">Description</label>
								<textarea 
									name="description" 
									id="description" 
									class="form-textarea"
									placeholder="Describe this category..."
								><?= htmlspecialchars($editing_category['description'] ?? '') ?></textarea>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Provide details about the category (optional)</small>
							</div>

							<div class="form-actions">
								<button type="submit" name="save_category" class="btn btn-primary">
									<i class='bx bx-save'></i> <?= $editing_category ? 'Update Category' : 'Add Category' ?>
								</button>
								<button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">
									<i class='bx bx-x'></i> Cancel
								</button>
							</div>
						</form>
					</div>
				</div>

				<!-- Categories Table -->
				<div class="table-card">
					<div class="table-header">
						<div class="table-title">
							All Categories
							<i class='bx bx-chevron-down'></i>
						</div>
						<div class="table-actions">
							<?php if (!$editing_category): ?>
							<a href="javascript:void(0);" onclick="openCategoryModal();" class="table-btn btn-primary">
								<i class='bx bx-plus'></i>
								<span>Add New</span>
							</a>
							<?php endif; ?>
						</div>
					</div>
					
					<?php if (empty($categories)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bx-category' style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
							<p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 0.5rem;">No categories found</p>
							<p style="color: var(--text-muted); font-size: 0.9rem;">Get started by adding your first category</p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
									<th>ID</th>
									<th>Category Name</th>
									<th>Description</th>
									<th>Products</th>
									<th>Created</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($categories as $category): ?>
								<tr>
									<td><?= str_pad($category['category_id'], 2, '0', STR_PAD_LEFT) ?></td>
									<td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
									<td>
										<span style="color: var(--text-secondary); max-width: 300px; display: inline-block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
											<?= htmlspecialchars($category['description'] ?? 'No description') ?>
										</span>
									</td>
									<td><strong style="color: var(--blue);"><?= number_format($category['product_count']) ?></strong></td>
									<td><?= date('d-m-Y', strtotime($category['created_at'])) ?></td>
									<td>
										<a href="?edit=<?= $category['category_id'] ?>" class="btn-action btn-edit">
											<i class='bx bx-edit'></i> Edit
										</a>
										<a href="javascript:void(0);" onclick="confirmDelete(<?= $category['category_id'] ?>, '<?= htmlspecialchars(addslashes($category['name'])) ?>', <?= (int)$category['product_count'] ?>)" class="btn-action btn-delete">
											<i class='bx bx-trash'></i> Delete
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
	
	<script>
	function confirmDelete(categoryId, categoryName, productCount) {
		if (productCount > 0) {
			alert(`Cannot delete "${categoryName}"!\n\nThis category is being used by ${productCount} product(s).\n\nPlease remove or reassign products before deleting this category.`);
			return false;
		}
		
		if (confirm(`Are you sure you want to delete "${categoryName}"?\n\nThis action cannot be undone!`)) {
			window.location.href = `index.php?delete=${categoryId}`;
		}
	}

	function openCategoryModal() {
		const modal = document.getElementById('categoryModal');
		if (modal) {
			modal.style.display = 'flex';
			// Focus first input after a short delay
			setTimeout(() => {
				const nameInput = document.getElementById('name');
				if (nameInput) nameInput.focus();
			}, 100);
		}
	}

	function closeCategoryModal() {
		const modal = document.getElementById('categoryModal');
		if (modal) {
			modal.style.display = 'none';
			// If we were editing, going back to list view
			if (window.location.search.includes('edit=')) {
				window.location.href = 'index.php';
			}
		}
	}
	</script>
</body>
</html>

