<?php
session_start();
include 'db.php'; // Your database connection

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: orders.php"); // Redirect if no valid order ID
    exit();
}

$order_id = (int)$_GET['order_id'];

// Fetch order details
$stmt = $conn->prepare("SELECT name, contact, address, items, total_amount, status, payment_status FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Confirmation - Tasty Bites</title>
    <link rel="stylesheet" href="css/style-navbar.css"> <!-- Use existing style -->
    <style>
        .confirmation-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 25px;
            background-color: #fffaf4;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        .confirmation-container h2 {
            color: #2a9d8f;
            margin-bottom: 20px;
        }
        .confirmation-container p {
            font-size: 16px;
            color: #333;
            margin: 10px 0;
        }
        .confirmation-container a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #2a9d8f;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .confirmation-container a:hover {
            background-color: #21867a;
        }
    </style>
</head>
<body>
<header>
    <?php include("navbar.php"); ?>
</header>
<main>
    <div class="confirmation-container">
        <h2>✅ Order Confirmed!</h2>
        <p>Thank you for your order, <?= htmlspecialchars($order['name']) ?>!</p>
        <p><strong>Order ID:</strong> <?= $order_id ?></p>
        <p><strong>Contact:</strong> <?= htmlspecialchars($order['contact']) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($order['address']) ?></p>
        <p><strong>Items:</strong> <?= htmlspecialchars($order['items']) ?></p>
        <p><strong>Total Amount:</strong> ₹<?= number_format($order['total_amount'], 2) ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
        <p><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>
        <a href="index.php">Back to Home</a>
    </div>
</main>
<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">
</body>
</html>