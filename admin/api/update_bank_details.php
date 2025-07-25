<?php
// Set header to accept JSON
header("Content-Type: application/json");

include '../connection.php';

// Check DB connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit;
}

// Read the raw POST body and decode JSON
$data = json_decode(file_get_contents("php://input"), true);

// Check if all required fields are present
if (
    isset($data['user_id']) &&
    isset($data['account_no']) &&
    isset($data['ifsc_code']) &&
    isset($data['account_holder_name'])
) {
    // Escape values to prevent SQL injection
    $user_id = $conn->real_escape_string($data['user_id']);
    $account_no = $conn->real_escape_string($data['account_no']);
    $ifsc_code = $conn->real_escape_string($data['ifsc_code']);
    $account_holder_name = $conn->real_escape_string($data['account_holder_name']);

    // SQL to update user record
    $sql = "UPDATE users SET 
                account_no = '$account_no', 
                ifsc_code = '$ifsc_code', 
                account_holder_name = '$account_holder_name'
            WHERE id = '$user_id'";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Bank details updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to update record"]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
}

$conn->close();
?>
