<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$media_id = $_GET['id'] ?? 0;
$product_id = $_GET['product_id'] ?? 0;

if (!$media_id || !$product_id) die("Invalid request");

// Fetch file path
$stmt = $pdo->prepare("SELECT file_path FROM products_media WHERE media_id=?");
$stmt->execute([$media_id]);
$media = $stmt->fetch();

if ($media) {
    $filePath = $ROOT_PATH . '/uploads/' . $media['file_path'];

    // Delete record
    $pdo->prepare("DELETE FROM products_media WHERE media_id=?")->execute([$media_id]);

    // Delete actual file
    if (file_exists($filePath)) unlink($filePath);
}

header("Location: edit.php?id=" . $product_id);
exit;
?>
