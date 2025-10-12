<?php
// /SaveBite/pages/donationList/list_my_donations.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

function respond($c,$p){ http_response_code($c); echo json_encode($p); exit; }

try{
  session_start();
  $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 7;

  $sql = "
    SELECT
      d.donation_id,
      d.status            AS donation_status,
      d.pickup_location,
      d.description       AS donation_desc,
      d.donation_date,
      d.claimant_user_id,

      df.fooditem_id,
      df.quantity         AS donated_quantity,

      f.food_name,
      f.quantity          AS food_current_quantity,
      f.category,
      f.expiry_date,
      f.status            AS food_status,
      f.storage_location,
      f.description       AS food_desc
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
      'id'          => 'D-'.(int)$r['fooditem_id'],
      'manageId'    => 'M-'.(int)$r['fooditem_id'],
      'donation_id' => (int)$r['donation_id'],

      'name'        => (string)$r['food_name'],
      'quantity'    => (string)$r['donated_quantity'],
      'category'    => (string)$r['category'],
      'expiry'      => (string)$r['expiry_date'],
      'status'      => (string)$r['food_status'],   // DB 值：donation/used/...
      'desc'        => (string)($r['food_desc'] ?? ''),

      // 仅 pickup_location 有真实 DB 值；availability/contact 暂时为空串用于前端提示
      'pickup_location' => (string)($r['pickup_location'] ?? ''),
      'availability'    => '',
      'contact'         => '',

      'donation_status' => (string)($r['donation_status'] ?? ''),
      'donation_date'   => (string)$r['donation_date'],
      'claimant_user_id'=> $r['claimant_user_id'] === null ? null : (int)$r['claimant_user_id'],
    ];
  }, $rows);

  respond(200, ['success'=>true, 'items'=>$items, 'count'=>count($items)]);
}catch(Throwable $e){
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
