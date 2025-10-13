<?php
// /SaveBite/pages/donationList/create_donation.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

$raw = file_get_contents('php://input') ?: '';
$body = json_decode($raw, true) ?? [];
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  respond(405, ['success'=>false,'error'=>'Method not allowed']);
}

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 7;

$foodItemId = isset($body['fooditem_id']) ? (int)$body['fooditem_id'] : 0;
$qty        = isset($body['quantity']) ? (int)$body['quantity'] : 1;
$status     = isset($body['donation_status']) ? (string)$body['donation_status'] : 'pending';

if ($foodItemId <= 0) respond(400, ['success'=>false,'error'=>'fooditem_id required']);
if (!in_array($status, ['pending','picked_up'], true)) {
  respond(400, ['success'=>false,'error'=>'invalid donation_status']);
}

try {
  $pdo->beginTransaction();

  // 1) donation
  $stmt = $pdo->prepare("
    INSERT INTO donation (donor_user_id, status, pickup_location, availability, contact, donation_date, description)
    VALUES (:uid, :status, '', '', '', NOW(), '')
  ");
  $stmt->execute([':uid'=>$uid, ':status'=>$status]);
  $donationId = (int)$pdo->lastInsertId();

  // 2) 映射 donation_fooditem
  $m = $pdo->prepare("
    INSERT INTO donation_fooditem (donation_id, fooditem_id, quantity)
    VALUES (:d, :f, :q)
  ");
  $m->execute([':d'=>$donationId, ':f'=>$foodItemId, ':q'=>$qty]);

  // 3) 把 fooditem.status 置为 'donation'，便于 inventory 列表隐藏
  $u = $pdo->prepare("UPDATE fooditem SET status = 'donation' WHERE foodItem_id = :f");
  $u->execute([':f'=>$foodItemId]);

  $pdo->commit();
  respond(200, ['success'=>true, 'donation_id'=>$donationId]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}
