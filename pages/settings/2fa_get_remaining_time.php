<?php
session_start();
require_once '../../config.php';

// Set content type for JSON response
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action']) || $input['action'] !== 'get_remaining_time') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

if (!isset($input['email'])) {
    echo json_encode(['success' => false, 'error' => 'Email is required']);
    exit();
}

$email = $input['email'];

// Check if 2FA is already enabled
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
        'alreadyEnabled' => true,
        'remainingTime' => 0
    ]);
    exit();
}

// Check for active verification code
$stmt = $conn->prepare("SELECT expires_at FROM verification_codes WHERE email = ? AND used = 0 ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => true,
        'expired' => true,
        'remainingTime' => 0
    ]);
    exit();
}

$code = $result->fetch_assoc();
$expiresAt = strtotime($code['expires_at']);
$now = time();
$remainingTime = $expiresAt - $now;

if ($remainingTime <= 0) {
    echo json_encode([
        'success' => true,
        'expired' => true,
        'remainingTime' => 0
    ]);
} else {
    echo json_encode([
        'success' => true,
        'expired' => false,
        'remainingTime' => $remainingTime
    ]);
}
?>