<?php
session_start();
require_once __DIR__ . '/../config/config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$db = getCampaignDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

try {

    $campaign_id = (int)($_POST['campaign_id'] ?? 0);
    if ($campaign_id <= 0) {
        throw new Exception("Invalid campaign");
    }

    $email = trim($_POST['email'] ?? '');

    /* =========================
       DUPLICATE CHECK
    ========================= */
    $check = $db->prepare("
        SELECT id FROM submissions
        WHERE campaign_id = ? AND email = ?
        LIMIT 1
    ");
    $check->execute([$campaign_id, $email]);

    if ($check->fetch()) {
        throw new Exception("You have already submitted this campaign.");
    }

    /* =========================
       BEGIN TRANSACTION
    ========================= */
    $db->beginTransaction();

    /* =========================
       INSERT SUBMISSION
    ========================= */
    $stmt = $db->prepare("
        INSERT INTO submissions
        (campaign_id, full_name, email, phone_number)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->execute([
        $campaign_id,
        trim($_POST['full_name']),
        $email,
        trim($_POST['phone_number'])
    ]);

    $submission_id = $db->lastInsertId();

    /* =========================
       INSERT ANSWERS
    ========================= */
    if (!empty($_POST['answers'])) {
        $stmtAns = $db->prepare("
            INSERT INTO submission_answers
            (submission_id, question_id, answer_value)
            VALUES (?, ?, ?)
        ");

        foreach ($_POST['answers'] as $qid => $value) {
            $stmtAns->execute([
                $submission_id,
                (int)$qid,
                trim($value)
            ]);
        }
    }

    /* =========================
       INSERT MEDIA
    ========================= */
    if (!empty($_FILES['media']['name'])) {

        $uploadDir = dirname(__DIR__) . '/uploads/submissions/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $stmtMedia = $db->prepare("
            INSERT INTO submission_media
            (submission_id, question_id, media_url, media_type)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($_FILES['media']['name'] as $qid => $name) {

            if (!$name || $_FILES['media']['error'][$qid] !== UPLOAD_ERR_OK) continue;

            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $mime = $_FILES['media']['type'][$qid];
            $type = (strpos($mime, 'video') === 0) ? 'video' : 'image';

            $fileName = "sub_{$submission_id}_{$qid}_" . time() . "." . $ext;
            $relativePath = "uploads/submissions/" . $fileName;

            move_uploaded_file(
                $_FILES['media']['tmp_name'][$qid],
                $uploadDir . $fileName
            );

            $stmtMedia->execute([
                $submission_id,
                (int)$qid,
                $relativePath,
                $type
            ]);
        }
    }

    /* =========================
       COMMIT
    ========================= */
    $db->commit();

    $_SESSION['submission_success'] = true;
    header("Location: index.php");
    exit;

} catch (Exception $e) {

    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo "<h2 style='color:red'>Submission failed</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
