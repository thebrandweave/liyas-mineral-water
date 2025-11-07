<?php
require_once '../config/config.php';

if (isset($_SESSION['jwt_token'])) {
    $stmt = $pdo->prepare("DELETE FROM admin_tokens WHERE token = ?");
    $stmt->execute([$_SESSION['jwt_token']]);
}

// Kill session completely
session_unset();
session_destroy();

// Optional: expire session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: login.php");
exit;
?>
