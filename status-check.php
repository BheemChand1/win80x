<?php
/**
 * Quick SMTP Status Check
 */
echo "<h2>🔍 SMTP Configuration Status</h2>";

// Check if files exist
echo "<h3>File Status:</h3>";
echo "✅ vendor/autoload.php: " . (file_exists('vendor/autoload.php') ? "EXISTS" : "❌ MISSING") . "<br>";
echo "✅ config.php: " . (file_exists('config.php') ? "EXISTS" : "❌ MISSING") . "<br>";
echo "✅ contact-form.php: " . (file_exists('contact-form.php') ? "EXISTS" : "❌ MISSING") . "<br>";

if (file_exists('config.php')) {
    $config = require_once 'config.php';
    echo "<h3>Current SMTP Settings:</h3>";
    echo "Host: " . $config['smtp']['host'] . "<br>";
    echo "Port: " . $config['smtp']['port'] . "<br>";
    echo "Username: " . $config['smtp']['username'] . "<br>";
    echo "Encryption: " . $config['smtp']['encryption'] . "<br>";
    echo "Password: " . (empty($config['smtp']['password']) ? "❌ NOT SET" : "✅ SET (length: " . strlen($config['smtp']['password']) . ")") . "<br>";
}

echo "<h3>Server Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Mail Function: " . (function_exists('mail') ? "✅ Available" : "❌ Not Available") . "<br>";

if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    echo "PHPMailer: ✅ Loaded<br>";
    
    // Quick connection test
    echo "<h3>Quick Connection Test:</h3>";
    try {
        $config = require_once 'config.php';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);
        
        $connection = stream_socket_client(
            $config['smtp']['encryption'] . '://' . $config['smtp']['host'] . ':' . $config['smtp']['port'],
            $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $context
        );
        
        if ($connection) {
            echo "✅ Can connect to " . $config['smtp']['host'] . ":" . $config['smtp']['port'] . "<br>";
            fclose($connection);
        } else {
            echo "❌ Cannot connect: $errstr ($errno)<br>";
        }
    } catch (Exception $e) {
        echo "❌ Connection test failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo "PHPMailer: ❌ Not Available<br>";
}

echo "<h3>Log Files:</h3>";
if (file_exists('contact-logs.txt')) {
    $logs = file_get_contents('contact-logs.txt');
    $recent_logs = array_slice(explode("\n", trim($logs)), -5);
    echo "<strong>Recent contact logs:</strong><br>";
    foreach ($recent_logs as $log) {
        if (!empty($log)) {
            echo htmlspecialchars($log) . "<br>";
        }
    }
} else {
    echo "No contact logs found<br>";
}

if (file_exists('contact-errors.txt')) {
    $errors = file_get_contents('contact-errors.txt');
    $recent_errors = array_slice(explode("\n", trim($errors)), -3);
    echo "<br><strong>Recent errors:</strong><br>";
    foreach ($recent_errors as $error) {
        if (!empty($error)) {
            echo "<span style='color: red;'>" . htmlspecialchars($error) . "</span><br>";
        }
    }
} else {
    echo "No error logs found<br>";
}
?>
