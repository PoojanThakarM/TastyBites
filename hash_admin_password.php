<?php
// hash_admin_password.php
// ONE-TIME SCRIPT — remove after use!

include("db.php"); // make sure this path correctly includes your DB connection

$admin_username = "Poojan";            // <- change to the admin username in your DB
$new_plain_password = "963852741"; // <- change to the admin's current plaintext password

$hashed_password = password_hash($new_plain_password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $admin_username);

if ($stmt->execute()) {
    echo "✅ Password hashed and updated for user '{$admin_username}'.";
} else {
    echo "❌ Update failed: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
