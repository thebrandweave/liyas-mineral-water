<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = getCampaignDB();
        $db->beginTransaction();

        // 1. Insert Campaign
        $stmt = $db->prepare("INSERT INTO campaigns (title, slug, status, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['title'], $_POST['slug'], $_POST['status'], $_POST['start_date'],
            !empty($_POST['end_date']) ? $_POST['end_date'] : null, $_SESSION['admin_id'] ?? 1
        ]);
        $campaign_id = $db->lastInsertId();

        // 2. Handle Asset Upload
        if (!empty($_FILES['poster']['name'])) {
            $target_dir = "../../uploads/campaigns/";
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
            $file_ext = strtolower(pathinfo($_FILES["poster"]["name"], PATHINFO_EXTENSION));
            $rel_path = "uploads/campaigns/poster_" . $campaign_id . "_" . time() . "." . $file_ext;
            if (move_uploaded_file($_FILES["poster"]["tmp_name"], "../../" . $rel_path)) {
                $db->prepare("INSERT INTO campaign_assets (campaign_id, file_name, file_path, file_type) VALUES (?, ?, ?, ?)")
                   ->execute([$campaign_id, $_FILES['poster']['name'], $rel_path, ($file_ext=='pdf'?'pdf':'image')]);
            }
        }

        // 3. Dynamic Questions
        if (!empty($_POST['questions'])) {
            $stmtQ = $db->prepare("INSERT INTO campaign_questions (campaign_id, question_label, field_type, is_required, sort_order) VALUES (?, ?, ?, ?, ?)");
            foreach ($_POST['questions'] as $i => $q) {
                $stmtQ->execute([$campaign_id, $q['label'], $q['type'], isset($q['required'])?1:0, $i]);
            }
        }
        $db->commit();
        header("Location: index.php"); exit();
    } catch (Exception $e) { if(isset($db)) $db->rollBack(); $error = $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Campaign - Liyas Admin</title>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .form-card { background: #fff; padding: 2rem; border-radius: 20px; border: 1px solid var(--border-light); }
        .input-group { margin-bottom: 1.5rem; }
        .input-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 14px; }
        .input-group input, .input-group select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; }
        .question-row { background: #f8fafc; padding: 1.2rem; border-radius: 12px; margin-bottom: 1rem; border: 1px solid #edf2f7; }
    </style>
</head>
<body>
    <div class="container">
        <?php include '../includes/sidebar.php'; ?>
        <div class="main-content">
            <div class="header"><div class="breadcrumb"><span>Campaigns / New</span></div></div>
            <div class="content-area">
                <form method="POST" enctype="multipart/form-data" class="form-card">
                    <h2 style="margin-bottom: 1.5rem;">Campaign & Poster</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="input-group"><label>Title</label><input type="text" name="title" required></div>
                        <div class="input-group"><label>Poster (Image/PDF)</label><input type="file" name="poster" accept=".jpg,.jpeg,.png,.pdf"></div>
                        <div class="input-group"><label>URL Slug</label><input type="text" name="slug" required></div>
                        <div class="input-group"><label>Start Date</label><input type="date" name="start_date" value="<?=date('Y-m-d')?>" required></div>
                        <div class="input-group"><label>End Date</label><input type="date" name="end_date"></div>
                        <div class="input-group"><label>Status</label>
                            <select name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                        </div>
                    </div>
                    <hr style="margin:2rem 0; opacity:0.1;">
                    <h2>Form Builder</h2>
                    <div id="q-container"></div>
                    <button type="button" class="table-btn" onclick="addQ()" style="background:#f1f5f9; color:#475569;">+ Add Field</button>
                    <div style="text-align:right; margin-top:2rem;"><button type="submit" class="table-btn" style="background:var(--blue); color:white;">Save Campaign</button></div>
                </form>
            </div>
        </div>
    </div>
    <script>
// Replace your existing addQ() function with this
let count = 0; 
function addQ() {
    const html = `<div class="question-row" id="row_${count}">
        <div style="display:flex; gap:10px; align-items:center;">
            <input type="text" name="questions[${count}][label]" placeholder="Field Name (e.g. Upload your Receipt)" required style="flex:2; padding:8px; border-radius:6px; border:1px solid #ddd;">
            <select name="questions[${count}][type]" style="flex:1; padding:8px; border-radius:6px; border:1px solid #ddd;">
                <option value="text">Text Input</option>
                <option value="number">Number</option>
                <option value="dropdown">Dropdown Menu</option>
                <option value="image_upload">Image (Drag & Drop)</option>
                <option value="video_upload">Video (Drag & Drop)</option>
            </select>
            <label style="font-size:12px;"><input type="checkbox" name="questions[${count}][required]"> Required</label>
            <button type="button" onclick="document.getElementById('row_${count}').remove()" style="color:red; background:none; border:none; cursor:pointer;"><i class='bx bx-trash'></i></button>
        </div>
    </div>`;
    document.getElementById('q-container').insertAdjacentHTML('beforeend', html); 
    count++;
}
        window.onload = addQ;
    </script>
</body>
</html>