<?php
// PHPMailer email configuration for SaveBite Signup Verification
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendSignupEmail($toEmail, $verificationCode) {
    error_log("Attempting to send signup verification email to: $toEmail from directory: " . getcwd());
    
    // Check for PHPMailer in multiple locations
    $phpmailerPaths = [
        // Local PHPMailer installation (current directory) - when called from phpmailer folder
        'PHPMailer-6.10.0/src/PHPMailer.php',
        'src/PHPMailer.php', // If extracted without version folder
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
        return sendSignupEmailBasic($toEmail, $verificationCode);
    }
    
    // Load email credentials - check multiple locations
    $credentials = null;
    $credentialsPaths = [
        'email_credentials.php', // If called from phpmailer directory
        '../phpmailer/email_credentials.php' // If called from Login directory
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
        $mail->Subject = 'Welcome to SaveBite - Verify Your Account';
        $mail->Body    = getSignupEmailTemplate($verificationCode, $toEmail);
        $mail->AltBody = "Welcome to SaveBite! Your account verification code is: $verificationCode. This code expires in 1 minute. Complete your registration by entering this code on our verification page.";
        
        return $mail->send();
        
    } catch (Exception $e) {
        error_log("Signup email send failed: " . $mail->ErrorInfo);
        error_log("Exception: " . $e->getMessage());
        return false;
    }
}

// Basic fallback email function
function sendSignupEmailBasic($toEmail, $verificationCode) {
    $subject = "Welcome to SaveBite - Verify Your Account";
    $message = getSignupEmailTemplate($verificationCode, $toEmail);
    
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

function getSignupEmailTemplate($verificationCode, $email) {
    $verificationLink = "http://localhost/Projects/SaveBite/Login/verification.php?email=" . urlencode($email) . "&code=" . $verificationCode;
    
    return "
    <html>
    <head>
        <title>Welcome to SaveBite</title>
    </head>
    <body>
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='text-align: center; margin-bottom: 30px;'>
                <h1 style='color: #37a98d;'>SaveBite</h1>
                <h2 style='color: #333; margin-bottom: 10px;'>Welcome to the Food Waste Solution!</h2>
            </div>
            
            <div style='background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;'>
                <h3 style='color: #333; margin-bottom: 15px;'>Account Verification Required</h3>
                <p style='color: #666; line-height: 1.6;'>
                    <strong>Important:</strong> Thank you for joining SaveBite! To complete your registration and start reducing food waste, 
                    you must verify your account by clicking the link below and entering your verification code.
                </p>
                <p style='color: #666; line-height: 1.6;'>
                    <strong>This is a required step</strong> - your account will remain inactive until verified.
                </p>
            </div>
            
            <div style='text-align: center; margin: 30px 0;'>
                <div style='background-color: #37a98d; color: white; font-size: 32px; font-weight: bold; padding: 20px; border-radius: 8px; letter-spacing: 5px; margin-bottom: 20px;'>
                    " . $verificationCode . "
                </div>
                <p style='color: #666; margin-bottom: 20px;'>This code will expire in 1 minute</p>
                
                <div style='margin: 20px 0;'>
                    <table cellpadding='0' cellspacing='0' border='0' style='margin: 0 auto;'>
                        <tr>
                            <td style='background-color: #37a98d; border-radius: 5px; padding: 0;'>
                                <a href='" . $verificationLink . "' style='display: block; color: white !important; padding: 15px 30px; text-decoration: none; font-weight: bold; font-size: 16px; border-radius: 5px;' target='_blank'>
                                    Complete Registration
                                </a>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <p style='color: #666; font-size: 14px;'>
                    Or copy and paste this link in your browser:<br>
                    <a href='" . $verificationLink . "' style='color: #37a98d; word-break: break-all; text-decoration: underline;' target='_blank'>" . $verificationLink . "</a>
                </p>
            </div>
            
            <div style='background-color: #e8f5e8; border: 1px solid #37a98d; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
                <h4 style='color: #37a98d; margin-top: 0;'>✅ Next Steps to Activate Your Account:</h4>
                <ol style='color: #333; margin: 0; padding-left: 20px;'>
                    <li><strong>Click the 'Complete Registration Now' button above</strong></li>
                    <li>Enter your 6-digit verification code: <strong style='color: #37a98d;'>" . $verificationCode . "</strong></li>
                    <li>Set your account password</li>
                    <li>Start using SaveBite to reduce food waste!</li>
                </ol>
            </div>
            
            <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
                <p style='color: #856404; margin: 0;'>
                    <strong>Security Note:</strong> If you didn't create this account, please ignore this email. 
                    No account will be created without completing the verification process.
                </p>
            </div>
            
            <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;'>
                <p style='color: #666; font-size: 14px;'>
                    This is an automated welcome email from SaveBite. Please do not reply to this email.
                </p>
                <p style='color: #666; font-size: 12px; margin-top: 10px;'>
                    SaveBite - Reducing Food Waste, One Bite at a Time
                </p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>