<?php
/**
 * Win80x Email Debug Test
 * This script will test the SMTP connection and provide detailed error information
 */

// Include PHPMailer and config
require_once 'vendor/autoload.php';
$config = require_once 'config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

echo "<h2>🔧 Win80x Email Configuration Test</h2>";
echo "<div style='font-family: monospace; background: #f5f5f5; padding: 20px; border-radius: 8px;'>";

// Display current configuration
echo "<h3>📧 Current SMTP Configuration:</h3>";
echo "<strong>Host:</strong> " . $config['smtp']['host'] . "<br>";
echo "<strong>Port:</strong> " . $config['smtp']['port'] . "<br>";
echo "<strong>Username:</strong> " . $config['smtp']['username'] . "<br>";
echo "<strong>Encryption:</strong> " . $config['smtp']['encryption'] . "<br>";
echo "<strong>Password:</strong> " . (empty($config['smtp']['password']) ? "❌ NOT SET" : "✅ SET") . "<br><br>";

try {
    echo "<h3>🔗 Testing SMTP Connection...</h3>";
    
    $mail = new PHPMailer(true);
    
    // Enable verbose debug output
    $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
    $mail->Debugoutput = function($str, $level) {
        echo "DEBUG LEVEL $level; MESSAGE: " . htmlspecialchars($str) . "<br>";
    };
    
    // Server settings
    $mail->isSMTP();
    $mail->Host       = $config['smtp']['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $config['smtp']['username'];
    $mail->Password   = $config['smtp']['password'];
    
    // Set encryption method
    if ($config['smtp']['encryption'] === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } elseif ($config['smtp']['encryption'] === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    
    $mail->Port = $config['smtp']['port'];
    
    // Set timeout values
    $mail->Timeout = 30;
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
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = '🧪 Win80x Email Test - ' . date('Y-m-d H:i:s');
    $mail->Body    = "<h2>✅ Email Test Successful!</h2><p>This is a test email from Win80x contact form system.</p><p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>";
    $mail->AltBody = "Email Test Successful! This is a test email from Win80x contact form system. Time: " . date('Y-m-d H:i:s');
    
    echo "<h3>📤 Attempting to send test email...</h3>";
    $mail->send();
    echo "<h3 style='color: green;'>✅ SUCCESS: Test email sent successfully!</h3>";
    echo "<p>Check your inbox at: <strong>" . $config['email']['to_email'] . "</strong></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ SMTP CONNECTION FAILED</h3>";
    echo "<strong>Error Message:</strong> " . $mail->ErrorInfo . "<br>";
    echo "<strong>Exception:</strong> " . $e->getMessage() . "<br><br>";
    
    echo "<h3>🔍 Troubleshooting Suggestions:</h3>";
    echo "<ul>";
    echo "<li>✅ Check if the email password is correct</li>";
    echo "<li>✅ Verify that the cPanel email account exists</li>";
    echo "<li>✅ Check if your hosting provider blocks outgoing SMTP</li>";
    echo "<li>✅ Try alternative port configurations (587 with TLS)</li>";
    echo "<li>✅ Contact your hosting provider about SMTP settings</li>";
    echo "</ul>";
    
    echo "<h3>🔄 Alternative Configuration Test</h3>";
    echo "<p>Let's try TLS on port 587...</p>";
    
    try {
        $mail2 = new PHPMailer(true);
        $mail2->isSMTP();
        $mail2->Host = $config['smtp']['host'];
        $mail2->SMTPAuth = true;
        $mail2->Username = $config['smtp']['username'];
        $mail2->Password = $config['smtp']['password'];
        $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail2->Port = 587;
        $mail2->Timeout = 30;
        
        $mail2->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        $mail2->setFrom($config['email']['from_email'], $config['email']['from_name']);
        $mail2->addAddress($config['email']['to_email'], $config['email']['to_name']);
        $mail2->isHTML(true);
        $mail2->Subject = '🧪 Win80x TLS Test - ' . date('Y-m-d H:i:s');
        $mail2->Body = "<h2>✅ TLS Email Test Successful!</h2><p>This test used TLS on port 587.</p>";
        
        $mail2->send();
        echo "<p style='color: green;'>✅ <strong>TLS Alternative Successful!</strong> Consider switching to TLS configuration.</p>";
        
    } catch (Exception $e2) {
        echo "<p style='color: red;'>❌ TLS Alternative also failed: " . $e2->getMessage() . "</p>";
    }
}

echo "<h3>🌐 Server Information:</h3>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "<br>";
echo "<strong>Server Name:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "<br>";
echo "<strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "<br>";

echo "</div>";

echo "<br><h3>🔧 Quick Fix Options:</h3>";
echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 8px;'>";
echo "<p><strong>Option 1:</strong> If TLS test worked, update config.php to use port 587 with TLS encryption.</p>";
echo "<p><strong>Option 2:</strong> Check with your hosting provider for correct SMTP settings.</p>";
echo "<p><strong>Option 3:</strong> Verify the email password is correct in cPanel.</p>";
echo "</div>";
?>
