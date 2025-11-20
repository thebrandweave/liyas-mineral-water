<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "users";
$page_title = "Manage Users";

// Fetch all users from the database
$users = [];
try {
    $stmt = $pdo->query("SELECT admin_id, username, email, role, created_at FROM admins ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <title><?= $page_title ?> - Admin Panel</title>
    <style>
        /* --- BASE BUTTON STYLE --- */
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

        /* Prevent internal elements from interfering with hover state */
        .button .svgIcon, 
        .button::before {
            pointer-events: none; 
        }

        .svgIcon {
            width: 17px;
            transition-duration: .3s;
        }

        .svgIcon path {
            fill: white;
        }

        .button:hover {
            width: 120px;
            border-radius: 50px;
            transition-duration: .3s;
            background-color: rgb(255, 69, 69);
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
            content: "Delete";
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

        /* --- VARIATIONS --- */
        
        /* Delete Button (Default Red) */
        .delete-btn:hover { background-color: rgb(255, 69, 69); }
        .delete-btn::before { content: "Delete"; }

        /* Edit Button (Blue) */
        .edit-btn:hover { background-color: #3b82f6; }
        .edit-btn::before { content: "Edit"; }

        /* Add Button (Green) */
        .add-btn:hover { background-color: #22c55e; }
        .add-btn::before { content: "Add New"; }

        /* --- LAYOUT FIXES FOR FLICKERING --- */
        .table-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            align-items: center;
        }

        /* CRITICAL FIX: 
           Set a fixed minimum width for the Actions column.
           This prevents the table from resizing/jumping when buttons expand.
           120px (expanded button) + 50px (closed button) + gap = ~180px required.
           We set 200px to be safe.
        */
        .action-column {
            min-width: 200px;
            width: 200px;
        }
        
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <section id="content">
        <nav>
            <i class='bx bx-menu bx-sm'></i>
            <a href="#" class="nav-link"><?= $page_title ?></a>
            <a href="#" class="profile" style="margin-left: auto;">
                <img src="https://i.pravatar.cc/36?u=<?= urlencode($admin_name) ?>" alt="Profile">
            </a>
        </nav>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1><?= $page_title ?></h1>
                    <ul class="breadcrumb">
                        <li><a href="../index.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Users</a></li>
                    </ul>
                </div>
                
                <a href="add.php" class="button add-btn" title="Add New User">
                    <svg class="svgIcon" viewBox="0 0 448 512">
                        <path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/>
                    </svg>
                </a>
            </div>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>All Users</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined On</th>
                                <th class="action-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['username']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars(ucfirst($user['role'])) ?></td>
                                    <td><?= date('d M, Y', strtotime($user['created_at'])) ?></td>
                                    <td class="table-actions action-column">
                                        <a href="edit.php?id=<?= $user['admin_id'] ?>" class="button edit-btn" title="Edit">
                                            <svg class="svgIcon" viewBox="0 0 512 512">
                                                <path d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1v32c0 8.8 7.2 16 16 16h32zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z"></path>
                                            </svg>
                                        </a>

                                        <a href="delete.php?id=<?= $user['admin_id'] ?>" class="button delete-btn" title="Delete" onclick="return confirm('Are you sure you want to delete this user?');">
                                            <svg viewBox="0 0 448 512" class="svgIcon">
                                                <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
        </section>
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>