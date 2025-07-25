<?php
// Enable CORS for local testing (optional)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Require Razorpay SDK
require('vendor/autoload.php');

use Razorpay\Api\Api;

// Razorpay credentials
$keyId = 'rzp_test_f6nUeAuEPTNfL4';
$keySecret = 'qHasXViTnQwEYIqufGtNuzB6';

// Read incoming JSON from React Native
$input = json_decode(file_get_contents("php://input"), true);

// Check if amount is provided
if (!isset($input['amount'])) {
    echo json_encode(['status' => 'error', 'message' => 'Amount is required']);
    exit;
}

// Amount in paise (e.g., ₹500 = 50000)
$amount = intval($input['amount']);

try {
    // Initialize Razorpay API
    $api = new Api($keyId, $keySecret);

    // Create order
    $order = $api->order->create([
        'receipt'         => 'rcpt_' . rand(1000, 9999),
        'amount'          => $amount,
        'currency'        => 'INR',
        'payment_capture' => 1
    ]);

    echo json_encode([
        'status'   => 'success',
        'order_id' => $order['id'],
        'amount'   => $order['amount'],
        'currency' => $order['currency']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}
