<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: categories.php");
    exit;
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);

            if (empty($name)) {
                $_SESSION['error_message'] = "Category name is required.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                $_SESSION['success_message'] = "Category created successfully.";
            }
            break;

        case 'update':
            $category_id = $_POST['category_id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);

            if (empty($name) || empty($category_id)) {
                $_SESSION['error_message'] = "Category name and ID are required.";
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE category_id = ?");
                $stmt->execute([$name,  $description, $category_id]);
                $_SESSION['success_message'] = "Category updated successfully.";
            }
            break;

        case 'delete':
            $category_id = $_POST['category_id'];

            if (empty($category_id)) {
                $_SESSION['error_message'] = "Category ID is required.";
            } else {
                // Optional: Check if any products are using this category before deleting.
                // For now, we will just delete it.
                $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = ?");
                $stmt->execute([$category_id]);
                $_SESSION['success_message'] = "Category deleted successfully.";
            }
            break;

        default:
            $_SESSION['error_message'] = "Invalid action.";
            break;
    }
} catch (PDOException $e) {
    // Check for duplicate entry
    if ($e->getCode() == 23000) {
        $_SESSION['error_message'] = "A category with this name already exists.";
    } else {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
}

header("Location: categories.php");
exit;
?>
```

### 3. Update Sidebar Links

Finally, let's update the sidebar in `index.php` to ensure the "Categories" link is correctly highlighted when you are on that page.

**File to Edit: `c:\xampp\htdocs\liyas-mineral-water\admin\index.php`**

```diff