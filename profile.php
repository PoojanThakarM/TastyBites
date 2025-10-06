<?php
session_start();
include("db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT fullname, username, email, created_at FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch Reservations
$stmt = $conn->prepare("SELECT * FROM reservations WHERE user_id=? ORDER BY reservation_date DESC, reservation_time DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$reservations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch Orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - Tasty Bites</title>
<link rel="stylesheet" href="css/style-navbar.css">
<link rel="stylesheet" href="css/style-profile.css">

<style>/* Grid layout for main content */
.main-sections {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 30px;
  margin: 40px auto;
  max-width: 1200px;
  padding: 0 20px;
}

/* Shared styles for both sections */
    .orders-container {
      background: #fffaf4;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      font-family: 'Poppins', sans-serif;
    }
    .orders-container h2 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 26px;
      color: #E63946;
      font-weight: bold;
    }
    .orders-container table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    .orders-container th, .orders-container td {
      padding: 14px;
      text-align: center;
      border-bottom: 1px solid #ddd;
    }
    .orders-container th {
      background: #E63946;
      color: #fff;
      font-weight: bold;
    }
    .orders-container tr:nth-child(even) {
      background-color: #fafafa;
    }
    .orders-container tr:hover {
      background: #fceaea;
      transition: 0.3s;
    }
    .order-status {
      font-weight: bold;
      padding: 6px 12px;
      border-radius: 20px;
      display: inline-block;
    }
    .order-status.pending {
      background: #fff3cd;
      color: #856404;
    }
    .order-status.cancelled {
      background: #f8d7da;
      color: #721c24;
    }
    .order-status.completed {
      background: #d4edda;
      color: #155724;
    }
    <style>/* === Reservations Section === */ .reservation-container {
       max-width: 900px; margin: 40px auto; padding: 20px; background: #fff8f0; 
       border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); font-family: 'Poppins', sans-serif; } 
       .reservation-container h2 { text-align: center; margin-bottom: 20px; font-size: 26px; color: #E63946; font-weight: bold; } 
       /* === Table Styling === */ .reservation-container table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .reservation-container th, .reservation-container td { padding: 14px; text-align: center; border-bottom: 1px solid #ddd; } 
        .reservation-container th { background: #E63946; color: #fff; font-weight: bold; font-size: 16px; } 
        .reservation-container tr:nth-child(even) { background-color: #fafafa; }
         .reservation-container tr:hover { background: #fceaea; transition: 0.3s; }
          /* === Reservation Status Colors === */ .status { font-weight: bold; padding: 6px 12px; border-radius: 20px; display: inline-block; }
           .status.pending { background: #fff3cd; color: #856404; } .status.approved { background: #d4edda; color: #155724; } 
           .status.declined { background: #f8d7da; color: #721c24; } .status.cancelled { background: #e2e3e5; color: #383d41; } 
           /* === Empty Message === */ .no-reservation { text-align: center; color: #777; margin-top: 20px; font-size: 16px; }
.no-reservation { text-align: center; color: #777; margin-top: 20px; font-size: 16px; }
.no-orders { text-align: center; color: #777; margin-top: 20px; font-size: 16px; }
</style>
</head>
<body>
<header>
  <h1 class="restaurant-title">🍽️ Tasty Bites Restaurant</h1>
  <?php include("navbar.php"); ?>
</header>

<main>
  <div style="text-align:center; margin:20px 0; color:#E63946;
  font-family:'Poppins',sans-serif; font-size:22px; font-weight:bold;">
    <h1>Your Profile</h1>
  </div>

  <div class="profile-container">
    <h2><?php echo htmlspecialchars($user['fullname']); ?></h2>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Member Since:</strong> <?php echo $user['created_at']; ?></p>
  </div>

  <div class="main-sections">
    <!-- Reservations Section -->
    <div class="reservation-container">
      <h2>📋 My Reservations</h2>
      <?php if(count($reservations)): ?>
        <table>
          <tr>
            <th>Date</th>
            <th>Time</th>
            <th>Guests</th>
            <th>Status</th>
          </tr>
          <?php foreach($reservations as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r['reservation_date']) ?></td>
            <td><?= htmlspecialchars($r['reservation_time']) ?></td>
            <td><?= htmlspecialchars($r['guests']) ?></td>
            <td><span class="status <?= strtolower($r['status']) ?>"><?= $r['status'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p class="no-reservation">❌ You have no reservations yet.</p>
      <?php endif; ?>
    </div>

    <!-- Orders Section -->
    <div class="orders-container">
      <h2>🛒 My Orders</h2>
      <?php if(count($orders)): ?>
        <table>
          <tr>
            <th>Date</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
          </tr>
          <?php foreach($orders as $o): ?>
          <tr>
            <td><?= htmlspecialchars(date("Y-m-d", strtotime($o['order_date']))) ?></td>
            <td><?= htmlspecialchars($o['items']) ?></td>
            <td>₹<?= htmlspecialchars($o['total_amount']) ?></td>
            <td><span class="order-status <?= strtolower($o['status']) ?>"><?= $o['status'] ?></span></td>
          </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p class="no-reservation">🛍️ You have no orders yet.</p>
      <?php endif; ?>
    </div>
  </div>
</main>

    

<br>
<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">
</body>
</html>
