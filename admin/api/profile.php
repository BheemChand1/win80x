<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json');

// Include DB connection
include '../connection.php';

// Initialize response array
$response = [];

$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);

if ($result) {
    $users = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    $response['success'] = true;
    $response['data'] = $users;
} else {
    $response['success'] = false;
    $response['message'] = 'Error fetching users: ' . mysqli_error($conn);
}

// Output JSON response
echo json_encode($response);
?>
