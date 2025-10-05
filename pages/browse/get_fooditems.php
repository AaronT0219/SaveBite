<?php
header('Content-Type: application/json');
require_once '../../config.php';

session_start();
if(isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $result = mysqli_query($conn, "SELECT * FROM fooditem WHERE user_id='$id'");
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database query failed', 'details' => mysqli_error($conn)]);
        exit;
    }
    
    $foodItems = array_map(function($row) {
        return [
            'foodItem_id' => $row['foodItem_id'],
            'name' => $row['food_name'],
            'quantity' => (int)$row['quantity'],
            'category' => $row['category'],
            'donation' => $row['status'] === 'donation',
            'reserved' => $row['status'] === 'reserved',
            'used' => $row['status'] === 'used',
            'expiry' => $row['expiry_date'],
            'storage' => $row['storage_location'],
            'description' => $row['description'] ?? ''
        ];
    }, mysqli_fetch_all($result, MYSQLI_ASSOC));
    
    header('Content-Type: application/json');
    echo json_encode($foodItems);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'user id not exist.'
    ]);
    exit();
}
