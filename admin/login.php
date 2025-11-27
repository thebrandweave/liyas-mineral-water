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

<!-- Favicon -->
<link rel="icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
<link rel="shortcut icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
<link rel="apple-touch-icon" href="../assets/images/logo/logo-bg.jpg">
<link rel="icon" type="image/jpeg" sizes="32x32" href="../assets/images/logo/logo-bg.jpg">
<link rel="icon" type="image/jpeg" sizes="16x16" href="../assets/images/logo/logo-bg.jpg">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/new-login.css">
</head>
<body>
<main class="main">
	<div class="container">
		<section class="wrapper">
			<div class="heading">
				<h1 class="text text-large">Sign In</h1>
				<p class="text text-normal">New user? <span><a href="signup.php" class="text text-links">Create an account</a></span>
				</p>
			</div>

      <?php if ($error): ?>
        <div class="alert alert-error" style="margin-top: 1rem;"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

			<form name="signin" class="form" method="post">
				<div class="input-control">
					<input type="email" name="email" id="email" class="input-field" placeholder="Email Address" required autofocus>
				</div>
				<div class="input-control">
					<input type="password" name="password" id="password" class="input-field" placeholder="Password" required>
				</div>
				<div class="input-control">
					<input type="submit" name="submit" class="input-submit" value="Sign In">
				</div>
			</form>
		</section>
	</div>
</main>
</body>
</html>
