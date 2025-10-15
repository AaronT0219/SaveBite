<?php
// PHPMailer email configuration for 2FA setup
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send2FASetupEmail($toEmail, $activationCode) {
    error_log("Attempting to send 2FA setup email to: $toEmail from directory: " . getcwd());
    
    // Check for PHPMailer in multiple locations
    $phpmailerPaths = [
        // Local PHPMailer installation (current directory) - when called from phpmailer folder
        'PHPMailer-6.10.0/src/PHPMailer.php',
        'src/PHPMailer.php', // If extracted without version folder
        // When called from settings folder
        '../../phpmailer/PHPMailer-6.10.0/src/PHPMailer.php',
        '../../phpmailer/src/PHPMailer.php',
        // When called from Login folder
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
        return send2FASetupEmailBasic($toEmail, $activationCode);
    }
    
    // Load email credentials - check multiple locations
    $credentials = null;
    $credentialsPaths = [
        'email_credentials.php', // If called from phpmailer directory
        '../phpmailer/email_credentials.php', // If called from Login directory
        '../../phpmailer/email_credentials.php' // If called from settings directory
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
        $mail->Subject = 'SaveBite - Two-Factor Authentication Setup';
        $mail->Body    = get2FASetupEmailTemplate($activationCode, $toEmail);
        $mail->AltBody = "SaveBite Two-Factor Authentication Setup. Your verification code is: $activationCode. This code expires in 1 minute. Complete your 2FA setup by entering this code in the verification page.";
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("2FA email send failed: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}

// Basic fallback email function
function send2FASetupEmailBasic($toEmail, $activationCode) {
    $subject = "SaveBite - Two-Factor Authentication Setup";
    $message = get2FASetupEmailTemplate($activationCode, $toEmail);
    
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

function get2FASetupEmailTemplate($activationCode, $email) {
    $activationLink = "http://localhost/SaveBite/pages/settings/2fa_verification.php?email=" . urlencode($email) . "&code=" . $activationCode;
    
    return "
    <html>
    <head>
        <title>Two-Factor Authentication Setup - SaveBite</title>
    </head>
    <body>
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #37a98d;'>SaveBite</h1>
                <h2 style='color: #333; margin-bottom: 10px;'>Two-Factor Authentication Setup</h2>
            </div>
            
            <div style='background-color: #e8f5e8; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #37a98d;'>
                <h3 style='color: #37a98d; margin-top: 0; margin-bottom: 15px;'>👋 Welcome to SaveBite!</h3>
                <p style='color: #333; line-height: 1.6; margin-bottom: 10px;'>
                    We're excited to have you as part of our community working together to reduce food waste and make a positive impact on the environment.
                </p>
                <p style='color: #666; line-height: 1.6; margin: 0;'>
                    Thank you for taking the extra step to secure your account with Two-Factor Authentication. Your security is important to us!
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <div style='background-color: #37a98d; color: white; font-size: 32px; font-weight: bold; padding: 20px; border-radius: 8px; letter-spacing: 5px; margin-bottom: 20px;'>
                    " . $activationCode . "
                </div>
                <p style='color: #666; margin-bottom: 20px;'>This code will expire in 1 minute</p>
                
                <div style='margin: 20px 0;'>
                    <table cellpadding='0' cellspacing='0' border='0' style='margin: 0 auto;'>
                        <tr>
                            <td style='background-color: #37a98d; border-radius: 5px; padding: 0;'>
                                <a href='" . $activationLink . "' style='display: block; color: white !important; padding: 15px 30px; text-decoration: none; font-weight: bold; font-size: 16px; border-radius: 5px;' target='_blank'>
                                    Setup 2FA Now
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p style='color: #666; font-size: 14px;'>
                    Or copy and paste this link in your browser:<br>
                    <a href='" . $activationLink . "' style='color: #37a98d; word-break: break-all; text-decoration: underline;' target='_blank'>" . $activationLink . "</a>
                </p>
            </div>
            
            <div style='background-color: #e8f5e8; border: 1px solid #37a98d; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
                <h4 style='color: #37a98d; margin-top: 0;'>✅ Steps to Enable 2FA:</h4>
                <ol style='color: #333; margin: 0; padding-left: 20px;'>
                    <li><strong>Click 'Setup 2FA Now' button above</strong></li>
                    <li>Enter your 6-digit verification code: <strong style='color: #37a98d;'>" . $activationCode . "</strong></li>
                    <li>Your 2FA will be enabled automatically</li>
                    <li>Return to settings to see your updated security status</li>
                </ol>
            </div>
            
            <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
                <p style='color: #856404; margin: 0;'>
                    <strong>Security Note:</strong> If you didn't request to enable 2FA, please ignore this email. 
                    Your account security settings will remain unchanged.
                </p>
            </div>
            
            <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;'>
                <p style='color: #666; font-size: 14px;'>
                    This is an automated security email from SaveBite. Please do not reply to this email.
                </p>
                <p style='color: #666; font-size: 12px; margin-top: 10px;'>
                    SaveBite - Securing Your Food Waste Solutions
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>