<?php
include("db.php");

// Fetch all upcoming events
$res = $conn->query("SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC, event_time ASC");
$events = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Events - Tasty Bites</title>
  <link rel="stylesheet" href="css/style-navbar.css" />
  <link rel="stylesheet" href="css/events.css" />
</head>
<body>

<header>
  <h1 class="restaurant-title">🍽️ Tasty Bites Restaurant</h1>
  <?php include("navbar.php"); ?>
</header>

<main>
  <div class="event-container">
    <h2>🎉 Upcoming Events</h2>
    <p>Join us for exciting culinary experiences at Tasty Bites! From festive dinners to live cooking shows, there’s always something special waiting for you.</p>
    
    <?php if(count($events)): ?>
      <ul class="event-list">
        <?php foreach($events as $e): ?>
          <li>
            <h3>🍽️ <?= htmlspecialchars($e['title']) ?> – <?= date("d M Y", strtotime($e['event_date'])) ?></h3>
            <p><?= htmlspecialchars($e['description']) ?></p>
            <?php if($e['image_url']): ?>
              <img src="<?= $e['image_url'] ?>" alt="<?= htmlspecialchars($e['title']) ?>" style="max-width:100%; border-radius:6px; margin-top:5px;">
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No upcoming events at the moment. Check back soon!</p>
    <?php endif; ?>
  </div>
</main>

<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">
</body>
</html>
