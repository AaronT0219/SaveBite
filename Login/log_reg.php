<?php

session_start();
require_once '../config.php';

if (isset($_POST['register'])) {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $household_size = $_POST['household_size'];

    $checkEmail = $conn->query("SELECT Email FROM user WHERE Email = '$email'");
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = "Email is already registered!";
        $_SESSION['active_form'] = 'register';
        // Save form data
        $_SESSION['old_fullname'] = $name;
        $_SESSION['old_email'] = $email;
        $_SESSION['old_household_size'] = $household_size;
        $_SESSION['old_password'] = $_POST['password'];
    } else {
        $conn->query("INSERT INTO user (User_name, Email, Password, Household_number) VALUES ('$name', '$email', '$password', '$household_size')");
    }

    header("Location: login.php");
    exit();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM user WHERE Email = '$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['Password'])) {
            $_SESSION['name'] = $user['User_name'];
            $_SESSION['email'] = $user['Email'];
            header("Location: ../ForgotPassword/forgotPassword.php");
            exit();
        }
    }
    $_SESSION['login_error'] = "Invalid email or password.";
    $_SESSION['active_form'] = 'login';
    // Save login email and password
    $_SESSION['old_login_email'] = $email;
    $_SESSION['old_login_password'] = $password;
    header("Location: login.php");
    exit();
}

?>