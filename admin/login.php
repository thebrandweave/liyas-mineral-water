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
<title>Admin Login</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<style>
body {
  font-family: Arial, sans-serif;
  background: #f4f6f8;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  margin: 0;
}
.login-box {
  background: #fff;
  padding: 30px 40px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  width: 340px;
  text-align: center;
}
.login-box h2 {
  margin-bottom: 20px;
  color: #333;
}
.login-box input {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-sizing: border-box;
  font-size: 14px;
}
.login-box input:focus {
  outline: none;
  border-color: #007bff;
}
.login-box button {
  width: 100%;
  padding: 10px;
  background: #007bff;
  border: none;
  color: #fff;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  font-size: 14px;
  margin-top: 10px;
}
.login-box button:hover {
  background: #0056b3;
}
.message {
  margin-top: 15px;
  font-size: 14px;
}
.error {
  color: red;
  background: #ffe6e6;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 10px;
}
.message a {
  color: #007bff;
  text-decoration: none;
}
.message a:hover {
  text-decoration: underline;
}
</style>
</head>
<body>
<div class="login-box">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email" required autofocus><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>

    <div class="message">
        Don't have an account? <a href="signup.php">Sign Up</a>
    </div>
</div>
</body>
</html>
