<?php
require_once '../../config/config.php';

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT image FROM advertisements WHERE ad_id=?");
$stmt->execute([$id]);
$image = $stmt->fetchColumn();

if ($image && file_exists("uploads/$image")) {
    unlink("uploads/$image");
}

$pdo->prepare("DELETE FROM advertisements WHERE ad_id=?")->execute([$id]);

header("Location: index.php");
exit;
