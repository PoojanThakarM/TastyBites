<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us - Tasty Bites</title>
  <link rel="stylesheet" href="css/style-navbar.css" />
  <link rel="stylesheet" href="css/about.css">

</head>
<body>

<header>
  <h2 class="restaurant-title">🍽️ Tasty Bites Restaurant</h2>
  <?php include("navbar.php"); ?>
</header>

<main>
  <section class="specials-container">
    <h1>About Tasty Bites</h1>

    <h2>📸 Our Journey</h2>
    <div id="slider" role="region" aria-label="Restaurant journey image carousel">
      <img src="images/banner.jpg" alt="Restaurant banner" class="slide" style="display:block;" loading="eager">
      <img src="images/about-team.jpg" alt="Tasty Bites team" class="slide" loading="lazy">
      <img src="images/about-awards.jpg" alt="Awards and recognitions" class="slide" loading="lazy">
      <img src="images/about-event.jpg" alt="Catering a special event" class="slide" loading="lazy">
      <img src="images/about-kitchen.jpg" alt="Chefs at work in kitchen" class="slide" loading="lazy">

      <button id="prev" aria-label="Previous slide" tabindex="0">&#10094;</button>
      <button id="next" aria-label="Next slide" tabindex="0">&#10095;</button>
    </div>

    <p>Welcome to <strong>Tasty Bites</strong> — where every dish tells a story of flavor and freshness. Since our opening in 2020, we’ve been serving a mix of traditional Indian favorites and modern café specials, all made with love and the freshest ingredients.</p>

    <h2>Our Mission</h2>
    <p>We aim to create a cozy space for food lovers to enjoy high-quality meals at affordable prices. Whether you’re dining in, ordering online, or reserving for a special event — our focus is always on taste, service, and your satisfaction.</p>

    <h2>What We Offer</h2>
    <ul>
      <li>Freshly prepared dishes from our extensive menu 🍛</li>
      <li>Quick and reliable delivery 🚚</li>
      <li>Cozy ambiance for family and friends gatherings 🪑</li>
      <li>Special events and seasonal offers 🎉</li>
    </ul>

    <p class="centered-text">
      <a href="menu.php" class="main-btn">View Our Menu</a>
      <a href="reservation.php" class="main-btn">Reserve a Table</a>
    </p>
  </section>
</main>

<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">

<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const prevBtn = document.getElementById('prev');
const nextBtn = document.getElementById('next');

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.style.display = (i === index) ? 'block' : 'none';
  });
}

function showNext() {
  currentSlide = (currentSlide + 1) % slides.length;
  showSlide(currentSlide);
}

function showPrev() {
  currentSlide = (currentSlide - 1 + slides.length) % slides.length;
  showSlide(currentSlide);
}

if (nextBtn) nextBtn.onclick = showNext;
if (prevBtn) prevBtn.onclick = showPrev;

// Initialize
showSlide(currentSlide);
</script>

</body>
</html>
