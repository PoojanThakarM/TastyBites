<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="navbar">
    <div class="nav-left">
        
        <div class="nav-links">
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="reservation.php">Reservation</a>
            <a href="gallery.php">Gallery</a>
            <a href="events.php">Events</a>
            <a href="reviews.php">Reviews</a>
            <a href="faq.php">FAQs</a>
            <a href="blog.php">Blogs</a>
            <a href="about.php">About Us</a>
        </div>
    </div>

    <div class="nav-right">
        <?php if(isset($_SESSION['user_id'])): ?>
            <div class="user-section">
                <div class="profile-circle" onclick="toggleDropdown()">
                    <?php echo strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                </div>
                <div id="dropdown" class="dropdown">
                    <p>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                    <a href="profile.php">Profile</a>
                    <a href="logout.php">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <div class="auth-buttons">
                <a href="login.php" class="btn-auth">Login</a>
                <a href="register.php" class="btn-auth">Register</a>
            </div>
        <?php endif; ?>
    </div>
</nav>

<script>
function toggleDropdown() {
    var dropdown = document.getElementById("dropdown");
    dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
}

window.onclick = function(event) {
    if (!event.target.closest('.profile-circle')) {
        var dropdown = document.getElementById("dropdown");
        if (dropdown && dropdown.style.display === "block") dropdown.style.display = "none";
    }
}
</script>
