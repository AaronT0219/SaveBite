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

if ($input['action'] === 'get_remaining_time') {
    if (!isset($input['email'])) {
        echo json_encode(['success' => false, 'error' => 'Email is required']);
        exit();
    }
    
    $email = $input['email'];
    
    // First check if user account is already activated
    $stmt = $conn->prepare("SELECT isAuthActive FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'User not found']);
        exit();
    }
    
    $user = $result->fetch_assoc();
    if ($user['isAuthActive'] == 1) {
        echo json_encode([
            'success' => true,
            'alreadyActivated' => true,
            'message' => 'Account is already activated'
        ]);
        exit();
    }
    
    // Get the latest active verification code for this email
    $stmt = $conn->prepare("SELECT expires_at FROM verification_codes WHERE email = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'No active verification code found']);
        exit();
    }
    
    $verificationData = $result->fetch_assoc();
    $expiryTime = strtotime($verificationData['expires_at']);
    $currentTime = time();
    $remainingTime = max(0, $expiryTime - $currentTime);
    
    echo json_encode([
        'success' => true,
        'remainingTime' => $remainingTime,
        'expired' => $remainingTime <= 0,
        'alreadyActivated' => false
    ]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
?>