<?php

session_start();
require_once '../config.php';

// Set content type for JSON response
header('Content-Type: application/json');

if (isset($_POST['register'])) {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $household_size = $_POST['household_size'];

    $checkEmail = $conn->query("SELECT email FROM user WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Email is already registered!'
        ]);
        exit();
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user directly with active status
        $stmt = $conn->prepare("INSERT INTO user (user_name, email, password, household_number, isAuthActive) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("sssi", $name, $email, $hashedPassword, $household_size);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful!',
                'redirect_to_login' => true
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Registration failed. Please try again.'
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
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['id'] = $user['user_id'];
            $_SESSION['name'] = $user['user_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['household_size'] = $user['household_number'];
            $_SESSION['isAuthActive'] = $user['isAuthActive'];
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