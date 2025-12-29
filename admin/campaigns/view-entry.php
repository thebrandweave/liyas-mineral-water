<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$db = getCampaignDB();

$id = (int)($_GET['id'] ?? 0);
if(!$id) die("Invalid entry");

$stmt = $db->prepare("
    SELECT s.*, c.title AS campaign_title
    FROM submissions s
    JOIN campaigns c ON c.id = s.campaign_id
    WHERE s.id=?
");
$stmt->execute([$id]);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$entry) die("Entry not found");

/* Answers */
$answers = $db->prepare("
    SELECT q.question_label, a.answer_value
    FROM submission_answers a
    JOIN campaign_questions q ON q.id = a.question_id
    WHERE a.submission_id=?
");
$answers->execute([$id]);
$answers = $answers->fetchAll(PDO::FETCH_ASSOC);

/* Media */
$media = $db->prepare("
    SELECT media_url, media_type
    FROM submission_media
    WHERE submission_id=?
");
$media->execute([$id]);
$media = $media->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Entry</title>
<link rel="stylesheet" href="../assets/css/prody-admin.css">

<style>
.card{
    background:#fff;
    padding:25px;
    border-radius:18px;
    margin-bottom:25px;
}
.label{
    font-size:13px;
    color:#64748b;
}
.value{
    font-weight:600;
    margin-bottom:12px;
}
.media img{
    width:160px;
    border-radius:12px;
    margin-right:10px;
    margin-bottom:10px;
}
</style>
</head>

<body>
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">

<h2>Entry Details</h2>

<div class="card">
    <div class="label">Campaign</div>
    <div class="value"><?= htmlspecialchars($entry['campaign_title']) ?></div>

    <div class="label">Full Name</div>
    <div class="value"><?= htmlspecialchars($entry['full_name']) ?></div>

    <div class="label">Email</div>
    <div class="value"><?= htmlspecialchars($entry['email']) ?></div>

    <div class="label">Phone</div>
    <div class="value"><?= htmlspecialchars($entry['phone_number']) ?></div>

    <div class="label">Submitted On</div>
    <div class="value"><?= date('d M Y, h:i A', strtotime($entry['submitted_at'])) ?></div>
</div>

<div class="card">
    <h3>Answers</h3>
    <?php if(!$answers): ?>
        <p>No answers</p>
    <?php endif; ?>

    <?php foreach($answers as $a): ?>
        <div class="label"><?= htmlspecialchars($a['question_label']) ?></div>
        <div class="value"><?= nl2br(htmlspecialchars($a['answer_value'])) ?></div>
    <?php endforeach; ?>
</div>

<?php if($media): ?>
<div class="card">
    <h3>Uploaded Media</h3>
    <div class="media">
        <?php foreach($media as $m): ?>
            <img src="../../<?= htmlspecialchars($m['media_url']) ?>">
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<a href="entries.php" class="btn">← Back to Entries</a>

</div>
</body>
</html>
