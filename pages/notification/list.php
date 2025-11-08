<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
session_start();

try {
  $filter = $_GET['filter'] ?? 'all';
  
  $userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? 1;

  // 字段名按你的表来
  $sql = "SELECT 
            notification_id      AS id,
            target_type          AS type,
            target_id            AS ref_id,
            title,
            description          AS summary,
            NULL                 AS details,    -- 预留
            CASE WHEN status='seen' THEN 1 ELSE 0 END AS is_read,
            notification_date    AS created_at
          FROM notification
          WHERE user_id = :uid";
  if ($filter === 'unread') $sql .= " AND status='unread'";
  $sql .= " ORDER BY notification_date DESC";

  $stmt = $pdo->prepare($sql);
  $stmt->execute([':uid'=>$userId]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // 未读角标
  $cnt = $pdo->prepare("SELECT COUNT(*) FROM notification WHERE user_id=:uid AND status='unread'");
  $cnt->execute([':uid'=>$userId]);
  $unread = (int)$cnt->fetchColumn();

  echo json_encode(['ok'=>true,'unread_count'=>$unread,'items'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
