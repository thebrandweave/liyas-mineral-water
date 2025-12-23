<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "advertisements";
$page_title   = "Edit Advertisement";

/* VALIDATE ID */
$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$ad_id) {
    header("Location: index.php");
    exit;
}

/* FETCH AD */
$stmt = $pdo->prepare("SELECT * FROM advertisements WHERE ad_id = ?");
$stmt->execute([$ad_id]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    header("Location: index.php");
    exit;
}

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title        = trim($_POST['title']);
    $position     = $_POST['position'];
    $status       = $_POST['status'];
    $redirect_url = trim($_POST['redirect_url']);
    $start_date   = $_POST['start_date'] ?: null;
    $end_date     = $_POST['end_date'] ?: null;

    $image_name = $ad['image'];

    /* IMAGE REPLACE */
    if (!empty($_FILES['image']['name'])) {
        if ($image_name && file_exists($upload_dir . $image_name)) {
            unlink($upload_dir . $image_name);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = 'ad_' . time() . '_' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
    }

    $stmt = $pdo->prepare("
        UPDATE advertisements SET
            title = ?,
            image = ?,
            redirect_url = ?,
            position = ?,
            start_date = ?,
            end_date = ?,
            status = ?
        WHERE ad_id = ?
    ");
    $stmt->execute([
        $title,
        $image_name,
        $redirect_url,
        $position,
        $start_date,
        $end_date,
        $status,
        $ad_id
    ]);

    quickLog($pdo, 'update', 'advertisement', $ad_id, "Updated advertisement: {$title}");
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Advertisement</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="../assets/css/prody-admin.css">

<style>
.preview-img{
    width:120px;
    height:120px;
    object-fit:cover;
    border-radius:10px;
    border:1px solid #e2e8f0;
    margin-bottom:10px;
}
</style>
</head>

<body>
<div class="container">

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<div class="content-area">

<div class="table-card">
<div class="table-header">
    <div class="table-title">Edit Advertisement</div>
</div>

<div style="padding:2rem">

<form method="POST" enctype="multipart/form-data" class="form-modern">

    <!-- TITLE -->
    <div class="form-group">
        <label>Title *</label>
        <input type="text" name="title" class="form-input"
               value="<?= htmlspecialchars($ad['title']) ?>" required>
    </div>

    <!-- POSITION -->
    <div class="form-group">
        <label>Position</label>
        <select name="position" class="form-select">
            <option value="home_top" <?= $ad['position']=='home_top'?'selected':'' ?>>Home Top (Banner)</option>
            <option value="home_middle" <?= $ad['position']=='home_middle'?'selected':'' ?>>Home Middle</option>
            <option value="home_bottom" <?= $ad['position']=='home_bottom'?'selected':'' ?>>Home Bottom</option>
            <option value="popup" <?= $ad['position']=='popup'?'selected':'' ?>>Popup</option>
            <option value="sidebar" <?= $ad['position']=='sidebar'?'selected':'' ?>>Sidebar</option>
        </select>
    </div>

    <!-- STATUS -->
    <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-select">
            <option value="active" <?= $ad['status']=='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= $ad['status']=='inactive'?'selected':'' ?>>Inactive</option>
        </select>
    </div>

    <!-- REDIRECT -->
    <div class="form-group">
        <label>Redirect URL</label>
        <input type="url" name="redirect_url" class="form-input"
               value="<?= htmlspecialchars($ad['redirect_url']) ?>">
    </div>

    <!-- IMAGE -->
    <div class="form-group">
        <label>Image</label><br>

        <?php if ($ad['image'] && file_exists($upload_dir.$ad['image'])): ?>
            <img src="uploads/<?= $ad['image'] ?>" class="preview-img">
        <?php endif; ?>

        <input type="file" name="image" class="form-input" accept="image/*">
        <small class="text-muted-custom">Leave empty to keep current image</small>
    </div>

    <!-- DATES -->
    <div class="grid-3">
        <div class="form-group">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-input"
                   value="<?= $ad['start_date'] ?>">
        </div>
        <div class="form-group">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-input"
                   value="<?= $ad['end_date'] ?>">
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="form-actions" style="margin-top:2rem">
        <button type="submit" class="btn-action btn-add">Update Advertisement</button>
        <a href="index.php" class="btn-action"
           style="background:#6c757d;color:#fff;text-decoration:none">
           Cancel
        </a>
    </div>

</form>

</div>
</div>

</div>
</div>
</div>
</body>
</html>
