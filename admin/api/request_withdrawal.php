<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");
include '../connection.php';

$data = json_decode(file_get_contents('php://input'), true);

$amount = $data['amount'] ?? 0;
$user_id = $data['user_id'] ?? null;

if ($amount <= 0 || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Auto generate transaction ID
$generated_transaction_id = 'WIDRW' . strtoupper(uniqid());

$conn->begin_transaction();

try {
    // Step 1: Get current balance
    $balanceStmt = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $balanceStmt->bind_param("i", $user_id);
    $balanceStmt->execute();
    $result = $balanceStmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception("User not found");
    }

    if ($user['wallet_balance'] < $amount) {
        throw new Exception("Insufficient wallet balance");
    }

    // Step 2: Deduct wallet balance
    $updateWallet = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?");
    $updateWallet->bind_param("di", $amount, $user_id);
    $updateWallet->execute();

    if ($updateWallet->affected_rows <= 0) {
        throw new Exception("Failed to deduct wallet amount");
    }

    // Step 3: Insert into withdrawal_requests with transaction_id
    $insertRequest = $conn->prepare("INSERT INTO withdrawal_requests (user_id, amount, transaction_id) VALUES (?, ?, ?)");
    $insertRequest->bind_param("ids", $user_id, $amount, $generated_transaction_id);
    $insertRequest->execute();

    if ($insertRequest->affected_rows <= 0) {
        throw new Exception("Failed to insert withdrawal request");
    }

    // Step 4: Insert into transaction history
    $transaction_for = "Withdrawal Requested";
    $insertHistory = $conn->prepare("INSERT INTO user_transaction_history (user_id, amount, transaction_type, transaction_for, transaction_id) VALUES (?, ?, 'Withdrew', ?, ?)");
    $insertHistory->bind_param("idss", $user_id, $amount, $transaction_for, $generated_transaction_id);
    $insertHistory->execute();

    if ($insertHistory->affected_rows <= 0) {
        throw new Exception("Failed to insert transaction history");
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Withdrawal requested successfully',
        'new_balance' => $user['wallet_balance'] - $amount
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$balanceStmt->close();
$updateWallet->close();
$insertRequest->close();
$insertHistory->close();
?>
