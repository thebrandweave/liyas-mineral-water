<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

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
                $deleted_admin_name = $admin_data['username'];
                
                // Delete admin
                $deleteStmt = $pdo->prepare("DELETE FROM admins WHERE admin_id = ?");
                $deleteStmt->execute([$admin_id]);
                
                // Log activity
                quickLog($pdo, 'delete', 'user', $admin_id, "Deleted admin user: {$deleted_admin_name}");
                
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
                            
                            // Log activity
                            quickLog($pdo, 'update', 'user', $admin_id, "Updated admin user: {$username} (Role: {$role})");
                            
                            $form_success = "Admin updated successfully!";
                            $editing_admin = null;
                            $edit_id = 0;
                        }
                    } else {
                        // Update without password
                        $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, role = ? WHERE admin_id = ?");
                        $stmt->execute([$username, $email, $role, $admin_id]);
                        
                        // Log activity
                        quickLog($pdo, 'update', 'user', $admin_id, "Updated admin user: {$username} (Role: {$role})");
                        
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
                        $new_admin_id = $pdo->lastInsertId();
                        
                        // Log activity
                        quickLog($pdo, 'create', 'user', $new_admin_id, "Created admin user: {$username} (Role: {$role})");
                        
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

// Determine if admin modal should be visible on load
$show_modal = ($editing_admin || $form_error || $form_success);

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
	
	<!-- Google Font: Poppins -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link rel="stylesheet" href="../assets/css/prody-admin.css">
	<title>Admin Users - Liyas Admin</title>
	<style>
		.role-badge {
			display: inline-flex;
			padding: 0.25rem 0.625rem;
			border-radius: 9999px;
			font-size: 12px;
			font-weight: 500;
		}
		.role-badge.superadmin {
			background: var(--yellow-light);
			color: #92400e;
		}
		.role-badge.editor {
			background: var(--blue-light);
			color: var(--blue-dark);
		}
		.role-badge.moderator {
			background: #e0e7ff;
			color: #3730a3;
		}
		.current-user {
			background: #f0f9ff;
		}

		/* Modal overlay for Add/Edit Admin */
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
		
		.table-responsive-wrapper {
			width: 100%;
			overflow-x: auto;
			overflow-y: visible;
			-webkit-overflow-scrolling: touch;
			position: relative;
		}
		
		.table-responsive-wrapper table {
			min-width: 750px;
			width: 100%;
		}
		
		/* Mobile optimizations */
		@media (max-width: 768px) {
			.table-responsive-wrapper {
				overflow-x: scroll;
				-webkit-overflow-scrolling: touch;
				scrollbar-width: thin;
				scrollbar-color: var(--border-medium) transparent;
			}
			
			.table-responsive-wrapper::-webkit-scrollbar {
				height: 8px;
			}
			
			.table-responsive-wrapper::-webkit-scrollbar-track {
				background: var(--bg-main);
				border-radius: 4px;
			}
			
			.table-responsive-wrapper::-webkit-scrollbar-thumb {
				background: var(--border-medium);
				border-radius: 4px;
			}
			
			.table-responsive-wrapper::-webkit-scrollbar-thumb:hover {
				background: var(--text-secondary);
			}
			
			.table-responsive-wrapper table {
				min-width: 850px;
			}
			
			.table-responsive-wrapper table th,
			.table-responsive-wrapper table td {
				padding: 0.75rem 1rem;
				font-size: 13px;
			}
			
			.table-responsive-wrapper table th:first-child,
			.table-responsive-wrapper table td:first-child {
				position: sticky;
				left: 0;
				background: var(--bg-white);
				z-index: 10;
				box-shadow: 2px 0 4px rgba(0,0,0,0.05);
			}
			
			.table-responsive-wrapper table th:last-child,
			.table-responsive-wrapper table td:last-child {
				min-width: 180px;
			}
		}
		
		@media (max-width: 480px) {
			.table-responsive-wrapper table {
				min-width: 950px;
			}
			
			.table-responsive-wrapper table th,
			.table-responsive-wrapper table td {
				padding: 0.5rem 0.75rem;
				font-size: 12px;
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
					<span>Users</span>
				</div>
				<div class="header-actions">
					<form action="index.php" method="GET" style="display: flex; align-items: center; gap: 0.5rem;">
						<input type="search" name="search" placeholder="Search admins..." value="<?= htmlspecialchars($search) ?>" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border-light); border-radius: 6px; font-size: 14px; font-family: inherit;">
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

				<!-- Add/Edit Admin Modal -->
				<div 
					id="adminModal" 
					class="modal-overlay" 
					style="<?= $show_modal ? 'display:flex;' : 'display:none;' ?>"
				>
					<div class="form-card modal-card" id="adminForm">
						<div class="form-header" style="display:flex;justify-content:space-between;align-items:center;">
							<h2><?= $editing_admin ? 'Edit Admin User' : 'Add New Admin User' ?></h2>
							<button type="button" class="btn btn-secondary" style="padding:0.25rem 0.75rem;font-size:12px;" onclick="closeAdminModal()">
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
							<?php if ($editing_admin): ?>
								<input type="hidden" name="admin_id" value="<?= $editing_admin['admin_id'] ?>">
							<?php endif; ?>
							
							<div class="form-group">
								<label for="username">Username <span style="color: var(--red);">*</span></label>
								<input 
									type="text" 
									name="username" 
									id="username" 
									class="form-input"
									value="<?= htmlspecialchars($editing_admin['username'] ?? '') ?>" 
									required
									placeholder="Enter username"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Unique username for the admin account</small>
							</div>

							<div class="form-group">
								<label for="email">Email Address <span style="color: var(--red);">*</span></label>
								<input 
									type="email" 
									name="email" 
									id="email" 
									class="form-input"
									value="<?= htmlspecialchars($editing_admin['email'] ?? '') ?>" 
									required
									placeholder="Enter email address"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Valid email address for the admin account</small>
							</div>

							<div class="form-group">
								<label for="password">Password <?= $editing_admin ? '' : '<span style="color: var(--red);">*</span>' ?></label>
								<input 
									type="password" 
									name="password" 
									id="password" 
									class="form-input"
									placeholder="<?= $editing_admin ? 'Leave blank to keep current password' : 'Enter password' ?>"
									<?= $editing_admin ? '' : 'required' ?>
									minlength="6"
								>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;"><?= $editing_admin ? 'Leave blank to keep current password. Minimum 6 characters if changing.' : 'Minimum 6 characters' ?></small>
							</div>

							<div class="form-group">
								<label for="role">Role <span style="color: var(--red);">*</span></label>
								<select name="role" id="role" class="form-select" required>
									<option value="editor" <?= ($editing_admin['role'] ?? 'editor') === 'editor' ? 'selected' : '' ?>>Editor</option>
									<option value="moderator" <?= ($editing_admin['role'] ?? '') === 'moderator' ? 'selected' : '' ?>>Moderator</option>
									<option value="superadmin" <?= ($editing_admin['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Super Admin</option>
								</select>
								<small style="color: var(--text-muted); font-size: 12px; margin-top: 0.25rem; display: block;">Select the admin role/permission level</small>
							</div>

							<div class="form-actions">
								<?php if ($editing_admin): ?>
								<button type="submit" name="save_admin" class="btn-action btn-edit noselect">
									<span class="text">Admin</span>
									<span class="icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M5 18.08V19h.92l9.06-9.06-.92-.92z" fill="currentColor"/><path d="M20.71 6.04c.39-.39.39-1.02 0-1.41l-2.34-2.34c-.39-.39-1.02-.39-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
									</span>
								</button>
								<?php else: ?>
								<button type="submit" name="save_admin" class="btn-action btn-add noselect">
									<span class="text">Add</span>
									<span class="icon">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
									</span>
								</button>
								<?php endif; ?>
								<button type="button" class="btn btn-secondary" onclick="closeAdminModal()">
									<i class='bx bx-x'></i> Cancel
								</button>
							</div>
						</form>
					</div>
				</div>

				<!-- Admins Table -->
				<div class="table-card">
					<div class="table-header">
						<div class="table-title">
							All Admin Users
							<!-- <i class='bx bx-chevron-down'></i> -->
						</div>
						<div class="table-actions">
							<?php if (!$editing_admin): ?>
							<a href="javascript:void(0);" onclick="openAdminModal();" class="btn-action btn-add noselect">
								<span class="text">Add</span>
								<span class="icon">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
								</span>
							</a>
							<?php endif; ?>
						</div>
					</div>
					
					<?php if (empty($admins)): ?>
						<div style="padding: 3rem; text-align: center;">
							<i class='bx bx-group' style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
							<p style="color: var(--text-secondary); font-size: 1.1rem; margin-bottom: 0.5rem;">No admin users found</p>
							<p style="color: var(--text-muted); font-size: 0.9rem;">Get started by adding your first admin user</p>
						</div>
					<?php else: ?>
						<div class="table-responsive-wrapper">
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
												<span style="color: var(--blue); font-size: 12px; margin-left: 0.5rem;">(You)</span>
											<?php endif; ?>
										</td>
										<td><?= htmlspecialchars($admin['email']) ?></td>
										<td>
											<span class="role-badge <?= htmlspecialchars($admin['role']) ?>">
												<?= htmlspecialchars($admin['role']) ?>
											</span>
										</td>
										<td><?= date('d-m-Y', strtotime($admin['created_at'])) ?></td>
										<td>
											<a href="?edit=<?= $admin['admin_id'] ?>" class="btn-action btn-edit noselect">
												<span class="text">Edit</span>
												<span class="icon">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
												</span>
											</a>
											<?php if ($admin['admin_id'] != $current_admin_id): ?>
											<a href="javascript:void(0);" onclick="handleUserDelete(<?= $admin['admin_id'] ?>, '<?= htmlspecialchars(addslashes($admin['username'])) ?>')" class="btn-action btn-delete noselect">
												<span class="text">Delete</span>
												<span class="icon">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"></path></svg>
												</span>
											</a>
											<?php endif; ?>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>

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
	// User delete handler using shared modal, with graceful fallback
	function handleUserDelete(adminId, adminName) {
		if (typeof window.showDeleteConfirm === 'function') {
			window.showDeleteConfirm(adminId, adminName, function(id) {
				window.location.href = 'index.php?delete=' + id;
			});
		} else {
			if (confirm('Are you sure you want to delete admin user "' + adminName + '"?\n\nThis action cannot be undone!')) {
				window.location.href = 'index.php?delete=' + adminId;
			}
		}
	}

	function openAdminModal() {
		const modal = document.getElementById('adminModal');
		if (modal) {
			modal.style.display = 'flex';
			setTimeout(() => {
				const usernameInput = document.getElementById('username');
				if (usernameInput) usernameInput.focus();
			}, 100);
		}
	}

	function closeAdminModal() {
		const modal = document.getElementById('adminModal');
		if (modal) {
			modal.style.display = 'none';
			if (window.location.search.includes('edit=')) {
				window.location.href = 'index.php';
			}
		}
	}
	</script>
</body>
</html>
