<?php
session_start();
include("db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle status update via dropdown
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_reservations.php");
    exit();
}

// Handle reservation addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_reservation'])) {
    $name = htmlspecialchars(trim($_POST['name']));
    $contact = htmlspecialchars(trim($_POST['contact']));
    $date = $_POST['reservation_date'];
    $time = $_POST['reservation_time'];
    $guests = (int)$_POST['guests'];
    $status = $_POST['status'];
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

    $stmt = $conn->prepare("INSERT INTO reservations (user_id, name, contact, reservation_date, reservation_time, guests, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssis", $user_id, $name, $contact, $date, $time, $guests, $status);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_reservations.php");
    exit();
}

// Handle edit update (for fields other than status)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_reservation'])) {
    $id = (int)$_POST['id'];
    $name = htmlspecialchars(trim($_POST['name']));
    $contact = htmlspecialchars(trim($_POST['contact']));
    $date = $_POST['reservation_date'];
    $time = $_POST['reservation_time'];
    $guests = (int)$_POST['guests'];
    $status = $_POST['status'];
    $user_id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

    $stmt = $conn->prepare("UPDATE reservations 
                            SET user_id=?, name=?, contact=?, reservation_date=?, reservation_time=?, guests=?, status=? 
                            WHERE id=?");
    $stmt->bind_param("issssisi", $user_id, $name, $contact, $date, $time, $guests, $status, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_reservations.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_reservations.php");
    exit();
}

// Fetch reservations (with user info if exists)
$result = $conn->query("SELECT r.id, u.username, r.name, r.contact, r.reservation_date, r.reservation_time, 
                               r.guests, r.status, r.created_at
                        FROM reservations r
                        LEFT JOIN users u ON r.user_id = u.id
                        ORDER BY r.created_at DESC");
$reservations = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Reservations | Tasty Bites</title>
    <link rel="stylesheet" href="css/style-admin.css">
    <style>
        /* Reservation Management Specific Styles */
        main > h1 {
            font-size: 24px;
            color: #E63946;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 20px;
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

        /* Action Buttons */
        .action-btn {
            padding: 6px 10px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s ease-in-out;
        }

        .edit-btn {
            background: #457b9d;
            color: white;
        }

        .edit-btn:hover {
            background: #365f7a;
        }

        .delete-btn {
            background: #E63946;
            color: white;
        }

        .delete-btn:hover {
            background: #c43036;
        }

        /* Status Dropdown */
        .status-select {
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
            background: #fff;
            cursor: pointer;
            width: 120px;
        }

        .status-select:focus {
            outline: none;
            border-color: #457B9D;
        }

        /* Floating Add Button */
        .add-btn {
            background: #2a9d8f;
            color: white;
            border: none;
            border-radius: 50%;
            width: 55px;
            height: 55px;
            font-size: 30px;
            position: fixed;
            bottom: 25px;
            right: 25px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: background 0.3s;
        }

        .add-btn:hover {
            background: #21867a;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            padding: 25px;
            margin: 5% auto;
            border-radius: 10px;
            max-width: 500px;
            position: relative;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            font-weight: bold;
            color: #E63946;
            cursor: pointer;
        }

        /* Modal Form */
        .modal-content form label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .modal-content input,
        .modal-content select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .modal-content button[type="submit"] {
            background: #E63946;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
        }

        .modal-content button[type="submit"]:hover {
            background: #d62828;
        }

        /* Responsive Table Scroll */
        @media screen and (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .modal-content {
                width: 90%;
                margin: 10% auto;
            }
        }
    </style>
</head>
<body>
    <?php include("admin_header.php"); ?>
    <header>
        <h1>🍽️ Admin - Manage Reservations</h1>
    </header>
    <main>
        <h1>Reservation List</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Name</th>
                <th>Contact</th>
                <th>Date</th>
                <th>Time</th>
                <th>Guests</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($reservations as $row): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td><?= $row['username'] ? htmlspecialchars($row['username']) : '—'; ?></td>
                    <td><?= htmlspecialchars($row['name']); ?></td>
                    <td><?= htmlspecialchars($row['contact']); ?></td>
                    <td><?= date("d M Y", strtotime($row['reservation_date'])); ?></td>
                    <td><?= date("h:i A", strtotime($row['reservation_time'])); ?></td>
                    <td><?= $row['guests']; ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <select name="status" class="status-select" onchange="this.form.submit()">
                                <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Approved" <?= $row['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="Declined" <?= $row['status'] === 'Declined' ? 'selected' : '' ?>>Declined</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                    <td><?= date("d M Y H:i", strtotime($row['created_at'])); ?></td>
                    <td>
                        <button class="action-btn edit-btn" onclick="openEditModal(<?= $row['id']; ?>,
                            '<?= htmlspecialchars($row['name'], ENT_QUOTES); ?>',
                            '<?= htmlspecialchars($row['contact'], ENT_QUOTES); ?>',
                            '<?= $row['reservation_date']; ?>',
                            '<?= $row['reservation_time']; ?>',
                            <?= $row['guests']; ?>,
                            '<?= $row['status']; ?>')">Edit</button>
                        <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Delete this reservation?');">
                            <button class="action-btn delete-btn">Delete</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>

    <!-- Add Button -->
    <button class="add-btn" id="openAddModal">+</button>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeAdd">&times;</span>
            <h3>Add New Reservation</h3>
            <form method="POST">
                <input type="hidden" name="add_reservation" value="1">
                <label>Name:</label>
                <input type="text" name="name" required>
                <label>Contact:</label>
                <input type="text" name="contact" required>
                <label>Date:</label>
                <input type="date" name="reservation_date" required>
                <label>Time:</label>
                <input type="time" name="reservation_time" required>
                <label>Guests:</label>
                <input type="number" name="guests" min="1" required>
                <label>Status:</label>
                <select name="status">
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Declined">Declined</option>
                </select>
                <button type="submit">Add Reservation</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEdit">&times;</span>
            <h3>Edit Reservation</h3>
            <form method="POST">
                <input type="hidden" name="edit_reservation" value="1">
                <input type="hidden" name="id" id="edit_id">
                <label>Name:</label>
                <input type="text" name="name" id="edit_name" required>
                <label>Contact:</label>
                <input type="text" name="contact" id="edit_contact" required>
                <label>Date:</label>
                <input type="date" name="reservation_date" id="edit_date" required>
                <label>Time:</label>
                <input type="time" name="reservation_time" id="edit_time" required>
                <label>Guests:</label>
                <input type="number" name="guests" id="edit_guests" min="1" required>
                <label>Status:</label>
                <select name="status" id="edit_status">
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Declined">Declined</option>
                </select>
                <button type="submit">Update</button>
            </form>
        </div>
    </div>

    <script>
        // Modal Handling
        const addModal = document.getElementById("addModal");
        const editModal = document.getElementById("editModal");
        document.getElementById("openAddModal").onclick = () => addModal.style.display = "block";
        document.getElementById("closeAdd").onclick = () => addModal.style.display = "none";
        document.getElementById("closeEdit").onclick = () => editModal.style.display = "none";

        window.onclick = (e) => {
            if (e.target === addModal) addModal.style.display = "none";
            if (e.target === editModal) editModal.style.display = "none";
        };

        // Fill edit modal
        function openEditModal(id, name, contact, date, time, guests, status) {
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_name").value = name;
            document.getElementById("edit_contact").value = contact;
            document.getElementById("edit_date").value = date;
            document.getElementById("edit_time").value = time;
            document.getElementById("edit_guests").value = guests;
            document.getElementById("edit_status").value = status;
            editModal.style.display = "block";
        }
    </script>

    <?php include("admin_footer.php"); ?>
</body>
</html>