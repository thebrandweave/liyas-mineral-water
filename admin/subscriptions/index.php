<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "subscriptions";
$page_title   = "Newsletter Subscriptions";

$admin_id = $_SESSION['admin_id'] ?? 0;

/* --- TOGGLE STATUS --- */
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    $current_status = $_GET['status'];
    $new_status = ($current_status === 'subscribed') ? 'unsubscribed' : 'subscribed';
    $unsub_time = ($new_status === 'unsubscribed') ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare("UPDATE newsletter_subscriptions SET status = ?, unsubscribed_at = ? WHERE newsletter_id = ?");
    $stmt->execute([$new_status, $unsub_time, $id]);
    
    quickLog($pdo, 'update', 'newsletter', $id, "Changed subscription status to $new_status");
    $success_message = "Status updated successfully!";
}

/* --- DELETE --- */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM newsletter_subscriptions WHERE newsletter_id = ?")->execute([$id]);
    quickLog($pdo, 'delete', 'newsletter', $id, "Removed email from newsletter list");
    $success_message = "Subscription deleted successfully!";
}

/* --- SEARCH & FETCH --- */
$search = $_GET['search'] ?? '';
$searchTerm = "%$search%";

$stmt = $pdo->prepare("SELECT * FROM newsletter_subscriptions WHERE email LIKE ? ORDER BY subscribed_at DESC");
$stmt->execute([$searchTerm]);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <title>Newsletter - Liyas Admin</title>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .table-card table tbody td { font-size: 14px; color: #334155; }
        .status-badge { font-weight: 500; padding: 4px 10px; border-radius: 6px; font-size: 12px; }
        .bg-subscribed { background: #dcfce7; color: #166534; }
        .bg-unsubscribed { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <div class="breadcrumb"><i class='bx bx-envelope'></i> <span>Newsletter</span></div>
                <div class="header-actions">
                    <form action="index.php" method="GET" style="display: flex; gap: 0.5rem;">
                        <input type="search" name="search" placeholder="Search emails..." value="<?= htmlspecialchars($search) ?>" class="form-input" style="width: 200px;">
                        <button type="submit" class="header-btn"><i class='bx bx-search'></i></button>
                    </form>
                </div>
            </div>
            
            <div class="content-area">
                <?php if (isset($success_message)): ?><div class="alert alert-success"><?= $success_message ?></div><?php endif; ?>

                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">Subscribers List</div>
                    </div>
                    
                    <div class="table-responsive-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Email Address</th>
                                    <th>Status</th>
                                    <th>Subscribed Date</th>
                                    <th style="text-align: right; padding-right: 25px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!$subscriptions): ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding: 3rem; color: #94a3b8;">
                                            <i class='bx bx-mail-send' style="font-size: 3rem; display: block; margin-bottom: 10px;"></i>
                                            No subscriptions found.
                                        </td>
                                    </tr>
                                <?php endif; ?>

                                <?php foreach ($subscriptions as $s): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 35px; height: 35px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                                                <i class='bx bx-envelope'></i>
                                            </div>
                                            <strong><?= htmlspecialchars($s['email']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge bg-<?= $s['status'] ?>">
                                            <?= ucfirst($s['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d-m-Y', strtotime($s['subscribed_at'])) ?></td>
                                    <td style="text-align: right;">
                                        <a href="?toggle_id=<?= $s['newsletter_id'] ?>&status=<?= $s['status'] ?>" class="btn-action btn-edit noselect" style="text-decoration: none;">
                                            <span class="text"><?= ($s['status'] == 'subscribed') ? 'Unsub' : 'Sub' ?></span>
                                            <span class="icon"><i class='bx bx-refresh'></i></span>
                                        </a>

                                        <a href="javascript:void(0);" onclick="confirmDelete(<?= $s['newsletter_id'] ?>)" class="btn-action btn-delete noselect" style="text-decoration: none;">
                                            <span class="text">Delete</span>
                                            <span class="icon"><i class='bx bx-x'></i></span>
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
        if (confirm(`Remove this subscriber permanently?`)) {
            window.location.href = `index.php?delete=${id}`;
        }
    }
    </script>
</body>
</html>