<?php
session_start();
include("db.php");

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// CREATE or UPDATE
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $image_url = $_POST['image_url'];

    if (isset($_POST['create'])) {
        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, event_time, image_url) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $description, $event_date, $event_time, $image_url);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['update_id'])) {
        $update_id = $_POST['update_id'];
        $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, image_url=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $description, $event_date, $event_time, $image_url, $update_id);
        $stmt->execute();
        $stmt->close();
    }

    if (isset($_POST['delete_id'])) {
        $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
        $stmt->bind_param("i", $_POST['delete_id']);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_events.php");
    exit();
}

// Fetch all events
$res = $conn->query("SELECT * FROM events ORDER BY event_date ASC, event_time ASC");
$events = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Events | Tasty Bites</title>
<link rel="stylesheet" href="css/style-admin.css">
</head>
<body>

<?php include("admin_header.php"); ?>

<div class="admin-main">
    <h2>🎉 Events Dashboard</h2>

    <!-- Floating Add Button -->
    <button class="floating-btn" onclick="document.getElementById('create-form').style.display='block'">➕ Add Event</button>

    <!-- Create / Edit Form -->
    <div id="create-form" class="create-box" style="display:none;">
        <h3>➕ Add New Event</h3>
        <form method="post">
            <input type="text" name="title" placeholder="Event Title" required>
            <textarea name="description" placeholder="Event Description" required></textarea>
            <input type="date" name="event_date" required>
            <input type="time" name="event_time" required>
            <input type="text" name="image_url" placeholder="Image URL">
            <button type="submit" name="create" class="btn">Add Event</button>
        </form>
    </div>

    <table>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Description</th>
            <th>Date</th>
            <th>Time</th>
            <th>Image</th>
            <th>Actions</th>
        </tr>

        <?php if(count($events)): ?>
            <?php foreach($events as $e): ?>
            <tr>
                <td><?= $e['id'] ?></td>
                <td><?= htmlspecialchars($e['title']) ?></td>
                <td><?= htmlspecialchars($e['description']) ?></td>
                <td><?= $e['event_date'] ?></td>
                <td><?= $e['event_time'] ?></td>
                <td>
                    <?php if($e['image_url']): ?>
                        <img src="<?= $e['image_url'] ?>" alt="Event" style="width:80px;">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $e['id'] ?>">
                        <button class="btn delete">Delete</button>
                    </form>
                    <button class="btn edit" onclick="editEvent(<?= $e['id'] ?>,'<?= htmlspecialchars(addslashes($e['title'])) ?>','<?= htmlspecialchars(addslashes($e['description'])) ?>','<?= $e['event_date'] ?>','<?= $e['event_time'] ?>','<?= $e['image_url'] ?>')">Edit</button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7">No events yet.</td></tr>
        <?php endif; ?>
    </table>
</div>
<style>/* Admin - floating add button */
.floating-btn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    background:#E63946;
    color:white;
    border:none;
    padding:15px 20px;
    font-size:20px;
    border-radius:50%;
    cursor:pointer;
    box-shadow:0 4px 8px rgba(0,0,0,0.2);
    z-index:1000;
}

.create-box {
    background:white;
    padding:20px;
    margin:20px 0;
    border:1px solid #ccc;
    border-radius:8px;
}

.create-box input, .create-box textarea {
    width:100%;
    padding:10px;
    margin:8px 0;
    border-radius:4px;
    border:1px solid #ccc;
}

.create-box button.btn {
    background:#E63946;
    color:white;
    padding:10px 15px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    margin-top:10px;
}

.create-box button.btn:hover { background:#d62828; }

table { width:100%; border-collapse:collapse; margin-top:20px; }
table th, table td { padding:12px; border:1px solid #ddd; text-align:center; }
table th { background:#E63946; color:white; }
.btn { padding:6px 12px; border:none; border-radius:6px; cursor:pointer; margin:2px; }
.btn.delete { background:#D62828; color:white; }
.btn.edit { background:#2D6A4F; color:white; }

/* User events */
.events-container {
    display:grid;
    grid-template-columns: repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
    padding:20px;
}

.event-card {
    border:1px solid #ddd;
    border-radius:8px;
    overflow:hidden;
    box-shadow:0 4px 8px rgba(0,0,0,0.1);
    padding:15px;
    background:white;
}

.event-card img.event-img {
    width:100%;
    height:160px;
    object-fit:cover;
    border-radius:6px;
    margin-bottom:10px;
}

.event-card h3 { color:#E63946; margin:5px 0; }
.event-card p { font-size:14px; color:#555; }
</style>
<script>
function editEvent(id, title, desc, date, time, img) {
    let form = document.getElementById('create-form');
    form.style.display = 'block';
    form.innerHTML = `
        <h3>✏️ Edit Event</h3>
        <form method="post">
            <input type="hidden" name="update_id" value="${id}">
            <input type="text" name="title" value="${title}" required>
            <textarea name="description" required>${desc}</textarea>
            <input type="date" name="event_date" value="${date}" required>
            <input type="time" name="event_time" value="${time}" required>
            <input type="text" name="image_url" value="${img}">
            <button type="submit" name="update" class="btn">Update Event</button>
        </form>
    `;
}
</script>

<?php include("admin_footer.php"); ?>
</body>
</html>
