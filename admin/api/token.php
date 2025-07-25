<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include '../connection.php';

$response = [];

// Get JSON input
$data = json_decode(file_get_contents("php://input"));

// Check required fields
if (isset($data->user_id) && isset($data->game_id) && isset($data->token)) {
    $user_id = intval($data->user_id);
    $game_id = intval($data->game_id);
    $token = intval($data->token);

    $price = $token * 100;
    $unique_id = uniqid('gameplay_', true);

    $conn->begin_transaction();

    try {
        // Insert gameplay
        $stmt = $conn->prepare("INSERT INTO gameplays (user_id, game_id, price, unique_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $user_id, $game_id, $price, $unique_id);
        $stmt->execute();
        if ($stmt->affected_rows <= 0) {
            throw new Exception("Failed to insert gameplay");
        }

        // Update wallet
        $update_stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
        $update_stmt->bind_param("di", $price, $user_id);
        $update_stmt->execute();
        if ($update_stmt->affected_rows <= 0) {
            throw new Exception("Failed to update wallet balance");
        }

        // Insert transaction
        $transaction_type = 'Debited';
        $transaction_for = 'Token Purchased';
        $history_stmt = $conn->prepare("INSERT INTO user_transaction_history (user_id, amount, transaction_type, transaction_for) VALUES (?, ?, ?, ?)");
        $history_stmt->bind_param("idss", $user_id, $price, $transaction_type, $transaction_for);
        $history_stmt->execute();
        if ($history_stmt->affected_rows <= 0) {
            throw new Exception("Failed to insert transaction history");
        }

        $conn->commit();

        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Gameplay inserted and wallet updated successfully!",
            "gameplay" => [
                "user_id" => $user_id,
                "game_id" => $game_id,
                "price" => $price,
                "unique_id" => $unique_id
            ]
        ]);

        // Close statements
        $stmt->close();
        $update_stmt->close();
        $history_stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Transaction failed: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Incomplete data. user_id, game_id, and token are required."]);
}

$conn->close();
