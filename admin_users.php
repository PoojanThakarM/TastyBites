<?php
session_start();
include("db.php");

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Messages to show after actions
$message = "";
$message_type = "success";

// Handle delete user
if (isset($_GET['delete'])) {
    $idToDelete = (int)$_GET['delete'];
    if ($_SESSION['user_id'] != $idToDelete) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $idToDelete);
        $stmt->execute();
        $stmt->close();
        $message = "User deleted successfully.";
    } else {
        $message = "You cannot delete your own account.";
        $message_type = "error";
    }
}

// Handle promote/demote user role
if (isset($_GET['toggle_role'])) {
    $idToToggle = (int)$_GET['toggle_role'];
    if ($_SESSION['user_id'] != $idToToggle) {
        // Get current role
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->bind_param("i", $idToToggle);
        $stmt->execute();
        $stmt->bind_result($currentRole);
        if ($stmt->fetch()) {
            $newRole = ($currentRole === 'admin') ? 'user' : 'admin';
            $stmt->close();

            // Update role
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $newRole, $idToToggle);
            $stmt->execute();
            $stmt->close();

            $message = "User role updated to '$newRole'.";
        } else {
            $message = "User not found.";
            $message_type = "error";
        }
    } else {
        $message = "You cannot change your own role.";
        $message_type = "error";
    }
}

// Handle edit user form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user_id'])) {
    $editId = (int)$_POST['edit_user_id'];
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    if (!empty($fullname) && !empty($username) && !empty($email) && ($role === 'user' || $role === 'admin')) {
        // Check if username or email is taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->bind_param("ssi", $username, $email, $editId);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = "Username or Email already taken by another user.";
            $message_type = "error";
        } else {
            $stmt->close();
            $stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $fullname, $username, $email, $role, $editId);
            $stmt->execute();
            $stmt->close();
            $message = "User updated successfully.";
        }
        $stmt->close();
    } else {
        $message = "Please fill all fields correctly.";
        $message_type = "error";
    }
}

// Search/filter functionality
$search = "";
$whereClause = "";
$params = [];
$paramTypes = "";

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = trim($_GET['search']);
    $searchLike = "%$search%";
    $whereClause = "WHERE fullname LIKE ? OR username LIKE ? OR email LIKE ? OR role LIKE ?";
    $params = [$searchLike, $searchLike, $searchLike, $searchLike];
    $paramTypes = "ssss";
}

// Fetch users with optional search filter
if ($whereClause) {
    $stmt = $conn->prepare("SELECT id, fullname, username, email, role, created_at FROM users $whereClause ORDER BY created_at DESC");
    $stmt->bind_param($paramTypes, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT id, fullname, username, email, role, created_at FROM users ORDER BY created_at DESC");
}

$users = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - Manage Users</title>
  <link rel="stylesheet" href="css/style-admin.css" />
  <style>
    /* Basic styling for table and modal */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }
    th {
      background-color: #f4a261;
      color: white;
    }
    .btn {
      padding: 5px 10px;
      margin-right: 5px;
      cursor: pointer;
      border: none;
      border-radius: 4px;
      font-size: 0.9rem;
    }
    .btn.edit { background-color: #2a9d8f; color: white; }
    .btn.delete { background-color: #e76f51; color: white; }
    .btn.promote { background-color: #264653; color: white; }
    .btn.demote { background-color: #f4a261; color: white; }

    .message {
      margin: 1rem 0;
      padding: 10px;
      border-radius: 4px;
      font-weight: bold;
    }
    .message.success { background-color: #d4edda; color: #155724; }
    .message.error { background-color: #f8d7da; color: #721c24; }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.6);
    }
    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border-radius: 8px;
      max-width: 400px;
      position: relative;
    }
    .close-btn {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 24px;
      cursor: pointer;
      font-weight: bold;
    }
    .modal-content label {
      display: block;
      margin: 10px 0 5px;
    }
    .modal-content input[type="text"],
    .modal-content input[type="email"],
    .modal-content select {
      width: 100%;
      padding: 8px;
      border-radius: 4px;
      border: 1px solid #ccc;
    }
    .modal-content button {
      margin-top: 15px;
      background-color: #2a9d8f;
      color: white;
      border: none;
      padding: 10px;
      width: 100%;
      border-radius: 4px;
      font-size: 1rem;
      cursor: pointer;
    }
    .modal-content button:hover {
      background-color: #21867a;
    }

    /* Search form */
    .search-form {
      margin-top: 1rem;
    }
    .search-form input[type="text"] {
      padding: 7px;
      width: 250px;
      border-radius: 4px;
      border: 1px solid #ccc;
      margin-right: 5px;
    }
    .search-form button {
      padding: 7px 15px;
      border-radius: 4px;
      border: none;
      background-color: #264653;
      color: white;
      cursor: pointer;
    }
    .search-form button:hover {
      background-color: #1b3330;
    }
  </style>
</head>
<body>

<?php include("admin_header.php"); ?>

<header class="admin-header">
  <h1>👥 Admin - Manage Users</h1>
</header>

<main class="admin-main">

  <?php if ($message): ?>
    <div class="message <?= $message_type === 'error' ? 'error' : 'success' ?>">
      <?= htmlspecialchars($message) ?>
    </div>
  <?php endif; ?>

  <!-- Search Form -->
  <form method="GET" class="search-form" action="">
    <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>" />
    <button type="submit">Search</button>
    <?php if($search): ?>
      <a href="admin_users.php" style="margin-left: 10px;">Clear</a>
    <?php endif; ?>
  </form>

  <!-- Users Table -->
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Username</th>
        <th>Email</th>
        <th>Role</th>
        <th>Created At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($users) === 0): ?>
        <tr><td colspan="7" style="text-align:center;">No users found.</td></tr>
      <?php else: ?>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['fullname']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['role']) ?></td>
            <td><?= $user['created_at'] ?></td>
            <td>
              <?php if ($_SESSION['user_id'] != $user['id']): ?>
                <button 
                  class="btn edit" 
                  onclick="openEditModal(<?= $user['id'] ?>, '<?= htmlspecialchars(addslashes($user['fullname'])) ?>', '<?= htmlspecialchars(addslashes($user['username'])) ?>', '<?= htmlspecialchars(addslashes($user['email'])) ?>', '<?= $user['role'] ?>')">
                  Edit
                </button>

                <?php if ($user['role'] === 'user'): ?>
                  <a href="?toggle_role=<?= $user['id'] ?>" onclick="return confirm('Promote user to admin?');">
                    <button class="btn promote">Promote</button>
                  </a>
                <?php else: ?>
                  <a href="?toggle_role=<?= $user['id'] ?>" onclick="return confirm('Demote admin to user?');">
                    <button class="btn demote">Demote</button>
                  </a>
                <?php endif; ?>

                <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Are you sure you want to delete this user?');">
                  <button class="btn delete">Delete</button>
                </a>
              <?php else: ?>
                <em>You</em>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Edit User Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeEditModal()">&times;</span>
      <h2>Edit User</h2>
      <form method="POST" action="">
        <input type="hidden" name="edit_user_id" id="edit_user_id" />
        <label for="fullname">Full Name</label>
        <input type="text" name="fullname" id="edit_fullname" required />

        <label for="username">Username</label>
        <input type="text" name="username" id="edit_username" required />

        <label for="email">Email</label>
        <input type="email" name="email" id="edit_email" required />

        <label for="role">Role</label>
        <select name="role" id="edit_role" required>
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>

        <button type="submit">Save Changes</button>
      </form>
    </div>
  </div>

</main>

<?php include("admin_footer.php"); ?>

<script>
  const modal = document.getElementById("editModal");

  function openEditModal(id, fullname, username, email, role) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_fullname').value = fullname;
    document.getElementById('edit_username').value = username;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;

    modal.style.display = "block";
  }

  function closeEditModal() {
    modal.style.display = "none";
  }

  window.onclick = function(event) {
    if (event.target == modal) {
      closeEditModal();
    }
  };
</script>

</body>
</html>
