<?php
// /SaveBite/pages/donationList/update_donation.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';

function respond(int $code, array $payload): void {
  http_response_code($code);
  // 确保没有意外输出
  if (ob_get_length()) { ob_clean(); }
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

function read_input(): array {
  $raw = file_get_contents('php://input') ?: '';
  $json = json_decode($raw, true);
  if (is_array($json)) return $json;
  if (!empty($_POST)) return $_POST;
  return [];
}

try {
  if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    respond(405, ['success'=>false, 'error'=>'Method not allowed']);
  }

  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  $uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (int)($_SESSION['id'] ?? 0);
  if ($uid <= 0) respond(401, ['success'=>false, 'error'=>'not logged in']);

  $b = read_input();

  $donationId = isset($b['donation_id']) ? (int)$b['donation_id'] : 0;
  if ($donationId <= 0) respond(400, ['success'=>false, 'error'=>'Missing or invalid donation_id']);

  // donation 归属校验
  $stmt = $pdo->prepare("SELECT donor_user_id FROM donation WHERE donation_id = ?");
  $stmt->execute([$donationId]);
  $ownerId = $stmt->fetchColumn();
  if ($ownerId === false) respond(404, ['success'=>false, 'error'=>'Donation not found']);
  if ((int)$ownerId !== $uid) respond(403, ['success'=>false, 'error'=>'Permission denied']);

  // ---- 构造 donation 更新 ----
  $donSet = [];
  $donParam = [':id' => $donationId];

  if (array_key_exists('pickup_location', $b)) { $donSet[] = "pickup_location = :p"; $donParam[':p'] = trim((string)$b['pickup_location']); }
  if (array_key_exists('availability',    $b)) { $donSet[] = "availability = :a";    $donParam[':a'] = trim((string)$b['availability']); }
  if (array_key_exists('contact',         $b)) { $donSet[] = "contact = :c";         $donParam[':c'] = trim((string)$b['contact']); }
  if (array_key_exists('donation_status', $b)) {
    $val = trim((string)$b['donation_status']);
    $allowed = ['pending','picked_up'];
    if (!in_array($val, $allowed, true)) respond(400, ['success'=>false, 'error'=>'Invalid donation_status']);
    $donSet[] = "status = :s"; $donParam[':s'] = $val;
  }
<<<<<<< Updated upstream
  if (array_key_exists('desc', $b)) {
    $donSet[] = "description = :desc";
    $donParam[':desc'] = trim((string)$b['desc']);
  }

  if (array_key_exists('donation_date', $b)) {
     $dd = trim((string)$b['donation_date']);
     if ($dd !== '') {
       if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dd)) {
         respond(400, ['success'=>false, 'error'=>'Invalid donation_date format (YYYY-MM-DD)']);
       }
       $donSet[] = "donation_date = :dd";
       $donParam[':dd'] = $dd;
     }
   }

=======
  if (array_key_exists('donation_date', $b)) {
+    $dd = trim((string)$b['donation_date']);
+    if ($dd !== '') {
+      if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dd)) {
+        respond(400, ['success'=>false, 'error'=>'Invalid donation_date format (YYYY-MM-DD)']);
+      }
+      $donSet[] = "donation_date = :dd";
+      $donParam[':dd'] = $dd;
+    }
+  }
>>>>>>> Stashed changes

  // ---- donation_fooditem.quantity 可选更新 ----
  $doQtyUpdate = false;
  $qty  = null;
  $fid  = null;
  if (array_key_exists('quantity', $b))    { $qty = (int)$b['quantity']; $doQtyUpdate = true; }
  if (array_key_exists('fooditem_id', $b)) { $fid = (int)$b['fooditem_id']; }

  if ($doQtyUpdate && ($fid === null || $fid <= 0)) {
    respond(400, ['success'=>false, 'error'=>'quantity update requires valid fooditem_id']);
  }

  // ---- fooditem 可选更新（不再更新 description）----
  $fiSet = []; $fiParam = [];
  if ($fid && $fid > 0) {
    if (array_key_exists('food_name', $b)) { $fiSet[]="food_name = :fn"; $fiParam[':fn'] = trim((string)$b['food_name']); }
    if (array_key_exists('category',  $b)) { $fiSet[]="category = :fc";  $fiParam[':fc'] = trim((string)$b['category']); }
    if (array_key_exists('expiry',    $b)) {
      $exp = trim((string)$b['expiry']);
      if ($exp !== '') { $fiSet[]="expiry_date = :fe"; $fiParam[':fe'] = $exp; }
    }
    // 不再：if (array_key_exists('desc', $b)) { ... }  —— 描述只写 donation
  }

  if (empty($donSet) && !$doQtyUpdate && empty($fiSet)) {
    respond(400, ['success'=>false, 'error'=>'No updatable fields provided']);
  }

  $pdo->beginTransaction();

  if (!empty($donSet)) {
    $sql = "UPDATE donation SET ".implode(', ', $donSet)." WHERE donation_id = :id";
    $u = $pdo->prepare($sql);
    $u->execute($donParam);
  }

  if ($doQtyUpdate) {
    $u = $pdo->prepare("UPDATE donation_fooditem SET quantity = :q WHERE donation_id = :d AND fooditem_id = :f");
    $u->execute([':q'=>$qty, ':d'=>$donationId, ':f'=>$fid]);
  }

  if (!empty($fiSet)) {
    // 校验 fooditem 属于当前用户
    $o = $pdo->prepare("SELECT user_id FROM fooditem WHERE foodItem_id = ?");
    $o->execute([$fid]);
    $fiOwner = $o->fetchColumn();
    if ($fiOwner === false) throw new RuntimeException('fooditem not found');
    if ((int)$fiOwner !== $uid) throw new RuntimeException('permission denied for fooditem');

    $fiSql = "UPDATE fooditem SET ".implode(', ', $fiSet)." WHERE foodItem_id = :id";
    $fiParam[':id'] = $fid;
    $uu = $pdo->prepare($fiSql);
    $uu->execute($fiParam);
  }

  $pdo->commit();
  respond(200, ['success'=>true, 'updated'=>true, 'donation_id'=>$donationId]);
}
catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  // 不把 HTML 错误页返回到前端
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
