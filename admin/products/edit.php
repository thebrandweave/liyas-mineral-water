<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM products WHERE product_id=?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) die("Product not found");

// Fetch categories + media
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$media = $pdo->prepare("SELECT * FROM products_media WHERE product_id=? ORDER BY is_primary DESC, uploaded_at ASC");
$media->execute([$id]);
$mediaFiles = $media->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?: null;
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;

    $pdo->prepare("
        UPDATE products 
        SET category_id=?, name=?, description=?, price=?, stock=?, is_active=?, featured=? 
        WHERE product_id=?
    ")->execute([$category_id, $name, $desc, $price, $stock, $is_active, $featured, $id]);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Edit Product</title></head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Edit Product</h2>

<form method="post">
  <label>Category:</label><br>
  <select name="category_id">
    <option value="">-- None --</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= $c['category_id'] ?>" <?= $product['category_id'] == $c['category_id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($c['name']) ?>
      </option>
    <?php endforeach; ?>
  </select><br><br>

  <label>Name:</label><br>
  <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br><br>
  <label>Description:</label><br>
  <textarea name="description"><?= htmlspecialchars($product['description']) ?></textarea><br><br>
  <label>Price:</label><br>
  <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>"><br><br>
  <label>Stock:</label><br>
  <input type="number" name="stock" value="<?= $product['stock'] ?>"><br><br>
  <label><input type="checkbox" name="is_active" <?= $product['is_active'] ? 'checked' : '' ?>> Active</label><br>
  <label><input type="checkbox" name="featured" <?= $product['featured'] ? 'checked' : '' ?>> Featured</label><br><br>
  <button type="submit">Update</button>
</form>

<h3>Media</h3>
<div style="display:flex;flex-wrap:wrap;gap:15px;">
<?php foreach ($mediaFiles as $m): ?>
  <div style="text-align:center;">
    <?php if ($m['file_type'] === 'image'): ?>
      <img src="../../uploads/<?= htmlspecialchars($m['file_path']) ?>" width="120"><br>
    <?php else: ?>
      <video width="120" controls><source src="../../uploads/<?= htmlspecialchars($m['file_path']) ?>"></video><br>
    <?php endif; ?>
    <small><?= htmlspecialchars($m['alt_text']) ?></small><br>
    <?= $m['is_primary'] ? '<strong>Primary</strong>' : '' ?><br>
    <a href="delete_media.php?id=<?= $m['media_id'] ?>&product_id=<?= $id ?>" onclick="return confirm('Delete this file permanently?')">Delete</a>
  </div>
<?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
