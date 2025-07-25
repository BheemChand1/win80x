<?php
/**
 * SMTP Configuration for Win80x Contact Form
 * 
 * Instructions for Setup:
 * 
 * 1. Gmail Setup:
 *    - Enable 2-factor authentication on your Gmail account
 *    - Generate an "App Password" (not your regular Gmail password)
 *    - Use smtp.gmail.com with port 587 (TLS)
 * 
 * 2. Outlook/Hotmail Setup:
 *    - Use smtp-mail.outlook.com with port 587 (TLS)
 *    - Use your regular Outlook password
 * 
 * 3. Other Email Providers:
 *    - Check your email provider's SMTP settings
 *    - Update the configuration below accordingly
 * 
 * 4. Security Notes:
 *    - Never commit real passwords to version control
 *    - Consider using environment variables for production
 *    - Keep this file secure and restrict access
 */

return [
    // SMTP Server Configuration - cPanel SSL/TLS Settings (Recommended)
    'smtp' => [
        'host'     => 'win80x.com',               // Your domain's mail server (from cPanel)
        'port'     => 465,                        // SMTP SSL port (secure)
        'username' => 'support@win80x.com',       // Your cPanel email address
        'password' => 'win80x@123',               // Your cPanel email password
        'encryption' => 'ssl',                    // SSL encryption (secure)
    ],
    
    // Email Settings
    'email' => [
        'from_email' => 'support@win80x.com',     // Sender email (your cPanel email)
        'from_name'  => 'Win80x Contact Form',    // Sender name
        'to_email'   => 'support@win80x.com',     // Where to receive contact form emails
        'to_name'    => 'Win80x Support Team',    // Receiver name
        'reply_to'   => true,                     // Set reply-to as the form submitter's email
    ],
    
    // Security Settings
    'security' => [
        'rate_limit'     => 5,                    // Max submissions per IP per hour
        'honeypot'       => true,                 // Enable honeypot anti-spam
        'validate_email' => true,                 // Validate email format
        'max_message_length' => 1000,             // Maximum message length
        'blocked_domains' => [                    // Block emails from these domains
            'tempmail.com',
            '10minutemail.com',
            'guerrillamail.com'
        ]
    ],
    
    // Logging Settings
    'logging' => [
        'enabled'        => true,                 // Enable logging
        'log_file'       => 'contact-logs.txt',   // Log file name
        'log_errors'     => 'contact-errors.txt', // Error log file
        'log_level'      => 'info',               // Log level: 'debug', 'info', 'warning', 'error'
    ],
    
    // Response Messages
    'messages' => [
        'success' => 'Thank you for contacting Win80x! Your message has been sent successfully. We\'ll get back to you within 24 hours.',
        'error'   => 'Sorry, there was an error sending your message. Please try again later or contact us directly at support@win80x.com.',
        'validation' => 'Please correct the validation errors and try again.',
        'rate_limit' => 'Too many submissions. Please wait before sending another message.',
        'blocked_email' => 'Email address not allowed. Please use a valid email address.'
    ],
    
    // Alternative cPanel SMTP Configurations
    // Your cPanel provides these exact settings:
    // Incoming Server: win80x.com (IMAP Port: 993, POP3 Port: 995)
    // Outgoing Server: win80x.com (SMTP Port: 465)
    // All require SSL/TLS authentication
    
    // Alternative 1: TLS configuration (if SSL doesn't work)
    /*
    'smtp' => [
        'host'     => 'win80x.com',
        'port'     => 587,                        // TLS port (alternative to SSL)
        'username' => 'support@win80x.com',
        'password' => 'win80x@123',
        'encryption' => 'tls',
    ],
    */
    
    // Alternative 2: Non-SSL configuration (least secure, use only if others fail)
    /*
    'smtp' => [
        'host'     => 'win80x.com',
        'port'     => 25,                         // Non-encrypted port
        'username' => 'support@win80x.com',
        'password' => 'win80x@123',
        'encryption' => '',
    ],
    */
    
    // Alternative 3: Using mail subdomain (some cPanel setups)
    /*
    'smtp' => [
        'host'     => 'mail.win80x.com',
        'port'     => 465,
        'username' => 'support@win80x.com',
        'password' => 'win80x@123',
        'encryption' => 'ssl',
    ],
    */
    
    // Outlook/Hotmail Configuration
    /*
    'smtp' => [
        'host'     => 'smtp-mail.outlook.com',
        'port'     => 587,
        'username' => 'your-email@outlook.com',
        'password' => 'your-password',
        'encryption' => 'tls',
    ],
    */
    
    // Yahoo Mail Configuration
    /*
    'smtp' => [
        'host'     => 'smtp.mail.yahoo.com',
        'port'     => 587,
        'username' => 'your-email@yahoo.com',
        'password' => 'your-app-password',
        'encryption' => 'tls',
    ],
    */
    
    // Custom SMTP Configuration
    /*
    'smtp' => [
        'host'     => 'mail.yourdomain.com',
        'port'     => 587,
        'username' => 'contact@yourdomain.com',
        'password' => 'your-password',
        'encryption' => 'tls',
    ],
    */
];
?>
