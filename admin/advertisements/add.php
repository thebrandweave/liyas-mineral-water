<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$current_page = "advertisements";

if ($_SERVER['REQUEST_METHOD']==='POST') {

    $image = null;
    $upload_dir = __DIR__.'/uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir,0777,true);

    if (!empty($_FILES['image']['name'])) {
        $image = 'ad_'.time().'_'.uniqid().'.'.pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir.$image);
    }

    $stmt = $pdo->prepare("
        INSERT INTO advertisements (title,image,redirect_url,position,start_date,end_date,status)
        VALUES (?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        $_POST['title'],$image,$_POST['redirect_url'],
        $_POST['position'],$_POST['start_date'],$_POST['end_date'],$_POST['status']
    ]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Advertisement</title>
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
<div class="table-header"><div class="table-title">Add Advertisement</div></div>
<div style="padding:2rem">

<form method="POST" enctype="multipart/form-data" class="form-modern">
<div class="grid-3">
<div class="form-group"><label>Title</label><input name="title" class="form-input" required></div>
<div class="form-group"><label>Position</label>
<select name="position" class="form-select">
<option value="home_top">Home Top</option>
<option value="home_middle">Home Middle</option>
<option value="home_bottom">Home Bottom</option>
<option value="popup">Popup</option>
<option value="sidebar">Sidebar</option>
</select>
</div>
<div class="form-group"><label>Status</label>
<select name="status" class="form-select">
<option value="active">Active</option>
<option value="inactive">Inactive</option>
</select>
</div>
</div>

<div class="form-group" style="margin-top:1rem">
<label>Redirect URL</label>
<input name="redirect_url" class="form-input">
</div>

<div class="form-group" style="margin-top:1rem">
<label>Image</label>
<input type="file" name="image" class="form-input" required>
</div>

<div class="grid-3">
<div class="form-group"><label>Start Date</label><input type="date" name="start_date" class="form-input"></div>
<div class="form-group"><label>End Date</label><input type="date" name="end_date" class="form-input"></div>
</div>

<div class="form-actions" style="margin-top:2rem">
<button class="btn-action btn-add">Save Advertisement</button>
<a href="index.php" class="btn-action" style="background:#6c757d;color:#fff;text-decoration:none">Cancel</a>
</div>

</form>
</div>
</div>

</div>
</div>
</div>
</body>
</html>
