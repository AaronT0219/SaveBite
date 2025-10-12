<?php
session_start();
require_once '../../config.php';

// Set content type for JSON response
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$action = $input['action'];

switch ($action) {
    case 'request_2fa_setup':
        handle2FASetupRequest($input, $conn);
        break;
    
    case 'verify_2fa_code':
        verify2FACode($input, $conn);
        break;
    
    case 'resend_2fa_code':
        resend2FACode($input, $conn);
        break;
    
    case 'disable_2fa':
        disable2FA($input, $conn);
        break;
    
    case 'update_password':
        updatePassword($input, $conn);
        break;
    
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
        break;
}

function handle2FASetupRequest($input, $conn) {
    if (!isset($input['email'])) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        return;
    }
    
    $email = $input['email'];
    
    // Check if user exists and current 2FA status
    $stmt = $conn->prepare("SELECT user_id, isAuthActive FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        return;
    }
    
    $user = $result->fetch_assoc();
    
    if ($user['isAuthActive'] == 1) {
        echo json_encode(['success' => false, 'error' => '2FA is already enabled']);
        return;
    }
    
    // Generate 2FA code
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Set expiry time - 1 minute from now
    $expiryTimestamp = time() + 60;
    $expiresAt = date('Y-m-d H:i:s', $expiryTimestamp);
    
    // Store 2FA code (reuse verification_codes table)
    $stmt = $conn->prepare("DELETE FROM verification_codes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $stmt = $conn->prepare("INSERT INTO verification_codes (email, code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $code, $expiresAt);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to generate 2FA code']);
        return;
    }
    
    // Send 2FA setup email
    require_once '../../phpmailer/2fa_email_config.php';
    if (send2FASetupEmail($email, $code)) {
        echo json_encode([
            'success' => true,
            'message' => '2FA setup email sent successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send 2FA setup email']);
    }
}

function verify2FACode($input, $conn) {
    if (!isset($input['email']) || !isset($input['code'])) {
        echo json_encode(['success' => false, 'error' => 'Email and code are required']);
        return;
    }
    
    $email = $input['email'];
    $code = $input['code'];
    
    // Get the code and check if it exists
    $stmt = $conn->prepare("SELECT code, expires_at, used FROM verification_codes WHERE email = ? AND code = ?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid verification code']);
        return;
    }
    
    $codeData = $result->fetch_assoc();
    
    // Check if already used
    if ($codeData['used'] == 1) {
        echo json_encode(['success' => false, 'error' => 'Verification code already used']);
        return;
    }
    
    // Check expiry
    $expiresAt = strtotime($codeData['expires_at']);
    $currentTime = time();
    
    if ($currentTime > $expiresAt) {
        echo json_encode(['success' => false, 'error' => 'Verification code has expired']);
        return;
    }
    
    // Code is valid - mark as used
    $stmt = $conn->prepare("UPDATE verification_codes SET used = 1 WHERE email = ? AND code = ?");
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    
    // DO NOT enable 2FA yet - wait for password update
    // Just return success for code verification
    echo json_encode([
        'success' => true,
        'message' => '2FA code verified successfully'
    ]);
}

function resend2FACode($input, $conn) {
    if (!isset($input['email'])) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        return;
    }
    
    $email = $input['email'];
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        return;
    }
    
    // Generate new code
    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', time() + 60); // 1 minute from now
    
    // Delete old codes and insert new one
    $stmt = $conn->prepare("DELETE FROM verification_codes WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    
    $stmt = $conn->prepare("INSERT INTO verification_codes (email, code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $code, $expiresAt);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to generate new code']);
        return;
    }
    
    // Send new email
    require_once '../../phpmailer/2fa_email_config.php';
    if (send2FASetupEmail($email, $code)) {
        echo json_encode([
            'success' => true,
            'message' => 'New 2FA code sent successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send new code']);
    }
}

function disable2FA($input, $conn) {
    if (!isset($input['email'])) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        return;
    }
    
    $email = $input['email'];
    
    // Disable 2FA for the user
    $stmt = $conn->prepare("UPDATE user SET isAuthActive = 0 WHERE email = ?");
    $stmt->bind_param("s", $email);
    
    if ($stmt->execute()) {
        // Update session if this is the current user
        if (isset($_SESSION['email']) && $_SESSION['email'] === $email) {
            $_SESSION['isAuthActive'] = 0;
        }
        
        // Clean up any pending verification codes
        $stmt = $conn->prepare("DELETE FROM verification_codes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => '2FA disabled successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to disable 2FA']);
    }
}

function updatePassword($input, $conn) {
    if (!isset($input['email']) || !isset($input['newPassword'])) {
        echo json_encode(['success' => false, 'error' => 'Email and new password are required']);
        return;
    }
    
    $email = $input['email'];
    $newPassword = $input['newPassword'];
    
    // Validate password length
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters long']);
        return;
    }
    
    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update user password AND enable 2FA in a single transaction
    $conn->begin_transaction();
    
    try {
        // Update password
        $stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $email);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update password");
        }
        
        // Enable 2FA for the user
        $stmt = $conn->prepare("UPDATE user SET isAuthActive = 1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to enable 2FA");
        }
        
        // Update session if this is the current user
        if (isset($_SESSION['email']) && $_SESSION['email'] === $email) {
            $_SESSION['isAuthActive'] = 1;
        }
        
        // Clean up verification codes
        $stmt = $conn->prepare("DELETE FROM verification_codes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Password updated and 2FA enabled successfully'
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => 'Failed to complete setup: ' . $e->getMessage()]);
    }
}
?>