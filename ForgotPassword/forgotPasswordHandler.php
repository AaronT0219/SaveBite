<?php
// Turn off error display and log errors instead
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();

// Set content type for JSON response
header('Content-Type: application/json');

// Ensure no output before JSON
ob_start();

try {
    require_once '../config.php';
    require_once '../phpmailer/email_config.php'; // Include email functions
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Configuration failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'send_code':
        sendVerificationCode();
        break;
    case 'verify_code':
        verifyCode();
        break;
    case 'reset_password':
        resetPassword();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function sendVerificationCode() {
    global $conn;
    
    try {
        $email = $_POST['email'] ?? '';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
            return;
        }
        
        // Check if email exists in database
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => 'Database prepare failed']);
            return;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'error' => 'Email not found in our system']);
            return;
        }
        
        // Generate 6-digit verification code
        $verificationCode = sprintf('%06d', mt_rand(0, 999999));
        
        // Store verification code in session with timestamp
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['verification_email'] = $email;
        $_SESSION['code_generated_at'] = time();
        
        // Send email
        $emailResult = sendEmail($email, $verificationCode);
        if ($emailResult) {
            echo json_encode([
                'success' => true,
                'message' => 'Verification code sent to your email'
            ]);
        } else {
            // Check error logs for more details
            error_log("Email sending failed for: $email");
            echo json_encode([
                'success' => false,
                'error' => 'Failed to send email. Please check your email configuration.'
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Server error occurred']);
    }
}

function verifyCode() {
    $submittedCode = $_POST['code'] ?? '';
    $storedCode = $_SESSION['verification_code'] ?? '';
    $email = $_SESSION['verification_email'] ?? '';
    $codeGeneratedAt = $_SESSION['code_generated_at'] ?? 0;
    
    if (!$storedCode || !$email) {
        echo json_encode(['success' => false, 'error' => 'No verification code found. Please request a new one.']);
        return;
    }
    
    // Check if code has expired (1 minute = 60 seconds)
    if (time() - $codeGeneratedAt > 60) {
        // Clear expired code
        unset($_SESSION['verification_code']);
        unset($_SESSION['verification_email']);
        unset($_SESSION['code_generated_at']);
        
        echo json_encode(['success' => false, 'error' => 'Verification code has expired. Please request a new one.']);
        return;
    }
    
    if ($submittedCode === $storedCode) {
        // Code is valid, mark as verified
        $_SESSION['code_verified'] = true;
        echo json_encode(['success' => true, 'message' => 'Code verified successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid verification code']);
    }
}

function resetPassword() {
    global $conn;
    
    $newPassword = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Check if code was verified
    if (!isset($_SESSION['code_verified']) || !$_SESSION['code_verified']) {
        echo json_encode(['success' => false, 'error' => 'Please verify your code first']);
        return;
    }
    
    $email = $_SESSION['verification_email'] ?? '';
    if (!$email) {
        echo json_encode(['success' => false, 'error' => 'Session expired. Please start over.']);
        return;
    }
    
    // Validate passwords
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters long']);
        return;
    }
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        return;
    }
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);
    
    if ($stmt->execute()) {
        // Clear all session data
        session_destroy();
        
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update password. Please try again.']);
    }
}

?>