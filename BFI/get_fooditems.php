<?php
include '../Main/config.php';

$get_foodItems = "SELECT * FROM fooditem";
$result = mysqli_query($conn, $get_foodItems);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed', 'details' => mysqli_error($conn)]);
    exit;
}

$foodItems = array();

while ($row = mysqli_fetch_assoc($result)) {
    $foodItems[] = array(
        'foodItem_id' => $row['foodItem_id'],
        'name' => $row['food_name'],
        'quantity' => (int)$row['quantity'],
        'category' => $row['category'],
        'donation' => $row['status'] === 'donated',
        'reserved' => $row['status'] === 'reserved',
        'used' => $row['status'] === 'used',
        'expiry' => $row['expiry_date'],
        'storage' => $row['storage_location'],
        'description' => $row['description'] ?? ''
    );
}

header('Content-Type: application/json');
echo json_encode($foodItems);