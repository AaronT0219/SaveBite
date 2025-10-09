<?php

session_start();
require_once '../config.php';

// Set content type for JSON response
header('Content-Type: application/json');

if (isset($_POST['register'])) {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $temp_password = $_POST['password']; // Store temporarily, will be set after verification
    $household_size = $_POST['household_size'];

    $checkEmail = $conn->query("SELECT email FROM user WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Email is already registered!'
        ]);
        exit();
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert user with temporary password and inactive status
            $tempPassword = password_hash('temp_' . uniqid(), PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO user (user_name, email, password, household_number, isAuthActive) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param("sssi", $name, $email, $tempPassword, $household_size);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create user account');
            }
            
            // Generate verification code
            $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', time() + 60); // 1 minute from now
            
            // Store verification code
            $stmt = $conn->prepare("INSERT INTO verification_codes (email, code, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $verificationCode, $expiresAt);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to generate verification code');
            }
            
            // Store temp password in session for later use
            $_SESSION['temp_signup_data'] = [
                'email' => $email,
                'password' => $temp_password
            ];
            
            // Send verification email
            require_once '../phpmailer/signup_email_config.php';
            if (!sendSignupEmail($email, $verificationCode)) {
                throw new Exception('Failed to send verification email');
            }
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Registration initiated! Please check your email for a verification link to complete your account setup.',
                'showEmailMessage' => true
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'error' => 'Registration failed: ' . $e->getMessage()
            ]);
        }
        exit();
    }
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM user WHERE email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Check if account is activated
        if ($user['isAuthActive'] == 0) {
            echo json_encode([
                'success' => false,
                'error' => 'Account not verified. Please check your email for verification instructions.',
                'needsVerification' => true,
                'email' => $email
            ]);
            exit();
        }
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['user_id'];
            $_SESSION['name'] = $user['user_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['household_size'] = $user['household_number'];
            echo json_encode([
                'success' => true,
                'redirect' => '../templates/base.php?page=inventory'
            ]);
            exit();
        }
    }
    echo json_encode([
        'success' => false,
        'error' => 'Invalid email or password.'
    ]);
    exit();
}

?>