<?php
header('Content-Type: application/json');
require_once '../../config.php';

function respond($code, $msg) {
    http_response_code($code);
    echo json_encode($msg);
    exit;
}

session_start();
if (!isset($_SESSION['user_id'])) {
    respond(401, ['success'=>false,'error'=>'Not logged in']);
}

$uid  = (int)$_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
if (!$data
    || !isset($data['fooditem_id'])
    || !isset($data['quantity'])
    || !isset($data['pickup_location'])
    || !isset($data['availability'])
    || !isset($data['contact'])
) {
    respond(400, ['success'=>false,'error'=>'Missing parameters']);
}

$fooditem_id     = (int)$data['fooditem_id'];
$quantity        = max(0, (int)$data['quantity']);
$pickup_location = trim((string)$data['pickup_location']);
$availability    = trim((string)$data['availability']);
$contact         = trim((string)$data['contact']);

try {
    $pdo->beginTransaction();

    // 1) 创建 donation（status = 'pending'，donation_date = NOW()）
    $sql = "
      INSERT INTO donation (donor_user_id, status, pickup_location, availability, contact, donation_date)
      VALUES (:uid, 'pending', :p, :a, :c, NOW())
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':uid' => $uid,
        ':p'   => $pickup_location,
        ':a'   => $availability,
        ':c'   => $contact
    ]);
    $donation_id = (int)$pdo->lastInsertId();

    // 2) 关联 donation_fooditem
    $map = $pdo->prepare("INSERT INTO donation_fooditem (donation_id, fooditem_id, quantity) VALUES (:d, :f, :q)");
    $map->execute([':d'=>$donation_id, ':f'=>$fooditem_id, ':q'=>$quantity]);

    // 3) 将 fooditem 标记为 'donation'（用于在 inventory 列表隐藏）
    $up = $pdo->prepare("UPDATE fooditem SET status = 'donation' WHERE foodItem_id = :f");
    $up->execute([':f'=>$fooditem_id]);

    $pdo->commit();
    respond(200, ['success'=>true, 'donation_id'=>$donation_id]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}
