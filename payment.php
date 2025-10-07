<?php
session_start();
include 'db.php'; // Your database connection

if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    header("Location: orders.php"); // Redirect if no valid order ID
    exit();
}

$order_id = (int)$_GET['order_id'];

// Fetch order details (for display)
$stmt = $conn->prepare("SELECT total_amount, items FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    echo "Order not found. Please create an order first.";
    exit();
}

$total = $order['total_amount'];

// Handle form submission (mock payment)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $card_number = $_POST['card_number'] ?? '';
    if (strlen($card_number) == 16 && is_numeric($card_number)) { // Simple validation
        $stmt = $conn->prepare("UPDATE orders SET status = 'Paid', payment_status = 'Completed' WHERE id = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        header("Location: order_confirmation.php?order_id=" . $order_id);
        exit();
    } else {
        $error = "Invalid card number. Please use a 16-digit number.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment for Order #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="css/style-navbar.css"> <!-- Use existing style -->
</head>
<body>
    <h2>Payment for Order #<?php echo $order_id; ?></h2>
    <p>Total Amount: ₹<?php echo number_format($total, 2); ?></p>
    <p>Items: <?php echo htmlspecialchars($order['items']); ?></p>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" action="">
        <label>Card Number (16 digits, e.g., 4111111111111111):</label><br>
        <input type="text" name="card_number" required><br><br>
        <button type="submit">Pay Now</button>
    </form>
    <a href="orders.php">Cancel</a>
    <?php include("footer.php"); ?>
    <link rel="stylesheet" href="css/style-footer.css">
</body>
</html>