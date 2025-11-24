<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');

// --- PAGE TITLE DEFINITION ---
$page_title = "Manage Categories";

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
    $page_error = "Error fetching categories: " . $e->getMessage();
}

$current_page = "categories";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <title>Manage Categories - Admin Panel</title>
    <style>
        /* --- MODERN FORM STYLES --- */
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
            width: 100%;
        }
        .form-modern input:focus, .form-modern textarea:focus {
            outline: none;
            border-color: #3C91E6;
            background-color: var(--light);
            box-shadow: 0 0 0 3px rgba(60, 145, 230, 0.1);
        }
        .form-group-buttons { flex-direction: row !important; gap: 1rem; margin-top: 1rem; }

        /* --- ANIMATED BUTTON STYLES (STABLE) --- */
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

        /* CRITICAL FIX: Prevent mouse events on children to stop flickering */
        .button .svgIcon, 
        .button::before {
            pointer-events: none; 
        }

        .svgIcon { width: 17px; transition-duration: .3s; }
        .svgIcon path { fill: white; }

        .button:hover {
            width: 140px; /* Width when expanded */
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

        /* --- BUTTON COLOR VARIATIONS --- */
        
        /* Form Actions */
        .action-btn:hover { background-color: #22c55e; } /* Green */
        .action-btn.edit::before { content: "Update"; }
        .action-btn.add::before { content: "Add New"; }
        
        .cancel-btn:hover { background-color: #f97316; } /* Orange */
        .cancel-btn::before { content: "Cancel"; }

        /* Table Actions */
        .table-edit-btn:hover { background-color: #3b82f6; } /* Blue */
        .table-edit-btn::before { content: "Edit"; }
        
        .table-delete-btn:hover { background-color: #ef4444; } /* Red */
        .table-delete-btn::before { content: "Delete"; }
        .table-delete-btn .svgIcon { width: 12px; }

        /* --- TABLE LAYOUT STABILITY --- */
        .actions { display: flex; gap: 0.5rem; align-items: center; }

        /* CRITICAL FIX: Fixed width for Action column prevents table jumping */
        .action-column {
            min-width: 180px; 
            width: 180px;
        }

        /* Alert Styles */
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <section id="content">
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
                                <th class="action-column">Actions</th>
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
                                        <td class="actions action-column">
                                            <a href="categories.php?action=edit&id=<?= $cat['category_id'] ?>" class="button table-edit-btn" title="Edit">
                                                <svg class="svgIcon" viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.8-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"></path></svg>
                                            </a>
                                            
                                            <form action="category_handler.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?');" style="display: contents;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                <button type="submit" class="button table-delete-btn" title="Delete">
                                                    <svg class="svgIcon" viewBox="0 0 448 512"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
                                                </button>
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
        </section>
    <script src="assets/js/admin-script.js"></script>
</body>
</html>