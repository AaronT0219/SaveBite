<?php
// /SaveBite/pages/inventory/create_fooditem.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function respond(int $code, array $msg): void {
  http_response_code($code);
  echo json_encode($msg, JSON_UNESCAPED_UNICODE);
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  respond(405, ['success' => false, 'error' => 'Method not allowed']);
}

// 读取 JSON
$raw  = file_get_contents('php://input') ?: '';
$body = json_decode($raw, true);
if (!is_array($body)) {
  respond(400, ['success' => false, 'error' => 'Invalid JSON']);
}

// 入参
$food_name        = trim((string)($body['food_name'] ?? ''));
$quantity         = isset($body['quantity']) ? (int)$body['quantity'] : null;
$category         = trim((string)($body['category'] ?? ''));
$expiry_date      = trim((string)($body['expiry_date'] ?? '')); // YYYY-MM-DD
$status           = trim((string)($body['status'] ?? ''));      // used | reserved | expired
$storage_location = trim((string)($body['storage_location'] ?? ''));
$description      = trim((string)($body['description'] ?? ''));

// 允许值（枚举）
$ALLOWED_CATEGORIES = ['Produce','Protein','Dairy & Bakery','Grains & Pantry','Snacks & Beverages'];
$ALLOWED_LOCATIONS  = ['Fridge','Freezer','Pantry','Countertop'];
$ALLOWED_STATUS     = ['used','available','expired','donation'];

// 校验
$errs = [];
if ($food_name === '')                              $errs[] = 'food_name required';
if (!is_int($quantity) || $quantity < 0)            $errs[] = 'quantity invalid';
if (!in_array($category, $ALLOWED_CATEGORIES, true))$errs[] = 'category invalid';
if ($expiry_date === '')                            $errs[] = 'expiry_date required';
if (!in_array($status, $ALLOWED_STATUS, true))      $errs[] = 'status invalid (used/available/expired/donation)';
if (!in_array($storage_location, $ALLOWED_LOCATIONS, true)) $errs[] = 'storage_location invalid';
if ($errs) respond(400, ['success'=>false, 'error'=>implode('; ', $errs)]);

// 执行插入
try {
  $userId = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
  if ($userId <= 0) {
    respond(401, ['success'=>false,'error'=>'not logged in']);
  }

  $stmt = $pdo->prepare("
    INSERT INTO fooditem
      (food_name, quantity, category, expiry_date, status, storage_location, description, user_id)
    VALUES
      (:food_name, :quantity, :category, :expiry_date, :status, :storage_location, :description, :user_id)
  ");

  $stmt->execute([
    ':food_name'        => $food_name,
    ':quantity'         => $quantity,
    ':category'         => $category,
    ':expiry_date'      => $expiry_date,
    ':status'           => $status, // 只有 used/available/expired
    ':storage_location' => $storage_location,
    ':description'      => $description,
    ':user_id'          => $userId,
  ]);

  $id = (int)$pdo->lastInsertId();
  respond(200, ['success'=>true, 'fooditem_id'=>$id]);
} catch (Throwable $e) {
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
