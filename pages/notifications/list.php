<?php
// /SaveBite/pages/notifications/list.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

try {
  $filter = $_GET['filter'] ?? 'all';
  $userId = 1; // TODO: 接登录

  $sql = "SELECT id, type, ref_id, title, summary, details, is_read, created_at
          FROM notification
          WHERE user_id = :uid";
  if ($filter === 'unread') $sql .= " AND is_read = 0";
  $sql .= " ORDER BY created_at DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([':uid'=>$userId]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 若你早期数据结构用的是 message 字段，这里兼容：title 为空时回落到 message
  foreach ($rows as &$r) {
    if (!isset($r['title']) || $r['title'] === null) $r['title'] = '';
  }

  $cnt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE user_id=:uid AND is_read=0");
  $cnt->execute([':uid'=>$userId]);
  $unread = (int)$cnt->fetchColumn();

  echo json_encode(['ok'=>true,'unread_count'=>$unread,'items'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
