<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "social-links";
$page_title   = "Add Social Media";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $platform   = trim($_POST['platform']);
    $icon_class = trim($_POST['icon_class']);
    $url        = trim($_POST['url']);
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $is_active  = $_POST['is_active'] ?? 1;

    if ($platform === '' || $icon_class === '' || $url === '') {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO social_links (platform, icon_class, url, sort_order, is_active)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $platform,
            $icon_class,
            $url,
            $sort_order,
            $is_active
        ]);

        quickLog($pdo, 'create', 'social_link', $pdo->lastInsertId(), "Added social link: {$platform}");
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Social Media</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/prody-admin.css">

    <style>
        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
        }
    </style>
</head>

<body>
<div class="container">

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="content-area">

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-card">

                <div class="table-header">
                    <div class="table-title">Add Social Media Link</div>
                </div>

                <div style="padding:2rem">

                    <form method="POST" class="form-modern">

                        <!-- PLATFORM -->
                        <div class="form-group">
                            <label>Platform Name *</label>
                            <input type="text"
                                   name="platform"
                                   class="form-input"
                                   placeholder="Instagram, Facebook, WhatsApp"
                                   required>
                        </div>

                        <!-- ICON + ORDER + STATUS -->
                        <div class="grid-3">

                            <div class="form-group">
                                <label>Icon Class *</label>
                                <input type="text"
                                       name="icon_class"
                                       class="form-input"
                                       placeholder="bx bxl-instagram"
                                       required>
                                <small class="text-muted-custom">
                                    Use Boxicons / FontAwesome class
                                </small>
                            </div>

                            <div class="form-group">
                                <label>Sort Order</label>
                                <input type="number"
                                       name="sort_order"
                                       class="form-input"
                                       value="0">
                            </div>

                            <div class="form-group">
                                <label>Status</label>
                                <select name="is_active" class="form-select">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                        </div>

                        <!-- URL -->
                        <div class="form-group" style="margin-top:1.5rem">
                            <label>Profile URL *</label>
                            <input type="url"
                                   name="url"
                                   class="form-input"
                                   placeholder="https://instagram.com/yourpage"
                                   required>
                        </div>

                        <!-- ACTIONS -->
                        <div class="form-actions" style="margin-top:2rem">
                            <button type="submit" class="btn-action btn-add">
                                Save Social Media
                            </button>

                            <a href="index.php"
                               class="btn-action"
                               style="background:#6c757d;color:white;text-decoration:none">
                                Cancel
                            </a>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
