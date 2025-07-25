<?php
include '../../connection.php';

header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    // Validate JSON decoding
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response = [
            'status' => 'error',
            'message' => 'Invalid JSON input.'
        ];
        echo json_encode($response);
        exit;
    }

    // Extract user_id and token from decoded JSON
    $user_id = isset($data['user_id']) ? intval($data['user_id']) : null;
    $token = isset($data['token']) ? $data['token'] : null;

    // Log file path: Save log file in the same directory as the current script
    $logFile = __DIR__ . '/log.txt'; // Log file saved in the current directory

    // Log user_id and token to a custom log file (be cautious with sensitive data)
    $logMessage = 'API Hit: User ID = ' . $user_id . ', Token = ' . $token . "\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // Validate input
    if (!$user_id || !$token) {
        $response = [
            'status' => 'error',
            'message' => 'Invalid input. User ID and Token are required.'
        ];
        echo json_encode($response);
        exit;
    }

    try {
        // Start a transaction
        mysqli_begin_transaction($conn);

        // Check if the token exists in the users table
        $query = "SELECT id FROM users WHERE expo_notification_token = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existingUser = mysqli_fetch_assoc($result);

        // If the token exists, remove it from that user
        if ($existingUser) {
            $query = "UPDATE users SET expo_notification_token = NULL WHERE id = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $existingUser['id']);
            mysqli_stmt_execute($stmt);
        }

        // Assign the token to the given user_id
        $query = "UPDATE users SET expo_notification_token = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $token, $user_id);
        mysqli_stmt_execute($stmt);

        // Commit the transaction
        mysqli_commit($conn);

        $response = [
            'status' => 'success',
            'message' => 'Token updated successfully.'
        ];
    } catch (Exception $e) {
        // Rollback in case of error
        mysqli_rollback($conn);
        $response = [
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ];
        file_put_contents($logFile, 'Error: ' . $e->getMessage() . "\n", FILE_APPEND); // Log the error
    }
} else {
    $response = [
        'status' => 'error',
        'message' => 'Invalid request method. Use POST.'
    ];
}

echo json_encode($response);
?>