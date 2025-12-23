<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "notifications";
$page_title   = "Notifications";

$admin_id = $_SESSION['admin_id'];

/* MARK AS READ */
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    $stmt = $pdo->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE notification_id = ? 
          AND recipient_type = 'admin'
          AND admin_id = ?
    ");
    $stmt->execute([$id, $admin_id]);
}

/* DELETE */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("
        DELETE FROM notifications 
        WHERE notification_id = ? 
          AND recipient_type = 'admin'
          AND admin_id = ?
    ");
    $stmt->execute([$id, $admin_id]);

    quickLog($pdo, 'delete', 'notification', $id, 'Deleted admin notification');
    $success_message = "Notification deleted successfully!";
}

/* SEARCH */
$search = $_GET['search'] ?? '';
$searchTerm = "%$search%";

/* FETCH NOTIFICATIONS */
$stmt = $pdo->prepare("
    SELECT * FROM notifications
    WHERE recipient_type = 'admin'
      AND admin_id = ?
      AND (title LIKE ? OR message LIKE ?)
    ORDER BY created_at DESC
");
$stmt->execute([$admin_id, $searchTerm, $searchTerm]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <title>Notifications - Liyas Admin</title>
    <style>
        /* Matches Products UI exactly */
        body { font-family: 'Poppins', sans-serif; }
        .table-card table tbody td { font-size: 14px; color: #334155; }
        .text-muted-custom { font-size: 13px; color: #94a3b8; }
        .status-badge { font-weight: 500; padding: 4px 10px; border-radius: 6px; font-size: 12px; }
        .bg-unread { background: #fef9c3; color: #854d0e; } /* Soft warning */
        .bg-read { background: #dcfce7; color: #166534; }   /* Soft success */
        
        /* Ensure table row highlights unread */
        .unread-row { background-color: #f8fafc !important; border-left: 4px solid #3b82f6; }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb"><i class='bx bx-bell'></i> <span>Notifications</span></div>
                <div class="header-actions">
                    <form action="index.php" method="GET" style="display: flex; text-align:center; gap: 0.5rem;">
                        <input type="search" name="search" placeholder="Search notifications..." value="<?= htmlspecialchars($search) ?>" class="form-input" style="width: 200px;">
                        <button type="submit" class="header-btn" style="padding: 0.5rem;"><i class='bx bx-search'></i></button>
                    </form>
                </div>
            </div>
            
            <div class="content-area">
                <?php if (isset($success_message)): ?><div class="alert alert-success"><?= $success_message ?></div><?php endif; ?>

                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">Admin Notifications</div>
                        <div class="table-actions">
                            </div>
                    </div>
                    
                    <div class="table-responsive-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$notifications): ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding: 2rem; color: #94a3b8;">
                                            No notifications found.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($notifications as $n): ?>
                                <tr class="<?= !$n['is_read'] ? 'unread-row' : '' ?>">
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <i class='bx bx-info-circle' style="color: #3b82f6; font-size: 1.2rem;"></i>
                                            <strong><?= htmlspecialchars($n['title']) ?></strong>
                                        </div>
                                    </td>
                                    <td><span class="text-muted-custom"><?= htmlspecialchars(substr($n['message'], 0, 50)) ?>...</span></td>
                                    <td><span class="badge"><?= ucfirst($n['type']) ?></span></td>
                                    <td>
                                        <?php if(!$n['is_read']): ?>
                                            <span class="status-badge bg-unread">Unread</span>
                                        <?php else: ?>
                                            <span class="status-badge bg-read">Read</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d-m-Y H:i', strtotime($n['created_at'])) ?></td>
                                    <td>
                                        <?php if (!$n['is_read']): ?>
                                        <a href="?read=<?= $n['notification_id'] ?>" class="btn-action btn-edit noselect">
                                            <span class="text">Read</span>
                                            <span class="icon"><i class='bx bx-check-double'></i></span>
                                        </a>
                                        <?php endif; ?>

                                        <a href="javascript:void(0);" onclick="confirmDelete(<?= $n['notification_id'] ?>)" class="btn-action btn-delete noselect">
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
    
    <script>
    function confirmDelete(id) {
        if (confirm(`Are you sure you want to delete this notification?`)) {
            window.location.href = `index.php?delete=${id}`;
        }
    }
    </script>
</body>
</html>