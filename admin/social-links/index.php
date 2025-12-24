<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "social-links";

/* DELETE */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM social_links WHERE social_id=?")->execute([$id]);
    quickLog($pdo,'delete','social_link',$id,'Deleted social link');
    header("Location: index.php");
    exit;
}

/* TOGGLE */
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $pdo->prepare("
        UPDATE social_links
        SET status = IF(status='active','inactive','active')
        WHERE social_id=?
    ")->execute([$id]);
    quickLog($pdo,'update','social_link',$id,'Toggled social link');
    header("Location: index.php");
    exit;
}

/* FETCH */
$links = $pdo->query("
    SELECT * FROM social_links
    ORDER BY sort_order ASC, created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Social Media</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/prody-admin.css">

<style>
/* ========== MATCH PRODUCTS TABLE SPACING ========== */
.table-card table thead th{
    padding:14px 16px;
    font-size:13px;
    color:#64748b;
}

.table-card table tbody td{
    padding:14px 16px;
    vertical-align:middle;
    font-size:14px;
    color:#334155;
}

/* ICON BOX */
.social-icon-box{
    width:40px;
    height:40px;
    border-radius:8px;
    background:#f1f5f9;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
}

/* STATUS BADGES */
.badge-active{
    background:#16a34a;
    color:#fff;
    padding:4px 12px;
    border-radius:12px;
    font-size:12px;
    font-weight:500;
}
.badge-inactive{
    background:#64748b;
    color:#fff;
    padding:4px 12px;
    border-radius:12px;
    font-size:12px;
    font-weight:500;
}

/* ACTIONS – SAME HEIGHT & ALIGNMENT AS PRODUCTS */
.actions-group{
    display:flex;
    gap:10px;
    align-items:center;
    white-space:nowrap;
}

/* WHITE TOGGLE */
.btn-toggle{
    background:#ffffff;
    color:#000;
    border:1px solid #e5e7eb;
    padding:8px 16px;
    border-radius:8px;
    font-size:14px;
    min-width:120px;
    text-align:center;
}
.btn-toggle:hover{
    background:#f8fafc;
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
            <span>Social Media</span>
        </div>
    </div>

    <div class="content-area">

        <div class="table-card">
            <div class="table-header">
                <div class="table-title">Social Media</div>

                <!-- ADD BUTTON (PRODUCTS STYLE) -->
                <div class="table-actions">
                    <a href="add.php" class="btn-action btn-add noselect">
                        <span class="text">Add</span>
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>

            <div class="table-responsive-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Icon</th>
                            <th>Platform</th>
                            <th>URL</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($links as $s): ?>
                    <tr>
                        <td>
                            <div class="social-icon-box">
                                <i class="<?= htmlspecialchars($s['icon_class']) ?>"></i>
                            </div>
                        </td>

                        <td><strong><?= htmlspecialchars($s['platform']) ?></strong></td>

                        <td class="text-muted-custom"><?= htmlspecialchars($s['url']) ?></td>

                        <td>
                            <?= $s['status']==='active'
                                ? '<span class="badge-active">Active</span>'
                                : '<span class="badge-inactive">Inactive</span>' ?>
                        </td>

                        <td>
                            <div class="actions-group">

                                <!-- EDIT -->
                                <a href="edit.php?id=<?= $s['social_id'] ?>" class="btn-action btn-edit noselect">
                                    <span class="text">Edit</span>
                                    <span class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </span>
                                </a>

                                <!-- TOGGLE -->
                                <a href="?toggle=<?= $s['social_id'] ?>" class="btn-toggle noselect">
                                    <?= $s['status']==='active' ? 'Deactivate' : 'Activate' ?>
                                </a>

                                <!-- DELETE -->
                                <a href="?delete=<?= $s['social_id'] ?>"
                                   class="btn-action btn-delete noselect"
                                   onclick="return confirm('Delete this social link?')">
                                    <span class="text">Delete</span>
                                    <span class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
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
