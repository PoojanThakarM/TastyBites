<?php
session_start();
include("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$menu_items = [];
$prefilled_item = isset($_GET['item']) ? urldecode(trim($_GET['item'])) : '';

// Fetch all menu items for datalist and validation
$result = $conn->query("SELECT name, price FROM menu_items WHERE available = 'Yes' ORDER BY category_order ASC, order_index ASC");
while ($row = $result->fetch_assoc()) {
    $menu_items[$row['name']] = $row['price'];
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);
    $items = $_POST['item'];
    $quantities = $_POST['quantity'];

    if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        $message = "❌ Name should only contain letters and spaces.";
    } elseif (!preg_match("/^[0-9]{10}$/", $contact)) {
        $message = "❌ Contact number must be exactly 10 digits.";
    } elseif (!(preg_match("/[A-Za-z]/", $address) && preg_match("/[0-9]/", $address))) {
        $message = "❌ Address must contain both letters and numbers.";
    } else {
        $order_summary = [];
        $total = 0.00;

        foreach ($items as $index => $item_name) {
            $item_name = trim($item_name);
            $qty = intval($quantities[$index]);

            if (isset($menu_items[$item_name]) && $qty > 0) {
                $price = $menu_items[$item_name];
                $subtotal = $price * $qty;
                $total += $subtotal;
                $order_summary[] = "$item_name x$qty";
            }
        }

        if (empty($order_summary)) {
            $message = "❌ No valid items selected.";
        } else {
            $items_string = implode(", ", $order_summary);
            $stmt = $conn->prepare("INSERT INTO orders (user_id, name, contact, address, items, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssd", $user_id, $name, $contact, $address, $items_string, $total);
            if ($stmt->execute()) {
                $order_id = $conn->insert_id; // Get the last inserted ID
                // Instead of redirecting to profile.php, offer payment option
                header("Location: payment.php?order_id=" . $order_id);
                exit();
            } else {
                $message = "❌ Failed to place order.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Order Food - Tasty Bites</title>
    <link rel="stylesheet" href="css/style-navbar.css">
    <link rel="stylesheet" href="css/style-footer.css">
    <style>
        .order-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 25px;
            background-color: #fffaf4;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            font-family: 'Poppins', sans-serif;
        }
        .order-container h2 {
            color: #E63946;
            text-align: center;
            margin-bottom: 20px;
        }
        .order-container label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .order-container input, .order-container textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        .item-row {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .item-row input {
            flex: 1;
        }
        .main-btn {
            background-color: #E63946;
            color: white;
            border: none;
            padding: 10px 16px;
            font-size: 16px;
            border-radius: 6px;
            margin-top: 20px;
            cursor: pointer;
            transition: 0.3s;
        }
        .main-btn:hover {
            background-color: #d62828;
        }
        .green-btn {
            background-color: #2a9d8f;
        }
        .green-btn:hover {
            background-color: #21867a;
        }
        .error {
            color: red;
            text-align: center;
            font-weight: bold;
        }
        .pay-btn {
            background-color: #2a9d8f;
            color: white;
            border: none;
            padding: 10px 16px;
            font-size: 16px;
            border-radius: 6px;
            margin-top: 10px;
            cursor: pointer;
            transition: 0.3s;
        }
        .pay-btn:hover {
            background-color: #21867a;
        }
    </style>
</head>
<body>
<header>
    <h1 class="restaurant-title">🍽️ Tasty Bites Restaurant</h1>
    <?php include("navbar.php"); ?>
</header>
<main>
    <div class="order-container">
        <h2>🧾 Place Your Order</h2>
        <?php if (!empty($message)): ?>
            <p class="error"><?= $message ?></p>
        <?php endif; ?>
        <form method="POST" id="orderForm">
            <label for="name">👤 Your Name:</label>
            <input type="text" id="name" name="name" required>
            <label for="contact">📞 Contact Number:</label>
            <input type="text" id="contact" name="contact" required>
            <label for="address">📦 Delivery Address:</label>
            <input type="text" id="address" name="address" required>
            <div id="itemsContainer">
                <div class="item-row">
                    <input list="menuItems" name="item[]" placeholder="🍔 Food Item" value="<?= htmlspecialchars($prefilled_item) ?>" required>
                    <input type="number" name="quantity[]" min="1" value="1" placeholder="🔢 Qty" required>
                </div>
            </div>
            <datalist id="menuItems">
                <?php foreach ($menu_items as $itemName => $price): ?>
                    <option value="<?= htmlspecialchars($itemName) ?>">
                <?php endforeach; ?>
            </datalist>
            <button type="button" class="main-btn green-btn" id="addItemBtn">➕ Add Another Item</button>
            <button type="submit" class="main-btn">Create Order</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($message)) {
            $order_id = $conn->insert_id; // Use last inserted ID if successful
            echo "<a href='payment.php?order_id=" . $order_id . "' class='pay-btn'>Pay Now for Order #$order_id</a>";
        }
        ?>
        <p style="text-align:center; margin-top:20px;"><a href="menu.php">⬅️ Back to Menu</a></p>
    </div>
</main>
<?php include("footer.php"); ?>
<script>
const addItemBtn = document.getElementById("addItemBtn");
const itemsContainer = document.getElementById("itemsContainer");
addItemBtn.addEventListener("click", () => {
    const newItem = document.createElement("div");
    newItem.classList.add("item-row");
    newItem.innerHTML = `
        <input list="menuItems" name="item[]" placeholder="🍔 Food Item" required>
        <input type="number" name="quantity[]" min="1" value="1" placeholder="🔢 Qty" required>
    `;
    itemsContainer.appendChild(newItem);
});
</script>
</body>
</html>