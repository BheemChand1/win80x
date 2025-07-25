<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

include '../connection.php';

$response = array();

// Get the posted data
$data = json_decode(file_get_contents('php://input'), true);
$amount = $data['amount'] ?? 0;
$user_id = $data['user_id'] ?? null;
$transaction_id = $data['transaction_id'] ?? null;

if ($amount <= 0 || !$user_id || !$transaction_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid amount, user_id or transaction_id']);
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    $updateSql = "UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('di', $amount, $user_id);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        throw new Exception('Failed to update wallet');
    }

    $transaction_for = 'Wallet Update';
    $transactionSql = "INSERT INTO user_transaction_history (user_id, amount, transaction_type, transaction_for, transaction_id) VALUES (?, ?, 'Credited', ?, ?)";
    $stmt = $conn->prepare($transactionSql);
    $stmt->bind_param('idss', $user_id, $amount, $transaction_for, $transaction_id);
    $stmt->execute();

    if ($stmt->affected_rows <= 0) {
        throw new Exception('Failed to insert transaction');
    }

    $conn->commit();

    $balanceStmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $balanceStmt->bind_param('i', $user_id);
    $balanceStmt->execute();
    $result = $balanceStmt->get_result();
    $user = $result->fetch_assoc();

    echo json_encode([
        'success' => true,
        'new_balance' => $user['wallet_balance']
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$stmt->close();
?>
