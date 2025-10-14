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
? ($status ? 'used' : null)
: ($status ? 'donation' : null);

try {
    $pdo->beginTransaction();

    $update_fooditem = $pdo->prepare("UPDATE fooditem SET status=? WHERE fooditem_id=?");
    $update_fooditem->execute([$status, $foodItem_id]);

    // run if it's delete operation
    if ($status === null) {
        $get_donation = $pdo->prepare("SELECT donation_id FROM donation_fooditem WHERE fooditem_id=?");
        $get_donation->execute([$foodItem_id]);
        $donation = $get_donation->fetch(PDO::FETCH_ASSOC);

        if ($donation) {
            $donation_id = $donation['donation_id'];
    
            $del_donation = $pdo->prepare("DELETE FROM donation WHERE donation_id=?");
            $del_donation->execute([$donation_id]);
            error_log("DEBUG: Donation row deleted for donation_id = {$donation_id}");
        }
    }
    
    $pdo->commit();

    respond(200, ["success" => true, "message" => "Food status updated successfully"]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    respond(500, ["success" => false, "message" => $e->getMessage()]);
}