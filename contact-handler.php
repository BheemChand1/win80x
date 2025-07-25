<?php
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
$required_fields = ['firstName', 'lastName', 'email', 'message', 'privacy'];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

// Validate email format
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit;
}

// SMTP Configuration
$smtp_config = [
    'host' => 'smtp.gmail.com',  // Change to your SMTP host
    'port' => 587,               // SMTP port (587 for TLS, 465 for SSL)
    'username' => 'your-email@gmail.com',  // Your email
    'password' => 'your-app-password',     // Your app password
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'Win80x Contact Form',
    'to_email' => 'support@win80x.com',    // Where to send the emails
    'to_name' => 'Win80x Support'
];

// Sanitize input data
$firstName = htmlspecialchars(trim($input['firstName']));
$lastName = htmlspecialchars(trim($input['lastName']));
$email = htmlspecialchars(trim($input['email']));
$phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : '';
$message = htmlspecialchars(trim($input['message']));

// Create email content
$subject = "New Contact Form Submission - Win80x";
$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 8px 8px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #667eea; }
        .value { padding: 8px; background: white; border-radius: 4px; margin-top: 5px; }
        .message-box { background: white; padding: 15px; border-left: 4px solid #667eea; margin-top: 10px; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🎮 Win80x Contact Form Submission</h2>
            <p>New message received from your website</p>
        </div>
        <div class='content'>
            <div class='field'>
                <div class='label'>👤 Full Name:</div>
                <div class='value'>$firstName $lastName</div>
            </div>
            
            <div class='field'>
                <div class='label'>📧 Email Address:</div>
                <div class='value'>$email</div>
            </div>
            
            " . ($phone ? "<div class='field'><div class='label'>📱 Phone Number:</div><div class='value'>$phone</div></div>" : "") . "
            
            <div class='field'>
                <div class='label'>💬 Message:</div>
                <div class='message-box'>$message</div>
            </div>
            
            <div class='field'>
                <div class='label'>🕒 Submission Time:</div>
                <div class='value'>" . date('Y-m-d H:i:s') . " (Server Time)</div>
            </div>
        </div>
        
        <div class='footer'>
            <p>This email was sent from the Win80x website contact form.</p>
            <p>Please reply directly to the sender's email address: $email</p>
        </div>
    </div>
</body>
</html>
";

// Send email using PHP's mail function with HTML headers
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: ' . $smtp_config['from_name'] . ' <' . $smtp_config['from_email'] . '>',
    'Reply-To: ' . $firstName . ' ' . $lastName . ' <' . $email . '>',
    'X-Mailer: PHP/' . phpversion()
];

$headers_string = implode("\r\n", $headers);

// Attempt to send email
$mail_sent = mail($smtp_config['to_email'], $subject, $email_body, $headers_string);

if ($mail_sent) {
    // Log successful submission (optional)
    $log_entry = date('Y-m-d H:i:s') . " - Contact form submission from: $email ($firstName $lastName)\n";
    file_put_contents('contact-logs.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for your message! We\'ll get back to you within 24 hours.'
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error sending your message. Please try again later or contact us directly.'
    ]);
}
?>
