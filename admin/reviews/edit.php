<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "reviews";

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("
    SELECT r.*, p.name AS product_name, u.name AS user_name
    FROM reviews r
    JOIN products p ON p.product_id = r.product_id
    JOIN users u ON u.user_id = r.user_id
    WHERE r.review_id = ?
");
$stmt->execute([$id]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pdo->prepare("UPDATE reviews SET status=? WHERE review_id=?")
        ->execute([$_POST['status'],$id]);

    quickLog($pdo,'update','review',$id,'Updated review status');
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Review Details</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="../assets/css/prody-admin.css">
</head>

<body>
<div class="container">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<div class="content-area">

<div class="table-card">
<div class="table-header">
    <div class="table-title">Review Details</div>
</div>

<div style="padding:2rem">
<p><strong>Product:</strong> <?= htmlspecialchars($review['product_name']) ?></p>
<p><strong>User:</strong> <?= htmlspecialchars($review['user_name']) ?></p>
<p><strong>Rating:</strong> <?= $review['rating'] ?>/5</p>

<div style="margin:1rem 0; padding:1rem; background:#f8fafc; border-radius:8px">
<?= nl2br(htmlspecialchars($review['review_text'])) ?>
</div>

<form method="POST">
<div class="form-group">
<label>Status</label>
<select name="status" class="form-select">
<option value="pending" <?= $review['status']=='pending'?'selected':'' ?>>Pending</option>
<option value="approved" <?= $review['status']=='approved'?'selected':'' ?>>Approved</option>
<option value="rejected" <?= $review['status']=='rejected'?'selected':'' ?>>Rejected</option>
</select>
</div>

<div class="form-actions" style="margin-top:1.5rem">
<button class="btn-action btn-add">Update Status</button>
<a href="index.php" class="btn-action" style="background:#6c757d;color:white;text-decoration:none">Back</a>
</div>
</form>
</div>
</div>

</div>
</div>
</div>
</body>
</html>
