<?php
/**
 * PHPMailer Installation Test
 * Run this file to verify PHPMailer is properly installed
 */

echo "<h2>🎮 Win80x - PHPMailer Installation Test</h2>";

// Check if Composer autoloader exists
if (!file_exists('vendor/autoload.php')) {
    echo "<p style='color: red;'>❌ Error: Composer autoloader not found. Run 'composer install' first.</p>";
    exit;
}

// Include Composer autoloader
require_once 'vendor/autoload.php';

try {
    // Check if PHPMailer classes are available
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    
    echo "<p style='color: green;'>✅ Composer autoloader loaded successfully</p>";
    
    // Create PHPMailer instance
    $mail = new PHPMailer();
    echo "<p style='color: green;'>✅ PHPMailer class instantiated successfully</p>";
    
    // Check PHPMailer version
    echo "<p><strong>📦 PHPMailer Version:</strong> " . PHPMailer::VERSION . "</p>";
    
    // Check if config file exists
    if (file_exists('config.php')) {
        echo "<p style='color: green;'>✅ Configuration file found</p>";
        $config = require_once 'config.php';
        echo "<p><strong>📧 SMTP Host:</strong> " . $config['smtp']['host'] . "</p>";
        echo "<p><strong>🔌 SMTP Port:</strong> " . $config['smtp']['port'] . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Configuration file not found. Please set up config.php</p>";
    }
    
    // Check required files
    $required_files = [
        'contact-form.php' => 'Main contact handler (Composer)',
        'index.html' => 'Website with contact form',
        'script.js' => 'AJAX form functionality',
        'styles.css' => 'Form styling'
    ];
    
    echo "<h3>📁 File Check:</h3>";
    foreach ($required_files as $file => $description) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✅ $file - $description</p>";
        } else {
            echo "<p style='color: red;'>❌ $file - $description (Missing)</p>";
        }
    }
    
    // Check PHP extensions
    echo "<h3>🔧 PHP Extensions:</h3>";
    $extensions = ['openssl', 'mbstring', 'json'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            echo "<p style='color: green;'>✅ $ext extension loaded</p>";
        } else {
            echo "<p style='color: red;'>❌ $ext extension not loaded</p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li>Update your SMTP credentials in <code>config.php</code></li>";
    echo "<li>Test the contact form on your website</li>";
    echo "<li>Check <code>contact-logs.txt</code> for successful submissions</li>";
    echo "<li>Monitor <code>contact-errors.txt</code> for any issues</li>";
    echo "</ol>";
    
    echo "<p style='background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; padding: 15px; color: #155724;'>";
    echo "<strong>🎉 Success!</strong> PHPMailer is properly installed and ready to use.<br>";
    echo "Your Win80x contact form should now work with professional SMTP email delivery.";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure PHPMailer is properly installed via Composer.</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Fatal Error: " . $e->getMessage() . "</p>";
    echo "<p>Check if all required PHP extensions are loaded.</p>";
}

echo "<hr>";
echo "<p><small>🎮 Win80x Contact Form System - " . date('Y-m-d H:i:s') . "</small></p>";
?>
