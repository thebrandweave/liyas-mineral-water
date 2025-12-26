<?php
require_once '../config/config.php';

$db = getCampaignDB();
$slug = $_GET['slug'] ?? null;
$today = date('Y-m-d');

if (!$slug) {
    die("Invalid campaign.");
}

/* Fetch campaign */
$stmt = $db->prepare("
    SELECT c.*, ca.file_path, ca.file_type
    FROM campaigns c
    LEFT JOIN campaign_assets ca ON ca.campaign_id = c.id
    WHERE c.slug = ?
      AND c.status = 'active'
      AND c.start_date <= ?
      AND (c.end_date IS NULL OR c.end_date >= ?)
    LIMIT 1
");
$stmt->execute([$slug, $today, $today]);
$campaign = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$campaign) {
    die("<h3 style='text-align:center;margin-top:50px;'>Campaign not available.</h3>");
}

/* Fetch questions */
$stmtQ = $db->prepare("
    SELECT * FROM campaign_questions
    WHERE campaign_id = ?
    ORDER BY sort_order ASC
");
$stmtQ->execute([$campaign['id']]);
$questions = $stmtQ->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($campaign['title']) ?> | Liyas</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

<style>
body {
    font-family: Poppins, sans-serif;
    background:#f8fafc;
    margin:0;
}
.container {
    max-width:700px;
    margin:30px auto;
    background:#fff;
    border-radius:16px;
    padding:24px;
    box-shadow:0 10px 30px rgba(0,0,0,.06);
}
h1 { margin-top:0; font-size:22px; }
.poster img {
    width:100%;
    border-radius:12px;
    margin-bottom:20px;
}
.field { margin-bottom:16px; }
label {
    display:block;
    font-size:14px;
    margin-bottom:6px;
}
input, select {
    width:100%;
    padding:10px;
    border-radius:8px;
    border:1px solid #cbd5e1;
}
button {
    width:100%;
    padding:12px;
    background:#2563eb;
    color:#fff;
    border:none;
    border-radius:10px;
    font-size:16px;
    cursor:pointer;
}
.required { color:red; font-size:12px; }
</style>
</head>

<body>

<div class="container">
    <h1><?= htmlspecialchars($campaign['title']) ?></h1>

    <?php if($campaign['file_path'] && $campaign['file_type'] === 'image'): ?>
        <div class="poster">
            <img src="../<?= htmlspecialchars($campaign['file_path']) ?>">
        </div>
    <?php endif; ?>

    <form method="POST" action="submit.php" enctype="multipart/form-data">
        <input type="hidden" name="campaign_id" value="<?= $campaign['id'] ?>">

        <!-- Basic fields -->
        <div class="field">
            <label>Full Name <span class="required">*</span></label>
            <input type="text" name="full_name" required>
        </div>

        <div class="field">
            <label>Email <span class="required">*</span></label>
            <input type="email" name="email" required>
        </div>

        <div class="field">
            <label>Phone Number <span class="required">*</span></label>
            <input type="text" name="phone_number" required>
        </div>

        <!-- Dynamic Questions -->
        <?php foreach($questions as $q): ?>
            <div class="field">
                <label>
                    <?= htmlspecialchars($q['question_label']) ?>
                    <?= $q['is_required'] ? '<span class="required">*</span>' : '' ?>
                </label>

                <?php if(in_array($q['field_type'], ['text','number'])): ?>
                    <input type="<?= $q['field_type'] ?>"
                           name="answers[<?= $q['id'] ?>]"
                           <?= $q['is_required'] ? 'required' : '' ?>>

                <?php elseif(in_array($q['field_type'], ['image_upload','video_upload'])): ?>
                    <input type="file"
                           name="media[<?= $q['id'] ?>]"
                           accept="<?= $q['field_type']=='image_upload'?'image/*':'video/*' ?>"
                           <?= $q['is_required'] ? 'required' : '' ?>>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <button type="submit">Submit Entry</button>
    </form>
</div>

</body>
</html>
