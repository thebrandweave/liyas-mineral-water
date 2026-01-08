<?php
require_once '../../config/config.php';

$id = (int)($_GET['id'] ?? 0);
if(!$id) {
    http_response_code(400);
    exit('Invalid ID');
}

$db = getCampaignDB();

try {
    // Basic entry + campaign
    $stmt = $db->prepare("
        SELECT s.*, c.title AS campaign_title
        FROM submissions s
        JOIN campaigns c ON c.id = s.campaign_id
        WHERE s.id=?
    ");
    $stmt->bindParam(1, $id, PDO::PARAM_INT);
    $stmt->execute();
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$entry) {
        http_response_code(404);
        exit('Entry not found');
    }

    // Answers
    $answers = $db->prepare("
        SELECT q.question_label, a.answer_value
        FROM submission_answers a
        JOIN campaign_questions q ON q.id = a.question_id
        WHERE a.submission_id=?
        ORDER BY q.sort_order
    ");
    $answers->bindParam(1, $id, PDO::PARAM_INT);
    $answers->execute();
    $answers = $answers->fetchAll(PDO::FETCH_ASSOC);

    // Media
    $media = $db->prepare("
        SELECT media_url, media_type
        FROM submission_media
        WHERE submission_id=?
    ");
    $media->bindParam(1, $id, PDO::PARAM_INT);
    $media->execute();
    $media = $media->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    http_response_code(500);
    exit('Database error');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Entry #<?= $id ?></title>
    <style>
        .header { display: grid; grid-template-columns: 1fr auto; gap: 24px; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1px solid #e5e7eb; }
        .name { font-size: 24px; font-weight: 700; color: #111; }
        .campaign { color: #666; font-size: 14px; }
        .submitted { text-align: right; }
        .submitted div:first-child { color: #666; font-size: 12px; }
        .field { margin-bottom: 20px; }
        .label { font-size: 13px; color: #666; margin-bottom: 4px; font-weight: 500; }
        .value { font-size: 16px; color: #111; }
        .answers h3 { margin: 32px 0 16px 0; font-size: 18px; color: #111; }
        .media { margin-top: 20px; }
        .media img, .media video { max-width: 100%; height: auto; border-radius: 12px; margin: 8px 0; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="name"><?= htmlspecialchars($entry['full_name']) ?></div>
            <div class="campaign"><?= htmlspecialchars($entry['campaign_title']) ?></div>
        </div>
        <div class="submitted">
            <div>Submitted</div>
            <div style="font-weight: 600;"><?= date('M j, Y \\a\\t g:i A', strtotime($entry['submitted_at'])) ?></div>
        </div>
    </div>

    <div class="field">
        <div class="label">Email</div>
        <div class="value"><?= htmlspecialchars($entry['email']) ?></div>
    </div>

    <div class="field">
        <div class="label">Phone</div>
        <div class="value"><?= htmlspecialchars($entry['phone_number']) ?></div>
    </div>

    <?php if($answers): ?>
    <div class="answers">
        <h3>Responses</h3>
        <?php foreach($answers as $answer): ?>
        <div class="field">
            <div class="label"><?= htmlspecialchars($answer['question_label']) ?></div>
            <div class="value"><?= nl2br(htmlspecialchars($answer['answer_value'])) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if($media): ?>
    <div class="media">
        <h3>Uploaded Files</h3>
        <?php foreach($media as $file): ?>
            <?php if($file['media_type'] === 'video'): ?>
                <video controls preload="metadata">
                    <source src="../../<?= htmlspecialchars($file['media_url']) ?>" type="video/mp4">
                    Your browser does not support video playback.
                </video>
            <?php else: ?>
                <img src="../../<?= htmlspecialchars($file['media_url']) ?>" 
                     alt="Uploaded media" loading="lazy">
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</body>
</html>
