<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$db = getCampaignDB();

/* =====================================================
   SHOW CREATE FORM (GET REQUEST)
===================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Campaign - Liyas Admin</title>

<link rel="stylesheet" href="../assets/css/prody-admin.css">
<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

<style>
.form-card {
    background:#fff;
    border:1px solid var(--border-light);
    border-radius:18px;
    padding:2rem;
    max-width:900px;
}
.form-grid {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}
label {
    font-size:13px;
    font-weight:500;
    color:#475569;
    margin-bottom:6px;
    display:block;
}
input, select {
    width:100%;
    padding:10px 12px;
    border-radius:8px;
    border:1px solid #e5e7eb;
}
.question-row {
    display:flex;
    gap:10px;
    align-items:center;
    margin-bottom:10px;
    background:#f8fafc;
    padding:10px;
    border-radius:10px;
}
.trash-btn {
    background:none;
    border:none;
    font-size:18px;
    cursor:pointer;
    color:#ef4444;
}
</style>
</head>

<body>
<div class="container">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<div class="header">
    <div class="breadcrumb">Campaigns / Create</div>
</div>

<div class="content-area">

<form method="POST" enctype="multipart/form-data" class="form-card">
<h2 style="margin-bottom:1.5rem;">Create New Campaign</h2>

<div class="form-grid">

    <div>
        <label>Campaign Title</label>
        <input type="text" name="title" required>
    </div>
    <div>
        <label> Description</label>
        <input type="text" name="description" required>
    </div>
    <div>
        <label>Slug</label>
        <input type="text" name="slug" required>
    </div>

    <div>
        <label>Start Date</label>
        <input type="date" name="start_date" required>
    </div>

    <div>
        <label>End Date</label>
        <input type="date" name="end_date">
    </div>

    <div>
        <label>Status</label>
        <select name="status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>

    <div>
        <label>Poster / PDF</label>
        <input type="file" name="poster" accept=".jpg,.jpeg,.png,.pdf">
    </div>

</div>

<hr style="margin:2rem 0;">

<h3>Form Fields</h3>
<div id="q-container"></div>

<button type="button" class="table-btn" onclick="addQ()">+ Add Field</button>

<div style="text-align:right;margin-top:2rem;">
    <button type="submit" class="table-btn" style="background:var(--blue);color:white;">
        Create Campaign
    </button>
</div>

</form>

</div>
</div>
</div>

<script>
let count = 0;
function addQ() {
    const html = `
    <div class="question-row" id="row_${count}">
        <input type="text" name="questions[${count}][label]" placeholder="Field label" required>
        <select name="questions[${count}][type]">
            <option value="text">Text</option>
            <option value="number">Number</option>
            <option value="dropdown">Dropdown</option>
            <option value="image_upload">Image Upload</option>
            <option value="video_upload">Video Upload</option>
        </select>
        <label style="font-size:12px;">
            <input type="checkbox" name="questions[${count}][required]"> Required
        </label>
        <button type="button" class="trash-btn" onclick="document.getElementById('row_${count}').remove()">🗑</button>
    </div>`;
    document.getElementById('q-container').insertAdjacentHTML('beforeend', html);
    count++;
}
</script>

</body>
</html>
<?php
exit;
}

/* =====================================================
   HANDLE CREATE (POST REQUEST)
===================================================== */
try {
    $db->beginTransaction();

    /* Enforce single campaign */
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    $db->exec("DELETE FROM submission_media");
    $db->exec("DELETE FROM submission_answers");
    $db->exec("DELETE FROM submissions");
    $db->exec("DELETE FROM campaign_questions");
    $db->exec("DELETE FROM campaign_assets");
    $db->exec("DELETE FROM campaigns");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    /* Insert Campaign */
    $stmt = $db->prepare("
        INSERT INTO campaigns (title,description, slug, status, start_date, end_date, created_by)
        VALUES (?,?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['title'],
        $_POST['description'],
        $_POST['slug'],
        $_POST['status'],
        $_POST['start_date'],
        !empty($_POST['end_date']) ? $_POST['end_date'] : null,
        $_SESSION['admin_id'] ?? 1
    ]);

    $campaign_id = $db->lastInsertId();

    /* Upload Asset */
    if (!empty($_FILES['poster']['name'])) {
        $dir = "../../uploads/campaigns/";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['poster']['name'], PATHINFO_EXTENSION));
        $rel = "uploads/campaigns/poster_{$campaign_id}_" . time() . "." . $ext;

        if (move_uploaded_file($_FILES['poster']['tmp_name'], "../../" . $rel)) {
            $db->prepare("
                INSERT INTO campaign_assets (campaign_id, file_name, file_path, file_type)
                VALUES (?, ?, ?, ?)
            ")->execute([
                $campaign_id,
                $_FILES['poster']['name'],
                $rel,
                ($ext === 'pdf' ? 'pdf' : 'image')
            ]);
        }
    }

    /* Insert Questions */
    if (!empty($_POST['questions'])) {
        $stmtQ = $db->prepare("
            INSERT INTO campaign_questions
            (campaign_id, question_label, field_type, is_required, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($_POST['questions'] as $i => $q) {
            $stmtQ->execute([
                $campaign_id,
                $q['label'],
                $q['type'],
                isset($q['required']) ? 1 : 0,
                $i
            ]);
        }
    }

    $db->commit();
    header("Location: index.php?created=1");
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("Create failed: " . $e->getMessage());
}
