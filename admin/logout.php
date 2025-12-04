<?php
require_once '../config/config.php';
require_once 'includes/activity_logger.php';

// Get admin info before destroying session
$admin_id = $_SESSION['admin_id'] ?? null;
$admin_name = $_SESSION['admin_name'] ?? null;

if (isset($_SESSION['jwt_token'])) {
    $stmt = $pdo->prepare("DELETE FROM admin_tokens WHERE token = ?");
    $stmt->execute([$_SESSION['jwt_token']]);
}

// Log activity before destroying session
if ($admin_id && $admin_name) {
    logActivity($pdo, $admin_id, $admin_name, 'logout', null, null, "Admin logged out");
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
