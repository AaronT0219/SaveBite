<?php
// /SaveBite/pages/donationList/delete_donation.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

function respond(int $c, array $p){
  http_response_code($c);
  echo json_encode($p, JSON_UNESCAPED_UNICODE);
  exit;
}

try{
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(405, ['success'=>false,'error'=>'Method not allowed']);
  }

  $raw = file_get_contents('php://input') ?: '';
  $b   = json_decode($raw, true) ?? $_POST;

  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $uid = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
  if ($uid <= 0) respond(401, ['success'=>false,'error'=>'not logged in']);

  $donationId = isset($b['donation_id']) ? (int)$b['donation_id'] : 0;
  if ($donationId <= 0) respond(400, ['success'=>false,'error'=>'Missing donation_id']);

  // 鉴权：确保 donation 属于当前用户
  $st = $pdo->prepare("SELECT donor_user_id FROM donation WHERE donation_id = ?");
  $st->execute([$donationId]);
  $owner = $st->fetchColumn();
  if ($owner === false) respond(404, ['success'=>false,'error'=>'Donation not found']);
  if ((int)$owner !== $uid) respond(403, ['success'=>false,'error'=>'Permission denied']);

  // 找到所有关联的 fooditem（用于回写 status）
  $q = $pdo->prepare("SELECT fooditem_id FROM donation_fooditem WHERE donation_id = ?");
  $q->execute([$donationId]);
  $ids = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));

  $pdo->beginTransaction();

  // 1) 删除映射
  $delMap = $pdo->prepare("DELETE FROM donation_fooditem WHERE donation_id = ?");
  $delMap->execute([$donationId]);
  $mapRows = $delMap->rowCount(); // 可能为 0（允许）

  // 2) 删除 donation（必须删到 1 行）
  $delDon = $pdo->prepare("DELETE FROM donation WHERE donation_id = ?");
  $delDon->execute([$donationId]);
  $donRows = $delDon->rowCount();
  if ($donRows !== 1) {
    $pdo->rollBack();
    respond(404, ['success'=>false, 'error'=>'Delete failed: donation not found or already deleted', 'donation_id'=>$donationId]);
  }

  // 3) 把这些库存项的状态设回 available
  if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $upd = $pdo->prepare("UPDATE fooditem SET status = 'available' WHERE foodItem_id IN ($placeholders)");
    $upd->execute($ids);
  }

  $pdo->commit();

  respond(200, [
    'success' => true,
    'deleted' => true,
    'donation_id' => $donationId,
    'map_rows'     => $mapRows,
    'donation_rows'=> $donRows,
    'fooditem_ids' => $ids
  ]);
}
catch (Throwable $e){
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}
