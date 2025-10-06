<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Photo Gallery - Tasty Bites</title>
    <link rel="stylesheet" href="css/style-navbar.css">
    <link rel="stylesheet" href="css/gallery.css">
    <style>
        /* =========================
           Gallery Hover Overlay
        ========================= */
        .gallery-item {
            position: relative;
            overflow: hidden;
        }

        .gallery-item::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(241, 250, 60, 0.25); /* subtle yellow overlay */
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 15px;
        }

        .gallery-item:hover::after {
            opacity: 1;
        }

        .gallery-item img {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<header>
  <h2 class="restaurant-title">🍽️ Tasty Bites Restaurant</h2>
  <?php include("navbar.php"); ?>
</header>

<main>
    <h1>📸 Tasty Bites Photo Gallery</h1>
    <p>Take a visual tour of our delicious dishes, cozy interiors, and hardworking chefs!</p>

    <!-- Gallery Grid -->
    <div class="gallery-grid">
        <!-- Veg Dishes -->
        <div class="gallery-item">
            <img src="https://media.istockphoto.com/id/1292629539/photo/paneer-tikka-masala-is-a-famous-indian-dish-served-over-a-rustic-wooden-background-selective.jpg?s=612x612&w=0&k=20&c=GCvoJ3lBcvvRJmeENmSpa_7rLkh_1OKPaM6gKNYqUGM=" alt="Veg Paneer Tikka Masala">
            <p>Veg Paneer Tikka Masala</p>
        </div>
        <div class="gallery-item">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRMVZifm4LlnZJx1qAYUmPQQjQPLnpLX3tW3w&s" alt="South Indian Dosa">
            <p>South Indian Dosa</p>
        </div>
        <div class="gallery-item">
            <img src="https://i.ytimg.com/vi/SlftwKILSVo/hqdefault.jpg" alt="Cheese Pineapple Ice Cream Sandwich">
            <p>Cheese Pineapple Ice Cream Sandwich</p>
        </div>

        <!-- Restaurant Interior -->
        <div class="gallery-item">
            <img src="https://images.pexels.com/photos/1307698/pexels-photo-1307698.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2" alt="Restaurant Interior">
            <p>Restaurant Interior</p>
        </div>
        <div class="gallery-item">
            <img src="https://plus.unsplash.com/premium_photo-1661883237884-263e8de8869b?q=80&w=1189&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Restaurant Interior">
            <p>Restaurant Interior</p>
        </div>
        <div class="gallery-item">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRkUYuN-bHqiKlHYNImMuTuvghO4Lb-I44FwQ&s" alt="Dining Space">
            <p>Dining Space</p>
        </div>

        <!-- Chefs -->
        <div class="gallery-item">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS3DpNIhwJAe7gVMZ-A-Z0tiO3PeJFgx1N9bUTNsXUNjTBFsInx0vYkYu2AKcTtq10rJCY&usqp=CAU" alt="Chef Cooking">
            <p>Chef Cooking</p>
        </div>
        <div class="gallery-item">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTyuumyqGcyU7oOHvw-a6uU8P_KgSwRnkWD1Q&s" alt="Chef at Work">
            <p>Chef at Work</p>
        </div>
    </div>
</main>

<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">

</body>
</html>
