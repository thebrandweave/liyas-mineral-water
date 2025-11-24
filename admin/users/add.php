<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../includes/auth_check.php';

$admin_name = htmlspecialchars($_SESSION['admin_name'] ?? 'Admin');
$current_page = "users";
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
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
                        <button type="submit" class="button add-btn" title="Add User">
                            <svg class="svgIcon" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"></path></svg>
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
        .add-btn:hover { background-color: var(--green); }
        .add-btn::before { content: "Add User"; }
        .cancel-btn:hover { background-color: var(--orange); }
        .cancel-btn::before { content: "Cancel"; }
    </style>
</body>
</html>
```

### 3. Create `edit.php` for Updating Users

**New File: `c:\xampp\htdocs\liyas-mineral-water\admin\users\edit.php`**
```diff