<?php
require_once '../config/config.php';

// Import JWT classes
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Redirect if already logged in
if (isset($_SESSION['admin_id']) && isset($_SESSION['jwt_token'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Email and password are required.";
    } else {
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password_hash'])) {
            // Generate JWT token
            $payload = [
                'admin_id' => $admin['admin_id'],
                'email' => $admin['email'],
                'iat' => time(),
                'exp' => time() + $JWT_EXPIRE
            ];
            
            $token = JWT::encode($payload, $JWT_SECRET, 'HS256');

            // Store token in database
            $tokenStmt = $pdo->prepare("INSERT INTO admin_tokens (admin_id, token, is_valid) VALUES (?, ?, TRUE)");
            $tokenStmt->execute([$admin['admin_id'], $token]);

            // Set session variables
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['jwt_token'] = $token;
            $_SESSION['admin_name'] = $admin['username'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role'] = $admin['role'];

            // Redirect to dashboard
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Liyas Mineral Water</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
<div class="login-wrapper">
  <div class="login-container">
    <div class="login-header">
      <div class="login-logo">
        <i class="fas fa-tint"></i>
      </div>
      <h1 class="login-title">Admin Login</h1>
      <p class="login-subtitle">Sign in to access your admin panel</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="login-form">
      <div class="form-group">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required autofocus>
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>

      <button type="submit" class="login-btn">Sign In</button>
    </form>

    <div class="login-footer">
      <p class="login-footer-text">
        Don't have an account? <a href="signup.php" class="login-footer-link">Sign Up</a>
      </p>
    </div>
  </div>
</div>
</body>
</html>
