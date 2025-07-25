<?php
// Database credentials
$host = "localhost"; // Change this to your MySQL server address
$username = "win80x_win80x"; 
$password = "k*NSZnA0n;5q"; 
$database = "win80x_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
