<?php
session_start();
include("admin_header.php");
include("db.php");

// --- CRUD OPERATIONS ---

// CREATE (Admin can add blog too)
if (isset($_POST['create'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'] ?? 1; // fallback if admin posts

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $content);
        $stmt->execute();
        $stmt->close();
    }
}

// UPDATE
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=? WHERE id=?");
        $stmt->bind_param("ssi", $title, $content, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// DELETE
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM blog_posts WHERE id=$id");
}

// FETCH ALL BLOGS
$blogs = [];
$result = $conn->query("SELECT b.id, b.title, b.content, b.created_at, u.username 
                        FROM blog_posts b 
                        JOIN users u ON b.user_id = u.id 
                        ORDER BY b.created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
}
?>
<link rel="stylesheet" href="css/style-admin.css">
<div class="admin-main">
  <h2>📝 Manage Blogs</h2>

  <!-- ✅ Add Blog Floating Button -->
  <button id="addBlogBtn" class="floating-btn">+</button>

  <table>
    <tr>
      <th>ID</th>
      <th>Title</th>
      <th>Author</th>
      <th>Content</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>
    <?php if (count($blogs) > 0): ?>
      <?php foreach ($blogs as $b): ?>
        <tr>
          <td><?php echo $b['id']; ?></td>
          <td><?php echo htmlspecialchars($b['title']); ?></td>
          <td><?php echo htmlspecialchars($b['username']); ?></td>
          <td><?php echo substr(htmlspecialchars($b['content']), 0, 50) . "..."; ?></td>
          <td><?php echo $b['created_at']; ?></td>
          <td>
            <!-- Edit -->
            <button class="btn edit" onclick="openEditModal(<?php echo $b['id']; ?>)">✏ Edit</button>

            <!-- Delete -->
            <form method="post" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?php echo $b['id']; ?>">
              <button type="submit" class="btn delete">🗑 Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="6">No blogs posted yet.</td></tr>
    <?php endif; ?>
  </table>
</div>

<!-- ✅ Modal for Add Blog -->
<div id="createModal" class="modal-form">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('createModal')">&times;</span>
    <h3>Add New Blog</h3>
    <form method="post">
      <label>Title:</label>
      <input type="text" name="title" required>
      <label>Content:</label>
      <textarea name="content" rows="6" required></textarea>
      <button type="submit" name="create" class="btn">Post</button>
    </form>
  </div>
</div>

<!-- ✅ Modal for Edit Blog -->
<div id="editModal" class="modal-form">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
    <h3>Edit Blog</h3>
    <form method="post">
      <input type="hidden" name="id" id="edit_id">
      <label>Title:</label>
      <input type="text" name="title" id="edit_title" required>
      <label>Content:</label>
      <textarea name="content" rows="6" id="edit_content" required></textarea>
      <button type="submit" name="update" class="btn edit">Update</button>
    </form>
  </div>
</div>
<style>
/* Base styling */
body {
    font-family: 'Lato', sans-serif;
    background-color: #FAFAFA;
    margin: 0;
    padding: 0;
}

/* Main container for blog management */
.admin-main {
    max-width: 1100px;
    margin: 60px auto;
    padding: 30px;
    background-color: #fff3e0;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
}

.admin-main h2 {
    font-family: 'Poppins', sans-serif;
    font-size: 26px;
    font-weight: bold;
    text-align: center;
    color: #E63946;
    margin-bottom: 30px;
}

/* Blog Table */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
    background-color: #fffaf4;
    border-radius: 8px;
    overflow: hidden;
}

th, td {
    padding: 14px 12px;
    text-align: left;
    border-bottom: 1px solid #f1d7be;
}

th {
    background-color: #fcd5b5;
    color: #333;
    font-weight: bold;
    font-family: 'Poppins', sans-serif;
}

td {
    color: #444;
}

td:last-child {
    white-space: nowrap;
}

/* Action buttons inside table */
.btn {
    display: inline-block;
    padding: 7px 14px;
    font-size: 14px;
    font-weight: bold;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn.edit {
    background-color: #F4A261;
    color: #fff;
}

.btn.edit:hover {
    background-color: #E76F51;
}

.btn.delete {
    background-color: #E63946;
    color: #fff;
}

.btn.delete:hover {
    background-color: #D62828;
}

/* Floating add blog button */
.floating-btn {
    position: fixed;
    bottom: 25px;
    right: 25px;
    width: 55px;
    height: 55px;
    border-radius: 50%;
    font-size: 28px;
    background: #E63946;
    color: #fff;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transition: background 0.3s ease;
}

.floating-btn:hover {
    background: #D62828;
}

/* Modal styles */
.modal-form {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-content {
    background: #fff3e0;
    padding: 25px 30px;
    border-radius: 12px;
    width: 450px;
    max-width: 90%;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    position: relative;
    font-family: 'Lato', sans-serif;
}

.modal-content h3 {
    margin-top: 0;
    font-size: 22px;
    text-align: center;
    font-family: 'Poppins', sans-serif;
    color: #E63946;
}

/* Close (X) button */
.close-btn {
    position: absolute;
    top: 12px;
    right: 16px;
    font-size: 22px;
    cursor: pointer;
    color: #888;
    transition: color 0.2s ease;
}

.close-btn:hover {
    color: #333;
}

/* Form elements inside modals */
.modal-content label {
    display: block;
    margin-top: 10px;
    margin-bottom: 4px;
    font-weight: bold;
    color: #333;
}

.modal-content input,
.modal-content textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    margin-bottom: 15px;
    font-family: inherit;
    background-color: #fffaf4;
}

.modal-content textarea {
    resize: vertical;
}

.modal-content button {
    background-color: #E63946;
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    width: 100%;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
}

.modal-content button:hover {
    background-color: #D62828;
}

/* Responsive tweaks */
@media (max-width: 768px) {
    .admin-main {
        padding: 20px;
        margin: 30px 15px;
    }

    table, thead, tbody, th, td, tr {
        display: block;
    }

    tr {
        margin-bottom: 20px;
        background: #fffaf4;
        padding: 10px;
        border-radius: 10px;
    }

    th {
        display: none;
    }

    td {
        padding-left: 50%;
        position: relative;
        border-bottom: none;
    }

    td::before {
        position: absolute;
        left: 15px;
        top: 14px;
        font-weight: bold;
        content: attr(data-label);
        color: #E63946;
    }
}
</style>
<script>
  let blogs = <?php echo json_encode($blogs); ?>;

  // Open Create Modal
  document.getElementById("addBlogBtn").onclick = () => {
    document.getElementById("createModal").style.display = "flex";
  };

  // Open Edit Modal
  function openEditModal(id) {
    const blog = blogs.find(b => b.id == id);
    if (blog) {
      document.getElementById("edit_id").value = blog.id;
      document.getElementById("edit_title").value = blog.title;
      document.getElementById("edit_content").value = blog.content;
      document.getElementById("editModal").style.display = "flex";
    }
  }

  // Close Modal
  function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
  }

  window.onclick = function(e) {
    if (e.target.classList.contains('modal-form')) {
      e.target.style.display = "none";
    }
  }
</script>

<?php include("admin_footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">
