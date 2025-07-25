<?php
/**
 * cPanel Email Configuration Test for Win80x
 * This script helps you find the correct SMTP settings for your cPanel email
 */

echo "<h2>🎮 Win80x - cPanel Email Configuration Test</h2>";
echo "<p><strong>Email:</strong> support@win80x.com</p>";

// Include Composer autoloader
if (!file_exists('vendor/autoload.php')) {
    echo "<p style='color: red;'>❌ Error: Composer autoloader not found. Run 'composer install' first.</p>";
    exit;
}

require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load configuration
$config = require_once 'config.php';

echo "<h3>📧 Current Configuration:</h3>";
echo "<p><strong>SMTP Host:</strong> " . $config['smtp']['host'] . "</p>";
echo "<p><strong>SMTP Port:</strong> " . $config['smtp']['port'] . "</p>";
echo "<p><strong>Username:</strong> " . $config['smtp']['username'] . "</p>";
echo "<p><strong>Encryption:</strong> " . $config['smtp']['encryption'] . "</p>";

// Test different cPanel SMTP configurations based on your cPanel settings
$test_configs = [
    'Current Config (SSL Port 465 - Recommended)' => [
        'host' => 'win80x.com',
        'port' => 465,
        'encryption' => 'ssl'
    ],
    'Alternative 1 (TLS Port 587)' => [
        'host' => 'win80x.com',
        'port' => 587,
        'encryption' => 'tls'
    ],
    'Alternative 2 (Mail Subdomain SSL)' => [
        'host' => 'mail.win80x.com',
        'port' => 465,
        'encryption' => 'ssl'
    ],
    'Alternative 3 (Mail Subdomain TLS)' => [
        'host' => 'mail.win80x.com',
        'port' => 587,
        'encryption' => 'tls'
    ],
    'Alternative 4 (Non-encrypted Port 25)' => [
        'host' => 'win80x.com',
        'port' => 25,
        'encryption' => ''
    ]
];

echo "<h3>🔧 Testing SMTP Configurations:</h3>";

foreach ($test_configs as $config_name => $test_config) {
    echo "<h4>Testing: $config_name</h4>";
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $test_config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = 'support@win80x.com';
        $mail->Password = 'win80x@123';
        
        if ($test_config['encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($test_config['encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        
        $mail->Port = $test_config['port'];
        
        // Set a short timeout for testing
        $mail->Timeout = 10;
        
        // Test connection without sending email
        if ($mail->smtpConnect()) {
            echo "<p style='color: green; padding: 10px; background: #d4edda; border-radius: 5px;'>✅ <strong>SUCCESS!</strong> Connection established to {$test_config['host']}:{$test_config['port']}</p>";
            $mail->smtpClose();
            
            // Update config.php with working settings
            echo "<p><strong>💡 Recommended config.php settings:</strong></p>";
            echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
            echo "'smtp' => [\n";
            echo "    'host'     => '{$test_config['host']}',\n";
            echo "    'port'     => {$test_config['port']},\n";
            echo "    'username' => 'support@win80x.com',\n";
            echo "    'password' => 'win80x@123',\n";
            echo "    'encryption' => '{$test_config['encryption']}',\n";
            echo "],";
            echo "</pre>";
            break; // Stop testing once we find a working configuration
        } else {
            echo "<p style='color: red;'>❌ Failed to connect to {$test_config['host']}:{$test_config['port']}</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

// Additional troubleshooting tips
echo "<h3>🛠️ Troubleshooting Tips:</h3>";
echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px;'>";
echo "<h4>If no configuration works, try these:</h4>";
echo "<ol>";
echo "<li><strong>Check cPanel Email Settings:</strong> Log into your cPanel and go to 'Email Accounts' to verify SMTP settings</li>";
echo "<li><strong>Contact your hosting provider</strong> for the correct SMTP server hostname</li>";
echo "<li><strong>Check if SMTP is enabled</strong> on your hosting account</li>";
echo "<li><strong>Verify email authentication</strong> - some hosts require SPF/DKIM setup</li>";
echo "<li><strong>Try using server's hostname</strong> instead of mail.win80x.com</li>";
echo "</ol>";

echo "<h4>Common cPanel SMTP Hostnames:</h4>";
echo "<ul>";
echo "<li><code>mail.win80x.com</code> (most common)</li>";
echo "<li><code>win80x.com</code> (domain name)</li>";
echo "<li><code>localhost</code> (if PHP mail() is enabled)</li>";
echo "<li><code>server.yourhost.com</code> (check cPanel for exact hostname)</li>";
echo "</ul>";
echo "</div>";

// Test send email function
echo "<h3>📤 Send Test Email:</h3>";
echo "<form method='post' style='background: #f8f9fa; padding: 20px; border-radius: 5px;'>";
echo "<p><strong>Test the working configuration by sending an actual email:</strong></p>";
echo "<input type='email' name='test_email' placeholder='Enter your email to receive test' style='padding: 10px; margin: 5px; width: 300px;' required>";
echo "<button type='submit' name='send_test' style='padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;'>Send Test Email</button>";
echo "</form>";

// Handle test email sending
if (isset($_POST['send_test']) && isset($_POST['test_email'])) {
    $test_email = filter_var($_POST['test_email'], FILTER_VALIDATE_EMAIL);
    
    if ($test_email) {
        try {
            $mail = new PHPMailer(true);
            
            // Use current config
            $mail->isSMTP();
            $mail->Host = $config['smtp']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['smtp']['username'];
            $mail->Password = $config['smtp']['password'];
            $mail->SMTPSecure = $config['smtp']['encryption'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = $config['smtp']['port'];
            
            // Recipients
            $mail->setFrom('support@win80x.com', 'Win80x Test');
            $mail->addAddress($test_email);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = '🎮 Win80x Email Test - Success!';
            $mail->Body = "
            <h2>🎉 Email Configuration Test Successful!</h2>
            <p>Congratulations! Your Win80x contact form email system is working correctly.</p>
            <p><strong>Configuration Details:</strong></p>
            <ul>
                <li>SMTP Host: {$config['smtp']['host']}</li>
                <li>SMTP Port: {$config['smtp']['port']}</li>
                <li>Encryption: {$config['smtp']['encryption']}</li>
                <li>From Email: support@win80x.com</li>
            </ul>
            <p>Your contact form submissions will now be delivered to <strong>support@win80x.com</strong></p>
            <hr>
            <p><small>🎮 Win80x Contact Form System - " . date('Y-m-d H:i:s') . "</small></p>
            ";
            
            $mail->send();
            echo "<p style='color: green; padding: 15px; background: #d4edda; border-radius: 5px; margin-top: 15px;'>";
            echo "🎉 <strong>Test email sent successfully!</strong> Check your inbox at: $test_email";
            echo "</p>";
            
        } catch (Exception $e) {
            echo "<p style='color: red; padding: 15px; background: #f8d7da; border-radius: 5px; margin-top: 15px;'>";
            echo "❌ <strong>Failed to send test email:</strong> " . $e->getMessage();
            echo "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Invalid email address provided</p>";
    }
}

echo "<hr>";
echo "<p><small>🎮 Win80x cPanel Email Test - " . date('Y-m-d H:i:s') . "</small></p>";
?>
