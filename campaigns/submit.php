<?php
require_once '../config/config.php';

$db = getCampaignDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

$campaign_id = (int)$_POST['campaign_id'];
$full_name   = trim($_POST['full_name']);
$email       = trim($_POST['email']);
$phone       = trim($_POST['phone_number']);

try {
    $db->beginTransaction();

    /* Insert submission */
    $stmt = $db->prepare("
        INSERT INTO submissions (campaign_id, full_name, email, phone_number)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$campaign_id, $full_name, $email, $phone]);
    $submission_id = $db->lastInsertId();

    /* Insert answers */
    if (!empty($_POST['answers'])) {
        $stmtA = $db->prepare("
            INSERT INTO submission_answers (submission_id, question_id, answer_value)
            VALUES (?, ?, ?)
        ");
        foreach ($_POST['answers'] as $qid => $value) {
            $stmtA->execute([$submission_id, $qid, trim($value)]);
        }
    }

    /* Handle media uploads */
    if (!empty($_FILES['media'])) {
        $upload_dir = "../uploads/submissions/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $stmtM = $db->prepare("
            INSERT INTO submission_media (submission_id, question_id, media_url, media_type)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($_FILES['media']['tmp_name'] as $qid => $tmp) {
            if (!$tmp) continue;

            $ext = pathinfo($_FILES['media']['name'][$qid], PATHINFO_EXTENSION);
            $filename = "media_{$submission_id}_{$qid}_" . time() . "." . $ext;
            $path = $upload_dir . $filename;

            move_uploaded_file($tmp, $path);

            $type = strpos($_FILES['media']['type'][$qid], 'video') !== false ? 'video' : 'image';

            $stmtM->execute([
                $submission_id,
                $qid,
                "uploads/submissions/" . $filename,
                $type
            ]);
        }
    }

    $db->commit();

    header("Location: ../thank-you.php");
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("Submission failed: " . $e->getMessage());
}
