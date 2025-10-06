<?php
session_start();
include("db.php");

// Redirect if not admin (optional security check)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Example after admin login validation
$_SESSION['user_id'] = $admin_id;
$_SESSION['username'] = $admin_username;
$_SESSION['is_admin'] = true;


exit();

    exit();
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM reviews WHERE id = $id");
    header("Location: admin_reviews.php");
    exit();
}

// Handle edit update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_review'])) {
    $id = (int)$_POST['id'];
    $review_text = htmlspecialchars(trim($_POST['review_text']));
    $rating = (int)$_POST['rating'];

    if (!empty($review_text) && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("UPDATE reviews SET review_text = ?, rating = ? WHERE id = ?");
        $stmt->bind_param("sii", $review_text, $rating, $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_reviews.php");
    exit();
}

// Fetch all reviews with user info
$result = $conn->query("SELECT r.id, r.review_text, r.rating, r.created_at, u.username
                        FROM reviews r
                        JOIN users u ON r.user_id = u.id
                        ORDER BY r.created_at DESC");
$reviews = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Manage Reviews</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/style-admin.css">
</head>
<body>
    <style>/* Review Admin Specific Styles */
.action-btn {
  padding: 6px 10px;
  border: none;
  border-radius: 5px;
  font-size: 14px;
  cursor: pointer;
  transition: 0.2s ease-in-out;
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

/* Modal */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background-color: rgba(0,0,0,0.6);
}

.modal-content {
  background: white;
  padding: 20px;
  margin: 5% auto;
  border-radius: 10px;
  max-width: 500px;
  position: relative;
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}

.modal-content textarea,
.modal-content select {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.modal-content button {
  background: #E63946;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 5px;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.3s;
}

.modal-content button:hover {
  background: #d62828;
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
</style>
<?php include("admin_header.php"); ?>

<header>
  <h1>📝 Admin - Manage Reviews</h1>
</header>

<main>
  <h1>Review List</h1>
  <table>
    <tr>
      <th>ID</th>
      <th>User</th>
      <th>Review</th>
      <th>Rating</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>
    <?php foreach($reviews as $review): ?>
      <tr>
        <td><?= $review['id']; ?></td>
        <td><?= htmlspecialchars($review['username']); ?></td>
        <td><?= htmlspecialchars($review['review_text']); ?></td>
        <td><?= str_repeat("⭐", $review['rating']); ?></td>
        <td><?= $review['created_at']; ?></td>
        <td>
          <button class="action-btn edit-btn"
            onclick="openEditModal(<?= $review['id']; ?>,
              `<?= htmlspecialchars($review['review_text'], ENT_QUOTES); ?>`,
              <?= $review['rating']; ?>)">Edit</button>

          <a href="?delete=<?= $review['id']; ?>" onclick="return confirm('Delete this review?');">
            <button class="action-btn delete-btn">Delete</button>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</main>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeEdit">&times;</span>
    <h3>Edit Review</h3>
    <form method="POST">
      <input type="hidden" name="edit_review" value="1">
      <input type="hidden" name="id" id="edit_id">
      <label>Review Text:</label>
      <textarea name="review_text" id="edit_text" rows="4" required></textarea>
      <label>Rating:</label>
      <select name="rating" id="edit_rating" required>
        <option value="5">5 - Excellent</option>
        <option value="4">4 - Very Good</option>
        <option value="3">3 - Good</option>
        <option value="2">2 - Fair</option>
        <option value="1">1 - Poor</option>
      </select>
      <button type="submit">Update</button>
    </form>
  </div>
</div>

<script>
// Modal JS
const editModal = document.getElementById("editModal");
document.getElementById("closeEdit").onclick = () => editModal.style.display = "none";

window.onclick = (e) => {
  if (e.target === editModal) editModal.style.display = "none";
};

function openEditModal(id, text, rating) {
  document.getElementById("edit_id").value = id;
  document.getElementById("edit_text").value = text;
  document.getElementById("edit_rating").value = rating;
  editModal.style.display = "block";
}
</script>

<?php include("admin_footer.php"); ?>
</body>
</html>
