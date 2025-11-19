<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: products.php");
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
            $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
            $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $featured = isset($_POST['featured']) ? 1 : 0;

            if (empty($name) || $price === false || $stock === false) {
                $_SESSION['error_message'] = "Name, valid price, and stock are required.";
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO products (name, description, price, stock, category_id, is_active, featured) VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$name, $description, $price, $stock, $category_id, $is_active, $featured]);
                $_SESSION['success_message'] = "Product created successfully.";
            }
            break;

        case 'update':
            $product_id = $_POST['product_id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
            $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
            $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            $featured = isset($_POST['featured']) ? 1 : 0;

            if (empty($name) || $price === false || $stock === false || empty($product_id)) {
                $_SESSION['error_message'] = "All fields are required.";
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category_id = ?, is_active = ?, featured = ? WHERE product_id = ?"
                );
                $stmt->execute([$name, $description, $price, $stock, $category_id, $is_active, $featured, $product_id]);
                $_SESSION['success_message'] = "Product updated successfully.";
            }
            break;

        case 'delete':
            $product_id = $_POST['product_id'];
            if (!empty($product_id)) {
                $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $_SESSION['success_message'] = "Product deleted successfully.";
            }
            break;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

header("Location: products.php");
exit;
?>
```

With these changes, your admin panel is now much more robust. The sidebar links are correct, and you have a powerful and user-friendly interface for managing both products and categories.