<?php
include("db.php");
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $guests = intval($_POST['guests']);

    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        $message = "❌ Name should only contain letters and spaces.";
    } elseif (!preg_match("/^[0-9]{10}$/", $contact)) {
        $message = "❌ Contact number must be exactly 10 digits.";
    } elseif ($guests < 1) {
        $message = "❌ Number of guests must be at least 1.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, name, contact, reservation_date, reservation_time, guests, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->bind_param("issssi", $user_id, $name, $contact, $date, $time, $guests);
        if ($stmt->execute()) {
            $message = "✅ Reservation submitted! Waiting for admin approval.";
        } else {
            $message = "❌ Error saving reservation.";
        }
        $stmt->close();
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Table Reservation - Tasty Bites</title>
  <link rel="stylesheet" href="css/reservation.css">
  <link rel="stylesheet" href="css/style-navbar.css">
</head>
<body>
  <header>
    <h2 class="restaurant-title">🍽️ Tasty Bites Restaurant</h2>
  </header>

  <?php include("navbar.php"); ?>

  <main>
    <section class="hero">
      <h1>📅 Reserve Your Table</h1>
      <p>Plan your perfect meal at Tasty Bites. Book a table easily and enjoy a delightful dining experience!</p>
    </section>

    <div class="reservation-container">
      <h2>📝 Reservation Form</h2>
      <?php if ($message): ?>
        <p style="color:<?= strpos($message,'✅')!==false ? 'green' : 'red' ?>; font-weight:bold;"><?= $message ?></p>
      <?php endif; ?>
      <form method="POST">
        <label for="name">👤 Full Name:</label>
        <input type="text" id="name" name="name" placeholder="Enter your full name" required>

        <label for="contact">📞 Contact Number:</label>
        <input type="text" id="contact" name="contact" placeholder="10-digit number" required>

        <label for="date">📆 Reservation Date:</label>
        <input type="date" id="date" name="date" required>

        <label for="time">⏰ Reservation Time:</label>
        <input type="time" id="time" name="time" required>

        <label for="guests">👥 Number of Guests:</label>
        <input type="number" id="guests" name="guests" min="1" value="1" required>

        <center><button type="submit" class="main-btn">Book Now</button></center>
      </form>
    </div>

    
  </main>
<style>/* Reservation Form Container */
.reservation-container {
    max-width: 500px;
    margin: 40px auto;
    padding: 30px;
    background-color: #fff3e0;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    font-family: 'Lato', sans-serif;
}

.reservation-container h2 {
    font-family: 'Poppins', sans-serif;
    color: #E63946;
    font-size: 22px;
    margin-bottom: 20px;
    text-align: center;
}

/* Form Fields */
.reservation-container form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.reservation-container label {
    font-weight: bold;
    color: #333;
    margin-bottom: 4px;
}

.reservation-container input {
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    transition: border 0.3s ease;
}

.reservation-container input:focus {
    outline: none;
    border-color: #E63946;
    box-shadow: 0 0 5px rgba(230, 57, 70, 0.3);
}

/* Submit Button */
.main-btn {
    background-color: #E63946;
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    font-weight: bold;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
    margin-top: 10px;
}

.main-btn:hover {
    background-color: #d62828;
}

/* Success/Error Message */
.reservation-container p {
    margin: 10px 0;
    font-weight: bold;
    text-align: center;
}
</style>
  <?php include("footer.php"); ?>
  <link rel="stylesheet" href="css/style-footer.css">
</body>
</html>
