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
    SELECT media_url
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
<title>Entry Details</title>

<link rel="stylesheet" href="../assets/css/prody-admin.css">

<style>
.section{
    background:#fff;
    border-radius:18px;
    padding:24px;
    margin-bottom:22px;
    border:1px solid #e5e7eb;
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.header h2{
    margin:0;
}
.meta{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-top:18px;
}
.label{
    font-size:12px;
    color:#64748b;
}
.value{
    font-size:15px;
    font-weight:600;
}
.answer{
    margin-bottom:18px;
}
.media img{
    width:180px;
    border-radius:14px;
    margin-right:10px;
    margin-bottom:10px;
}
.back-btn{
    display:inline-block;
    margin-top:10px;
    color:#0ea5e9;
    font-weight:600;
    text-decoration:none;
}
</style>
</head>

<body>

<?php include '../includes/sidebar.php'; ?>

<div class="main-content">

    <!-- HEADER -->
    <div class="section header">
        <div>
            <h2><?= htmlspecialchars($entry['full_name']) ?></h2>
            <p style="color:#64748b;font-size:13px;margin-top:4px">
                <?= htmlspecialchars($entry['campaign_title']) ?>
            </p>
        </div>
    </div>

    <!-- META -->
    <div class="section">
        <div class="meta">
            <div>
                <div class="label">Email</div>
                <div class="value"><?= htmlspecialchars($entry['email']) ?></div>
            </div>
            <div>
                <div class="label">Phone</div>
                <div class="value"><?= htmlspecialchars($entry['phone_number']) ?></div>
            </div>
            <div>
                <div class="label">Submitted On</div>
                <div class="value"><?= date('d M Y, h:i A', strtotime($entry['submitted_at'])) ?></div>
            </div>
        </div>
    </div>

    <!-- ANSWERS -->
    <div class="section">
        <h3>Responses</h3>
        <?php if(!$answers): ?>
            <p>No answers submitted.</p>
        <?php endif; ?>
        <?php foreach($answers as $a): ?>
            <div class="answer">
                <div class="label"><?= htmlspecialchars($a['question_label']) ?></div>
                <div class="value"><?= nl2br(htmlspecialchars($a['answer_value'])) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- MEDIA -->
<!-- MEDIA -->
<?php if($media): ?>
<div class="section">
    <h3>Uploaded Media</h3>
    <div class="media">
        <?php foreach($media as $m): ?>
            <?php if(str_ends_with(strtolower($m['media_url']), '.mp4')): ?>
                <video controls width="220" style="border-radius:14px;margin:10px;display:block">
                    <source src="../../<?= htmlspecialchars($m['media_url']) ?>">
                    Your browser does not support video.
                </video>
            <?php else: ?>
                <img src="../../<?= htmlspecialchars($m['media_url']) ?>" 
                     style="width:180px;border-radius:14px;margin:10px;display:block" 
                     alt="Media">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>


    <a href="entries.php" class="back-btn">← Back to Entries</a>

</div>

</body>
</html>
