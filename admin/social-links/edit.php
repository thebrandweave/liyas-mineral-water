<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$id = (int)$_GET['id'];
$link = $pdo->prepare("SELECT * FROM social_links WHERE social_id=?");
$link->execute([$id]);
$s = $link->fetch();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pdo->prepare("
        UPDATE social_links SET platform=?, icon_class=?, url=?, sort_order=?, is_active=?
        WHERE social_id=?
    ")->execute([
        $_POST['platform'],
        $_POST['icon_class'],
        $_POST['url'],
        $_POST['sort_order'],
        $_POST['is_active'],
        $id
    ]);
    header("Location: index.php"); exit;
}
?>
<form method="POST" class="form-modern">
<input name="platform" value="<?= $s['platform'] ?>">
<input name="icon_class" value="<?= $s['icon_class'] ?>">
<input name="url" value="<?= $s['url'] ?>">
<input name="sort_order" value="<?= $s['sort_order'] ?>">
<select name="is_active">
<option value="1" <?= $s['is_active']?'selected':'' ?>>Active</option>
<option value="0" <?= !$s['is_active']?'selected':'' ?>>Inactive</option>
</select>
<button>Update</button>
</form>
