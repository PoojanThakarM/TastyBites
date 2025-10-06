<?php
session_start();
include("db.php"); // MySQL connection
$message = "";

// Handle login submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    $role = isset($_POST['role']) ? $_POST['role'] : ''; // Avoid undefined index

    if (!empty($username) && !empty($password) && !empty($role)) {
        $stmt = $conn->prepare("SELECT id, fullname, password, role FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $fullname, $db_password, $user_role);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();

            // Simple password check (no hashing)
            if (password_verify($password, $db_password))  {
                // Check role match
                if ($role === $user_role) {
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    $_SESSION['fullname'] = $fullname;
                    $_SESSION['role'] = $role;
                    // Set a cookie that lasts 1 day (86400 seconds)
                    setcookie("login_user", $username, time() + 86400, "/"); // Path "/" makes cookie available site-wide


                    if ($role === 'admin') {
                        header("Location: admin_dashboard.php");
                        exit();
                    } else {
                        header("Location: profile.php");
                        exit();
                    }
                } else {
                    $message = "❌ Role mismatch. Please select the correct role.";
                }
            } else {
                $message = "❌ Incorrect password.";
            }
        } else {
            $message = "❌ User not found.";
        }
        $stmt->close();
    } else {
        $message = "❌ Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Tasty Bites</title>
<link rel="stylesheet" href="css/style-navbar.css">
<link rel="stylesheet" href="css/style-login.css">

</head>
<body>
<header>
    <h2 class="restaurant-title">🍽️ Tasty Bites Restaurant</h2>
<?php include("navbar.php"); ?>
</header>

<main>
<div class="login-container">
<h2>Login</h2>
<?php if(!empty($message)) echo "<p style='color:red; font-weight:bold;'>$message</p>"; ?>
<form method="POST" action="">
    <label>Username or Email:</label>
    <input type="text" name="username" placeholder="Enter username or email" required>

    <label>Password:</label>
    <input type="password" name="password" placeholder="Enter password" required>

    <label>Role:</label>
    <select name="role" required>
        <option value="">--Select Role--</option>
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>

    <button type="submit">Login</button>
</form>
<p style="text-align:center;">Don't have an account? <a href="register.php">Register here</a></p>
</div>
</main>

<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">
</body>
</html>
