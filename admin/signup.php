<?php
require_once '../config/config.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basic validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email already exists
        $check = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "Email already registered. Please login.";
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert into admins table
            $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, role) VALUES (?, ?, ?, 'editor')");
            $stmt->execute([$username, $email, $hashedPassword]);

            $success = "Signup successful! You can now <a href='login.php'>login</a>.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Signup</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<style>
body {
  font-family: Arial, sans-serif;
  background: #f4f6f8;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
}
.signup-box {
  background: #fff;
  padding: 30px 40px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  width: 340px;
  text-align: center;
}
.signup-box h2 {
  margin-bottom: 20px;
  color: #333;
}
.signup-box input {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: 1px solid #ccc;
  border-radius: 6px;
}
.signup-box button {
  width: 100%;
  padding: 10px;
  background: #007bff;
  border: none;
  color: #fff;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}
.signup-box button:hover {
  background: #0056b3;
}
.message {
  margin-top: 10px;
  font-size: 14px;
}
.error { color: red; }
.success { color: green; }
</style>
</head>
<body>
<div class="signup-box">
    <h2>Admin Signup</h2>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <?php if ($success): ?>
      <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required><br>
        <button type="submit">Sign Up</button>
    </form>

    <div class="message">
        Already have an account? <a href="login.php">Login</a>
    </div>
</div>
</body>
</html>
