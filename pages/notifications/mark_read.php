<?php
// /SaveBite/pages/notifications/mark_read.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

try {
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) throw new Exception('invalid id');
  $userId = 1; // TODO: 接登录

  $stmt = $pdo->prepare("UPDATE notification SET is_read=1 WHERE id=:id AND user_id=:uid");
  $stmt->execute([':id'=>$id, ':uid'=>$userId]);

  echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
