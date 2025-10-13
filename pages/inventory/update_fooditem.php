<?php
// /SaveBite/pages/inventory/update_fooditem.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(405, ['success'=>false, 'error'=>'Method not allowed']);
  }

  // 读取 JSON 或表单
  $raw = file_get_contents('php://input') ?: '';
  $b = json_decode($raw, true);
  if (!is_array($b) || empty($b)) $b = $_POST;

  $id   = isset($b['fooditem_id']) ? (int)$b['fooditem_id'] : 0;
  if ($id <= 0) respond(400, ['success'=>false, 'error'=>'Missing or invalid fooditem_id']);

  // 允许更新的字段
  $food_name        = array_key_exists('food_name',        $b) ? trim((string)$b['food_name'])        : null;
  $quantity         = array_key_exists('quantity',         $b) ? (int)$b['quantity']                  : null;
  $category         = array_key_exists('category',         $b) ? trim((string)$b['category'])         : null;
  $expiry_date      = array_key_exists('expiry_date',      $b) ? trim((string)$b['expiry_date'])      : null;
  $status           = array_key_exists('status',           $b) ? (string)$b['status']                 : null; // '' | 'used' | 'reserved'
  $storage_location = array_key_exists('storage_location', $b) ? trim((string)$b['storage_location']) : null;
  $description      = array_key_exists('description',      $b) ? trim((string)$b['description'])      : null;

  $set = [];
  $p   = [':id'=>$id];

  if (!is_null($food_name))        { $set[] = 'food_name = :fn';        $p[':fn'] = $food_name; }
  if (!is_null($quantity))         { if ($quantity < 0) respond(400, ['success'=>false,'error'=>'quantity < 0']); $set[] = 'quantity = :qt'; $p[':qt'] = $quantity; }
  if (!is_null($category))         { $set[] = 'category = :ct';         $p[':ct'] = $category; }
  if (!is_null($expiry_date))      { $set[] = 'expiry_date = :ed';      $p[':ed'] = $expiry_date; }
  if (!is_null($status)) {  // 空字符串表示 available → 存 NULL
    if (!in_array($status, ['', 'used', 'reserved','expired'], true)) {
      respond(400, ['success'=>false,'error'=>'invalid status']);
    }
    $set[] = 'status = :st';
    $p[':st'] = ($status === '') ? null : $status;
  }
  if (!is_null($storage_location)) { $set[] = 'storage_location = :sl'; $p[':sl'] = $storage_location; }
  if (!is_null($description))      { $set[] = 'description = :ds';      $p[':ds'] = $description; }

  if (empty($set)) respond(400, ['success'=>false, 'error'=>'No updatable fields']);

  $sql = 'UPDATE fooditem SET '.implode(', ', $set).' WHERE foodItem_id = :id';
  $stmt = $pdo->prepare($sql);
  $stmt->execute($p);

  respond(200, ['success'=>true, 'updated'=>true, 'fooditem_id'=>$id]);
}
catch (Throwable $e) {
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
