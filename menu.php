<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include("db.php"); // your database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Our Menu - Tasty Bites</title>
  <link rel="stylesheet" href="css/style-navbar.css">
  <link rel="stylesheet" href="css/menu.css">
  <link rel="stylesheet" href="css/style-footer.css">
</head>
<body>

<header>
  <h1 class="restaurant-title">🍽️ Tasty Bites Restaurant</h1>
</header>

<?php include("navbar.php"); ?>

<main>
  <!-- 🔎 Search + Category Filter -->
  <div class="menu-toolbar">
    <select id="categoryFilter">
      <option value="">🍴 All Categories</option>
    </select>
    <input type="search" id="menuSearch" placeholder="🔍 Search by name">
  </div>

  <!-- Action Buttons -->
  <div class="menu-buttons">
    <a href="orders.php"><button class="main-btn">🧾 Order Now</button></a>
    <a href="reservation.php"><button class="main-btn">📅 Reserve Table</button></a>
  </div>

  <?php
  // Fetch menu items ordered by category_order and order_index
  $query = "SELECT * FROM menu_items ORDER BY category_order ASC, order_index ASC";
  $result = mysqli_query($conn, $query);

  $currentCategory = '';
  if ($result) {
      while ($row = mysqli_fetch_assoc($result)) {
          $category = $row['category'];
          if ($category !== $currentCategory) {
              if ($currentCategory !== '') echo '</section>'; // close previous category section
              echo '<h2 class="menu-heading">' . htmlspecialchars($category) . '</h2>';
              echo '<section class="menu-grid">';
              $currentCategory = $category;
          }
          echo '<div class="menu-card">';
          echo '<img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '">';
          echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
          echo '<p class="price">₹' . htmlspecialchars($row['price']) . '</p>';
          echo '<a href="orders.php?item=' . urlencode($row['name']) . '"><button class="order-btn">Order This</button></a>';
          echo '</div>';
      }
      if ($currentCategory !== '') echo '</section>'; // close last category section
  } else {
      echo "<p>No menu items found.</p>";
  }
  ?>
</main>

<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">

<script>
const searchInput = document.getElementById("menuSearch");
const categoryFilter = document.getElementById("categoryFilter");
const sections = document.querySelectorAll("section.menu-grid");
const headings = document.querySelectorAll(".menu-heading");

// Auto-fill dropdown
headings.forEach(h => {
  const option = document.createElement("option");
  option.value = h.textContent.toLowerCase();
  option.textContent = h.textContent;
  categoryFilter.appendChild(option);
});

function filterMenu() {
  const query = searchInput.value.toLowerCase();
  const selectedCategory = categoryFilter.value.toLowerCase();
  sections.forEach(section => {
    const heading = section.previousElementSibling;
    let hasVisible = false;
    section.querySelectorAll(".menu-card").forEach(card => {
      const name = card.querySelector("h3").textContent.toLowerCase();
      const price = card.querySelector(".price").textContent.toLowerCase();
      const category = heading.textContent.toLowerCase();
      const matchesSearch = !query || name.includes(query) || price.includes(query);
      const matchesCategory = !selectedCategory || category.includes(selectedCategory);
      if (matchesSearch && matchesCategory) {
        card.style.display = "";
        hasVisible = true;
      } else card.style.display = "none";
    });
    heading.style.display = hasVisible ? "" : "none";
    section.style.display = hasVisible ? "" : "none";
  });
}

searchInput.addEventListener("input", filterMenu);
categoryFilter.addEventListener("change", filterMenu);
</script>

</body>
</html>
