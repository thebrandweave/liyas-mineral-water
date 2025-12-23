<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$current_page = "social-links";

/* DELETE */
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM social_links WHERE social_id=?")->execute([(int)$_GET['delete']]);
    header("Location: index.php"); exit;
}

/* TOGGLE */
if (isset($_GET['toggle'])) {
    $pdo->prepare("UPDATE social_links SET is_active = NOT is_active WHERE social_id=?")
        ->execute([(int)$_GET['toggle']]);
    header("Location: index.php"); exit;
}

$links = $pdo->query("SELECT * FROM social_links ORDER BY sort_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../assets/css/prody-admin.css">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="container">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<div class="content-area">

<div class="table-card">
<div class="table-header">
<div class="table-title">Social Media Links</div>
<a href="add.php" class="btn-action btn-add">Add Social</a>
</div>

<table>
<thead>
<tr>
<th>Platform</th>
<th>Icon</th>
<th>URL</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach ($links as $s): ?>
<tr>
<td><?= htmlspecialchars($s['platform']) ?></td>
<td><i class="<?= $s['icon_class'] ?>"></i></td>
<td><?= htmlspecialchars($s['url']) ?></td>
<td><?= $s['is_active'] ? 'Active' : 'Inactive' ?></td>
<td>
<a href="edit.php?id=<?= $s['social_id'] ?>" class="btn-action btn-edit">Edit</a>
<a href="?toggle=<?= $s['social_id'] ?>" class="btn-action">Toggle</a>
<a href="?delete=<?= $s['social_id'] ?>" class="btn-action btn-delete"
   onclick="return confirm('Delete?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

</div>
</div>
</div>
</div>
</body>
</html>
