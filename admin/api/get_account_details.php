<?php
// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include DB connection
include "../connection.php";

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Get user_id from JSON
$user_id = $input['user_id'] ?? null;

if (!$user_id) {
    echo json_encode([
        'status' => false,
        'message' => 'User ID is required in JSON body.'
    ]);
    exit;
}

// Prepare and execute query
$sql = "SELECT account_no, ifsc_code, account_holder_name FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => true,
        'data' => $row
    ]);
} else {
    echo json_encode([
        'status' => false,
        'message' => 'User not found.'
    ]);
}

$stmt->close();
$conn->close();
?>
