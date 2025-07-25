<?php
// Database credentials
$servername = "localhost"; // Change this to your MySQL server address
$username = "win80x_win80x"; // Change this to your MySQL username
$password = "k*NSZnA0n;5q"; // Change this to your MySQL password
$database = "win80x_db"; // Change this to your MySQL database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>