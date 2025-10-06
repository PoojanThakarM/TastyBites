<?php
session_start();
session_unset();
session_destroy();

// Clear cookie
setcookie("login_user", "", time() - 3600, "/");

// Redirect to home
header("Location: index.php");
exit();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Logout - Tasty Bites</title>
  <link rel="stylesheet" href="css/style-navbar.css" />
</head>
<body>

<header>
  <h1>🍽️ Tasty Bites Restaurant</h1><br>
  <?php include("navbar.php"); ?>
</header>


<main class="logout-container">
  <h2>👋 You have been logged out</h2>
  <p>Thank you for visiting Tasty Bites!</p>
  <a href="index.php" class="main-btn">Go to Home</a>
</main>


<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">


</body>
</html>
