<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';
require_once '../includes/activity_logger.php';

$current_page = "reviews";
$page_title   = "Reviews";

/* ACTIONS */
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($_GET['action'] === 'approve') {
        $pdo->prepare("UPDATE reviews SET status='approved' WHERE review_id=?")->execute([$id]);
        quickLog($pdo,'update','review',$id,'Approved review');
    }
    if ($_GET['action'] === 'reject') {
        $pdo->prepare("UPDATE reviews SET status='rejected' WHERE review_id=?")->execute([$id]);
        quickLog($pdo,'update','review',$id,'Rejected review');
    }
    if ($_GET['action'] === 'delete') {
        $pdo->prepare("DELETE FROM reviews WHERE review_id=?")->execute([$id]);
        quickLog($pdo,'delete','review',$id,'Deleted review');
    }

    header("Location: index.php");
    exit;
}

/* FETCH */
$stmt = $pdo->query("
    SELECT r.*, p.name AS product_name, u.name AS user_name
    FROM reviews r
    JOIN products p ON p.product_id = r.product_id
    JOIN users u ON u.user_id = r.user_id
    ORDER BY r.created_at DESC
");
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reviews - Liyas Admin</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link rel="stylesheet" href="../assets/css/prody-admin.css">

<style>
.badge-pending{background:#f59e0b;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px}
.badge-approved{background:#16a34a;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px}
.badge-rejected{background:#dc2626;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px}
.star{color:#facc15}
</style>
</head>

<body>
<div class="container">
<?php include '../includes/sidebar.php'; ?>

<div class="main-content">
<div class="header">
    <div class="breadcrumb"><i class='bx bx-star'></i> <span>Reviews</span></div>
</div>

<div class="content-area">
<div class="table-card">
<div class="table-header">
    <div class="table-title">All Reviews</div>
</div>

<div class="table-responsive-wrapper">
<table>
<thead>
<tr>
<th>Product</th>
<th>User</th>
<th>Rating</th>
<th>Review</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>

<?php foreach ($reviews as $r): ?>
<tr>
<td><strong><?= htmlspecialchars($r['product_name']) ?></strong></td>
<td><?= htmlspecialchars($r['user_name']) ?></td>
<td>
<?php for($i=1;$i<=5;$i++): ?>
    <i class='bx bxs-star star' style="opacity:<?= $i <= $r['rating'] ? '1':'0.2' ?>"></i>
<?php endfor; ?>
</td>
<td><?= htmlspecialchars(substr($r['review_text'],0,60)) ?>...</td>
<td>
<?php
echo $r['status']=='approved'
 ? '<span class="badge-approved">Approved</span>'
 : ($r['status']=='rejected'
    ? '<span class="badge-rejected">Rejected</span>'
    : '<span class="badge-pending">Pending</span>');
?>
</td>
<td>
<a href="edit.php?id=<?= $r['review_id'] ?>" class="btn-action btn-edit noselect">
    <span class="text">View</span>
</a>

<?php if ($r['status'] !== 'approved'): ?>
<a href="index.php?action=approve&id=<?= $r['review_id'] ?>" class="btn-action btn-add noselect">
    <span class="text">Approve</span>
</a>
<?php endif; ?>

<?php if ($r['status'] !== 'rejected'): ?>
<a href="index.php?action=reject&id=<?= $r['review_id'] ?>" class="btn-action" style="background:#dc2626;color:white">
    <span class="text">Reject</span>
</a>
<?php endif; ?>

<a href="index.php?action=delete&id=<?= $r['review_id'] ?>"
   onclick="return confirm('Delete this review?')"
   class="btn-action btn-delete noselect">
   <span class="text">Delete</span>
</a>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>
</div>
</div>
</div>
</body>
</html>
