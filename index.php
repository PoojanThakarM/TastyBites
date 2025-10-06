<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tasty Bites - Home</title>
  <!-- Navbar CSS -->
  <link rel="stylesheet" href="css/style-navbar.css">
  <!-- Index Page CSS -->
  <link rel="stylesheet" href="css/style-index.css">
  <!-- Google Fonts -->
  
</head>
<body>
  <header>
    <h1 class="restaurant-title">🍽️ Tasty Bites Restaurant</h1>
  </header>
  <!-- Navbar -->
  <?php include("navbar.php"); ?>

  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>🍽️ Welcome to <span>Tasty Bites</span></h1>
      <p>Your go-to place for delicious meals, bold flavors, and unforgettable dining experiences.</p>
      <a href="menu.php" class="cta-btn">Explore Menu</a>
    </div>
  </section>

  <!-- Highlights Section -->
  <section class="highlights">
    <h2>✨ Why Choose Us?</h2>
    <div class="cards">
      <div class="card">
        <h3>🔥 Bold Flavors</h3>
        <p>Every dish is crafted with passion and the freshest ingredients.</p>
      </div>
      <div class="card">
        <h3>👨‍🍳 Master Chefs</h3>
        <p>Our chefs bring creativity and years of experience to your plate.</p>
      </div>
      <div class="card">
        <h3>🏆 Great Ambience</h3>
        <p>Dine in a cozy, vibrant atmosphere designed for food lovers.</p>
      </div>
    </div>
  </section>

  <!-- Call To Action -->
  <section class="cta-section">
    <h2>Book Your Table Now</h2>
    <p>Reserve your spot today and enjoy the best of Tasty Bites.</p>
    <a href="reservation.php" class="cta-btn">Reserve Now</a>
  </section>

  <!-- Footer -->
<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">
</body>
</html>
