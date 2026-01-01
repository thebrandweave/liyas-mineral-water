<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$db = getCampaignDB();
$campaign_id = (int)$_POST['campaign_id'];
$full_name = trim($_POST['full_name']);
$email = trim($_POST['email']);
$phone_number = trim($_POST['phone_number']);

try {
    $db->beginTransaction();

    // 1. Insert submission
    $stmt = $db->prepare("
        INSERT INTO submissions (campaign_id, full_name, email, phone_number)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bindParam(1, $campaign_id, PDO::PARAM_INT);
    $stmt->bindParam(2, $full_name, PDO::PARAM_STR);
    $stmt->bindParam(3, $email, PDO::PARAM_STR);
    $stmt->bindParam(4, $phone_number, PDO::PARAM_STR);
    $stmt->execute();
    $submission_id = $db->lastInsertId();

    // 2. Save answers
    if (isset($_POST['answers']) && is_array($_POST['answers'])) {
        foreach ($_POST['answers'] as $q_id => $answer) {
            if (!empty(trim($answer))) {
                $stmt = $db->prepare("
                    INSERT INTO submission_answers (submission_id, question_id, answer_value)
                    VALUES (?, ?, ?)
                ");
                $stmt->bindParam(1, $submission_id, PDO::PARAM_INT);
                $stmt->bindParam(2, $q_id, PDO::PARAM_INT);
                $stmt->bindParam(3, $answer, PDO::PARAM_STR);
                $stmt->execute();
            }
        }
    }

    // 3. Handle file uploads
    if (isset($_FILES['media']) && is_array($_FILES['media']['tmp_name'])) {
        $upload_dir = '../uploads/submissions/' . $submission_id . '/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['media']['tmp_name'] as $q_id => $tmp_name_array) {
            $tmp_name = $tmp_name_array[0] ?? '';
            $orig_name = $_FILES['media']['name'][$q_id][0] ?? '';
            
            if (!empty($tmp_name) && $orig_name) {
                $file_extension = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','mp4','mov','avi'];
                if (!in_array($file_extension, $allowed)) {
                    continue; // Skip invalid files
                }
                
                $file_name = time() . '_' . uniqid() . '.' . $file_extension;
                $target = $upload_dir . $file_name;
                $media_url = 'uploads/submissions/' . $submission_id . '/' . $file_name;

                if (move_uploaded_file($tmp_name, $target)) {
                    $media_type = in_array($file_extension, ['mp4','mov','avi']) ? 'video' : 'image';
                    $stmt = $db->prepare("
                        INSERT INTO submission_media (submission_id, question_id, media_url, media_type)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->bindParam(1, $submission_id, PDO::PARAM_INT);
                    $stmt->bindParam(2, $q_id, PDO::PARAM_INT);
                    $stmt->bindParam(3, $media_url, PDO::PARAM_STR);
                    $stmt->bindParam(4, $media_type, PDO::PARAM_STR);
                    $stmt->execute();
                }
            }
        }
    }

    $db->commit();
    header("Location: success.php");
    exit;
} catch (Exception $e) {
    $db->rollBack();
    error_log("Submit error: " . $e->getMessage());
    http_response_code(500);
    die("Submission failed. Please try again. Check server logs.");
}
?>
