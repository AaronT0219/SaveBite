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
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) throw new Exception('invalid id');

  $stmt = $pdo->prepare(
    "UPDATE notification SET status='seen'
     WHERE notification_id=:id AND user_id=:uid"
  );
  $stmt->execute([':id'=>$id, ':uid'=>$userId]);

  echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
