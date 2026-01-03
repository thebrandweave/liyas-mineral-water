<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$db = getCampaignDB();
$id = $_GET['id'] ?? 0;

/* Basic info */
$stmt = $db->prepare("
    SELECT full_name, email, phone_number, submitted_at
    FROM submissions WHERE id=?
");
$stmt->execute([$id]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

/* Answers */
$ans = $db->prepare("
    SELECT q.question_label, a.answer_value
    FROM submission_answers a
    JOIN campaign_questions q ON q.id = a.question_id
    WHERE a.submission_id=?
");
$ans->execute([$id]);

/* Media */
$media = $db->prepare("
    SELECT q.question_label, m.media_url, m.media_type
    FROM submission_media m
    JOIN campaign_questions q ON q.id = m.question_id
    WHERE m.submission_id=?
");
$media->execute([$id]);
?>

<div class="detail-group"><b>Name:</b> <?= htmlspecialchars($s['full_name']) ?></div>
<div class="detail-group"><b>Email:</b> <?= htmlspecialchars($s['email']) ?></div>

<?php foreach ($ans as $a): ?>
<div class="detail-group">
    <div class="detail-label"><?= htmlspecialchars($a['question_label']) ?></div>
    <div class="detail-value"><?= htmlspecialchars($a['answer_value']) ?></div>
</div>
<?php endforeach; ?>

<?php foreach ($media as $m): ?>
<div class="detail-group">
    <div class="detail-label"><?= htmlspecialchars($m['question_label']) ?></div>
    <?php if ($m['media_type'] === 'image'): ?>
        <img src="../../<?= $m['media_url'] ?>" class="media-preview">
    <?php else: ?>
        <video controls class="media-preview">
            <source src="../../<?= $m['media_url'] ?>">
        </video>
    <?php endif; ?>
</div>
<?php endforeach; ?>
