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
    $stmt = $conn->prepare("UPDATE faqs SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success'] = "FAQ status updated to $status!";
    header("Location: admin_faqs.php");
    exit();
}

// Handle FAQ addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_faq'])) {
    $question = htmlspecialchars(trim($_POST['question']));
    $answer = htmlspecialchars(trim($_POST['answer']));
    $status = $_POST['status'];
    $stmt = $conn->prepare("INSERT INTO faqs (question, answer, status) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $question, $answer, $status);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success'] = "FAQ added successfully!";
    header("Location: admin_faqs.php");
    exit();
}

// Handle FAQ edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_faq'])) {
    $id = (int)$_POST['id'];
    $question = htmlspecialchars(trim($_POST['question']));
    $answer = htmlspecialchars(trim($_POST['answer']));
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE faqs SET question = ?, answer = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssi", $question, $answer, $status, $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success'] = "FAQ updated successfully!";
    header("Location: admin_faqs.php");
    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['success'] = "FAQ deleted successfully!";
    header("Location: admin_faqs.php");
    exit();
}

// Fetch FAQs
$result = $conn->query("SELECT id, question, answer, status, created_at FROM faqs ORDER BY created_at DESC");
$faqs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage FAQs | Tasty Bites</title>
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
        .modal-content textarea,
        .modal-content select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .modal-content textarea {
            height: 100px;
            resize: vertical;
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

        /* Answer Column */
        .answer-col {
            max-width: 300px;
            word-wrap: break-word;
            font-size: 12px;
            color: #555;
        }

        /* Success Message */
        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 10px;
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
        <h1>🍽️ Admin - Manage FAQs</h1>
    </header>
    <main>
        <h1>FAQ List</h1>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Answer</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php if (count($faqs)): ?>
                <?php foreach ($faqs as $faq): ?>
                    <tr>
                        <td><?= $faq['id'] ?></td>
                        <td><?= htmlspecialchars($faq['question']) ?></td>
                        <td class="answer-col"><?= htmlspecialchars(substr($faq['answer'], 0, 100)) . (strlen($faq['answer']) > 100 ? '...' : '') ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $faq['id'] ?>">
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <option value="Published" <?= $faq['status'] === 'Published' ? 'selected' : '' ?>>Published</option>
                                    <option value="Unpublished" <?= $faq['status'] === 'Unpublished' ? 'selected' : '' ?>>Unpublished</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                        <td><?= date("d M Y H:i", strtotime($faq['created_at'])) ?></td>
                        <td>
                            <button class="action-btn edit-btn" onclick="openEditModal(<?= $faq['id'] ?>,
                                '<?= htmlspecialchars($faq['question'], ENT_QUOTES) ?>',
                                '<?= htmlspecialchars($faq['answer'], ENT_QUOTES) ?>',
                                '<?= $faq['status'] ?>')">Edit</button>
                            <a href="?delete=<?= $faq['id'] ?>" onclick="return confirm('Delete this FAQ?');">
                                <button class="action-btn delete-btn">Delete</button>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No FAQs yet.</td></tr>
            <?php endif; ?>
        </table>
    </main>

    <!-- Add Button -->
    <button class="add-btn" id="openAddModal">+</button>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeAdd">&times;</span>
            <h3>Add New FAQ</h3>
            <form method="POST">
                <input type="hidden" name="add_faq" value="1">
                <label>Question:</label>
                <input type="text" name="question" required>
                <label>Answer:</label>
                <textarea name="answer" required></textarea>
                <label>Status:</label>
                <select name="status">
                    <option value="Published">Published</option>
                    <option value="Unpublished">Unpublished</option>
                </select>
                <button type="submit">Add FAQ</button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeEdit">&times;</span>
            <h3>Edit FAQ</h3>
            <form method="POST">
                <input type="hidden" name="edit_faq" value="1">
                <input type="hidden" name="id" id="edit_id">
                <label>Question:</label>
                <input type="text" name="question" id="edit_question" required>
                <label>Answer:</label>
                <textarea name="answer" id="edit_answer" required></textarea>
                <label>Status:</label>
                <select name="status" id="edit_status">
                    <option value="Published">Published</option>
                    <option value="Unpublished">Unpublished</option>
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
        function openEditModal(id, question, answer, status) {
            document.getElementById("edit_id").value = id;
            document.getElementById("edit_question").value = question;
            document.getElementById("edit_answer").value = answer;
            document.getElementById("edit_status").value = status;
            editModal.style.display = "block";
        }
    </script>

    <?php include("admin_footer.php"); ?>
</body>
</html>