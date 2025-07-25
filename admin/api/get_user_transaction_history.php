<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../connection.php';

// Get JSON input from the request body
$data = json_decode(file_get_contents("php://input"));

// Validate the user_id
if (isset($data->user_id)) {
    $user_id = intval($data->user_id);

    // Prepare and execute the SQL query
    $sql = "SELECT id, amount, transaction_type, transaction_for, date(created_at) as created_at
            FROM user_transaction_history 
            WHERE user_id = ? 
            ORDER BY created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $transactions = [];

    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    // Respond with transaction history
    echo json_encode([
        "success" => true,
        "transactions" => $transactions
    ]);

    $stmt->close();
} else {
    // Invalid input
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "user_id is required"
    ]);
}

$conn->close();
?>
