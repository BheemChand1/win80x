<?php
/**
 * Win80x OTP Sender - Clean Version
 */

// Clean output buffer and set headers
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Method not allowed']));
}

// Get and validate JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid JSON input']));
}

if (!isset($data['email']) || empty($data['email'])) {
    die(json_encode(['status' => 'error', 'message' => 'Email is required']));
}

// Validate email format
$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid email format']));
}

// Generate 6-digit OTP
$otp = rand(100000, 999999);

// Create email content
$subject = 'Win80x Email Verification OTP';
$body = "Dear user,\n\nYour OTP for email verification is: $otp\n\nThis OTP is valid for 10 minutes.\n\nRegards,\nWin80x Team\n\nTime: " . date('Y-m-d H:i:s');
$headers = "From: Win80x <support@win80x.com>\r\nReply-To: support@win80x.com\r\nContent-Type: text/plain; charset=utf-8";

// Ensure otps directory exists
$otpDir = __DIR__ . '/otps';
if (!is_dir($otpDir)) {
    mkdir($otpDir, 0777, true);
}

// Sanitize filename (replace @ and .)
$otpFile = $otpDir . '/' . str_replace(['@', '.'], ['_at_', '_dot_'], $email) . '.txt';

// Send email
$sent = mail($email, $subject, $body, $headers);

// Log result
$log = date('Y-m-d H:i:s') . " - " . ($sent ? "SUCCESS" : "FAILED") . " - OTP sent to $email\n";
file_put_contents(__DIR__ . '/otp-logs.txt', $log, FILE_APPEND | LOCK_EX);

// Return clean JSON response
if ($sent) {
    // Store OTP for verification
    file_put_contents($otpFile, $otp . '|' . (time() + 600)); // OTP expires in 10 minutes
    
    die(json_encode([
        'status' => 'success',
        'message' => 'OTP sent successfully',
        'otp' => $otp
    ]));
} else {
    die(json_encode([
        'status' => 'error',
        'message' => 'Failed to send OTP. Please try again.'
    ]));
}
