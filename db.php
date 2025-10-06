<?php
$servername = "localhost";
$username = "root";    // your MySQL username
$password = "";        // your MySQL password
$dbname = "tastybites"; // your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("❌Connection failed: " . $conn->connect_error);
}
?>
