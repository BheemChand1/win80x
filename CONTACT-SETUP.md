# Win80x Dynamic Contact Form Setup

## 📧 SMTP Email Configuration Guide

### Quick Setup Steps:

1. **Choose your email provider** and follow the specific setup below
2. **Update the configuration** in `config.php`
3. **Test the contact form** to ensure emails are working
4. **Deploy to your web server** with PHP support

---

## 🔧 Email Provider Setup

### Gmail Setup (Recommended)

```php
// In config.php, update these settings:
'smtp' => [
    'host'     => 'smtp.gmail.com',
    'port'     => 587,
    'username' => 'your-email@gmail.com',     // Your Gmail address
    'password' => 'your-app-password',        // NOT your Gmail password!
    'encryption' => 'tls',
],
```

**Gmail App Password Setup:**

1. Go to [Google Account Settings](https://myaccount.google.com/)
2. Security → 2-Step Verification (enable if not already)
3. Security → App passwords
4. Generate a new app password for "Mail"
5. Use this 16-character password in `config.php`

### Outlook/Hotmail Setup

```php
'smtp' => [
    'host'     => 'smtp-mail.outlook.com',
    'port'     => 587,
    'username' => 'your-email@outlook.com',
    'password' => 'your-regular-password',
    'encryption' => 'tls',
],
```

### Yahoo Mail Setup

```php
'smtp' => [
    'host'     => 'smtp.mail.yahoo.com',
    'port'     => 587,
    'username' => 'your-email@yahoo.com',
    'password' => 'your-app-password',        // Generate in Yahoo settings
    'encryption' => 'tls',
],
```

---

## 📁 File Structure

```
win80x/
├── index.html                    # Main website with contact form
├── contact-form.php              # 🆕 Recommended: Composer + PHPMailer handler
├── contact-handler.php           # Basic PHP email handler
├── contact-handler-advanced.php  # Advanced PHPMailer handler (manual)
├── config.php                    # Email configuration
├── composer.json                 # 🆕 Composer dependencies
├── composer.lock                 # 🆕 Composer lock file
├── script.js                     # AJAX form functionality
├── styles.css                    # Enhanced form styles
├── contact-logs.txt              # Success logs (auto-created)
├── contact-errors.txt            # Error logs (auto-created)
├── rate_limit.json               # 🆕 Rate limiting data (auto-created)
└── vendor/                       # 🆕 Composer dependencies (PHPMailer)
    ├── autoload.php              # 🆕 Composer autoloader
    ├── phpmailer/phpmailer/      # 🆕 PHPMailer library
    └── composer/                 # 🆕 Composer files
```

---

## 🚀 Installation Methods

### Method 1: Composer Installation (Recommended) ✅

PHPMailer is now installed via Composer:

1. ✅ **Composer installed** - Version 2.8.1 detected
2. ✅ **PHPMailer installed** - Latest version (6.8+) via `composer require phpmailer/phpmailer`
3. ✅ **Autoloader ready** - Use `contact-form.php` (recommended) or `contact-handler-advanced.php`
4. ✅ **Dependencies managed** - All PHPMailer dependencies automatically handled

### Method 2: Basic PHP Mail (Simpler)

Uses PHP's built-in `mail()` function:

1. Use `contact-handler.php` for basic functionality
2. Update SMTP settings in the file header
3. Update the form action in `script.js` (line with `fetch('contact-handler.php')`)

### Method 3: Manual PHPMailer (Alternative)

If Composer is not available:

1. Download PHPMailer from [GitHub](https://github.com/PHPMailer/PHPMailer)
2. Extract to a `PHPMailer/` folder in your project
3. Use `contact-handler-advanced.php` with manual includes

---

## ⚙️ Configuration Steps

### 1. Update Email Settings

Edit `config.php` or the PHP handler file:

```php
'email' => [
    'from_email' => 'your-email@gmail.com',      // Your sending email
    'from_name'  => 'Win80x Contact Form',       // Sender name
    'to_email'   => 'support@win80x.com',        // Where to receive emails
    'to_name'    => 'Win80x Support Team',       // Receiver name
],
```

### 2. Update JavaScript Endpoint

In `script.js`, find this line:

```javascript
const response = await fetch('contact-handler.php', {
```

Change `'contact-handler.php'` to your PHP file name.

### 3. Test the Setup

1. Open your website in a browser
2. Fill out the contact form
3. Submit and check for success message
4. Check your email inbox for the message
5. Check `contact-logs.txt` for successful submissions
6. Check `contact-errors.txt` if there are issues

---

## 🔐 Security Features

### Built-in Protection:

- ✅ Input validation and sanitization
- ✅ CSRF protection via form validation
- ✅ Rate limiting (5 submissions per IP per hour)
- ✅ Email format validation
- ✅ Blocked disposable email domains
- ✅ SQL injection protection
- ✅ XSS protection via `htmlspecialchars()`

### Additional Security:

- ✅ Honeypot fields for spam prevention
- ✅ Server-side validation
- ✅ Error logging for monitoring
- ✅ IP address logging

---

## 🎨 Form Features

### User Experience:

- ✅ Real-time form validation
- ✅ Character counter for message field
- ✅ Loading states with spinner
- ✅ Success/error alerts
- ✅ Smooth animations
- ✅ Mobile-responsive design

### Validation:

- ✅ Required field validation
- ✅ Email format validation
- ✅ Phone number validation
- ✅ Message length validation (10-1000 chars)
- ✅ Terms agreement validation

---

## 🐛 Troubleshooting

### Common Issues:

**❌ Emails not sending:**

- Check SMTP credentials in config
- Verify app password (not regular password for Gmail)
- Check server PHP mail configuration
- Review error logs in `contact-errors.txt`

**❌ Form validation errors:**

- Check browser console for JavaScript errors
- Ensure all required fields are filled
- Verify email format is correct

**❌ 500 Internal Server Error:**

- Check PHP error logs
- Verify file permissions
- Ensure PHP version compatibility (7.0+)

**❌ CORS errors:**

- Ensure form and PHP handler are on same domain
- Check server CORS configuration

### Debug Mode:

Add this to your PHP handler for debugging:

```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

---

## 📧 Email Template Preview

The system sends beautifully formatted HTML emails with:

- 🎮 Win80x branding
- 📋 Organized contact information
- 💬 Formatted message content
- 📊 Submission details (time, IP, etc.)
- 🔄 Easy reply functionality

---

## 🌟 Advanced Customization

### Custom Email Templates:

Edit the HTML in your PHP handler to match your brand:

```php
$mail->Body = "
<html>
<head>
    <style>
        /* Your custom CSS styles */
    </style>
</head>
<body>
    <!-- Your custom email layout -->
</body>
</html>";
```

### Form Field Customization:

Add new fields by:

1. Adding HTML in `index.html`
2. Adding validation in `script.js`
3. Adding field handling in PHP handler

### Webhook Integration:

Add webhook notifications to services like Slack, Discord, or Zapier by extending the PHP handler.

---

## 📞 Support

If you need help with setup:

1. Check the troubleshooting section above
2. Review PHP and JavaScript error logs
3. Test with a simple contact form first
4. Contact Win80x support team

---

**🎮 Win80x - Making Contact Forms Great Again!**
