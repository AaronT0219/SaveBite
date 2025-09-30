<?php
include '../Main/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['fooditem_id']) || !isset($data['used'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$fooditem_id = intval($data['fooditem_id']);
$used = $data['used'] ? 'used' : '';

$update = "UPDATE fooditem SET status='$used' WHERE fooditem_id=$fooditem_id";
if (mysqli_query($conn, $update)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update status']);
}
