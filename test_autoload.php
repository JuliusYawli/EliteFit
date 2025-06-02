<?php
require_once __DIR__ . '/vendor/autoload.php';

// Check if PHPMailer class exists
if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    echo 'PHPMailer is properly loaded!';
    // Try to create a new instance
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    echo 'PHPMailer instance created successfully!';
} else {
    echo 'PHPMailer is NOT loaded. Check the following:\n';
    echo '1. Run: composer require phpmailer/phpmailer\n';
    echo '2. Check PHP version (should be 7.4+)\n';
    echo '3. Check PHP error logs for issues';
}
?>
