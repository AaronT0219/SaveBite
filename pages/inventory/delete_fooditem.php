<?php
// /SaveBite/pages/inventory/delete_fooditem.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

function respond($code, $msg) {
  http_response_code($code);
  echo json_encode($msg);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(405, ['success'=>false,'error'=>'Method not allowed']);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$id = isset($data['fooditem_id']) ? (int)$data['fooditem_id'] : 0;
if ($id <= 0) respond(400, ['success'=>false,'error'=>'fooditem_id required']);

try {
  $stmt = $pdo->prepare("DELETE FROM fooditem WHERE foodItem_id = ?");
  $stmt->execute([$id]);
  respond(200, ['success'=>true, 'deleted'=>true, 'fooditem_id'=>$id]);
} catch (Throwable $e) {
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
