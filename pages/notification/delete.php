<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
session_start();

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'unauthorized'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'error'=>'method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
  }

  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) throw new Exception('invalid id');

  $stmt = $pdo->prepare(
    "DELETE FROM notification WHERE notification_id = :id AND user_id = :uid"
  );
  $stmt->execute([':id'=>$id, ':uid'=>$userId]);
  $deleted = $stmt->rowCount();

  echo json_encode(['ok'=>true, 'deleted'=>$deleted], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}

