<?php
require_once __DIR__ . '/../config/config.php';

$db = getCampaignDB();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

try {
    $db->beginTransaction();

    /* ======================
       INSERT SUBMISSION
    ====================== */
    $stmt = $db->prepare("
        INSERT INTO submissions 
        (campaign_id, full_name, email, phone_number, submitted_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $_POST['campaign_id'],
        trim($_POST['full_name']),
        trim($_POST['email']),
        trim($_POST['phone_number'])
    ]);

    $submission_id = $db->lastInsertId();

    /* ======================
       INSERT ANSWERS
    ====================== */
    if (!empty($_POST['answers'])) {
        $stmtA = $db->prepare("
            INSERT INTO submission_answers
            (submission_id, question_id, answer_text)
            VALUES (?, ?, ?)
        ");

        foreach ($_POST['answers'] as $qid => $ans) {
            $stmtA->execute([$submission_id, $qid, trim($ans)]);
        }
    }

    /* ======================
       HANDLE FILE UPLOADS
    ====================== */
    if (!empty($_FILES['media'])) {
        $stmtF = $db->prepare("
            INSERT INTO submission_answers
            (submission_id, question_id, answer_file)
            VALUES (?, ?, ?)
        ");

        foreach ($_FILES['media']['name'] as $qid => $name) {
            if (!$name) continue;

            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $fileName = "submission_{$submission_id}_{$qid}_" . time() . "." . $ext;
            $path = "uploads/submissions/" . $fileName;

            if (move_uploaded_file($_FILES['media']['tmp_name'][$qid], "../" . $path)) {
                $stmtF->execute([$submission_id, $qid, $path]);
            }
        }
    }

    $db->commit();

    /* ======================
       REDIRECT WITH SUCCESS
    ====================== */
    header("Location: index.php?success=1");
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    die("Submission failed: " . $e->getMessage());
}
