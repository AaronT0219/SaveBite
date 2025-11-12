<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

function respond(int $c, array $p){ http_response_code($c); echo json_encode($p, JSON_UNESCAPED_UNICODE); exit; }

try{
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $uid = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);
  if ($uid <= 0) respond(401, ['success'=>false,'error'=>'not logged in']);

  $sql = "
    SELECT
      d.donation_id, d.status AS donation_status, d.pickup_location, d.availability, d.contact,
      d.description AS donation_desc, d.donation_date,
      df.fooditem_id, df.quantity AS donated_quantity,
      f.food_name, f.quantity AS food_current_quantity, f.category, f.expiry_date, f.status AS food_status,
      f.storage_location, f.description AS food_desc
    FROM donation d
    JOIN donation_fooditem df ON df.donation_id = d.donation_id
    JOIN fooditem f           ON f.foodItem_id   = df.fooditem_id
    WHERE d.donor_user_id = :uid
    ORDER BY d.donation_date DESC, d.donation_id DESC
  ";
  $st = $pdo->prepare($sql);
  $st->execute([':uid'=>$uid]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  $items = array_map(function($r){
    return [
      'id'             => 'D-'.(int)$r['fooditem_id'],
      'manageId'       => (string)((int)$r['fooditem_id']),
      'donation_id'    => (int)$r['donation_id'],
      'name'           => (string)($r['food_name'] ?? ''),
      'quantity'       => (string)($r['donated_quantity'] ?? ''),
      'category'       => (string)($r['category'] ?? ''),
      'expiry'         => (string)($r['expiry_date'] ?? ''),
      'food_status'    => (string)($r['food_status'] ?? ''),
      'desc'           => (string)($r['donation_desc'] ?? ''),
      'pickup_location'=> (string)($r['pickup_location'] ?? ''),
      'availability'   => (string)($r['availability'] ?? ''),
      'contact'        => (string)($r['contact'] ?? ''),
      'donation_status'=> (string)($r['donation_status'] ?? 'pending'),
      'donation_date'  => (string)($r['donation_date'] ?? ''),
    ];
  }, $rows);

  respond(200, ['success'=>true, 'items'=>$items, 'count'=>count($items)]);
}catch(Throwable $e){
  respond(500, ['success'=>false,'error'=>$e->getMessage()]);
}
