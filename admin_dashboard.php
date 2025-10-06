<?php
include("db.php");
session_start();

// Optional: Restrict to admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch counts for dashboard overview
$res_count = $conn->query("SELECT COUNT(*) AS total FROM reservations")->fetch_assoc()['total'];
$event_count = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'];
$blog_count = $conn->query("SELECT COUNT(*) AS total FROM blog_posts")->fetch_assoc()['total'];
$user_count = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
?>
<?php include("admin_header.php"); ?>
<link rel="stylesheet" href="css/style-admin.css">
<main class="admin-dashboard">
    <h1>📊 Admin Dashboard</h1>
    <div class="dashboard-cards">
        <div class="card">
            <h2>Reservations</h2>
            <p><?= $res_count ?></p>
            <a href="admin_reservations.php">View Details</a>
        </div>
        <div class="card">
            <h2>Events</h2>
            <p><?= $event_count ?></p>
            <a href="admin_events.php">Manage Events</a>
        </div>
        <div class="card">
            <h2>Blog Posts</h2>
            <p><?= $blog_count ?></p>
            <a href="admin_blog.php">Manage Blogs</a>
        </div>
        <div class="card">
            <h2>Users</h2>
            <p><?= $user_count ?></p>
            <a href="admin_users.php">Manage Users</a>
        </div>
    </div>
</main>

<?php include("admin_footer.php"); ?>
