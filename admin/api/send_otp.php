<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Composer autoload

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// POST check
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Parse JSON
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE || empty($input['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing email']);
    exit;
}

// Validate email
$email = filter_var(trim($input['email']), FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

// Generate OTP
$otp = rand(100000, 999999);

// Compose email
$subject = 'Win80x Email Verification OTP';
$body = "Dear user,\n\nYour OTP for email verification is: $otp\n\nThis OTP is valid for 10 minutes.\n\nRegards,\nWin80x Team\nTime: " . date('Y-m-d H:i:s');

// Send via PHPMailer
$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host = 'smtp.win80x.com';       // ✅ Change this
    $mail->SMTPAuth = true;
    $mail->Username = 'support@win80x.com';     // ✅ Change this
    $mail->Password = 'win80x@123';    // ✅ Change this (App Password if Gmail)
    $mail->SMTPSecure = 'tls';                  // Or 'ssl' if port 465
    $mail->Port = 465;                          // 587 for TLS, 465 for SSL

    $mail->setFrom('support@win80x.com', 'Win80x Team'); // ✅ Verified email
    $mail->addAddress($email);

    $mail->Subject = $subject;
    $mail->Body    = $body;

    $mail->send();

    // Store OTP
    $otpDir = __DIR__ . '/otps';
    if (!is_dir($otpDir)) mkdir($otpDir, 0777, true);

    $otpFile = $otpDir . '/' . str_replace(['@', '.'], ['_at_', '_dot_'], $email) . '.txt';
    file_put_contents($otpFile, $otp . '|' . (time() + 600)); // expires in 10 min

    // Log
    file_put_contents(__DIR__ . '/otp-logs.txt', date('Y-m-d H:i:s') . " - SUCCESS - OTP to $email\n", FILE_APPEND);

    echo json_encode(['status' => 'success', 'message' => 'OTP sent successfully', 'otp' => $otp]);

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/otp-logs.txt', date('Y-m-d H:i:s') . " - ERROR - " . $mail->ErrorInfo . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => 'Mail error: ' . $mail->ErrorInfo]);
}
