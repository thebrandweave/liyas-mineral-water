<?php include 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel</title>
<link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
<header>
  <h1>Beverage Admin Panel</h1>
  <div style="text-align:right">
    Logged in as: <?= htmlspecialchars($_SESSION['admin_name']) ?> |
    <a href="../logout.php">Logout</a>
  </div>
</header>
<main>
