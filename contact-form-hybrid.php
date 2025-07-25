<?php
/**
 * Win80x Contact Form Handler - Hybrid Version
 * Primary: SMTP with PHPMailer (Secure)
 * Fallback: PHP mail() function (Basic)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include PHPMailer classes if available
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
}

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

// Sanitize input data
$firstName = htmlspecialchars(trim($input['firstName']));
$lastName = htmlspecialchars(trim($input['lastName']));
$email = htmlspecialchars(trim($input['email']));
$phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : '';
$message = htmlspecialchars(trim($input['message']));

// Rate limiting (simple version)
$client_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rate_file = 'simple_rate_limit.txt';
$current_time = time();

if (file_exists($rate_file)) {
    $rate_data = file_get_contents($rate_file);
    $submissions = explode("\n", trim($rate_data));
    $recent_submissions = array_filter($submissions, function($line) use ($current_time) {
        $parts = explode('|', $line);
        return isset($parts[1]) && ($current_time - (int)$parts[1]) < 3600; // 1 hour
    });
    
    $ip_count = count(array_filter($recent_submissions, function($line) use ($client_ip) {
        return strpos($line, $client_ip . '|') === 0;
    }));
    
    if ($ip_count >= 5) {
        echo json_encode(['success' => false, 'message' => 'Too many submissions. Please wait before sending another message.']);
        exit;
    }
}

// Try SMTP first, then fallback to mail()
$email_sent = false;
$method_used = '';
$error_details = '';

// Method 1: Try SMTP with PHPMailer
if (file_exists('vendor/autoload.php') && file_exists('config.php')) {
    try {
        $config = require_once 'config.php';
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp']['username'];
        $mail->Password = $config['smtp']['password'];
        
        if ($config['smtp']['encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($config['smtp']['encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $config['smtp']['port'];
        $mail->Timeout = 10; // Shorter timeout for faster fallback
        
        // SSL options for self-signed certificates
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom($config['email']['from_email'], $config['email']['from_name']);
        $mail->addAddress($config['email']['to_email'], $config['email']['to_name']);
        $mail->addReplyTo($email, $firstName . ' ' . $lastName);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = '🎮 New Contact Form Submission - Win80x';
        $mail->Body = generateEmailHTML($firstName, $lastName, $email, $phone, $message);
        $mail->AltBody = generateEmailText($firstName, $lastName, $email, $phone, $message);
        
        $mail->send();
        $email_sent = true;
        $method_used = 'SMTP (Secure)';
        
    } catch (Exception $e) {
        $error_details = 'SMTP Error: ' . $e->getMessage();
        // Continue to fallback method
    }
}

// Method 2: Fallback to PHP mail() function
if (!$email_sent) {
    $to = 'support@win80x.com';
    $subject = '🎮 New Contact Form Submission - Win80x';
    $headers = [
        'From: Win80x Contact Form <support@win80x.com>',
        'Reply-To: ' . $firstName . ' ' . $lastName . ' <' . $email . '>',
        'Content-Type: text/html; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    ];
    
    $email_body = generateEmailHTML($firstName, $lastName, $email, $phone, $message);
    
    if (mail($to, $subject, $email_body, implode("\r\n", $headers))) {
        $email_sent = true;
        $method_used = 'PHP Mail (Basic)';
    } else {
        $error_details .= ' | PHP Mail also failed';
    }
}

// Log the submission attempt
$log_entry = date('Y-m-d H:i:s') . " - " . ($email_sent ? "SUCCESS" : "FAILED") . 
             " - $method_used - From: $email ($firstName $lastName)" . 
             ($phone ? " - Phone: $phone" : "") . 
             (!$email_sent ? " - Error: $error_details" : "") . "\n";

file_put_contents('contact-hybrid-logs.txt', $log_entry, FILE_APPEND | LOCK_EX);

// Update rate limiting
file_put_contents($rate_file, $client_ip . '|' . $current_time . "\n", FILE_APPEND | LOCK_EX);

// Return response
if ($email_sent) {
    echo json_encode([
        'success' => true, 
        'message' => 'Thank you for contacting Win80x! Your message has been sent successfully. We\'ll get back to you within 24 hours.',
        'data' => [
            'name' => $firstName . ' ' . $lastName,
            'email' => $email,
            'method' => $method_used,
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Sorry, there was an error sending your message. Please try again later or contact us directly at support@win80x.com.',
        'error' => $error_details
    ]);
}

// Email template functions
function generateEmailHTML($firstName, $lastName, $email, $phone, $message) {
    return "
    <html>
    <head>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; }
            .content { background: #f8f9fa; padding: 30px; }
            .field { margin-bottom: 20px; background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #667eea; }
            .label { font-weight: 600; color: #667eea; margin-bottom: 5px; display: block; }
            .value { color: #333; }
            .message-box { background: #e8f5e8; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745; margin-top: 10px; }
            .footer { background: #343a40; color: white; padding: 20px; text-align: center; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎮 Win80x Contact Form</h1>
                <p>New message received from your website</p>
            </div>
            
            <div class='content'>
                <div class='field'>
                    <div class='label'>👤 Contact Information</div>
                    <div class='value'>
                        <strong>Name:</strong> $firstName $lastName<br>
                        <strong>Email:</strong> <a href='mailto:$email'>$email</a>" . 
                        ($phone ? "<br><strong>Phone:</strong> <a href='tel:$phone'>$phone</a>" : "") . "
                    </div>
                </div>
                
                <div class='field'>
                    <div class='label'>💬 Message</div>
                    <div class='message-box'>
                        " . nl2br(htmlspecialchars($message)) . "
                    </div>
                </div>
                
                <div class='field'>
                    <div class='label'>📊 Details</div>
                    <div class='value'>
                        <strong>Submitted:</strong> " . date('l, F j, Y \a\t g:i A T') . "<br>
                        <strong>IP Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "
                    </div>
                </div>
            </div>
            
            <div class='footer'>
                <p><strong>Win80x Contact Management System</strong></p>
                <p>Reply directly to this email to respond to: <strong>$email</strong></p>
            </div>
        </div>
    </body>
    </html>";
}

function generateEmailText($firstName, $lastName, $email, $phone, $message) {
    return "
New Contact Form Submission - Win80x

Name: $firstName $lastName
Email: $email" . 
($phone ? "\nPhone: $phone" : "") . "

Message:
$message

Submitted: " . date('Y-m-d H:i:s') . "
IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "

Reply to: $email
";
}
?>
