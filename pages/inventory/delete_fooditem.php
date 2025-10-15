<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

function respond($code, $msg) {
  http_response_code($code);
  echo json_encode($msg, JSON_UNESCAPED_UNICODE);
  exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
  respond(405, ['success'=>false,'error'=>'Method not allowed']);
}

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
$id = isset($data['fooditem_id']) ? (int)$data['fooditem_id'] : 0;
if ($id <= 0) respond(400, ['success'=>false,'error'=>'fooditem_id required']);

$uid = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
if ($uid <= 0) respond(401, ['success'=>false,'error'=>'not logged in']);

try {
  // 先校验归属
  $own = $pdo->prepare("SELECT user_id FROM fooditem WHERE foodItem_id = ?");
  $own->execute([$id]);
  $owner = $own->fetchColumn();
  if ($owner === false) respond(404, ['success'=>false,'error'=>'fooditem not found']);
  if ((int)$owner !== $uid) respond(403, ['success'=>false,'error'=>'permission denied']);

  // 删除
  $stmt = $pdo->prepare("DELETE FROM fooditem WHERE foodItem_id = ?");
  $stmt->execute([$id]);
  respond(200, ['success'=>true, 'deleted'=>true, 'fooditem_id'=>$id]);
} catch (Throwable $e) {
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
