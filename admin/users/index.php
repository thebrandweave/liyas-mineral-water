<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_admin_id = $_SESSION['admin_id'] ?? 0;
$current_page = "users";
$page_title = "Admin Users";

// Check for error message from redirect
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $admin_id = (int)$_GET['delete'];
    
    // Prevent self-deletion
    if ($admin_id == $current_admin_id) {
        $error_message = "You cannot delete your own account!";
    } else {
        try {
            // Check if admin exists
            $checkStmt = $pdo->prepare("SELECT admin_id, username FROM admins WHERE admin_id = ?");
            $checkStmt->execute([$admin_id]);
            $admin_data = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin_data) {
                // Delete admin
                $deleteStmt = $pdo->prepare("DELETE FROM admins WHERE admin_id = ?");
                $deleteStmt->execute([$admin_id]);
                $success_message = "Admin user deleted successfully!";
            } else {
                $error_message = "Admin user not found!";
            }
        } catch (PDOException $e) {
            $error_message = "Error deleting admin: " . $e->getMessage();
        }
    }
}

// Handle add/edit form submission
$form_error = '';
$form_success = '';
$editing_admin = null;
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

if ($edit_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT admin_id, username, email, role, created_at FROM admins WHERE admin_id = ?");
        $stmt->execute([$edit_id]);
        $editing_admin = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$editing_admin) {
            $form_error = "Admin user not found!";
            $edit_id = 0;
        }
    } catch (PDOException $e) {
        $form_error = "Error loading admin: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'editor');
    $admin_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;
    
    // Validation
    if (empty($username) || empty($email)) {
        $form_error = "Username and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = "Invalid email format.";
    } elseif (!in_array($role, ['superadmin', 'editor', 'moderator'])) {
        $form_error = "Invalid role selected.";
    } else {
        try {
            if ($admin_id > 0) {
                // Update existing admin
                // Check if username or email already exists (excluding current admin)
                $checkStmt = $pdo->prepare("SELECT admin_id FROM admins WHERE (username = ? OR email = ?) AND admin_id != ?");
                $checkStmt->execute([$username, $email, $admin_id]);
                $existing = $checkStmt->fetch();
                
                if ($existing) {
                    $form_error = "Username or email already exists.";
                } else {
                    if (!empty($password)) {
                        // Update with password
                        if (strlen($password) < 6) {
                            $form_error = "Password must be at least 6 characters long.";
                        } else {
                            $password_hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, password_hash = ?, role = ? WHERE admin_id = ?");
                            $stmt->execute([$username, $email, $password_hash, $role, $admin_id]);
                            $form_success = "Admin updated successfully!";
                            $editing_admin = null;
                            $edit_id = 0;
                        }
                    } else {
                        // Update without password
                        $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, role = ? WHERE admin_id = ?");
                        $stmt->execute([$username, $email, $role, $admin_id]);
                        $form_success = "Admin updated successfully!";
                        $editing_admin = null;
                        $edit_id = 0;
                    }
                }
            } else {
                // Insert new admin
                if (empty($password)) {
                    $form_error = "Password is required for new admin accounts.";
                } elseif (strlen($password) < 6) {
                    $form_error = "Password must be at least 6 characters long.";
                } else {
                    // Check if username or email already exists
                    $checkStmt = $pdo->prepare("SELECT admin_id FROM admins WHERE username = ? OR email = ?");
                    $checkStmt->execute([$username, $email]);
                    $existing = $checkStmt->fetch();
                    
                    if ($existing) {
                        $form_error = "Username or email already exists.";
                    } else {
                        $password_hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$username, $email, $password_hash, $role]);
                        $form_success = "Admin user added successfully!";
                    }
                }
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $form_error = "Username or email already exists.";
            } else {
                $form_error = "Error saving admin: " . $e->getMessage();
            }
        }
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
        $count_query = "SELECT COUNT(*) FROM admins WHERE username LIKE :search OR email LIKE :search";
        $count_stmt = $pdo->prepare($count_query);
        $count_stmt->bindValue(':search', "%$search%");
    } else {
        $count_query = "SELECT COUNT(*) FROM admins";
        $count_stmt = $pdo->prepare($count_query);
    }
    
    $count_stmt->execute();
    $total_records = $count_stmt->fetchColumn();
    $total_pages = ceil($total_records / $per_page);
} catch (PDOException $e) {
    $total_records = 0;
    $total_pages = 0;
    error_log("Admins count error: " . $e->getMessage());
}

// Fetch admins
try {
    if (!empty($search)) {
        $query = "SELECT admin_id, username, email, role, created_at
                  FROM admins 
                  WHERE username LIKE :search OR email LIKE :search
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':search', "%$search%");
    } else {
        $query = "SELECT admin_id, username, email, role, created_at
                  FROM admins 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);
    }
    
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $admins = [];
    error_log("Admins fetch error: " . $e->getMessage());
}

// Get statistics
try {
    $total_admins = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    $superadmins = $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'superadmin'")->fetchColumn();
    $editors = $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'editor'")->fetchColumn();
    $moderators = $pdo->query("SELECT COUNT(*) FROM admins WHERE role = 'moderator'")->fetchColumn();
} catch (PDOException $e) {
    $total_admins = 0;
    $superadmins = 0;
    $editors = 0;
    $moderators = 0;
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
	
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/admin-style.css">
	<title>Admin Users - Admin Panel</title>
	<style>
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
			overflow: visible;
			position: relative;
			text-decoration: none !important;
			will-change: width, border-radius, background-color;
			backface-visibility: hidden;
			transform: translateZ(0);
			flex-shrink: 0;
			margin: 0;
		}

		.button *,
		.button::before {
			pointer-events: none !important; 
		}

		.svgIcon { 
			width: 17px; 
			transition-duration: .3s;
			pointer-events: none !important;
			will-change: width, transform;
			backface-visibility: hidden;
		}
		.svgIcon path { 
			fill: white; 
			pointer-events: none !important;
		}

		.button.action-btn.add:hover {
			width: 140px;
			border-radius: 50px;
			transition-duration: .3s;
			align-items: center;
			background-color: #22c55e;
			z-index: 10;
		}
		
		.button.action-btn.edit:hover {
			width: 100px;
			border-radius: 50px;
			transition-duration: .3s;
			align-items: center;
			background-color: #3b82f6;
			z-index: 10;
		}
		
		.button.action-btn.delete:hover {
			width: 110px;
			border-radius: 50px;
			transition-duration: .3s;
			align-items: center;
			background-color: #dc2626;
			z-index: 10;
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
			opacity: 0;
			pointer-events: none !important;
			will-change: font-size, opacity, transform;
			backface-visibility: hidden;
		}

		.button:hover::before {
			font-size: 13px;
			opacity: 1;
			transform: translateY(30px);
			transition-duration: .3s;
		}

		.action-btn.add:hover { background-color: #22c55e; }
		.action-btn.add::before { content: "Add Admin"; }
		
		.action-btn.edit:hover { background-color: #3b82f6; }
		.action-btn.edit::before { content: "Edit"; }
		
		.action-btn.delete:hover { background-color: #dc2626; }
		.action-btn.delete::before { content: "Delete"; }

		.alert {
			padding: 1rem;
			border-radius: 8px;
			margin-bottom: 1rem;
		}

		.alert-success {
			background: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
		}

		.alert-error {
			background: #f8d7da;
			color: #721c24;
			border: 1px solid #f5c6cb;
		}

		table {
			table-layout: fixed;
			width: 100%;
		}
		
		table th:last-child,
		table td:last-child {
			width: 200px;
			min-width: 200px;
			max-width: 200px;
			overflow: visible;
			position: relative;
		}
		
		.action-buttons-wrapper {
			position: relative;
			width: 200px;
			height: 50px;
			margin: 0 auto;
			overflow: visible;
		}
		
		.action-buttons {
			position: relative;
			display: flex;
			gap: 0.5rem;
			align-items: center;
			justify-content: center;
			width: 100%;
			height: 100%;
		}
		
		.action-buttons .button {
			position: relative;
			flex-shrink: 0;
		}
		
		.action-buttons .button:hover {
			z-index: 100;
		}

		/* Form Styles */
		.admin-form {
			background: var(--light);
			padding: 2rem;
			border-radius: 12px;
			margin-top: 1rem;
		}

		.form-group {
			margin-bottom: 1.5rem;
		}

		.form-group label {
			display: block;
			margin-bottom: 0.5rem;
			color: var(--dark);
			font-weight: 600;
			font-size: 0.9rem;
		}

		.form-group input[type="text"],
		.form-group input[type="email"],
		.form-group input[type="password"],
		.form-group select {
			width: 100%;
			padding: 0.75rem 1rem;
			border: 1px solid var(--grey);
			border-radius: 8px;
			background: white;
			font-size: 1rem;
			font-family: var(--opensans);
			transition: border-color 0.2s;
		}

		.form-group input:focus,
		.form-group select:focus {
			outline: none;
			border-color: var(--blue);
		}

		.form-group small {
			display: block;
			margin-top: 0.25rem;
			color: var(--dark-grey);
			font-size: 0.85rem;
		}

		.btn-group {
			display: flex;
			gap: 1rem;
			margin-top: 1.5rem;
		}

		.btn {
			padding: 0.75rem 2rem;
			border: none;
			border-radius: 8px;
			font-size: 1rem;
			font-weight: 600;
			cursor: pointer;
			text-decoration: none;
			display: inline-flex;
			align-items: center;
			gap: 0.5rem;
			transition: all 0.2s;
		}

		.btn-primary {
			background: var(--blue);
			color: white;
		}

		.btn-primary:hover {
			background: #2563eb;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
		}

		.btn-secondary {
			background: var(--grey);
			color: var(--dark);
		}

		.btn-secondary:hover {
			background: #d1d5db;
		}

		.role-badge {
			display: inline-block;
			padding: 0.25rem 0.75rem;
			border-radius: 12px;
			font-size: 0.85rem;
			font-weight: 600;
			text-transform: uppercase;
		}

		.role-badge.superadmin {
			background: #fef3c7;
			color: #92400e;
		}

		.role-badge.editor {
			background: #dbeafe;
			color: #1e40af;
		}

		.role-badge.moderator {
			background: #e0e7ff;
			color: #3730a3;
		}

		.current-user {
			background: #f0f9ff;
			border-left: 3px solid var(--blue);
		}
	</style>
</head>
<body>
	<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>

	<!-- CONTENT -->
	<section id="content">
		<!-- NAVBAR -->
		<nav>
			<i class='bx bx-menu bx-sm' ></i>
			<a href="#" class="nav-link"><?= $page_title ?></a>
			<form action="index.php" method="GET">
				<div class="form-input">
					<input type="search" name="search" placeholder="Search admins..." value="<?= htmlspecialchars($search) ?>">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification">
				<i class='bx bxs-bell bx-tada-hover' ></i>
				<span class="num">0</span>
			</a>
			<a href="#" class="profile">
				<i class='bx bx-user-circle' style="font-size: 2rem; color: var(--dark-grey);"></i>
			</a>
		</nav>
		<!-- NAVBAR -->

		<!-- MAIN -->
		<main>
			<div class="head-title">
				<div class="left">
					<h1>Admin Users</h1>
					<ul class="breadcrumb">
						<li>
							<a href="../index.php">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right' ></i></li>
						<li>
							<a class="active" href="#">Admin Users</a>
						</li>
					</ul>
				</div>
				<?php if (!$editing_admin): ?>
				<a href="javascript:void(0);" onclick="document.getElementById('adminForm').scrollIntoView({behavior: 'smooth', block: 'start'});" class="button action-btn add" title="Add new admin">
					<svg class="svgIcon" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"></path></svg>
				</a>
				<?php endif; ?>
			</div>

			<!-- Messages -->
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

			<!-- Statistics Cards -->
			<ul class="box-info">
				<li>
					<i class='bx bxs-group' ></i>
					<span class="text">
						<h3><?= number_format($total_admins) ?></h3>
						<p>Total Admins</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-shield-alt-2' ></i>
					<span class="text">
						<h3><?= number_format($superadmins) ?></h3>
						<p>Super Admins</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-edit' ></i>
					<span class="text">
						<h3><?= number_format($editors) ?></h3>
						<p>Editors</p>
					</span>
				</li>
				<li>
					<i class='bx bxs-user-check' ></i>
					<span class="text">
						<h3><?= number_format($moderators) ?></h3>
						<p>Moderators</p>
					</span>
				</li>
			</ul>

			<!-- Add/Edit Admin Form -->
			<?php if ($editing_admin || !isset($_GET['search'])): ?>
			<div class="table-data" id="adminForm">
				<div class="order">
					<div class="head">
						<h3><?= $editing_admin ? 'Edit Admin User' : 'Add New Admin User' ?></h3>
						<i class='bx bxs-user' ></i>
					</div>

					<div class="admin-form">
						<?php if ($form_error): ?>
							<div class="alert alert-error">
								<i class='bx bx-error-circle' ></i> <?= htmlspecialchars($form_error) ?>
							</div>
						<?php endif; ?>

						<?php if ($form_success): ?>
							<div class="alert alert-success">
								<i class='bx bx-check-circle' ></i> <?= htmlspecialchars($form_success) ?>
							</div>
						<?php endif; ?>

						<form method="POST" action="">
							<?php if ($editing_admin): ?>
								<input type="hidden" name="admin_id" value="<?= $editing_admin['admin_id'] ?>">
							<?php endif; ?>
							
							<div class="form-group">
								<label for="username">Username <span style="color: #dc2626;">*</span></label>
								<input 
									type="text" 
									name="username" 
									id="username" 
									value="<?= htmlspecialchars($editing_admin['username'] ?? '') ?>" 
									required
									placeholder="Enter username"
								>
								<small>Unique username for the admin account</small>
							</div>

							<div class="form-group">
								<label for="email">Email Address <span style="color: #dc2626;">*</span></label>
								<input 
									type="email" 
									name="email" 
									id="email" 
									value="<?= htmlspecialchars($editing_admin['email'] ?? '') ?>" 
									required
									placeholder="Enter email address"
								>
								<small>Valid email address for the admin account</small>
							</div>

							<div class="form-group">
								<label for="password">Password <?= $editing_admin ? '' : '<span style="color: #dc2626;">*</span>' ?></label>
								<input 
									type="password" 
									name="password" 
									id="password" 
									placeholder="<?= $editing_admin ? 'Leave blank to keep current password' : 'Enter password' ?>"
									<?= $editing_admin ? '' : 'required' ?>
									minlength="6"
								>
								<small><?= $editing_admin ? 'Leave blank to keep current password. Minimum 6 characters if changing.' : 'Minimum 6 characters' ?></small>
							</div>

							<div class="form-group">
								<label for="role">Role <span style="color: #dc2626;">*</span></label>
								<select name="role" id="role" required>
									<option value="editor" <?= ($editing_admin['role'] ?? 'editor') === 'editor' ? 'selected' : '' ?>>Editor</option>
									<option value="moderator" <?= ($editing_admin['role'] ?? '') === 'moderator' ? 'selected' : '' ?>>Moderator</option>
									<option value="superadmin" <?= ($editing_admin['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
								</select>
								<small>Select the admin role/permission level</small>
							</div>

							<div class="btn-group">
								<button type="submit" name="save_admin" class="btn btn-primary">
									<i class='bx bx-save' ></i> <?= $editing_admin ? 'Update Admin' : 'Add Admin' ?>
								</button>
								<?php if ($editing_admin): ?>
									<a href="index.php" class="btn btn-secondary">
										<i class='bx bx-x' ></i> Cancel
									</a>
								<?php endif; ?>
							</div>
						</form>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<!-- Admins Table -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Admin Users (<?= number_format($total_records) ?> total)</h3>
						<i class='bx bx-search' ></i>
						<i class='bx bx-filter' ></i>
					</div>
					<?php if (empty($admins)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bxs-group' style="font-size: 4rem; color: var(--dark-grey); margin-bottom: 1rem;"></i>
							<p style="color: var(--dark-grey); font-size: 1.1rem; margin-bottom: 0.5rem;">No admin users found</p>
							<p style="color: var(--dark-grey); font-size: 0.9rem;">Get started by adding your first admin user</p>
						</div>
					<?php else: ?>
						<table>
							<thead>
								<tr>
									<th>Username</th>
									<th>Email</th>
									<th>Role</th>
									<th>Created</th>
									<th>Actions</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($admins as $admin): ?>
								<tr class="<?= $admin['admin_id'] == $current_admin_id ? 'current-user' : '' ?>">
									<td>
										<strong><?= htmlspecialchars($admin['username']) ?></strong>
										<?php if ($admin['admin_id'] == $current_admin_id): ?>
											<span style="color: var(--blue); font-size: 0.85rem; margin-left: 0.5rem;">(You)</span>
										<?php endif; ?>
									</td>
									<td>
										<?= htmlspecialchars($admin['email']) ?>
									</td>
									<td>
										<span class="role-badge <?= htmlspecialchars($admin['role']) ?>">
											<?= htmlspecialchars($admin['role']) ?>
										</span>
									</td>
									<td>
										<p><?= date('d-m-Y', strtotime($admin['created_at'])) ?></p>
									</td>
									<td>
										<div class="action-buttons-wrapper">
											<div class="action-buttons">
												<a href="?edit=<?= $admin['admin_id'] ?>" class="button action-btn edit" title="Edit Admin">
													<svg class="svgIcon" viewBox="0 0 512 512"><path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"></path></svg>
												</a>
												<?php if ($admin['admin_id'] != $current_admin_id): ?>
												<button 
													onclick="confirmDelete(<?= $admin['admin_id'] ?>, '<?= htmlspecialchars(addslashes($admin['username'])) ?>')" 
													class="button action-btn delete" 
													title="Delete Admin"
												>
													<svg class="svgIcon" viewBox="0 0 448 512"><path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path></svg>
												</button>
												<?php endif; ?>
											</div>
										</div>
									</td>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>

						<!-- Pagination -->
						<?php if ($total_pages > 1): ?>
						<div style="padding: 1.5rem; border-top: 1px solid var(--grey); display: flex; justify-content: center; gap: 0.5rem; align-items: center;">
							<?php if ($page > 1): ?>
								<a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--opensans);">
									<i class='bx bx-chevron-left' ></i> Previous
								</a>
							<?php endif; ?>
							
							<span style="padding: 0.5rem 1rem; color: var(--dark); font-family: var(--opensans);">
								Page <?= $page ?> of <?= $total_pages ?>
							</span>
							
							<?php if ($page < $total_pages): ?>
								<a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" style="padding: 0.5rem 1rem; background: var(--blue); color: white; text-decoration: none; border-radius: 8px; font-family: var(--opensans);">
									Next <i class='bx bx-chevron-right' ></i>
								</a>
							<?php endif; ?>
						</div>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->
	
	<script src="../assets/js/admin-script.js"></script>
	<script>
	function confirmDelete(adminId, adminName) {
		if (confirm(`Are you sure you want to delete admin user "${adminName}"?\n\nThis action cannot be undone!`)) {
			window.location.href = `index.php?delete=${adminId}`;
		}
	}
	</script>
</body>
</html>
