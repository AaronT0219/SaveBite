<?php
include '../Main/config.php';

function respond($code, $msg) {
    http_response_code($code);
    echo json_encode($msg);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['fooditem_id']) || !isset($data['donation']) || !isset($data['pickup_location']) || !isset($data['availability'])) {
    respond(400, ['success' => false, 'error' => 'Missing parameters']);
}

// Change food item's status
$foodItem_id = intval($data['fooditem_id']);
$donation = $data['donation'] ? 'donation' : '';
$pickup_location = trim($data['pickup_location']);
$availability = trim($data['availability']);

$pdo->beingTransaction();

// user_id as 1 temporarily
$post_donation = $pdo->prepare("INSERT INTO donation (donor_user_id, status, pickup_location, donation_date) VALUES (?, ?, ?, ?)");
$post_donation->execute([1, 'pending', $pickup_location, $availability]);

$update_fooditem = $pdo->prepare("UPDATE fooditem SET status=? FROM fooditem_id=?");
$update_fooditem->execute([$donation, $foodItem_id]);

$pdo->commit();

if($pdo) {
    respond(200, ['success' => true]);
} else {
    respond(500, ['success' => false, 'error' => 'Failed to update status']);
}