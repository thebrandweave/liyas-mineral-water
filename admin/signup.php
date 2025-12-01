<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Basic validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check for existing username or email
        $stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            $error = "An account with this username or email already exists.";
        } else {
            // Hash password and insert new admin
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
            if ($insertStmt->execute([$username, $email, $password_hash])) {
                $success = "Account created successfully! You can now sign in.";
            } else {
                $error = "Failed to create account. Please try again.";
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

<!-- Favicon -->
<link rel="icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
<link rel="shortcut icon" type="image/jpeg" href="../assets/images/logo/logo-bg.jpg">
<link rel="apple-touch-icon" href="../assets/images/logo/logo-bg.jpg">
<link rel="icon" type="image/jpeg" sizes="32x32" href="../assets/images/logo/logo-bg.jpg">
<link rel="icon" type="image/jpeg" sizes="16x16" href="../assets/images/logo/logo-bg.jpg">

<link rel="preload" href="https://cal.com/fonts/CalSans-SemiBold.woff2" as="font" type="font/woff2" crossorigin>
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="assets/css/prody-admin.css">
</head>
<body>
<div class="login-container">
	<div class="login-card">
		<div class="login-logo">
			<div class="login-logo-icon" style="background: transparent; padding: 0;">
				<img src="../assets/images/logo/logo-bg.jpg" alt="Liyas Logo" style="width: 48px; height: 48px; border-radius: 12px; object-fit: cover;">
			</div>
			<h1 class="login-title">Liyas</h1>
			<p class="login-subtitle">Create Admin Account</p>
		</div>

		<?php if ($error): ?>
			<div class="alert alert-error">
				<?= htmlspecialchars($error) ?>
			</div>
		<?php endif; ?>
		<?php if ($success): ?>
			<div class="alert alert-success">
				<?= htmlspecialchars($success) ?>
			</div>
		<?php endif; ?>

		<form method="post" class="form-modern">
			<div class="form-group">
				<label for="username">Username</label>
				<input 
					type="text" 
					name="username" 
					id="username" 
					class="form-input" 
					placeholder="Enter username" 
					required 
					autofocus
				>
			</div>
			<div class="form-group">
				<label for="email">Email Address</label>
				<input 
					type="email" 
					name="email" 
					id="email" 
					class="form-input" 
					placeholder="Enter your email" 
					required
				>
			</div>
			<div class="form-group">
				<label for="password">Password</label>
				<input 
					type="password" 
					name="password" 
					id="password" 
					class="form-input" 
					placeholder="Enter password (min 6 characters)" 
					required
					minlength="6"
				>
			</div>
			<div class="form-group">
				<label for="confirm_password">Confirm Password</label>
				<input 
					type="password" 
					name="confirm_password" 
					id="confirm_password" 
					class="form-input" 
					placeholder="Confirm your password" 
					required
					minlength="6"
				>
			</div>
			<div class="form-actions">
				<button type="submit" name="submit" class="btn btn-primary" style="width: 100%;">
					<i class='bx bx-user-plus'></i> Sign Up
				</button>
			</div>
			<div style="text-align: center; margin-top: 1rem;">
				<p style="color: var(--text-secondary); font-size: 14px;">
					Already a user? <a href="login.php" style="color: var(--blue); text-decoration: none;">Sign In</a>
				</p>
			</div>
		</form>
	</div>
</div>
</body>
</html>
