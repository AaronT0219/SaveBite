<?php
header('Content-Type: application/json');
require_once '../../config.php';

// error handling function
function respond($code, $msg) {
    http_response_code($code);
    echo json_encode($msg);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success' => false, 'error' => 'Method not allowed']);
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['fooditem_id']) || !isset($data['tagClassName']) || !isset($data['status'])) {
    respond(400, ['success' => false, 'error' => 'Missing parameters']);
}

// Change food item's status
$foodItem_id = intval($data['fooditem_id']);
$tagClassName = $data['tagClassName'];
$status = $data['status'];
$status = ($tagClassName === '.used-tag-modal')
    ? ($status ? 'used' : '')
    : ($status ? 'donation' : '');

$stmt = $conn->prepare("UPDATE fooditem SET status=? WHERE fooditem_id=?");
if (!$stmt) respond(500, ['success' => false, 'error' => 'Failed to prepare statement']);

$stmt->bind_param('si', $status, $foodItem_id);
if ($stmt->execute()) {
    respond(200, ['success' => true]);
} else {
    respond(500, ['success' => false, 'error' => 'Failed to update status']);
}

$stmt->close();
