<?php
// /SaveBite/pages/donationList/delete_donation.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

function respond(int $c, array $p){ http_response_code($c); echo json_encode($p, JSON_UNESCAPED_UNICODE); exit; }

try{
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') respond(405, ['success'=>false,'error'=>'Method not allowed']);
  $raw = file_get_contents('php://input') ?: '';
  $b = json_decode($raw, true) ?? $_POST;

  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $uid = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
  if ($uid <= 0) respond(401, ['success'=>false,'error'=>'not logged in']);

  $donationId = isset($b['donation_id']) ? (int)$b['donation_id'] : 0;
  if ($donationId<=0) respond(400, ['success'=>false,'error'=>'Missing donation_id']);

  // 鉴权
  $st = $pdo->prepare("SELECT donor_user_id FROM donation WHERE donation_id=?");
  $st->execute([$donationId]);
  $owner = $st->fetchColumn();
  if ($owner===false) respond(404, ['success'=>false,'error'=>'Donation not found']);
  if ((int)$owner !== $uid) respond(403, ['success'=>false,'error'=>'Permission denied']);

  // 找到所有关联 fooditem
  $q = $pdo->prepare("SELECT fooditem_id FROM donation_fooditem WHERE donation_id=?");
  $q->execute([$donationId]);
  $ids = array_map('intval', $q->fetchAll(PDO::FETCH_COLUMN));

  $pdo->beginTransaction();
  // 1) 置回库存状态
  if ($ids){
    $in = implode(',', array_fill(0, count($ids), '?'));
    $up = $pdo->prepare("UPDATE fooditem SET status = NULL WHERE foodItem_id IN ($in)");
    $up->execute($ids);
  }
  // 2) 删映射
  $pdo->prepare("DELETE FROM donation_fooditem WHERE donation_id=?")->execute([$donationId]);
  // 3) 删 donation
  $pdo->prepare("DELETE FROM donation WHERE donation_id=?")->execute([$donationId]);

  $pdo->commit();
  respond(200, ['success'=>true, 'deleted'=>true, 'donation_id'=>$donationId, 'restored_fooditem_ids'=>$ids]);
}catch(Throwable $e){
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}
