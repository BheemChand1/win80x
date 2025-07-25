<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Include DB connection
include '../connection.php';

// Read the raw input
$input = json_decode(file_get_contents('php://input'), true);

// Get game_id from JSON input
$game_id = isset($input['game_id']) ? intval($input['game_id']) : 0;

if ($game_id === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid or missing game_id"]);
    exit;
}

// SQL to get results with user info
$sql = "SELECT gr.user_id, gr.winning_price, gr.declared_at, 
               u.name, u.game_id
        FROM gameplay_results gr
        JOIN users u ON gr.user_id = u.id
        WHERE gr.game_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

$results = [];

while ($row = $result->fetch_assoc()) {
    $results[] = $row;
}

echo json_encode([
    "status" => "success",
    "game_id" => $game_id,
    "results" => $results
]);
