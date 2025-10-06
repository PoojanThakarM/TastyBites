<?php
session_start();
include("db.php");

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ CREATE
if (isset($_POST['create'])) {
    $stmt = $conn->prepare("INSERT INTO menu_items (name, category, price, image_url, category_order, order_index, available) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->bind_param("ssdssi", $_POST['name'], $_POST['category'], $_POST['price'], $_POST['image_url'], $_POST['category_order'], $_POST['order_index']);
    $stmt->execute();
    $stmt->close();
}

// ✅ UPDATE
if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE menu_items SET name=?, category=?, price=?, image_url=?, category_order=?, order_index=? WHERE id=?");
    $stmt->bind_param("ssdssii", $_POST['name'], $_POST['category'], $_POST['price'], $_POST['image_url'], $_POST['category_order'], $_POST['order_index'], $_POST['id']);
    $stmt->execute();
    $stmt->close();
}

// ✅ DELETE
if (isset($_POST['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM menu_items WHERE id=?");
    $stmt->bind_param("i", $_POST['delete_id']);
    $stmt->execute();
    $stmt->close();
}

// ✅ Toggle Available
if (isset($_POST['toggle_id'])) {
    $stmt = $conn->prepare("UPDATE menu_items SET available = NOT available WHERE id=?");
    $stmt->bind_param("i", $_POST['toggle_id']);
    $stmt->execute();
    $stmt->close();
}

// Fetch menu items
$res = $conn->query("SELECT * FROM menu_items ORDER BY category_order, order_index ASC");
$items = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin - Menu | Tasty Bites</title>
<link rel="stylesheet" href="css/style-admin.css">
<style>
/* ✅ Modal Styling */
.modal-form {
    display:none;
    position:fixed;
    top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.6);
    justify-content:center;
    align-items:center;
    z-index:1000;
}
.modal-content {
    background:#fff;
    padding:20px;
    border-radius:10px;
    width:400px;
    max-width:90%;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
}
.modal-content h3 { margin-top:0; color:#E63946; }
.modal-content input { width:100%; padding:8px; margin:6px 0; }
.modal-content button { margin-top:10px; }
</style>
</head>
<body>

<?php include("admin_header.php"); ?>

<div class="admin-main">
    <h2>🍽️ Manage Menu</h2>
<button type="button" class="btn create-btn" onclick="document.getElementById('create-form').style.display='flex'">➕ Add New Item</button>
    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Category Order</th>
            <th>Item Order</th>
            <th>Available</th>
            <th>Actions</th>
        </tr>

        <?php if(count($items)): ?>
            <?php foreach($items as $item): ?>
            <tr>
                <td><?= $item['id'] ?></td>
                <td>
                    <?php if($item['image_url']): ?>
                        <img src="<?= htmlspecialchars($item['image_url']) ?>" width="60">
                    <?php else: ?> - <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['category']) ?></td>
                <td>₹<?= number_format($item['price'], 2) ?></td>
                <td><?= $item['category_order'] ?></td>
                <td><?= $item['order_index'] ?></td>
                <td><?= $item['available'] ? "✔" : "❌" ?></td>
                <td>
                  <div id="create-form" class="modal-form">
  <div class="modal-content">
    <h3>Add New Menu Item</h3>
    <form method="post">
      <input type="text" name="name" placeholder="Item Name" required>
      <input type="text" name="category" placeholder="Category" required>
      <input type="number" step="0.01" name="price" placeholder="Price" required>
      <input type="text" name="image_url" placeholder="Image URL">
      <input type="number" name="category_order" placeholder="Category Order" required>
      <input type="number" name="order_index" placeholder="Item Order" required>
      <button type="submit" name="create" class="btn">Add Item</button>
      <button type="button" class="btn cancel" onclick="document.getElementById('create-form').style.display='none'">Cancel</button>
    </form>
  </div>
</div>
                    <!-- ✅ EDIT BUTTON -->
                    <button type="button" class="btn edit-btn"
                        data-id="<?= $item['id'] ?>"
                        data-name="<?= htmlspecialchars($item['name']) ?>"
                        data-category="<?= htmlspecialchars($item['category']) ?>"
                        data-price="<?= $item['price'] ?>"
                        data-image="<?= htmlspecialchars($item['image_url']) ?>"
                        data-catorder="<?= $item['category_order'] ?>"
                        data-itemorder="<?= $item['order_index'] ?>">✏ Edit</button>

                    <!-- DELETE -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                        <button class="btn delete" onclick="return confirm('Delete this item?')">🗑 Delete</button>
                    </form>

                    <!-- TOGGLE AVAILABLE -->
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="toggle_id" value="<?= $item['id'] ?>">
                        <button class="btn <?= $item['available'] ? 'unavail' : 'avail' ?>">
                            <?= $item['available'] ? '❌ Mark Unavailable' : '✔ Mark Available' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9">No menu items yet.</td></tr>
        <?php endif; ?>
    </table>
</div>

<!-- ✅ Edit Modal -->
<div id="edit-form" class="modal-form">
  <div class="modal-content">
    <h3>Edit Menu Item</h3>
    <form method="post">
      <input type="hidden" name="id" id="edit-id">
      <input type="text" name="name" id="edit-name" placeholder="Item Name" required>
      <input type="text" name="category" id="edit-category" placeholder="Category" required>
      <input type="number" step="0.01" name="price" id="edit-price" placeholder="Price" required>
      <input type="text" name="image_url" id="edit-image" placeholder="Image URL">
      <input type="number" name="category_order" id="edit-catorder" placeholder="Category Order" required>
      <input type="number" name="order_index" id="edit-itemorder" placeholder="Item Order" required>
      <button type="submit" name="update" class="btn">Update</button>
      <button type="button" class="btn cancel" onclick="document.getElementById('edit-form').style.display='none'">Cancel</button>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit-id').value = this.dataset.id;
            document.getElementById('edit-name').value = this.dataset.name;
            document.getElementById('edit-category').value = this.dataset.category;
            document.getElementById('edit-price').value = this.dataset.price;
            document.getElementById('edit-image').value = this.dataset.image;
            document.getElementById('edit-catorder').value = this.dataset.catorder;
            document.getElementById('edit-itemorder').value = this.dataset.itemorder;
            document.getElementById('edit-form').style.display = 'flex';
        });
    });
});
</script>
<style>/* General Layout */
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

table img {
  border-radius: 6px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
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

.edit-btn {
  background: #457B9D;
  color: #fff;
}
.edit-btn:hover {
  background: #35607a;
}

.delete {
  background: #E63946;
  color: #fff;
}
.delete:hover {
  background: #b32636;
}

.avail {
  background: #2a9d8f;
  color: #fff;
}
.avail:hover {
  background: #1d6f65;
}

.unavail {
  background: #f4a261;
  color: #fff;
}
.unavail:hover {
  background: #d97f32;
}

.cancel {
  background: #999;
  color: #fff;
}
.cancel:hover {
  background: #777;
}

/* Modal Styling (extra polish) */
.modal-form {
  display: none;
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 20px;
  border-radius: 10px;
  width: 420px;
  max-width: 90%;
  box-shadow: 0 6px 20px rgba(0,0,0,0.25);
  animation: fadeIn 0.25s ease-out;
}

.modal-content h3 {
  margin-top: 0;
  color: #E63946;
  text-align: center;
}

.modal-content input {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

.modal-content button {
  margin-top: 8px;
  width: 48%;
}

.modal-content form {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
<?php include("admin_footer.php"); ?>
</body>
</html>
