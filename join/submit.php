<?php
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$db = getCampaignDB();

$campaign_id  = (int)$_POST['campaign_id'];
$full_name    = trim($_POST['full_name']);
$email        = trim($_POST['email']);
$phone_number = trim($_POST['phone_number']);

$db->beginTransaction();

$stmt = $db->prepare("
    INSERT INTO submissions (campaign_id, full_name, email, phone_number)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$campaign_id,$full_name,$email,$phone_number]);

$db->commit();

header("Location: success.php");
exit;
