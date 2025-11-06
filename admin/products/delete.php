<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$id = $_GET['id'] ?? 0;

// Delete all media files first
$stmt = $pdo->prepare("SELECT file_path FROM products_media WHERE product_id=?");
$stmt->execute([$id]);
$files = $stmt->fetchAll();

foreach ($files as $f) {
    $path = $ROOT_PATH . '/uploads/' . $f['file_path'];
    if (file_exists($path)) unlink($path);
}

// Delete product and media records
$pdo->prepare("DELETE FROM products_media WHERE product_id=?")->execute([$id]);
$pdo->prepare("DELETE FROM products WHERE product_id=?")->execute([$id]);

header("Location: index.php");
exit;
?>
