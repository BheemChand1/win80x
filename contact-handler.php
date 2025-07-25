<?php
/**
 * Win80x Simple Contact Form Handler
 * Uses basic PHP mail() function - guaranteed to work
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($input['firstName']) || empty($input['lastName']) || empty($input['email']) || empty($input['message'])) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
    exit;
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// Sanitize input data
$firstName = htmlspecialchars(trim($input['firstName']));
$lastName = htmlspecialchars(trim($input['lastName']));
$email = htmlspecialchars(trim($input['email']));
$phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : 'Not provided';
$message = htmlspecialchars(trim($input['message']));

// Email configuration
$to = 'support@win80x.com';
$subject = '🎮 New Contact Form Submission - Win80x';

// Create email content
$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; }
        .field { margin-bottom: 15px; padding: 10px; background: white; border-radius: 4px; }
        .label { font-weight: bold; color: #667eea; }
        .value { margin-top: 5px; }
        .message-box { background: #e8f5e8; padding: 15px; border-radius: 4px; border-left: 4px solid #28a745; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🎮 Win80x Contact Form</h2>
            <p>New message from your website</p>
        </div>
        
        <div class='content'>
            <div class='field'>
                <div class='label'>👤 Contact Information:</div>
                <div class='value'>
                    <strong>Name:</strong> $firstName $lastName<br>
                    <strong>Email:</strong> <a href='mailto:$email'>$email</a><br>
                    <strong>Phone:</strong> $phone
                </div>
            </div>
            
            <div class='field'>
                <div class='label'>💬 Message:</div>
                <div class='message-box'>
                    " . nl2br($message) . "
                </div>
            </div>
            
            <div class='field'>
                <div class='label'>📊 Submission Details:</div>
                <div class='value'>
                    <strong>Time:</strong> " . date('Y-m-d H:i:s') . "<br>
                    <strong>IP Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "<br>
                    <strong>User Agent:</strong> " . substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 100) . "
                </div>
            </div>
        </div>
        
        <div style='background: #343a40; color: white; padding: 15px; text-align: center; border-radius: 0 0 8px 8px;'>
            <p><strong>Win80x Contact System</strong></p>
            <p>Reply directly to: <strong>$email</strong></p>
        </div>
    </div>
</body>
</html>";

// Email headers
$headers = array(
    'From: Win80x Contact <support@win80x.com>',
    'Reply-To: ' . $firstName . ' ' . $lastName . ' <' . $email . '>',
    'Content-Type: text/html; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion(),
    'X-Priority: 1',
    'Importance: High'
);

// Send email using PHP mail function
$mail_sent = mail($to, $subject, $email_body, implode("\r\n", $headers));

// Log the attempt
$log_entry = date('Y-m-d H:i:s') . " - " . ($mail_sent ? "SUCCESS" : "FAILED") . 
             " - Simple PHP Mail - From: $email ($firstName $lastName) - Phone: $phone\n";
file_put_contents('contact-simple-logs.txt', $log_entry, FILE_APPEND | LOCK_EX);

// Return response
if ($mail_sent) {
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for contacting Win80x! Your message has been sent successfully. We\'ll get back to you within 24 hours.',
        'data' => [
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'method' => 'PHP Mail (Simple)',
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error sending your message. Please try again later or contact us directly at support@win80x.com.',
        'error' => 'PHP mail() function failed'
    ]);
}
?>
