<?php
// Start the session
session_start();

// Destroy the session
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session data on the server

// Redirect to login page or another page
header("Location: ./index.php");
exit();
?>
