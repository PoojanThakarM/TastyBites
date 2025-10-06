<?php
session_start();
include("db.php"); // MySQL connection
$message = "";
$userFile = "users.txt"; // Text file for storing user data

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm = htmlspecialchars(trim($_POST['confirm-password']));

    if (!preg_match("/^[A-Za-z\s]{3,}$/", $fullname)) {
        $message = "❌ Full name must be at least 3 letters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "❌ Invalid email.";
    } elseif (!preg_match("/^[A-Za-z0-9]{3,}$/", $username)) {
        $message = "❌ Username must be at least 3 characters (letters/numbers).";
    } elseif (strlen($password) < 8) {
        $message = "❌ Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $message = "❌ Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if($stmt->num_rows > 0){
            $message = "❌ Username or email already exists.";
        } else {
            $stmt->close();

            // Hash the password for both DB and file storage
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert into MySQL database
            $stmt = $conn->prepare("INSERT INTO users (fullname, username, email, password) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $fullname, $username, $email, $hashed);

            if($stmt->execute()){
                // Write to text file as well (append mode)
                $entry = $fullname . "||" . $email . "||" . $username . "||" . $hashed . "\n";
                if (file_put_contents($userFile, $entry, FILE_APPEND | LOCK_EX) === false) {
                    $message = "⚠️ Warning: Could not save data to text file.";
                } else {
                    $message = "✅ Registration successful! You can now login.";
                }

                // Set session and redirect
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['fullname'] = $fullname;

                header("Location: profile.php");
                exit();

            } else { 
                $message = "❌ Registration failed.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Tasty Bites</title>
<link rel="stylesheet" href="css/style-navbar.css">
<link rel="stylesheet" href="css/style-register.css">
<style>
.register-container { max-width:400px; margin:40px auto; background:#fffaf4; padding:25px; border-left:5px solid #E63946; border-radius:8px; }
.register-container h2 { color:#E63946; text-align:center; margin-bottom:15px; }
.register-container input { width:100%; padding:10px; margin:8px 0; border-radius:4px; border:1px solid #ccc; }
.register-container button { width:100%; padding:10px; background:#E63946; color:#fff; border:none; border-radius:6px; font-weight:bold; cursor:pointer; }
.register-container button:hover{ background:#d62828; }
.register-container a{ color:#E63946; text-decoration:none; }
</style>
</head>
<body>
<header>
    <h1 class="restaurant-title">🍽️ Tasty Bites Restaurant</h1>
<?php include("navbar.php"); ?>
</header>

<main>
<div class="register-container">
<h2>Register</h2>
<?php if(!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
<form method="POST" action="">
    <label>Full Name:</label>
    <input type="text" name="fullname" placeholder="Enter full name" required>
    <label>Email:</label>
    <input type="email" name="email" placeholder="Enter email" required>
    <label>Username:</label>
    <input type="text" name="username" placeholder="Choose username" required>
    <label>Password:</label>
    <input type="password" name="password" placeholder="Enter password" required>
    <label>Confirm Password:</label>
    <input type="password" name="confirm-password" placeholder="Confirm password" required>
    <button type="submit">Register</button>
</form>
<p style="text-align:center;">Already have an account? <a href="login.php">Login here</a></p>
</div>
</main>

<?php include("footer.php"); ?>
<link rel="stylesheet" href="css/style-footer.css">
</body>
</html>
