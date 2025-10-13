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

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) respond(400, ['success'=>false,'error'=>'Invalid JSON']);

$food_name        = trim((string)($data['food_name'] ?? ''));
$quantity         = isset($data['quantity']) ? (int)$data['quantity'] : null;
$category         = trim((string)($data['category'] ?? ''));
$expiry_date      = trim((string)($data['expiry_date'] ?? '')); // YYYY-MM-DD
$status           = trim((string)($data['status'] ?? ''));      // '', 'used', 'reserved'
$storage_location = trim((string)($data['storage_location'] ?? ''));
$description      = trim((string)($data['description'] ?? ''));

$errs = [];
if ($food_name === '') $errs[] = 'food_name required';
if (!is_int($quantity) || $quantity < 0) $errs[] = 'quantity invalid';
if ($category === '') $errs[] = 'category required';
if ($expiry_date === '') $errs[] = 'expiry_date required';
if ($storage_location === '') $errs[] = 'storage_location required';
if ($errs) respond(400, ['success'=>false,'error'=>implode('; ', $errs)]);

try {
  $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 7;

  $stmt = $pdo->prepare("
    INSERT INTO fooditem (food_name, quantity, category, expiry_date, status, storage_location, description, user_id)
    VALUES (:food_name, :quantity, :category, :expiry_date, :status, :storage_location, :description, :user_id)
  ");
  $stmt->execute([
    ':food_name'        => $food_name,
    ':quantity'         => $quantity,
    ':category'         => $category,
    ':expiry_date'      => $expiry_date,
    // 这里不把 donation 作为可选值插入；donation 通过创建 donation 时再设置
    ':status'           => ($status === '' ? null : $status),
    ':storage_location' => $storage_location,
    ':description'      => $description,
    ':user_id'          => $userId,
  ]);

  $id = (int)$pdo->lastInsertId();
  respond(200, ['success'=>true, 'fooditem_id'=>$id]);
} catch (Throwable $e) {
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
