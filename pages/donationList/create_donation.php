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

// 读 JSON
$raw  = file_get_contents('php://input') ?: '';
$body = json_decode($raw, true) ?? [];

// 取会话用户（兼容 user_id / id）
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$uid = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
if ($uid <= 0) respond(401, ['success'=>false,'error'=>'not logged in']);

// 入参
$foodItemId = isset($body['fooditem_id']) ? (int)$body['fooditem_id'] : 0;
$qty        = array_key_exists('quantity', $body) ? (int)$body['quantity'] : null; // null = 用 fooditem 当前数量
$status     = isset($body['donation_status']) ? (string)$body['donation_status'] : 'pending';

if ($foodItemId <= 0) respond(400, ['success'=>false,'error'=>'fooditem_id required']);
if (!in_array($status, ['pending','picked_up'], true)) {
  respond(400, ['success'=>false,'error'=>'invalid donation_status']);
}

try {
  // 校验所有权 & 取当前信息
  $fi = $pdo->prepare("
    SELECT foodItem_id, user_id, quantity, category, food_name, expiry_date, storage_location, description, status
    FROM fooditem
    WHERE foodItem_id = ?
    LIMIT 1
  ");
  $fi->execute([$foodItemId]);
  $row = $fi->fetch(PDO::FETCH_ASSOC);
  if (!$row) respond(404, ['success'=>false,'error'=>'food item not found']);

  if ((int)$row['user_id'] !== $uid) {
    respond(403, ['success'=>false,'error'=>'permission denied']);
  }

  // 追加校验：status=used 禁止捐赠
  if (strcasecmp((string)$row['status'], 'used') === 0) {
    respond(409, ['success'=>false, 'error'=>'This item is already used and cannot be donated.']);
  }

  // 防重复：同一个 fooditem 若已存在 pending 的 donation，则拒绝
  $dup = $pdo->prepare("
    SELECT d.donation_id
    FROM donation d
    JOIN donation_fooditem df ON df.donation_id = d.donation_id
    WHERE d.donor_user_id = :uid
      AND df.fooditem_id = :fid
      AND d.status = 'pending'
    LIMIT 1
  ");
  $dup->execute([':uid'=>$uid, ':fid'=>$foodItemId]);
  if ($dup->fetchColumn()) {
    respond(409, ['success'=>false,'error'=>'This item is already in your Donation List.']);
  }

  $foodQty  = max(0, (int)($row['quantity'] ?? 0));
  $finalQty = is_null($qty) ? $foodQty : max(0, $qty);

  $pdo->beginTransaction();

  // 1) 新建 donation
  $insDon = $pdo->prepare("
    INSERT INTO donation (donor_user_id, status, pickup_location, availability, contact, donation_date, description, category)
    VALUES (:uid, :status, '', '', '', CURDATE(), NULL, :cat)
  ");
  $insDon->execute([
    ':uid'    => $uid,
    ':status' => $status,                 // pending / picked_up
    ':cat'    => (string)($row['category'] ?? '')
  ]);
  $donationId = (int)$pdo->lastInsertId();

  // 2) 建立映射
  $insMap = $pdo->prepare("INSERT INTO donation_fooditem (donation_id, fooditem_id, quantity) VALUES (?, ?, ?)");
  $insMap->execute([$donationId, $foodItemId, $finalQty]);

  // 3) 同步库存状态为 donation（用于 inventory 显示 donation）
  $updFi = $pdo->prepare("UPDATE fooditem SET status = 'donation' WHERE foodItem_id = ?");
  $updFi->execute([$foodItemId]);

  $pdo->commit();
  respond(200, ['success'=>true, 'donation_id'=>$donationId, 'quantity'=>$finalQty]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}
