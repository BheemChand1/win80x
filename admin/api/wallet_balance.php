<?php
header('Content-Type: application/json');

// Read raw JSON body
$input = json_decode(file_get_contents('php://input'), true);

include '../connection.php';

// Create DB connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => false, "message" => "Database connection failed"]);
    exit();
}

// Get user_id from JSON
$user_id = isset($input['user_id']) ? $input['user_id'] : null;

// Validate user_id
if (!$user_id || !is_numeric($user_id)) {
    echo json_encode(["status" => false, "message" => "Invalid or missing user_id"]);
    exit();
}

// Prepare SQL
$stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => true,
        "wallet_balance" => $row['wallet_balance']
    ]);
} else {
    echo json_encode(["status" => false, "message" => "User not found"]);
}

$stmt->close();
$conn->close();
?>
