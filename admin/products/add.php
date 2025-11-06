<?php
require_once '../../config/config.php';
require_once '../includes/auth_check.php';

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = $_POST['category_id'] ?: null;
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = $_POST['price'];
    $stock = $_POST['stock'] ?? 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $featured = isset($_POST['featured']) ? 1 : 0;

    $stmt = $pdo->prepare("
        INSERT INTO products (category_id, name, description, price, stock, is_active, featured)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$category_id, $name, $desc, $price, $stock, $is_active, $featured]);
    $product_id = $pdo->lastInsertId();

    // Upload directory
    $uploadDir = $ROOT_PATH . '/uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            die("Failed to create upload directory: " . $uploadDir);
        }
    }

    // Handle media uploads
    if (!empty($_FILES['media']['name'][0])) {
        foreach ($_FILES['media']['tmp_name'] as $i => $tmp) {
            if (!is_uploaded_file($tmp)) continue;
            
            // Check for upload errors
            if ($_FILES['media']['error'][$i] !== UPLOAD_ERR_OK) {
                error_log("Upload error code: " . $_FILES['media']['error'][$i]);
                continue;
            }
            
            $original = basename($_FILES['media']['name'][$i]);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $filename = uniqid('media_') . '.' . $ext;
            $dest = $uploadDir . $filename;
            $type = in_array($ext, ['mp4', 'mov', 'webm']) ? 'video' : 'image';
            
            if (move_uploaded_file($tmp, $dest)) {
                // Verify file was actually moved
                if (file_exists($dest)) {
                    $pdo->prepare("
                        INSERT INTO products_media (product_id, file_path, file_type, alt_text, is_primary)
                        VALUES (?, ?, ?, ?, ?)
                    ")->execute([$product_id, $filename, $type, $original, $i === 0]);
                } else {
                    error_log("File move failed: " . $dest);
                }
            } else {
                error_log("move_uploaded_file failed. Source: " . $tmp . " Dest: " . $dest);
            }
        }
    }

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Add Product</title></head>
<body>
<?php include '../includes/header.php'; ?>
<h2>Add Product</h2>
<form method="post" enctype="multipart/form-data">
  <label>Category:</label><br>
  <select name="category_id">
    <option value="">-- None --</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= $c['category_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
    <?php endforeach; ?>
  </select><br><br>

  <label>Name:</label><br>
  <input type="text" name="name" required><br><br>
  <label>Description:</label><br>
  <textarea name="description"></textarea><br><br>
  <label>Price:</label><br>
  <input type="number" step="0.01" name="price" required><br><br>
  <label>Stock:</label><br>
  <input type="number" name="stock" value="0"><br><br>
  <label><input type="checkbox" name="is_active" checked> Active</label><br>
  <label><input type="checkbox" name="featured"> Featured</label><br><br>
  <label>Upload Media:</label><br>
  <input type="file" name="media[]" multiple><br><br>
  <button type="submit">Save Product</button>
</form>
<?php include '../includes/footer.php'; ?>
</body>
</html>
