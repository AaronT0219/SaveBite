<?php
header('Content-Type: application/json');
require_once '../../config.php';

session_start();
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM fooditem WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database query failed', 'details' => mysqli_error($conn)]);
        exit;
    }
    $rows = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $foodItems = array_map(function($row) {
        return [
            'foodItem_id' => (int)$row['foodItem_id'],
            'name'        => (string)$row['food_name'],
            'quantity'    => (int)$row['quantity'],
            'category'    => (string)$row['category'],
            'donation'    => ($row['status'] === 'donation'),
            'reserved'    => ($row['status'] === 'reserved'),
            'used'        => ($row['status'] === 'used'),
            'expiry'      => (string)$row['expiry_date'],
            'storage'     => (string)$row['storage_location'],
            'description' => (string)($row['description'] ?? '')
        ];
    }, $rows);
    echo json_encode($foodItems);
} else {
    echo json_encode(['success'=>false,'error'=>'user id not exist.']);
    exit;
}
