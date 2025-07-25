<?php
// Advanced SMTP Contact Handler using PHPMailer
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

// Include PHPMailer via Composer autoloader
require_once 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

// SMTP Configuration - Update these with your actual SMTP settings
$smtp_config = [
    'host' => 'smtp.gmail.com',           // SMTP server (Gmail, Outlook, etc.)
    'port' => 587,                        // SMTP port (587 for TLS, 465 for SSL)
    'username' => 'your-email@gmail.com', // Your email address
    'password' => 'your-app-password',    // Your email app password
    'from_email' => 'your-email@gmail.com',
    'from_name' => 'Win80x Contact Form',
    'to_email' => 'support@win80x.com',   // Where to receive contact form emails
    'to_name' => 'Win80x Support Team'
];

// Sanitize input data
$firstName = htmlspecialchars(trim($input['firstName']));
$lastName = htmlspecialchars(trim($input['lastName']));
$email = htmlspecialchars(trim($input['email']));
$phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : '';
$message = htmlspecialchars(trim($input['message']));

try {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host       = $smtp_config['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_config['username'];
    $mail->Password   = $smtp_config['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $smtp_config['port'];

    // Recipients
    $mail->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
    $mail->addAddress($smtp_config['to_email'], $smtp_config['to_name']);
    $mail->addReplyTo($email, $firstName . ' ' . $lastName);

    // Content
    $mail->isHTML(true);
    $mail->Subject = '🎮 New Contact Form Submission - Win80x';
    
    $mail->Body = "
    <html>
    <head>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .header p { margin: 10px 0 0 0; opacity: 0.9; }
            .content { background: #f8f9fa; padding: 30px; }
            .field { margin-bottom: 20px; }
            .label { font-weight: 600; color: #667eea; margin-bottom: 5px; display: block; }
            .value { background: white; padding: 12px; border-radius: 6px; border-left: 4px solid #667eea; }
            .message-box { background: white; padding: 20px; border-radius: 6px; border-left: 4px solid #28a745; }
            .footer { background: #343a40; color: white; padding: 20px; text-align: center; }
            .footer p { margin: 5px 0; }
            .badge { background: #667eea; color: white; padding: 4px 8px; border-radius: 12px; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎮 Win80x Contact Form</h1>
                <p>New message received from your website</p>
                <span class='badge'>High Priority</span>
            </div>
            
            <div class='content'>
                <div class='field'>
                    <label class='label'>👤 Contact Information</label>
                    <div class='value'>
                        <strong>Name:</strong> $firstName $lastName<br>
                        <strong>Email:</strong> <a href='mailto:$email'>$email</a>" . 
                        ($phone ? "<br><strong>Phone:</strong> <a href='tel:$phone'>$phone</a>" : "") . "
                    </div>
                </div>
                
                <div class='field'>
                    <label class='label'>💬 Message Content</label>
                    <div class='message-box'>
                        " . nl2br($message) . "
                    </div>
                </div>
                
                <div class='field'>
                    <label class='label'>📊 Submission Details</label>
                    <div class='value'>
                        <strong>Time:</strong> " . date('l, F j, Y \a\t g:i A T') . "<br>
                        <strong>IP Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "<br>
                        <strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "
                    </div>
                </div>
            </div>
            
            <div class='footer'>
                <p><strong>Win80x Contact Management System</strong></p>
                <p>This email was automatically generated from your website contact form.</p>
                <p>To reply to this inquiry, simply reply to this email or contact: <strong>$email</strong></p>
            </div>
        </div>
    </body>
    </html>";

    // Alternative plain text version
    $mail->AltBody = "
    New Contact Form Submission - Win80x
    
    Name: $firstName $lastName
    Email: $email" . 
    ($phone ? "\nPhone: $phone" : "") . "
    
    Message:
    $message
    
    Submitted: " . date('Y-m-d H:i:s') . "
    
    Please reply directly to: $email
    ";

    // Send the email
    $mail->send();
    
    // Log successful submission
    $log_entry = date('Y-m-d H:i:s') . " - SUCCESS - Contact form submission from: $email ($firstName $lastName)" . 
                 ($phone ? " - Phone: $phone" : "") . "\n";
    file_put_contents('contact-logs.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for contacting Win80x! Your message has been sent successfully. We\'ll get back to you within 24 hours.',
        'data' => [
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    // Log error
    $error_log = date('Y-m-d H:i:s') . " - ERROR - Failed to send email from: $email - Error: " . $mail->ErrorInfo . "\n";
    file_put_contents('contact-errors.txt', $error_log, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error sending your message. Please try again later or contact us directly at support@win80x.com.',
        'error' => 'SMTP Error: ' . $mail->ErrorInfo
    ]);
}
?>
