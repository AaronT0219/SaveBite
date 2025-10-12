<?php
// /SaveBite/pages/donationList/update_donation.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

/** 统一响应 */
function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

/** 读取请求体：优先 JSON，其次 x-www-form-urlencoded */
function read_input(): array {
  $raw = file_get_contents('php://input') ?: '';
  $json = json_decode($raw, true);
  if (is_array($json)) return $json;
  // 兜底：表单
  if (!empty($_POST)) return $_POST;
  return [];
}

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success'=>false, 'error'=>'Method not allowed']);
  }

  session_start();
  // 如果你们还没做登录，这里用演示 uid=7；做了登录就从 session 取真实 user_id
  $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 7;

  $body = read_input();

  // ---- 入参解析 ----
  $donationId = isset($body['donation_id']) ? (int)$body['donation_id'] : 0;
  if ($donationId <= 0) {
    respond(400, ['success'=>false, 'error'=>'Missing or invalid donation_id']);
  }

  // 可更新字段（均为可选）
  $pickup  = array_key_exists('pickup_location', $body) ? trim((string)$body['pickup_location']) : null;
  $avail   = array_key_exists('availability',    $body) ? trim((string)$body['availability'])    : null;
  $contact = array_key_exists('contact',         $body) ? trim((string)$body['contact'])         : null;

  // donation.status 仅允许 pending / picked_up
  $status  = null;
  if (array_key_exists('donation_status', $body)) {
    $tmp = trim((string)$body['donation_status']);
    $allowed = ['pending','picked_up'];
    if (!in_array($tmp, $allowed, true)) {
      respond(400, ['success'=>false, 'error'=>'Invalid donation_status (allowed: pending, picked_up)']);
    }
    $status = $tmp;
  }

  // 可选：更新映射表数量
  $qty        = null;
  $foodItemId = null;
  if (array_key_exists('quantity', $body))    $qty = (int)$body['quantity'];
  if (array_key_exists('fooditem_id', $body)) $foodItemId = (int)$body['fooditem_id'];

  // ---- 鉴权：确认 donation 属于当前登录用户 ----
  $stmt = $pdo->prepare("SELECT donor_user_id FROM donation WHERE donation_id = ?");
  $stmt->execute([$donationId]);
  $ownerId = $stmt->fetchColumn();
  if ($ownerId === false) {
    respond(404, ['success'=>false, 'error'=>'Donation not found']);
  }
  if ((int)$ownerId !== $uid) {
    respond(403, ['success'=>false, 'error'=>'Permission denied']);
  }

  // ---- 构造更新 SQL（只更新传入的字段）----
  $setParts = [];
  $params   = [':id' => $donationId];

  if (!is_null($pickup))  { $setParts[] = "pickup_location = :p"; $params[':p'] = $pickup; }
  if (!is_null($avail))   { $setParts[] = "availability    = :a"; $params[':a'] = $avail; }
  if (!is_null($contact)) { $setParts[] = "contact         = :c"; $params[':c'] = $contact; }
  if (!is_null($status))  { $setParts[] = "status          = :s"; $params[':s'] = $status; }

  if (empty($setParts) && is_null($qty)) {
    respond(400, ['success'=>false, 'error'=>'No updatable fields provided']);
  }

  $pdo->beginTransaction();

  if (!empty($setParts)) {
    $sql = "UPDATE donation SET " . implode(', ', $setParts) . " WHERE donation_id = :id";
    $up  = $pdo->prepare($sql);
    $up->execute($params);
  }

  // 映射数量可选更新：需要 donation_fooditem 里有对应 fooditem_id
  if (!is_null($qty)) {
    if (is_null($foodItemId) || $foodItemId <= 0) {
      throw new RuntimeException('quantity update requires valid fooditem_id');
    }
    $u = $pdo->prepare("UPDATE donation_fooditem SET quantity = :q WHERE donation_id = :d AND fooditem_id = :f");
    $u->execute([':q'=>$qty, ':d'=>$donationId, ':f'=>$foodItemId]);
  }

  // ---- 查询更新后的数据回前端（便于同步 UI）----
  $detailSql = "
    SELECT
      d.donation_id,
      d.status            AS donation_status,
      d.pickup_location,
      d.availability,
      d.contact,
      d.description       AS donation_desc,
      d.donation_date,
      d.claimant_user_id,

      df.fooditem_id,
      df.quantity         AS donated_quantity,

      f.food_name,
      f.quantity          AS food_current_quantity,
      f.category,
      f.expiry_date,
      f.status            AS food_status,
      f.storage_location,
      f.description       AS food_desc
    FROM donation d
    LEFT JOIN donation_fooditem df ON df.donation_id = d.donation_id
    LEFT JOIN fooditem f           ON f.foodItem_id   = df.fooditem_id
    WHERE d.donation_id = :id
  ";
  $q = $pdo->prepare($detailSql);
  $q->execute([':id'=>$donationId]);
  $rows = $q->fetchAll(PDO::FETCH_ASSOC);

  $pdo->commit();

  // 规范化返回（可能一条 donation 对应多 fooditem；一般你们是一对一）
  $items = array_map(function($r){
    return [
      'donation_id'     => (int)$r['donation_id'],
      'donation_status' => (string)$r['donation_status'],
      'pickup_location' => (string)($r['pickup_location'] ?? ''),
      'availability'    => (string)($r['availability'] ?? ''),
      'contact'         => (string)($r['contact'] ?? ''),
      'donation_date'   => (string)$r['donation_date'],
      'claimant_user_id'=> $r['claimant_user_id'] === null ? null : (int)$r['claimant_user_id'],

      'fooditem_id'     => $r['fooditem_id'] === null ? null : (int)$r['fooditem_id'],
      'donated_quantity'=> $r['donated_quantity'] === null ? null : (int)$r['donated_quantity'],

      'food_name'       => (string)($r['food_name'] ?? ''),
      'category'        => (string)($r['category'] ?? ''),
      'expiry'          => (string)($r['expiry_date'] ?? ''),
      'food_status'     => (string)($r['food_status'] ?? ''),
      'storage_location'=> (string)($r['storage_location'] ?? ''),
      'food_desc'       => (string)($r['food_desc'] ?? ''),
    ];
  }, $rows);

  respond(200, ['success'=>true, 'updated'=>true, 'items'=>$items]);
}
catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
