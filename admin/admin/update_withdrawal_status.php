<?php
include '../connection.php';

$request_id = $_POST['request_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;
$amount = $_POST['amount'] ?? 0;
$action = $_POST['action'] ?? null;

if (!$request_id || !$user_id || !$action || $amount <= 0) {
    die("Invalid input.");
}

$conn->begin_transaction();

try {
    // 🔎 Get transaction_id from withdrawal_requests
    $getTxn = $conn->prepare("SELECT transaction_id FROM withdrawal_requests WHERE id = ?");
    $getTxn->bind_param("i", $request_id);
    $getTxn->execute();
    $txnResult = $getTxn->get_result();
    $txnRow = $txnResult->fetch_assoc();

    if (!$txnRow || empty($txnRow['transaction_id'])) {
        throw new Exception("Transaction ID not found for withdrawal request.");
    }

    $transaction_id = $txnRow['transaction_id'];

    if ($action === 'approve') {
        // ✅ Update withdrawal request status
        $update = $conn->prepare("UPDATE withdrawal_requests SET status = 'success', updated_at = NOW() WHERE id = ?");
        $update->bind_param("i", $request_id);
        $update->execute();

        // ✅ Update transaction_for to 'Withdrawal Successful'
        $updateTxn = $conn->prepare("UPDATE user_transaction_history 
            SET transaction_for = 'Withdrawal Successful' 
            WHERE user_id = ? AND transaction_id = ?");
        $updateTxn->bind_param("is", $user_id, $transaction_id);
        $updateTxn->execute();

    } elseif ($action === 'reject') {
        // ✅ Refund wallet balance
        $refund = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $refund->bind_param("di", $amount, $user_id);
        $refund->execute();

        // ✅ Update withdrawal request status
        $update = $conn->prepare("UPDATE withdrawal_requests SET status = 'rejected', updated_at = NOW() WHERE id = ?");
        $update->bind_param("i", $request_id);
        $update->execute();

        // ✅ Update existing transaction row instead of inserting new one
        $updateTxn = $conn->prepare("UPDATE user_transaction_history 
            SET transaction_for = 'Withdrawal Rejected', transaction_type = 'Credited' 
            WHERE user_id = ? AND transaction_id = ?");
        $updateTxn->bind_param("is", $user_id, $transaction_id);
        $updateTxn->execute();
    }

    $conn->commit();
    header("Location: withdrawal_requests.php");
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
