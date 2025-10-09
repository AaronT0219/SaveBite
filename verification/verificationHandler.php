<?php
session_start();
require_once '../config.php';

// Set content type for JSON response
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

switch ($input['action']) {
    case 'verify_code':
        verifyCode($conn, $input);
        break;
    case 'set_password':
        setPassword($conn, $input);
        break;
    case 'resend_code':
        resendCode($conn, $input);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit();
}

function verifyCode($conn, $input) {
    if (!isset($input['email']) || !isset($input['code'])) {
        echo json_encode(['success' => false, 'error' => 'Email and code are required']);
        return;
    }
    
    $email = $input['email'];
    $code = $input['code'];
    
    // Check if verification code exists and is not expired
    $stmt = $conn->prepare("SELECT id, expires_at FROM verification_codes WHERE email = ? AND code = ? AND used = 0");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid verification code']);
        return;
    }
    
    $verificationData = $result->fetch_assoc();
    
    // Check if code is expired
    if (strtotime($verificationData['expires_at']) < time()) {
        echo json_encode(['success' => false, 'error' => 'Verification code has expired']);
        return;
    }
    
    echo json_encode(['success' => true, 'message' => 'Code verified successfully']);
}

function setPassword($conn, $input) {
    if (!isset($input['email']) || !isset($input['password'])) {
        echo json_encode(['success' => false, 'error' => 'Email and password are required']);
        return;
    }
    
    $email = $input['email'];
    $password = password_hash($input['password'], PASSWORD_DEFAULT);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update user password and activate account
        $stmt = $conn->prepare("UPDATE user SET password = ?, isAuthActive = 1 WHERE email = ?");
        $stmt->bind_param("ss", $password, $email);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update user password');
        }
        
        if ($stmt->affected_rows === 0) {
            throw new Exception('User not found');
        }
        
        // Mark verification code as used
        $stmt = $conn->prepare("UPDATE verification_codes SET used = 1 WHERE email = ? AND used = 0");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Account created successfully']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Failed to create account: ' . $e->getMessage()]);
    }
}

function resendCode($conn, $input) {
    if (!isset($input['email'])) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        return;
    }
    
    $email = $input['email'];
    
    // Check if user exists and is not already activated
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? AND isAuthActive = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found or already activated']);
        return;
    }
    
    // Generate new verification code
    $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', time() + 60); // 1 minute from now
    
    // Mark previous codes as used
    $stmt = $conn->prepare("UPDATE verification_codes SET used = 1 WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    // Insert new verification code
    $stmt = $conn->prepare("INSERT INTO verification_codes (email, code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $verificationCode, $expiresAt);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to generate verification code']);
        return;
    }
    
    // Send email
    require_once '../phpmailer/signup_email_config.php';
    if (sendSignupEmail($email, $verificationCode)) {
        echo json_encode(['success' => true, 'message' => 'Verification code sent successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send verification email']);
    }
}
?>