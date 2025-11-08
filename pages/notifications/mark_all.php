<?php
// /SaveBite/pages/notifications/mark_all.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

try {
  $userId = 1; // TODO: 接登录
  $stmt = $pdo->prepare("UPDATE notification SET is_read=1 WHERE user_id=:uid AND is_read=0");
  $stmt->execute([':uid'=>$userId]);
  echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
