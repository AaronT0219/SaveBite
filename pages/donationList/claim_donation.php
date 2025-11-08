<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

function respond(int $code, array $payload): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(405, ['success'=>false,'error'=>'Method not allowed']);
  }

  $raw = file_get_contents('php://input') ?: '';
  $b   = json_decode($raw, true) ?? [];

  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $uid = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
  if ($uid <= 0) respond(401, ['success'=>false,'error'=>'not logged in']);

  $donationId = isset($b['donation_id']) ? (int)$b['donation_id'] : 0;
  if ($donationId <= 0) respond(400, ['success'=>false,'error'=>'Missing donation_id']);

  // Verify ownership and read date
  $st = $pdo->prepare('SELECT donor_user_id, donation_date FROM donation WHERE donation_id = ?');
  $st->execute([$donationId]);
  $row = $st->fetch(PDO::FETCH_ASSOC);
  if (!$row) respond(404, ['success'=>false,'error'=>'Donation not found']);
  if ((int)$row['donor_user_id'] !== $uid) respond(403, ['success'=>false,'error'=>'Permission denied']);
  $donDate = (string)($row['donation_date'] ?? '');

  // One item name for message
  $q = $pdo->prepare('SELECT f.food_name FROM donation_fooditem df JOIN fooditem f ON f.foodItem_id = df.fooditem_id WHERE df.donation_id = ? LIMIT 1');
  $q->execute([$donationId]);
  $firstName = (string)($q->fetchColumn() ?: 'Donation');

  $pdo->beginTransaction();

  $desc = $donDate !== ''
    ? ($firstName . ' claimed (Donated on: ' . $donDate . ')')
    : ($firstName . ' claimed');

  $ins = $pdo->prepare(
    'INSERT INTO notification (user_id, target_type, target_id, title, description, status, notification_date)
     VALUES (:uid, :type, :tid, :title, :desc, :status, NOW())'
  );
  $ins->execute([
    ':uid'   => $uid,
    ':type'  => 'donation',
    ':tid'   => $donationId,
    ':title' => 'Donation claimed',
    ':desc'  => $desc,
    ':status'=> 'unread',
  ]);

  $pdo->commit();

  respond(200, [
    'success' => true,
    'claimed' => true,
    'donation_id' => $donationId,
  ]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}

