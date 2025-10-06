<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Optional: Restrict to admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Panel - Tasty Bites</title>
<link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
<header class="admin-header">
    <h1>📊 Admin Panel</h1>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_reservations.php">Reservations</a>
        <a href="admin_orders.php">Orders</a>
        <a href="admin_menu.php">Menu</a>
        <a href="admin_events.php">Events</a>
        <a href="admin_blog.php">Blog</a>
        <a href="admin_reviews.php">Reviews</a>
        <a href="admin_users.php">Users</a>
        <a href="admin_inventory.php">Inventory</a>
        <a href="admin_faq.php">FAQ</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>
<main class="admin-main">
