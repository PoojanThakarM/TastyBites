<?php
session_start();
include("db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$orders = [];

// Update Order Status and Payment Status
if (isset($_POST['update']) && isset($_POST['id'])) {
    $stmt = $conn->prepare("UPDATE orders SET status = ?, payment_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $_POST['status'], $_POST['payment_status'], $_POST['id']);
    if ($stmt->execute()) {
        $message = "Order #" . $_POST['id'] . " updated successfully.";
    } else {
        $message = "Failed to update order #" . $_POST['id'] . ".";
    }
    $stmt->close();
    // Redirect to avoid form resubmission
    header("Location: admin_orders.php");
    exit();
}

// Delete Order
if (isset($_POST['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $_POST['delete_id']);
    $stmt->execute();
    $stmt->close();
    // Redirect to avoid form resubmission
    header("Location: admin_orders.php");
    exit();
}

// Fetch orders
$res = $conn->query("SELECT * FROM orders ORDER BY order_date DESC");
$orders = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Orders | Tasty Bites</title>
    <link rel="stylesheet" href="css/style-admin.css">
    <style>
        /* General Layout */
        body {
            font-family: 'Poppins', sans-serif;
            background: #fafafa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .admin-main {
            padding: 20px;
        }

        h2 {
            color: #E63946;
            margin-bottom: 15px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table th {
            background: #f4f4f4;
            font-weight: 600;
            color: #444;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        /* Buttons */
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s ease;
        }

        .delete {
            background: #E63946;
            color: #fff;
        }
        .delete:hover {
            background: #b32636;
        }

        /* Status Dropdowns */
        .status-select, .payment-select {
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            background: #fff;
            cursor: pointer;
            width: 120px;
        }
        .status-select:focus, .payment-select:focus {
            outline: none;
            border-color: #457B9D;
        }

        /* Items Column */
        .order-items {
            font-size: 12px;
            color: #555;
            max-width: 200px;
            word-wrap: break-word;
        }

        /* Message */
        .message {
            text-align: center;
            color: green;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include("admin_header.php"); ?>

    <div class="admin-main">
        <h2>📋 Manage Orders</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <table>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Customer Name</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Items</th>
                <th>Total</th>
                <th>Order Status</th>
                <th>Payment Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php if (count($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= $order['user_id'] ?></td>
                        <td><?= htmlspecialchars($order['name'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($order['contact'] ?: '-') ?></td>
                        <td><?= htmlspecialchars($order['address'] ?: '-') ?></td>
                        <td class="order-items"><?= htmlspecialchars($order['items'] ?: '-') ?></td>
                        <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Preparing" <?= $order['status'] === 'Preparing' ? 'selected' : '' ?>>Preparing</option>
                                    <option value="Ready" <?= $order['status'] === 'Ready' ? 'selected' : '' ?>>Ready</option>
                                    <option value="Delivered" <?= $order['status'] === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                                    <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="payment_status" value="<?= $order['payment_status'] ?>">
                                <input type="hidden" name="update" value="1">
                            </form>
                        </td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $order['id'] ?>">
                                <select name="payment_status" class="payment-select" onchange="this.form.submit()">
                                    <option value="Pending" <?= $order['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Completed" <?= $order['payment_status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="Failed" <?= $order['payment_status'] === 'Failed' ? 'selected' : '' ?>>Failed</option>
                                </select>
                                <input type="hidden" name="status" value="<?= $order['status'] ?>">
                                <input type="hidden" name="update" value="1">
                            </form>
                        </td>
                        <td><?= date('M d, Y H:i', strtotime($order['order_date'])) ?></td>
                        <td>
                            <!-- Delete Form -->
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?= $order['id'] ?>">
                                <button class="btn delete" onclick="return confirm('Delete this order?')">🗑 Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="11">No orders yet.</td></tr>
            <?php endif; ?>
        </table>
    </div>

    <?php include("admin_footer.php"); ?>
</body>
</html>