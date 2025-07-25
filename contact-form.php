<?php
/**
 * Win80x Contact Form Handler with PHPMailer & Composer
 * Professional SMTP email handling with security features
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

// Include PHPMailer via Composer autoloader
require_once 'vendor/autoload.php';

// Load configuration
$config = require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Rate limiting check
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_limit_file = 'rate_limit.json';
$current_time = time();
$rate_limit = $config['security']['rate_limit'];

// Load existing rate limit data
$rate_data = [];
if (file_exists($rate_limit_file)) {
    $rate_data = json_decode(file_get_contents($rate_limit_file), true) ?: [];
}

// Clean old entries (older than 1 hour)
$rate_data = array_filter($rate_data, function($timestamp) use ($current_time) {
    return ($current_time - $timestamp) < 3600; // 1 hour
});

// Check rate limit for current IP
$ip_submissions = array_filter($rate_data, function($timestamp, $ip) use ($client_ip) {
    return $ip === $client_ip;
}, ARRAY_FILTER_USE_BOTH);

if (count($ip_submissions) >= $rate_limit) {
    echo json_encode([
        'success' => false, 
        'message' => $config['messages']['rate_limit']
    ]);
    exit;
}

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

// Check blocked email domains
$email_domain = strtolower(substr(strrchr($input['email'], "@"), 1));
if (in_array($email_domain, $config['security']['blocked_domains'])) {
    echo json_encode([
        'success' => false, 
        'message' => $config['messages']['blocked_email']
    ]);
    exit;
}

// Validate message length
$message_length = strlen($input['message']);
if ($message_length < 10 || $message_length > $config['security']['max_message_length']) {
    echo json_encode([
        'success' => false, 
        'message' => 'Message must be between 10 and ' . $config['security']['max_message_length'] . ' characters'
    ]);
    exit;
}

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
    $mail->Host       = $config['smtp']['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp']['username'];
    $mail->Password   = $config['smtp']['password'];
    $mail->SMTPSecure = $config['smtp']['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = $config['smtp']['port'];
    
    // Enable verbose debug output (disable in production)
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    // Recipients
    $mail->setFrom($config['email']['from_email'], $config['email']['from_name']);
    $mail->addAddress($config['email']['to_email'], $config['email']['to_name']);
    
    if ($config['email']['reply_to']) {
        $mail->addReplyTo($email, $firstName . ' ' . $lastName);
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = '🎮 New Contact Form Submission - Win80x';
    
    $mail->Body = generateEmailTemplate($firstName, $lastName, $email, $phone, $message, $client_ip);
    $mail->AltBody = generatePlainTextEmail($firstName, $lastName, $email, $phone, $message);

    // Send the email
    $mail->send();
    
    // Update rate limiting
    $rate_data[$client_ip . '_' . $current_time] = $current_time;
    file_put_contents($rate_limit_file, json_encode($rate_data), LOCK_EX);
    
    // Log successful submission
    if ($config['logging']['enabled']) {
        $log_entry = [
            'timestamp' => date('c'),
            'ip' => $client_ip,
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'phone' => $phone,
            'message_length' => strlen($message),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        file_put_contents($config['logging']['log_file'], json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => $config['messages']['success'],
        'data' => [
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'timestamp' => date('c'),
            'reference' => 'WIN80X-' . strtoupper(substr(md5($email . $current_time), 0, 8))
        ]
    ]);

} catch (Exception $e) {
    // Log error
    if ($config['logging']['enabled']) {
        $error_log = [
            'timestamp' => date('c'),
            'ip' => $client_ip,
            'email' => $email,
            'error' => $mail->ErrorInfo,
            'exception' => $e->getMessage()
        ];
        file_put_contents($config['logging']['log_errors'], json_encode($error_log) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $config['messages']['error'],
        'debug' => [
            'error' => $mail->ErrorInfo,
            'reference' => 'ERR-' . strtoupper(substr(md5($current_time), 0, 8))
        ]
    ]);
}

/**
 * Generate HTML email template
 */
function generateEmailTemplate($firstName, $lastName, $email, $phone, $message, $ip) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Win80x Contact Form Submission</title>
        <style>
            body { 
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0; 
                padding: 0; 
                background-color: #f8f9fa;
            }
            .container { 
                max-width: 600px; 
                margin: 20px auto; 
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                color: white; 
                padding: 30px; 
                text-align: center; 
            }
            .header h1 { 
                margin: 0; 
                font-size: 24px; 
                font-weight: 700;
            }
            .header p { 
                margin: 10px 0 0 0; 
                opacity: 0.9; 
                font-size: 16px;
            }
            .content { 
                padding: 30px; 
            }
            .field { 
                margin-bottom: 20px; 
                border-radius: 8px;
                border: 1px solid #e9ecef;
                overflow: hidden;
            }
            .field-header {
                background: #f8f9fa;
                padding: 12px 16px;
                border-bottom: 1px solid #e9ecef;
                font-weight: 600;
                color: #667eea;
                font-size: 14px;
            }
            .field-content { 
                padding: 16px; 
                background: white;
            }
            .message-content {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                border-left: 4px solid #667eea;
                white-space: pre-wrap;
                font-family: inherit;
            }
            .footer { 
                background: #343a40; 
                color: white; 
                padding: 20px; 
                text-align: center; 
            }
            .footer p { 
                margin: 5px 0; 
                font-size: 14px;
            }
            .badge { 
                background: #667eea; 
                color: white; 
                padding: 6px 12px; 
                border-radius: 20px; 
                font-size: 12px; 
                font-weight: 600;
                display: inline-block;
                margin-bottom: 10px;
            }
            .info-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-top: 15px;
            }
            .info-item {
                background: #f8f9fa;
                padding: 12px;
                border-radius: 6px;
                font-size: 13px;
            }
            .info-label {
                font-weight: 600;
                color: #667eea;
                margin-bottom: 4px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎮 Win80x Contact Form</h1>
                <p>New message received from your website</p>
                <span class='badge'>New Submission</span>
            </div>
            
            <div class='content'>
                <div class='field'>
                    <div class='field-header'>
                        👤 Contact Information
                    </div>
                    <div class='field-content'>
                        <strong>Name:</strong> $firstName $lastName<br>
                        <strong>Email:</strong> <a href='mailto:$email' style='color: #667eea; text-decoration: none;'>$email</a>" . 
                        ($phone ? "<br><strong>Phone:</strong> <a href='tel:$phone' style='color: #667eea; text-decoration: none;'>$phone</a>" : "") . "
                    </div>
                </div>
                
                <div class='field'>
                    <div class='field-header'>
                        💬 Message Content
                    </div>
                    <div class='field-content'>
                        <div class='message-content'>$message</div>
                    </div>
                </div>
                
                <div class='field'>
                    <div class='field-header'>
                        📊 Submission Details
                    </div>
                    <div class='field-content'>
                        <div class='info-grid'>
                            <div class='info-item'>
                                <div class='info-label'>🕒 Submission Time</div>
                                " . date('l, F j, Y \a\t g:i A T') . "
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>🌐 IP Address</div>
                                " . htmlspecialchars($ip) . "
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>📱 User Agent</div>
                                " . htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50)) . "...
                            </div>
                            <div class='info-item'>
                                <div class='info-label'>📝 Message Length</div>
                                " . strlen($message) . " characters
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class='footer'>
                <p><strong>🎮 Win80x Contact Management System</strong></p>
                <p>This email was automatically generated from your website contact form.</p>
                <p>To reply to this inquiry, simply reply to this email or contact: <strong>$email</strong></p>
                <p style='margin-top: 15px; font-size: 12px; opacity: 0.8;'>
                    Reference: WIN80X-" . strtoupper(substr(md5($email . time()), 0, 8)) . "
                </p>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Generate plain text email version
 */
function generatePlainTextEmail($firstName, $lastName, $email, $phone, $message) {
    return "
🎮 WIN80X CONTACT FORM SUBMISSION
================================

👤 CONTACT INFORMATION:
Name: $firstName $lastName
Email: $email" . 
($phone ? "\nPhone: $phone" : "") . "

💬 MESSAGE:
" . $message . "

📊 SUBMISSION DETAILS:
Time: " . date('Y-m-d H:i:s T') . "
IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "
User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "

To reply to this inquiry, please contact: $email

---
This email was automatically generated from the Win80x website contact form.
Reference: WIN80X-" . strtoupper(substr(md5($email . time()), 0, 8)) . "
    ";
}
?>
