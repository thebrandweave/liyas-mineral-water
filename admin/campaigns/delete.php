<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$db = getCampaignDB();
$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

try {
    $db->beginTransaction();

    // Temporarily disable FK checks (safe here)
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Delete related data
    $db->prepare("DELETE FROM submission_media 
                  WHERE submission_id IN (SELECT id FROM submissions WHERE campaign_id = ?)")
       ->execute([$id]);

    $db->prepare("DELETE FROM submission_answers 
                  WHERE submission_id IN (SELECT id FROM submissions WHERE campaign_id = ?)")
       ->execute([$id]);

    $db->prepare("DELETE FROM submissions WHERE campaign_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM campaign_questions WHERE campaign_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM campaign_assets WHERE campaign_id = ?")->execute([$id]);
    $db->prepare("DELETE FROM campaigns WHERE id = ?")->execute([$id]);

    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    $db->commit();

    // ✅ IMPORTANT: redirect back
    header("Location: index.php?deleted=1");
    exit;

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    die("Delete failed: " . $e->getMessage());
}
