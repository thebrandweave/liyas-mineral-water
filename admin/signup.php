<?php
require_once '../config/config.php';
require_once 'includes/activity_logger.php';

// Import JWT (not used here, but kept for consistency)
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// OPTIONAL: restrict signup (recommended)
// Uncomment this AFTER first admin is created
/*
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'superadmin') {
    die("Unauthorized access.");
}
*/

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $cpass    = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? 'editor';

    // ================= VALIDATION =================
    if (!$username || !$email || !$password || !$cpass) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $cpass) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check duplicate admin
        $check = $pdo->prepare(
            "SELECT admin_id FROM admins WHERE email = ? OR username = ?"
        );
        $check->execute([$email, $username]);

        if ($check->fetch()) {
            $error = "Admin with this email or username already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare(
                "INSERT INTO admins (username, email, password_hash, role)
                 VALUES (?, ?, ?, ?)"
            );

            if ($insert->execute([$username, $email, $hash, $role])) {
                $newAdminId = $pdo->lastInsertId();

                // Log activity
                logActivity(
                    $pdo,
                    $newAdminId,
                    $username,
                    'signup',
                    null,
                    null,
                    'New admin account created'
                );

                $success = "Admin account created successfully. You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Signup - Liyas Mineral Water</title>

<link rel="icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<link rel="stylesheet" href="assets/css/prody-admin.css">
</head>
<body>

<div class="login-container">
  <div class="login-card">

    <div class="login-logo">
      <div class="login-logo-icon" style="background: transparent; padding: 0;">
        <img src="../assets/images/logo/logo-bg.jpg"
             alt="Liyas Logo"
             style="width:48px;height:48px;border-radius:12px;">
      </div>
      <h1 class="login-title">Liyas</h1>
      <p class="login-subtitle">Admin Signup</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="form-modern">

      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" class="form-input"
               placeholder="Admin username" required>
      </div>

      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-input"
               placeholder="Admin email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" class="form-input"
               placeholder="Create password" required>
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" class="form-input"
               placeholder="Confirm password" required>
      </div>

      <div class="form-group">
        <label>Role</label>
        <select name="role" class="form-input">
          <option value="editor">Editor</option>
          <option value="moderator">Moderator</option>
          <option value="superadmin">Super Admin</option>
        </select>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" style="width:100%;">
          <i class='bx bx-user-plus'></i> Create Admin
        </button>
      </div>

      <div style="text-align:center;margin-top:1rem;">
        <p style="font-size:14px;">
          Already have an account?
          <a href="login.php" style="color:var(--blue);text-decoration:none;">
            Sign in
          </a>
        </p>
      </div>

    </form>
  </div>
</div>

</body>
</html>
