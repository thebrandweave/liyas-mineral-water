<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "advertisements";
$page_title   = "Advertisements";

/* DELETE */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $stmt = $pdo->prepare("SELECT title, image FROM advertisements WHERE ad_id=?");
    $stmt->execute([$id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($ad) {
        if ($ad['image']) {
            $path = __DIR__ . '/uploads/' . $ad['image'];
            if (file_exists($path)) unlink($path);
        }
        $pdo->prepare("DELETE FROM advertisements WHERE ad_id=?")->execute([$id]);
        quickLog($pdo, 'delete', 'advertisement', $id, "Deleted ad: {$ad['title']}");
    }

    header("Location: index.php");
    exit;
}

/* FETCH */
$ads = $pdo->query("
    SELECT * FROM advertisements
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Advertisements - Liyas Admin</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/prody-admin.css">

<style>
.img-thumb-fixed{
    width:40px;
    height:40px;
    border-radius:8px;
    object-fit:cover;
    border:1px solid #e2e8f0;
}

.badge-active{
    background:#16a34a;
    color:#fff;
    padding:4px 12px;
    border-radius:12px;
    font-size:12px;
}
.badge-inactive{
    background:#64748b;
    color:#fff;
    padding:4px 12px;
    border-radius:12px;
    font-size:12px;
}

/* ACTIONS ALIGN LIKE PRODUCTS */
.actions-group{
    display:flex;
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

    <div class="header">
        <div class="breadcrumb">
            <i class='bx bx-image'></i>
            <span>Advertisements</span>
        </div>
    </div>

    <div class="content-area">

        <div class="table-card">
            <div class="table-header">
                <div class="table-title">All Advertisements</div>

                <!-- ADD BUTTON — PRODUCT STYLE -->
                <div class="table-actions">
                    <a href="add.php" class="btn-action btn-add noselect">
                        <span class="text">Add</span>
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 width="24" height="24"
                                 viewBox="0 0 24 24">
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
                            <th>Image</th>
                            <th>Title</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Dates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php foreach ($ads as $ad): ?>
                    <tr>
                        <td>
                            <?php if ($ad['image'] && file_exists(__DIR__.'/uploads/'.$ad['image'])): ?>
                                <img src="uploads/<?= $ad['image'] ?>" class="img-thumb-fixed">
                            <?php else: ?>
                                <div class="img-thumb-fixed" style="background:#f1f5f9;display:flex;align-items:center;justify-content:center">
                                    <i class='bx bx-image'></i>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td><strong><?= htmlspecialchars($ad['title']) ?></strong></td>

                        <td><?= htmlspecialchars($ad['position']) ?></td>

                        <td>
                            <?= $ad['status']==='active'
                                ? '<span class="badge-active">Active</span>'
                                : '<span class="badge-inactive">Inactive</span>' ?>
                        </td>

                        <td>
                            <?= $ad['start_date'] ?: '-' ?><br>
                            <?= $ad['end_date'] ?: '-' ?>
                        </td>

                        <td>
                            <div class="actions-group">

                                <!-- EDIT — PRODUCT STYLE -->
                                <a href="edit.php?id=<?= $ad['ad_id'] ?>"
                                   class="btn-action btn-edit noselect">
                                    <span class="text">Edit</span>
                                    <span class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                             width="24" height="24"
                                             viewBox="0 0 24 24"
                                             fill="none" stroke="currentColor"
                                             stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                    </span>
                                </a>

                                <!-- DELETE — PRODUCT STYLE -->
                                <a href="index.php?delete=<?= $ad['ad_id'] ?>"
                                   class="btn-action btn-delete noselect"
                                   onclick="return confirm('Delete this advertisement?')">
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
