
<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');

// Determine current action (view, add, edit)
$action = $_GET['action'] ?? 'view';
$category_id = $_GET['id'] ?? null;

$category = null;
if ($action === 'edit' && $category_id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$category) {
        // Category not found, redirect to view
        header("Location: categories.php");
        exit;
    }
}

// Fetch all categories for the view table
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error if the table doesn't exist, though it should based on schema
    $page_error = "Error fetching categories: " . $e->getMessage();
}

$current_page = "categories";
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="assets/css/admin-style.css">
	<title>Manage Categories - Admin Panel</title>
</head>
<body>
	<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu bx-sm'></i>
			<a href="#" class="nav-link"><?= $page_title ?></a>
			<form action="#">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification"><i class='bx bxs-bell bx-tada-hover'></i><span class="num">8</span></a>
			<a href="#" class="profile"><img src="https://i.pravatar.cc/36?u=<?= urlencode($admin_name) ?>"></a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Manage Categories</h1>
					<ul class="breadcrumb">
						<li><a href="index.php">Dashboard</a></li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li><a class="active" href="categories.php">Categories</a></li>
					</ul>
				</div>
			</div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success" style="margin-bottom: 1rem;"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <!-- Form for Adding/Editing -->
            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3><?= ($action === 'edit') ? 'Edit Category' : 'Add New Category' ?></h3>
                    </div>
                    <form action="category_handler.php" method="POST" class="form-modern">
                        <input type="hidden" name="action" value="<?= ($action === 'edit') ? 'update' : 'create' ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="category_id" value="<?= htmlspecialchars($category['category_id']) ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="name">Category Name</label>
                            <input type="text" name="name" id="name" value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group form-group-buttons">
                            <button type="submit" class="button action-btn <?= ($action === 'edit') ? 'edit' : 'add' ?>" title="<?= ($action === 'edit') ? 'Update Category' : 'Add Category' ?>">
                                <svg class="svgIcon" viewBox="0 0 448 512"><path d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"></path></svg>
                            </button>
                            <?php if ($action === 'edit'): ?>
                                <a href="categories.php" class="button cancel-btn" title="Cancel">
                                    <svg class="svgIcon" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"></path></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

			<!-- Category List -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>All Categories</h3>
						<i class='bx bx-search'></i>
						<i class='bx bx-filter'></i>
					</div>
					<table>
						<thead>
							<tr>
								<th>Name</th>
								<th>Description</th>
								<th>Actions</th>
							</tr>
						</thead>
						<tbody>
                            <?php if (empty($categories)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px;">No categories found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($categories as $cat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cat['name']) ?></td>
                                        <td><?= htmlspecialchars(substr($cat['description'], 0, 50)) . (strlen($cat['description']) > 50 ? '...' : '') ?></td>
                                        <td class="actions">
                                            <a href="categories.php?action=edit&id=<?= $cat['category_id'] ?>" class="btn-action btn-edit"><i class='bx bxs-edit'></i> Edit</a>
                                            <form action="category_handler.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                <button type="submit" class="btn-action btn-delete"><i class='bx bxs-trash'></i> Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<script src="assets/js/admin-script.js"></script>
</body>
</html>
<style>
    /* Modern Form Styles */
    .form-modern { display: flex; flex-direction: column; gap: 1rem; }
    .form-modern .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
    .form-modern label { margin-bottom: 0.5rem; font-weight: 600; font-size: 14px; color: var(--dark-grey); }
    .form-modern input, .form-modern textarea {
        padding: 0.75rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--grey);
        background-color: var(--grey);
        font-size: 1rem;
        color: var(--dark);
        transition: all 0.3s ease;
    }
    .form-group-buttons { flex-direction: row !important; gap: 1rem; margin-top: 1rem; }

    /* Actions column styles */
    .actions { display: flex; gap: 0.5rem; align-items: center; }
    .btn-action {
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        text-decoration: none;
    }
    .btn-edit { background-color: var(--blue); }
    .btn-delete { background-color: var(--red); }

    /* Animated Button Styles */
    .button {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: rgb(20, 20, 20);
        border: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.164);
        cursor: pointer;
        transition-duration: .3s;
        overflow: hidden;
        position: relative;
        text-decoration: none !important;
    }
    .svgIcon { width: 17px; transition-duration: .3s; }
    .svgIcon path { fill: white; }
    .button:hover {
        width: 160px; /* Adjusted for longer text */
        border-radius: 50px;
        transition-duration: .3s;
        align-items: center;
    }
    .button:hover .svgIcon {
        width: 20px;
        transition-duration: .3s;
        transform: translateY(60%);
    }
    .button::before {
        position: absolute;
        top: -20px;
        color: white;
        transition-duration: .3s;
        font-size: 2px;
    }
    .button:hover::before {
        font-size: 13px;
        opacity: 1;
        transform: translateY(30px);
        transition-duration: .3s;
    }

    /* Button Variations */
    .action-btn:hover { background-color: var(--green); }
    .action-btn.edit::before { content: "Update Category"; }
    .action-btn.add::before { content: "Add Category"; }

    .cancel-btn:hover { background-color: var(--orange); }
    .cancel-btn::before { content: "Cancel"; }
</style>
