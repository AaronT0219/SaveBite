<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
session_start();

try {
  $userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;

  $stmt = $pdo->prepare(
    "UPDATE notification SET status='seen'
     WHERE user_id=:uid AND status='unread'"
  );
  $stmt->execute([':uid'=>$userId]);

  echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->Message()], JSON_UNESCAPED_UNICODE);
}
