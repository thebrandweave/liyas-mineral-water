<?php
require_once '../config/config.php';

if (isset($_SESSION['jwt_token'])) {
    $stmt = $pdo->prepare("UPDATE admin_tokens SET is_valid = FALSE WHERE token = ?");
    $stmt->execute([$_SESSION['jwt_token']]);
}

session_unset();
session_destroy();
header("Location: login.php");
exit;
?>
