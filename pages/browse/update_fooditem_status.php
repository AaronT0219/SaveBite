<?php
// /SaveBite/pages/browse/update_fooditem_status.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

function respond($code, $msg) {
  http_response_code($code);
  echo json_encode($msg);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(405, ['success' => false, 'error' => 'Method not allowed']);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload || !isset($payload['fooditem_id'], $payload['tagClassName'], $payload['status'])) {
  respond(400, ['success' => false, 'error' => 'Missing parameters']);
}

$foodItemId   = (int)$payload['fooditem_id'];
$tagClassName = (string)$payload['tagClassName'];
$flag         = (bool)$payload['status'];

// 数据库存的值：donation / used / null
$newStatus = ($tagClassName === '.used-tag-modal')
  ? ($flag ? 'used' : null)
  : ($flag ? 'donation' : null);

try {
  session_start();
  $donorUserId = $_SESSION['user_id'] ?? 7;

  $pdo->beginTransaction();

  // 1) 更新 fooditem.status
  $up = $pdo->prepare("UPDATE fooditem SET status = ? WHERE foodItem_id = ?");
  $up->execute([$newStatus, $foodItemId]);

  if ($newStatus === 'donation') {
    // 若尚无 donation 关联，则创建 donation + 映射
    $chk = $pdo->prepare("SELECT donation_id FROM donation_fooditem WHERE fooditem_id = ?");
    $chk->execute([$foodItemId]);
    $row = $chk->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
      // 防止 NOT NULL：pickup_location 用空字符串
      $pickupLocation = '';
      $donationStatus = 'open';

      $insDonation = $pdo->prepare("
        INSERT INTO donation (status, pickup_location, description, donation_date, donor_user_id, claimant_user_id)
        VALUES (:status, :pickup_location, :description, NOW(), :donor_user_id, NULL)
      ");
      $insDonation->execute([
        ':status'          => $donationStatus,
        ':pickup_location' => $pickupLocation,
        ':description'     => null,
        ':donor_user_id'   => $donorUserId,
      ]);
      $donationId = (int)$pdo->lastInsertId();

      // 数量写在 donation_fooditem.quantity（取 fooditem 当前数量，至少 0）
      $qStmt = $pdo->prepare("SELECT quantity FROM fooditem WHERE foodItem_id = ?");
      $qStmt->execute([$foodItemId]);
      $fiQty = (int)($qStmt->fetchColumn() ?? 0);

      $insMap = $pdo->prepare("INSERT INTO donation_fooditem (donation_id, fooditem_id, quantity) VALUES (?, ?, ?)");
      $insMap->execute([$donationId, $foodItemId, max(0,$fiQty)]);
    }
  } else {
    // 取消 donation/used：清理映射，若 donation 不再有其它条目则删除 donation
    $get = $pdo->prepare("SELECT donation_id FROM donation_fooditem WHERE fooditem_id = ?");
    $get->execute([$foodItemId]);
    if ($map = $get->fetch(PDO::FETCH_ASSOC)) {
      $donationId = (int)$map['donation_id'];
      $pdo->prepare("DELETE FROM donation_fooditem WHERE donation_id = ? AND fooditem_id = ?")
          ->execute([$donationId, $foodItemId]);
      $left = $pdo->prepare("SELECT COUNT(*) FROM donation_fooditem WHERE donation_id = ?");
      $left->execute([$donationId]);
      if ((int)$left->fetchColumn() === 0) {
        $pdo->prepare("DELETE FROM donation WHERE donation_id = ?")->execute([$donationId]);
      }
    }
  }

  $pdo->commit();
  respond(200, ['success'=>true, 'status'=>$newStatus, 'fooditem_id'=>$foodItemId]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  respond(500, ['success'=>false, 'error'=>$e->getMessage()]);
}
