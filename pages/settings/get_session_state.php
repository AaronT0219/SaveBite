<?php
session_start();

// Set content type for JSON response
header('Content-Type: application/json');

// Return current session state
echo json_encode([
    'success' => true,
    'isAuthActive' => isset($_SESSION['isAuthActive']) ? (int)$_SESSION['isAuthActive'] : 0,
    'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '',
    'name' => isset($_SESSION['name']) ? $_SESSION['name'] : ''
]);
?>