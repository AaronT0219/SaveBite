<?php
// PHPMailer email configuration for SaveBite
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendEmail($toEmail, $verificationCode) {
    error_log("Attempting to send email to: $toEmail from directory: " . getcwd());
    
    // Check for PHPMailer in multiple locations
    $phpmailerPaths = [
        // Local PHPMailer installation (current directory) - when called from phpmailer folder
        'PHPMailer-6.10.0/src/PHPMailer.php',
        'src/PHPMailer.php', // If extracted without version folder
        // When called from ForgotPassword folder
        '../phpmailer/PHPMailer-6.10.0/src/PHPMailer.php',
        '../phpmailer/src/PHPMailer.php',
        // Composer location
        '../vendor/phpmailer/phpmailer/src/PHPMailer.php'
    ];
    
    $phpmailerPath = null;
    foreach ($phpmailerPaths as $path) {
        if (file_exists($path)) {
            $phpmailerPath = dirname($path);
            break;
        }
    }
    
    if (!$phpmailerPath) {
        error_log("PHPMailer not found in any expected location. Tried: " . implode(', ', $phpmailerPaths));
        return sendEmailBasic($toEmail, $verificationCode);
    }
    
    // Load email credentials - check multiple locations
    $credentials = null;
    $credentialsPaths = [
        'email_credentials.php', // If called from phpmailer directory
        '../phpmailer/email_credentials.php' // If called from ForgotPassword directory
    ];
    
    foreach ($credentialsPaths as $credPath) {
        if (file_exists($credPath)) {
            $credentials = include $credPath;
            break;
        }
    }
    
    if (!$credentials) {
        error_log("Email credentials file not found in any location. Tried: " . implode(', ', $credentialsPaths));
        return false;
    }
    
    require_once $phpmailerPath . '/PHPMailer.php';
    require_once $phpmailerPath . '/SMTP.php';
    require_once $phpmailerPath . '/Exception.php';
    
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $credentials['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $credentials['username'];
        $mail->Password   = $credentials['password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $credentials['smtp_port'];
        
        // Disable SSL verification (for development only)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom($credentials['from_email'], $credentials['from_name']);
        $mail->addAddress($toEmail);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'SaveBite - Password Reset Verification Code';
        $mail->Body    = getEmailTemplate($verificationCode);
        $mail->AltBody = "Your SaveBite password reset verification code is: $verificationCode. This code expires in 1 minute.";
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Email send failed: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}

// Basic fallback email function
function sendEmailBasic($toEmail, $verificationCode) {
    $subject = "SaveBite - Password Reset Verification Code";
    $message = getEmailTemplate($verificationCode);
    
    $headers = array(
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8',
        'From' => 'noreply@savebite.com'
    );
    
    $headerString = '';
    foreach ($headers as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }
    
    return mail($toEmail, $subject, $message, $headerString);
}

function getEmailTemplate($verificationCode) {
    return "
    <html>
    <head>
        <title>SaveBite - Password Reset</title>
    </head>
    <body>
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #37a98d;'>SaveBite</h1>
            </div>
            
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                <h2 style='color: #333; margin-bottom: 15px;'>Password Reset Request</h2>
                <p style='color: #666; line-height: 1.6;'>
                    We received a request to reset your password. Use the verification code below to proceed with resetting your password.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <div style='background-color: #37a98d; color: white; font-size: 32px; font-weight: bold; padding: 20px; border-radius: 8px; letter-spacing: 5px;'>
                    " . $verificationCode . "
                </div>
                <p style='color: #666; margin-top: 15px;'>This code will expire in 1 minute</p>
            </div>
            
            <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
                <p style='color: #856404; margin: 0;'>
                    <strong>Security Note:</strong> If you didn't request this password reset, please ignore this email. Your account remains secure.
                </p>
            </div>
            
            <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;'>
                <p style='color: #666; font-size: 14px;'>
                    This is an automated email from SaveBite. Please do not reply to this email.
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>