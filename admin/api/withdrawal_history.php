<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
include '../connection.php';

// Get user_id from JSON or query string
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'] ?? $_GET['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'user_id is required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, amount, status, created_at, updated_at FROM withdrawal_requests WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }

    echo json_encode([
        'success' => true,
        'history' => $history
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
?>
