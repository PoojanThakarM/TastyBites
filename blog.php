<?php
session_start();
include("db.php");

$message = "";

// Display and clear message from session (used after redirect)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// CREATE Blog
if (isset($_SESSION['user_id'], $_POST['title'], $_POST['content']) && isset($_POST['create_blog'])) {
    $title = htmlspecialchars(trim($_POST['title']));
    $content = htmlspecialchars(trim($_POST['content']));
    $user_id = $_SESSION['user_id'];

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $title, $content);
        if ($stmt->execute()) {
    // Optional: Set a success message in session to show after redirect
    $_SESSION['message'] = "✅ Blog posted successfully!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} else {
    $message = "❌ Error posting blog.";
}

        $stmt->close();
    } else {
        $message = "❌ Please provide both title and content.";
    }
}

// EDIT Blog
if (isset($_SESSION['user_id'], $_POST['edit_id'], $_POST['edit_title'], $_POST['edit_content'])) {
    $edit_id = intval($_POST['edit_id']);
    $title = htmlspecialchars(trim($_POST['edit_title']));
    $content = htmlspecialchars(trim($_POST['edit_content']));
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE blog_posts SET title=?, content=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssii", $title, $content, $edit_id, $user_id);
    if ($stmt->execute()) {
    $_SESSION['message'] = "✅ Blog updated!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} else {
    $message = "❌ Failed to update blog.";
}

    $stmt->close();
}

// DELETE Blog
if (isset($_SESSION['user_id'], $_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    if ($stmt->execute()) {
    $_SESSION['message'] = "✅ Blog deleted!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
} else {
    $message = "❌ Failed to delete blog.";
}

    $stmt->close();
}

// Fetch blogs
$blogs = [];
$result = $conn->query("SELECT b.id, b.title, b.content, b.created_at, u.username, b.user_id 
                        FROM blog_posts b 
                        JOIN users u ON b.user_id = u.id 
                        ORDER BY b.created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $blogs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Blogs - Tasty Bites</title>
  <link rel="stylesheet" href="css/style-blog.css">
  <link rel="stylesheet" href="css/style-navbar.css">
</head>
<body>

<header>
  <h1>🍽️ Tasty Bites Restaurant</h1>
  <?php include("navbar.php"); ?>
</header>

<main>
  <div class="centered-text">
    <h1 class="blog-heading">📝 Latest Blogs 📝</h1>
    <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>
  </div>

  <div class="blog-container">
    <?php if (count($blogs) > 0): ?>
      <?php foreach ($blogs as $b): ?>
        <div class="blog-card">
          <h3><?php echo htmlspecialchars($b['title']); ?> - <?php echo htmlspecialchars($b['username']); ?></h3>
          <p><?php echo nl2br(htmlspecialchars($b['content'])); ?></p>
          <p class="date"><?php echo $b['created_at']; ?></p>
          <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $b['user_id']): ?>
            <button class="editBtn" 
                    data-id="<?php echo $b['id']; ?>" 
                    data-title="<?php echo htmlspecialchars($b['title']); ?>" 
                    data-content="<?php echo htmlspecialchars($b['content']); ?>">
              ✏️ Edit
            </button>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="delete_id" value="<?php echo $b['id']; ?>">
              <button type="submit" class="deleteBtn">🗑️ Delete</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p>No blogs yet. Be the first to post!</p>
    <?php endif; ?>
  </div>

  <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Floating menu button -->
    <div class="fab-container">
      <button id="fabMain">+</button>
      <div class="fab-options">
        <button id="openCreate">➕ Create</button>
        <button id="openEdit">✏️ Edit</button>
        <button id="openDelete">🗑️ Delete</button>
      </div>
    </div>

    <!-- Create Blog Modal -->
    <div id="createModal" class="modal">
      <div class="modal-content">
        <span class="close-btn" data-close="createModal">&times;</span>
        <h3>Create Blog</h3>
        <form method="POST">
          <input type="hidden" name="create_blog" value="1">
          <input type="text" name="title" placeholder="Title" required>
          <textarea name="content" rows="6" placeholder="Write your blog..." required></textarea>
          <button type="submit">Post</button>
        </form>
      </div>
    </div>

    <!-- Edit Blog Modal -->
    <div id="editModal" class="modal">
      <div class="modal-content">
        <span class="close-btn" data-close="editModal">&times;</span>
        <h3>Edit Blog</h3>
        <form method="POST">
          <input type="hidden" name="edit_id" id="edit_id">
          <input type="text" name="edit_title" id="edit_title" required>
          <textarea name="edit_content" id="edit_content" rows="6" required></textarea>
          <button type="submit">Update</button>
        </form>
      </div>
    </div>

  <?php endif; ?>
</main>
<style>/* Floating action menu */
.fab-container {
  position: fixed;
  bottom: 25px;
  right: 25px;
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  z-index: 2000;
}

#fabMain {
  background: #E63946;
  color: #fff;
  border: none;
  border-radius: 50%;
  width: 60px;
  height: 60px;
  font-size: 32px;
  cursor: pointer;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.fab-options {
  display: none;
  flex-direction: column;
  margin-bottom: 10px;
}

.fab-options.show {
  display: flex;
}

.fab-options button {
  margin: 5px 0;
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  background: #fff3e0;
  cursor: pointer;
  font-weight: bold;
}

.fab-options button:hover {
  background: #ffe0cc;
}

.editBtn, .deleteBtn {
  margin-top: 10px;
  padding: 6px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.editBtn {
  background: #ffb347;
  color: #fff;
}

.deleteBtn {
  background: #d62828;
  color: #fff;
}
</style>
<?php include("footer.php"); ?>
<link rel="stylesheet" href="style-footer.css">

<script>
  // Floating menu toggle
  const fabMain = document.getElementById("fabMain");
  const fabOptions = document.querySelector(".fab-options");
  fabMain.onclick = () => fabOptions.classList.toggle("show");

  // Modal open/close
  document.querySelectorAll("[id^='open']").forEach(btn => {
    btn.onclick = () => {
      const target = btn.id.replace("open", "").toLowerCase() + "Modal";
      document.getElementById(target).style.display = "block";
    };
  });
  document.querySelectorAll(".close-btn").forEach(btn => {
    btn.onclick = () => document.getElementById(btn.dataset.close).style.display = "none";
  });
  window.onclick = e => {
    document.querySelectorAll(".modal").forEach(m => {
      if (e.target === m) m.style.display = "none";
    });
  };

  // Fill edit modal with blog data
  document.querySelectorAll(".editBtn").forEach(btn => {
    btn.onclick = () => {
      document.getElementById("edit_id").value = btn.dataset.id;
      document.getElementById("edit_title").value = btn.dataset.title;
      document.getElementById("edit_content").value = btn.dataset.content;
      document.getElementById("editModal").style.display = "block";
    };
  });
</script>
</body>
</html>
