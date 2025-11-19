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
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "An account with this username already exists.";
        } // Check if email already exists
        else {
            $stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
                $error = "An account with this email already exists.";
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Signup - Liyas Mineral Water</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/new-login.css">
</head>
<body>
<main class="main">
	<div class="container">
		<section class="wrapper">
			<div class="heading">
				<h1 class="text text-large">Create Account</h1>
				<p class="text text-normal">Already a user? <span><a href="login.php" class="text text-links">Sign In</a></span>
				</p>
			</div>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-top: 1rem;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-top: 1rem;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

			<form name="signup" class="form" method="post">
				<div class="input-control">
					<input type="text" name="username" id="username" class="input-field" placeholder="Username" required autofocus>
				</div>
				<div class="input-control">
					<input type="email" name="email" id="email" class="input-field" placeholder="Email Address" required>
				</div>
				<div class="input-control">
					<input type="password" name="password" id="password" class="input-field" placeholder="Password" required>
				</div>
				<div class="input-control">
					<input type="password" name="confirm_password" id="confirm_password" class="input-field" placeholder="Confirm Password" required>
				</div>
				<div class="input-control">
					<input type="submit" name="submit" class="input-submit" value="Sign Up">
				</div>
			</form>
		</section>
	</div>
</main>
</body>
</html>
