<?php
session_start();
include("db.php");

// Optional: Protect the page for admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Messages for feedback
$message = "";
$message_type = "success";

// CREATE: Add new product
if (isset($_POST['action']) && $_POST['action'] === 'create') {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    if ($product_name && $quantity >= 0 && $price >= 0) {
        $stmt = $conn->prepare("INSERT INTO inventory (product_name, description, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssid", $product_name, $description, $quantity, $price);
        if ($stmt->execute()) {
            $message = "Product added successfully!";
        } else {
            $message = "Error adding product: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill all required fields correctly.";
        $message_type = "error";
    }
}

// UPDATE: Edit product
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)$_POST['id'];
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $quantity = (int)$_POST['quantity'];
    $price = (float)$_POST['price'];

    if ($id && $product_name && $quantity >= 0 && $price >= 0) {
        $stmt = $conn->prepare("UPDATE inventory SET product_name = ?, description = ?, quantity = ?, price = ? WHERE id = ?");
        $stmt->bind_param("ssid", $product_name, $description, $quantity, $price, $id);
        if ($stmt->execute()) {
            $message = "Product updated successfully!";
        } else {
            $message = "Error updating product: " . $conn->error;
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill all required fields correctly.";
        $message_type = "error";
    }
}

// DELETE: Delete product
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
    } else {
        $message = "Error deleting product: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// READ: Fetch all products
$result = $conn->query("SELECT * FROM inventory ORDER BY created_at DESC");
$products = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Inventory Management</title>
<link rel="stylesheet" href="css/style-admin.css" />
<style>
    body { font-family: Arial, sans-serif; max-width: 1500px; margin: auto; padding: 1rem; }
    table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #E63946; color: white; }
    .message { margin: 1rem 0; padding: 10px; border-radius: 4px; font-weight: bold; }
    .message.success { background-color: #d4edda; color: #E63946; }
    .message.error { background-color: #f8d7da; color: #721c24; }
    form { margin-top: 1rem; border: 1px solid #ccc; padding: 1rem; border-radius: 6px; }
    label { display: block; margin: 0.5rem 0 0.2rem; }
    input[type="text"], input[type="number"], textarea { width: 100%; padding: 7px; border-radius: 4px; border: 1px solid #ccc; }
    button { margin-top: 10px; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; background-color: #E63946; color: white; }
    button.delete-btn { background-color: #e74c3c; }
    button.edit-btn { background-color: #3498db; }
</style>
<script>
function fillEditForm(id, product_name, description, quantity, price) {
    document.getElementById('action').value = 'update';
    document.getElementById('id').value = id;
    document.getElementById('product_name').value = product_name;
    document.getElementById('description').value = description;
    document.getElementById('quantity').value = quantity;
    document.getElementById('price').value = price;
    document.getElementById('submit-btn').textContent = 'Update Product';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function resetForm() {
    document.getElementById('inventory-form').reset();
    document.getElementById('action').value = 'create';
    document.getElementById('id').value = '';
    document.getElementById('submit-btn').textContent = 'Add Product';
}
</script>
</head>
<body>
<?php include("admin_header.php"); ?>
<h1>🍅 Inventory Management</h1>

<?php if ($message): ?>
    <div class="message <?= $message_type === 'error' ? 'error' : 'success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Inventory Form -->
<form id="inventory-form" method="POST" action="" onreset="resetForm()">
    <input type="hidden" name="action" id="action" value="create" />
    <input type="hidden" name="id" id="id" value="" />

    <label for="product_name">Product Name *</label>
    <input type="text" id="product_name" name="product_name" required />

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3"></textarea>

    <label for="quantity">Quantity *</label>
    <input type="number" id="quantity" name="quantity" min="0" required />

    <label for="price">Price (per unit) *</label>
    <input type="number" step="0.01" id="price" name="price" min="0" required />

    <button type="submit" id="submit-btn">Add Product</button>
    <button type="reset" style="background-color:#6c757d; margin-left: 10px;">Clear</button>
</form>

<!-- Inventory Table -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Product Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Price ($)</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($products) === 0): ?>
            <tr><td colspan="7" style="text-align:center;">No products found.</td></tr>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['product_name']) ?></td>
                <td><?= htmlspecialchars($p['description']) ?></td>
                <td><?= $p['quantity'] ?></td>
                <td><?= number_format($p['price'], 2) ?></td>
                <td><?= $p['created_at'] ?></td>
                <td>
                    <button class="edit-btn" 
                        onclick="fillEditForm(
                            <?= $p['id'] ?>, 
                            '<?= addslashes(htmlspecialchars($p['product_name'])) ?>', 
                            '<?= addslashes(htmlspecialchars($p['description'])) ?>', 
                            <?= $p['quantity'] ?>, 
                            <?= $p['price'] ?>
                        )">Edit</button>
                    <a href="?delete=<?= $p['id'] ?>" onclick="return confirm('Delete this product?');">
                        <button class="delete-btn" type="button">Delete</button>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<?php include("admin_footer.php"); ?>

</body>
</html>
