<?php
// /SaveBite/pages/inventory/update_fooditem.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function respond(int $code, array $msg): void {
  http_response_code($code);
  echo json_encode($msg, JSON_UNESCAPED_UNICODE);
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  respond(405, ['success'=>false,'error'=>'Method not allowed']);
}

// 读取 JSON
$raw  = file_get_contents('php://input') ?: '';
$body = json_decode($raw, true);
if (!is_array($body)) respond(400, ['success'=>false,'error'=>'Invalid JSON']);

// 入参
$fooditem_id      = isset($body['fooditem_id']) ? (int)$body['fooditem_id'] : 0;
$food_name        = trim((string)($body['food_name'] ?? ''));
$quantity         = isset($body['quantity']) ? (int)$body['quantity'] : null;
$category         = trim((string)($body['category'] ?? ''));
$expiry_date      = trim((string)($body['expiry_date'] ?? ''));
$status           = trim((string)($body['status'] ?? ''));      // used | reserved | expired
$storage_location = trim((string)($body['storage_location'] ?? ''));
$description      = trim((string)($body['description'] ?? ''));

if ($fooditem_id <= 0) respond(400, ['success'=>false,'error'=>'fooditem_id required']);

// 允许值（枚举）
$ALLOWED_CATEGORIES = ['Produce','Protein','Dairy & Bakery','Grains & Pantry','Snacks & Beverages'];
$ALLOWED_LOCATIONS  = ['Fridge','Freezer','Pantry','Countertop'];
$ALLOWED_STATUS     = ['used','reserved','expired'];

// 校验
$errs=[];
if ($food_name==='')                                $errs[]='food_name required';
if (!is_int($quantity) || $quantity < 0)            $errs[]='quantity invalid';
if (!in_array($category, $ALLOWED_CATEGORIES, true))$errs[]='category invalid';
if ($expiry_date==='')                              $errs[]='expiry_date required';
if (!in_array($status, $ALLOWED_STATUS, true))      $errs[]='status invalid (used/reserved/expired)';
if (!in_array($storage_location, $ALLOWED_LOCATIONS, true)) $errs[]='storage_location invalid';
if ($errs) respond(400, ['success'=>false,'error'=>implode('; ', $errs)]);

try{
  $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 7;

  // 权限：确保该记录属于当前用户
  $own = $pdo->prepare("SELECT user_id FROM fooditem WHERE foodItem_id = ?");
  $own->execute([$fooditem_id]);
  $owner = $own->fetchColumn();
  if ($owner === false) respond(404, ['success'=>false,'error'=>'fooditem not found']);
  if ((int)$owner !== $uid) respond(403, ['success'=>false,'error'=>'permission denied']);

  // 更新
  $sql = "
    UPDATE fooditem SET
      food_name        = :n,
      quantity         = :q,
      category         = :c,
      expiry_date      = :e,
      status           = :s,
      storage_location = :l,
      description      = :d
    WHERE foodItem_id  = :id
  ";
  $st = $pdo->prepare($sql);
  $st->execute([
    ':n'  => $food_name,
    ':q'  => $quantity,
    ':c'  => $category,
    ':e'  => $expiry_date,
    ':s'  => $status, // 只有 used/reserved/expired
    ':l'  => $storage_location,
    ':d'  => $description,
    ':id' => $fooditem_id
  ]);

  respond(200, ['success'=>true, 'updated'=>true, 'fooditem_id'=>$fooditem_id]);
} catch(Throwable $e){
  respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}
