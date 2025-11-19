<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$page_title = "Edit User";
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM admins WHERE admin_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($role)) {
        $_SESSION['error_message'] = "Username, email, and role are required.";
    } else {
        try {
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, role = ?, password_hash = ? WHERE admin_id = ?");
                $stmt->execute([$username, $email, $role, $password_hash, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE admins SET username = ?, email = ?, role = ? WHERE admin_id = ?");
                $stmt->execute([$username, $email, $role, $user_id]);
            }
            $_SESSION['success_message'] = "User updated successfully.";
            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        }
    }
    header("Location: edit.php?id=" . $user_id);
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
                <div class="head"><h3>Edit User Details</h3></div>
                <form action="edit.php?id=<?= $user_id ?>" method="POST" class="form-modern">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">New Password (leave blank to keep current)</label>
                        <input type="password" name="password" id="password">
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select name="role" id="role" required>
                            <option value="editor" <?= ($user['role'] === 'editor') ? 'selected' : '' ?>>Editor</option>
                            <option value="moderator" <?= ($user['role'] === 'moderator') ? 'selected' : '' ?>>Moderator</option>
                            <option value="superadmin" <?= ($user['role'] === 'superadmin') ? 'selected' : '' ?>>Superadmin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Update User</button>
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

### 4. Create `delete.php` for Removing Users

**New File: `c:\xampp\htdocs\liyas-mineral-water\admin\users\delete.php`**
```diff