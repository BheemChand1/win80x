<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'])) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(["status" => "error", "message" => "Invalid email format"]);
    exit;
}

// Generate 6-digit OTP
$otp = rand(100000, 999999);

// Email content

$subject = "Win80x Email Verification OTP";
$message = "Dear user,\n\nYour OTP for email verification is: $otp\n\nRegards,\nWin80x Team";
$headers = "From: support@win80x.com";

// Ensure otps directory exists
$otpDir = __DIR__ . '/otps';
if (!is_dir($otpDir)) {
    mkdir($otpDir, 0777, true);
}
// Sanitize filename (replace @ and .)
$otpFile = $otpDir . '/' . str_replace(['@', '.'], ['_at_', '_dot_'], $email) . '.txt';

if (mail($email, $subject, $message, $headers)) {
    file_put_contents($otpFile, $otp);
    echo json_encode([
        "status" => "success",
        "message" => "OTP sent successfully",
        "otp" => $otp
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to send OTP"]);
}
