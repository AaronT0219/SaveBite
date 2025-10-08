<?php

session_start();
require_once '../config.php';

// Set content type for JSON response
header('Content-Type: application/json');

if (isset($_POST['register'])) {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $household_size = $_POST['household_size'];

    $checkEmail = $conn->query("SELECT email FROM user WHERE email = '$email'");
    if ($checkEmail->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Email is already registered!'
        ]);
        exit();
    } else {
        $result = $conn->query("INSERT INTO user (user_name, email, password, household_number) VALUES ('$name', '$email', '$password', '$household_size')");
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Registration successful! You can now log in.'
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