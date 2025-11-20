<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "users";
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
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
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
                        <button type="submit" class="button update-btn" title="Update User">
                            <svg class="svgIcon" viewBox="0 0 448 512"><path d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"></path></svg>
                        </button>
                        <a href="index.php" class="button cancel-btn" title="Cancel">
                            <svg class="svgIcon" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"></path></svg>
                        </a>
                    </div>
                </form>
            </div></div>
        </main>
    </section>
    <script src="../assets/js/admin-script.js"></script>
    <style>
        /* Form Styles */
        .form-modern { display: flex; flex-direction: column; gap: 1.5rem; padding: 1rem 0; }
        .form-modern .form-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .form-modern .form-group:last-child { flex-direction: row; gap: 1rem; margin-top: 1rem; }
        .form-modern label { font-weight: 600; color: var(--dark-grey); }
        .form-modern input, .form-modern select { padding: 0.75rem 1rem; border-radius: 8px; border: 1px solid var(--grey); background-color: var(--grey); font-size: 1rem; color: var(--dark); }
        
        /* Alert Styles */
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }

        /* Animated Button Styles */
        .button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: rgb(20, 20, 20);
            border: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.164);
            cursor: pointer;
            transition-duration: .3s;
            overflow: hidden;
            position: relative;
            text-decoration: none !important;
        }
        .svgIcon { width: 17px; transition-duration: .3s; }
        .svgIcon path { fill: white; }
        .button:hover {
            width: 120px;
            border-radius: 50px;
            transition-duration: .3s;
            align-items: center;
        }
        .button:hover .svgIcon {
            width: 20px;
            transition-duration: .3s;
            transform: translateY(60%);
        }
        .button::before {
            position: absolute;
            top: -20px;
            color: white;
            transition-duration: .3s;
            font-size: 2px;
        }
        .button:hover::before {
            font-size: 13px;
            opacity: 1;
            transform: translateY(30px);
            transition-duration: .3s;
        }

        /* Button Variations */
        .update-btn:hover { background-color: var(--green); }
        .update-btn::before { content: "Update"; }
        .cancel-btn:hover { background-color: var(--orange); }
        .cancel-btn::before { content: "Cancel"; }
    </style>
</body>
</html>
```

### 4. Create `delete.php` for Removing Users

**New File: `c:\xampp\htdocs\liyas-mineral-water\admin\users\delete.php`**
```diff