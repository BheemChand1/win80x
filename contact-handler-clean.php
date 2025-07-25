<?php
/**
 * Win80x Contact Form Handler - Clean Version
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
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get and validate JSON input
$json = file_get_contents('php://input');
$input = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die(json_encode(['success' => false, 'message' => 'Invalid JSON input']));
}

// Validate required fields
$required = ['firstName', 'lastName', 'email', 'message'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        die(json_encode(['success' => false, 'message' => "Field '$field' is required"]));
    }
}

// Validate email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['success' => false, 'message' => 'Invalid email format']));
}

// Sanitize data
$firstName = htmlspecialchars(trim($input['firstName']));
$lastName = htmlspecialchars(trim($input['lastName']));
$email = htmlspecialchars(trim($input['email']));
$phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : 'Not provided';
$message = htmlspecialchars(trim($input['message']));

// Create email
$to = 'support@win80x.com';
$subject = 'New Contact Form - Win80x';
$body = "Name: $firstName $lastName\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n\nTime: " . date('Y-m-d H:i:s');
$headers = "From: Win80x <support@win80x.com>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=utf-8";

// Send email
$sent = mail($to, $subject, $body, $headers);

// Log result
$log = date('Y-m-d H:i:s') . " - " . ($sent ? "SUCCESS" : "FAILED") . " - $email ($firstName $lastName)\n";
file_put_contents('contact-logs.txt', $log, FILE_APPEND | LOCK_EX);

// Return clean JSON response
if ($sent) {
    die(json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully. We\'ll get back to you within 24 hours.'
    ]));
} else {
    die(json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again or contact us directly.'
    ]));
}
?>
