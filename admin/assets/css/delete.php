<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    $_SESSION['error_message'] = "Invalid user ID.";
    header("Location: index.php");
    exit;
}

if ($user_id == $_SESSION['admin_id']) {
    $_SESSION['error_message'] = "You cannot delete your own account.";
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("DELETE FROM admins WHERE admin_id = ?");
$stmt->execute([$user_id]);

$_SESSION['success_message'] = "User deleted successfully.";
header("Location: index.php");
exit;
?>
```

### 5. Update Sidebar Links in All Main Pages

Finally, I'll update the `href` for the "Users" link in the sidebar on your main pages to point to the new `users/index.php` file.

**File to Edit: `c:\xampp\htdocs\liyas-mineral-water\admin\index.php`**
```diff
--- a/c:/xampp/htdocs/liyas-mineral-water/admin/index.php
+++ b/c:/xampp/htdocs/liyas-mineral-water/admin/index.php