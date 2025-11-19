<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$page_title = "Add New User";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['error_message'] = "All fields except password confirmation are required.";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT admin_id FROM admins WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = "Username or email already exists.";
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([$username, $email, $password_hash, $role]);
                $_SESSION['success_message'] = "User created successfully.";
                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        }
    }
    header("Location: add.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <title><?= $page_title ?> - Admin Panel</title>
</head>
<body>
    <section id="sidebar">
        <a href="../index.php" class="brand"><i class='bx bxs-smile bx-lg'></i><span class="text">Admin Panel</span></a>
        <ul class="side-menu top">
			<li><a href="../index.php"><i class='bx bxs-dashboard bx-sm'></i><span class="text">Dashboard</span></a></li>
			<li><a href="../products.php"><i class='bx bxs-shopping-bag-alt bx-sm'></i><span class="text">Products</span></a></li>
			<li><a href="../categories.php"><i class='bx bxs-category bx-sm'></i><span class="text">Categories</span></a></li>
			<li class="active"><a href="index.php"><i class='bx bxs-group bx-sm'></i><span class="text">Users</span></a></li>
		</ul>
    </section>

    <section id="content">
        <nav>
            <i class='bx bx-menu bx-sm'></i>
            <a href="#" class="nav-link"><?= $page_title ?></a>
        </nav>
        <main>
            <div class="head-title"><div class="left"><h1><?= $page_title ?></h1></div></div>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <div class="table-data"><div class="order">
                <div class="head"><h3>User Details</h3></div>
                <form action="add.php" method="POST" class="form-modern">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select name="role" id="role" required>
                            <option value="editor">Editor</option>
                            <option value="moderator">Moderator</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Add User</button>
                        <a href="index.php" class="btn-secondary" style="margin-left: 1rem;">Cancel</a>
                    </div>
                </form>
            </div></div>
        </main>
    </section>
    <script src="../assets/js/admin-script.js"></script>
    <style>
        .form-modern { display: flex; flex-direction: column; gap: 1.5rem; }
        .form-modern .form-group { display: flex; flex-direction: column; }
        .form-modern label { margin-bottom: 0.5rem; font-weight: 600; }
        .form-modern input, .form-modern select { padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--grey); background-color: var(--grey); font-size: 1rem; }
        .form-modern .btn-primary, .btn-secondary { align-self: flex-start; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 1rem; border: none; cursor: pointer; text-decoration: none; }
        .btn-primary { background-color: var(--blue); color: var(--light); }
        .btn-secondary { background-color: var(--dark-grey); color: var(--light); }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
    </style>
</body>
</html>
```

### 3. Create `edit.php` for Updating Users

**New File: `c:\xampp\htdocs\liyas-mineral-water\admin\users\edit.php`**
```diff