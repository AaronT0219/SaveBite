<?php
header('Content-Type: application/json');
require_once '../Main/config.php';

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
$fooditem_id = intval($data['fooditem_id']);
$quantity = intval($data['quantity']);
$donation = $data['donation'] ? 'donation' : '';
$pickup_location = trim($data['pickup_location']);
$availability = trim($data['availability']);

try {
    $pdo->beginTransaction();
    
    // user_id as 1 temporarily
    $post_donation = $pdo->prepare("INSERT INTO donation (donor_user_id, status, pickup_location, donation_date) VALUES (?, ?, ?, ?)");
    $post_donation->execute([1, 'pending', $pickup_location, $availability]);

    $donation_id = $pdo->lastInsertId();

    $post_donation_fooditem = $pdo->prepare("INSERT INTO donation_fooditem (donation_id, fooditem_id, quantity) VALUES (?, ?, ?)");
    $post_donation_fooditem->execute([$donation_id, $fooditem_id, $quantity]);
    
    $update_fooditem = $pdo->prepare("UPDATE fooditem SET status=? WHERE fooditem_id=?");
    $update_fooditem->execute([$donation, $fooditem_id]);
    
    $pdo->commit();
    
    respond(200, ["success" => true, "message" => "Donation stored successfully"]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond(500, ["success" => false, "message" => $e->getMessage()]);
}