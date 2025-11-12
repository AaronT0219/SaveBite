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

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  respond(405, ['success'=>false,'error'=>'Method not allowed']);
}

$raw  = file_get_contents('php://input') ?: '';
$body = json_decode($raw, true) ?? [];

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$uid = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
if ($uid <= 0) respond(401, ['success'=>false,'error'=>'not logged in']);

$foodItemId = isset($body['fooditem_id']) ? (int)$body['fooditem_id'] : 0;
$qty        = array_key_exists('quantity', $body) ? (int)$body['quantity'] : null; // null = 用当前库存数量
$status     = isset($body['donation_status']) ? (string)$body['donation_status'] : 'pending';

if ($foodItemId <= 0) respond(400, ['success'=>false,'error'=>'fooditem_id required']);
if (!in_array($status, ['pending','picked_up'], true)) {
  respond(400, ['success'=>false,'error'=>'invalid donation_status']);
}

try {
  // —— 同事务处理：加锁读取 → 去重校验 → 新建 donation & 映射 → 更新库存状态为 donation
  $pdo->beginTransaction();

  // 1) 行级锁，保证并发一致性
  $lock = $pdo->prepare("
    SELECT user_id, status, quantity, category
    FROM fooditem
    WHERE foodItem_id = ?
    FOR UPDATE
  ");
  $lock->execute([$foodItemId]);
  $fiRow = $lock->fetch(PDO::FETCH_ASSOC);
  if (!$fiRow) { $pdo->rollBack(); respond(404, ['success'=>false,'error'=>'food item not found']); }
  if ((int)$fiRow['user_id'] !== $uid) { $pdo->rollBack(); respond(403, ['success'=>false,'error'=>'permission denied']); }

  $curStatus = strtolower((string)$fiRow['status']);
  if (in_array($curStatus, ['used','expired','donation'], true)) {
    $pdo->rollBack();
    respond(409, ['success'=>false,'error'=>'This item cannot be donated in current status.']);
  }

  // 2) 防重复：同一 fooditem 已存在 pending 记录则拒绝
  $dup = $pdo->prepare("
    SELECT 1
    FROM donation d
    JOIN donation_fooditem df ON df.donation_id = d.donation_id
    WHERE d.donor_user_id = ? AND df.fooditem_id = ? AND d.status = 'pending'
    LIMIT 1
  ");
  $dup->execute([$uid, $foodItemId]);
  if ($dup->fetchColumn()) {
    $pdo->rollBack();
    respond(409, ['success'=>false,'error'=>'This item is already in your Donation List.']);
  }

  $foodQty  = max(0, (int)($fiRow['quantity'] ?? 0));
  $finalQty = is_null($qty) ? $foodQty : max(0, $qty);

  // 3) 新建 donation
  $insDon = $pdo->prepare("
    INSERT INTO donation (donor_user_id, status, pickup_location, availability, contact, donation_date, description, category)
    VALUES (:uid, :status, '', '', '', NULL, NULL, :cat)
  ");
  $insDon->execute([
    ':uid'    => $uid,
    ':status' => $status,                      // pending / picked_up
    ':cat'    => (string)($fiRow['category'] ?? '')
  ]);
  $donationId = (int)$pdo->lastInsertId();

  // 4) 建立映射
  $insMap = $pdo->prepare("INSERT INTO donation_fooditem (donation_id, fooditem_id, quantity) VALUES (?, ?, ?)");
  $insMap->execute([$donationId, $foodItemId, $finalQty]);

  // 5) **把库存状态改为 donation**
  $upFi = $pdo->prepare("UPDATE fooditem SET status='donation' WHERE foodItem_id=?");
  $upFi->execute([$foodItemId]);

  $pdo->commit();
  respond(200, ['success'=>true, 'donation_id'=>$donationId, 'quantity'=>$finalQty]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}
