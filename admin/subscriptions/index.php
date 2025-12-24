<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "subscriptions";
$page_title   = "Newsletter Subscriptions";

/* --- TOGGLE STATUS --- */
if (isset($_GET['toggle_id'])) {
    $id = (int)$_GET['toggle_id'];
    $current_status = $_GET['status'];
    $new_status = ($current_status === 'subscribed') ? 'unsubscribed' : 'subscribed';
    $unsub_time = ($new_status === 'unsubscribed') ? date('Y-m-d H:i:s') : null;

    $stmt = $pdo->prepare("
        UPDATE newsletter_subscriptions
        SET status = ?, unsubscribed_at = ?
        WHERE newsletter_id = ?
    ");
    $stmt->execute([$new_status, $unsub_time, $id]);

    quickLog($pdo, 'update', 'newsletter', $id, "Changed subscription status to $new_status");
    header("Location: index.php");
    exit;
}

/* --- DELETE --- */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM newsletter_subscriptions WHERE newsletter_id=?")->execute([$id]);
    quickLog($pdo, 'delete', 'newsletter', $id, "Removed email from newsletter list");
    header("Location: index.php");
    exit;
}

/* --- SEARCH & FETCH --- */
$search = $_GET['search'] ?? '';
$searchTerm = "%$search%";

$stmt = $pdo->prepare("
    SELECT * FROM newsletter_subscriptions
    WHERE email LIKE ?
    ORDER BY subscribed_at DESC
");
$stmt->execute([$searchTerm]);
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/prody-admin.css">

<title>Newsletter - Liyas Admin</title>

<style>
/* MATCH PRODUCTS ROW SPACING */
.table-card table tbody tr td{
    padding:14px 16px;
    vertical-align: middle;
    font-size:14px;
    color:#334155;
}

/* STATUS BADGES — CONSISTENT */
.badge-subscribed{
    background:#16a34a;
    color:#fff;
    padding:4px 12px;
    border-radius:12px;
    font-size:12px;
    font-weight:500;
}
.badge-unsubscribed{
    background:#64748b;
    color:#fff;
    padding:4px 12px;
    border-radius:12px;
    font-size:12px;
    font-weight:500;
}

/* ACTIONS ALIGNMENT (PRODUCTS STYLE) */
.actions-group{
    display:flex;
    justify-content:flex-end;
    gap:10px;
    align-items:center;
    white-space:nowrap;
}
</style>
</head>

<body>
<div class="container">

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="header">
        <div class="breadcrumb">
            <i class='bx bx-envelope'></i>
            <span>Newsletter</span>
        </div>

        <div class="header-actions">
            <form method="GET" style="display:flex; gap:8px;">
                <input type="search"
                       name="search"
                       value="<?= htmlspecialchars($search) ?>"
                       placeholder="Search emails..."
                       class="form-input"
                       style="width:200px">
                <button class="header-btn"><i class='bx bx-search'></i></button>
            </form>
        </div>
    </div>

    <div class="content-area">

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
                            <th style="text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if (!$subscriptions): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; padding:3rem; color:#94a3b8;">
                                <i class='bx bx-mail-send' style="font-size:3rem; display:block; margin-bottom:10px;"></i>
                                No subscriptions found
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($subscriptions as $s): ?>
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:12px;">
                                    <div style="width:36px;height:36px;background:#f1f5f9;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                                        <i class='bx bx-envelope'></i>
                                    </div>
                                    <strong><?= htmlspecialchars($s['email']) ?></strong>
                                </div>
                            </td>

                            <td>
                                <?= $s['status']==='subscribed'
                                    ? '<span class="badge-subscribed">Subscribed</span>'
                                    : '<span class="badge-unsubscribed">Unsubscribed</span>' ?>
                            </td>

                            <td><?= date('d-m-Y', strtotime($s['subscribed_at'])) ?></td>

                            <td>
                                <div class="actions-group">

                                    <!-- TOGGLE (EDIT STYLE LIKE PRODUCTS) -->
                                    <a href="?toggle_id=<?= $s['newsletter_id'] ?>&status=<?= $s['status'] ?>"
                                       class="btn-action btn-edit noselect">
                                        <span class="text">
                                            <?= $s['status']==='subscribed' ? 'Unsub' : 'Sub' ?>
                                        </span>
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 width="24" height="24"
                                                 viewBox="0 0 24 24"
                                                 fill="none" stroke="currentColor"
                                                 stroke-width="2">
                                                <polyline points="23 4 23 10 17 10"/>
                                                <polyline points="1 20 1 14 7 14"/>
                                                <path d="M3.51 9a9 9 0 0 1 14.13-3.36L23 10"/>
                                                <path d="M20.49 15a9 9 0 0 1-14.13 3.36L1 14"/>
                                            </svg>
                                        </span>
                                    </a>

                                    <!-- DELETE -->
                                    <a href="index.php?delete=<?= $s['newsletter_id'] ?>"
                                       class="btn-action btn-delete noselect"
                                       onclick="return confirm('Remove this subscriber permanently?')">
                                        <span class="text">Delete</span>
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                 width="24" height="24"
                                                 viewBox="0 0 24 24">
                                                <path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"/>
                                            </svg>
                                        </span>
                                    </a>

                                </div>
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
</body>
</html>
