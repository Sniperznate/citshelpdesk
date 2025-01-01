<?php
$servername = "sql210.infinityfree.com"; // MySQL server
$username = "if0_37970113"; // your database username
$password = "33012004 "; // your database password
$dbname = "user_db"; // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
