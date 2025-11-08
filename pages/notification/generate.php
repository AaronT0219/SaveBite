<?php
// /SaveBite/pages/notification/generate.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
session_start();

$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
// 允许后台批跑时不带登录；若你只在前端调用，可改成强制鉴权
if (!$userId) {
  // 可选：return 401；此处放行，按每条业务记录自己的 user_id 写入
}

try {
  $pdo->beginTransaction();

  // —— 公用：用于“当日去重”的唯一键（建议真的建索引，见文末 D 节）——
  // 逻辑去重条件：同一个 (user_id, target_type, target_id, title) 当天只生成一次

  // 1) 7天内到期（inventory）
  $sql1 = "
  INSERT INTO notification (user_id, target_type, target_id, title, description, status, notification_date)
  SELECT i.user_id, 'inventory', i.foodItem_id,
         CONCAT(i.food_name, ' will expire soon'),
         CONCAT('Item \"', i.food_name, '\" expires on ', DATE_FORMAT(i.expiry_date,'%Y-%m-%d')),
         'unread', NOW()
  FROM fooditem i
  WHERE i.expiry_date BETWEEN CURDATE() AND (CURDATE() + INTERVAL 7 DAY)
    AND i.status IN ('available', 'donation')  -- 你可以按需放宽
    AND NOT EXISTS (
      SELECT 1 FROM notification n
      WHERE n.user_id=i.user_id AND n.target_type='inventory' AND n.target_id=i.foodItem_id
        AND n.title=CONCAT(i.food_name, ' will expire soon')
        AND DATE(n.notification_date)=CURDATE()
    )";
  $pdo->exec($sql1);

  // 2a) 物品从 inventory 进入 donation（两种来源都兜底）
  //   方案A：看 donation 新增
  $sql2a = "
  INSERT INTO notification (user_id, target_type, target_id, title, description, status, notification_date)
  SELECT d.donor_user_id, 'donation', d.donation_id,
         'Donation created',
         CONCAT('You donated \"', IFNULL(d.description,''), '\" at ', IFNULL(d.pickup_location,'N/A')),
         'unread', NOW()
  FROM donation d
  WHERE d.donor_user_id IS NOT NULL
    AND NOT EXISTS (
      SELECT 1 FROM notification n
      WHERE n.user_id=d.donor_user_id AND n.target_type='donation' AND n.target_id=d.donation_id
        AND n.title='Donation created'
        AND DATE(n.notification_date)=CURDATE()
    )";
  $pdo->exec($sql2a);

  //   方案B：看 inventory 的 status=donation（防止有人直接改 status）
  $sql2b = "
  INSERT INTO notification (user_id, target_type, target_id, title, description, status, notification_date)
  SELECT i.user_id, 'donation', i.foodItem_id,
         'Moved to donation',
         CONCAT('Item \"', i.food_name, '\" moved from inventory to donation'),
         'unread', NOW()
  FROM fooditem i
  WHERE i.status='donation'
    -- Avoid duplicate with rule 2a: if this fooditem is already mapped to a donation by the same user, skip here
    AND NOT EXISTS (
      SELECT 1
      FROM donation_fooditem df
      JOIN donation d ON d.donation_id = df.donation_id
      WHERE df.fooditem_id = i.foodItem_id
        AND d.donor_user_id = i.user_id
    )
    -- De-duplicate same title per item
    AND NOT EXISTS (
      SELECT 1 FROM notification n
      WHERE n.user_id=i.user_id AND n.target_type='donation' AND n.target_id=i.foodItem_id
        AND n.title='Moved to donation'
    )";
  $pdo->exec($sql2b);

  // 3) 捐赠领取日期前1天提醒（给捐赠者；若有认领者也可提醒）
  $sql3_donor = "
  INSERT INTO notification (user_id, target_type, target_id, title, description, status, notification_date)
  SELECT d.donor_user_id, 'donation', d.donation_id,
         'Pickup tomorrow',
         CONCAT('Scheduled pickup on ', DATE_FORMAT(d.donation_date,'%Y-%m-%d'),
                ' at ', IFNULL(d.pickup_location,'N/A')),
         'unread', NOW()
  FROM donation d
  WHERE d.donation_date = CURDATE() + INTERVAL 1 DAY
    AND d.status = 'pending'
    AND d.donor_user_id IS NOT NULL
    AND NOT EXISTS (
      SELECT 1 FROM notification n
      WHERE n.user_id=d.donor_user_id AND n.target_type='donation' AND n.target_id=d.donation_id
        AND n.title='Pickup tomorrow'
        AND DATE(n.notification_date)=CURDATE()
    )";
  $pdo->exec($sql3_donor);

  // 可选：提醒认领者（如果 claimant_user_id 有值）
  $sql3_claimant = "
  INSERT INTO notification (user_id, target_type, target_id, title, description, status, notification_date)
  SELECT d.claimant_user_id, 'donation', d.donation_id,
         'Pickup tomorrow',
         CONCAT('Please pick up on ', DATE_FORMAT(d.donation_date,'%Y-%m-%d'),
                ' at ', IFNULL(d.pickup_location,'N/A')),
         'unread', NOW()
  FROM donation d
  WHERE d.donation_date = CURDATE() + INTERVAL 1 DAY
    AND d.status = 'pending'
    AND d.claimant_user_id IS NOT NULL
    AND NOT EXISTS (
      SELECT 1 FROM notification n
      WHERE n.user_id=d.claimant_user_id AND n.target_type='donation' AND n.target_id=d.donation_id
        AND n.title='Pickup tomorrow'
        AND DATE(n.notification_date)=CURDATE()
    )";
  $pdo->exec($sql3_claimant);

  // 4) mealplan 的提醒时间（建议：提前 1 天 09:00，本地习惯）
  //   你的表只有 date，没有时间；这里按“明天”来判断，生成时间就用 NOW()
  $sql4 = "
  INSERT INTO notification (user_id, target_type, target_id, title, description, status, notification_date)
  SELECT m.user_id, 'meal_plan', m.mealplan_id,
         'Meal plan tomorrow',
         CONCAT('\"', m.meal_name, '\" for ', m.meal_type, ' on ',
                DATE_FORMAT(m.mealplan_date,'%Y-%m-%d')),
         'unread', NOW()
  FROM mealplan m
  WHERE m.mealplan_date = CURDATE() + INTERVAL 1 DAY
    AND NOT EXISTS (
      SELECT 1 FROM notification n
      WHERE n.user_id=m.user_id AND n.target_type='meal_plan' AND n.target_id=m.mealplan_id
        AND n.title='Meal plan tomorrow'
    )";
  $pdo->exec($sql4);

  $pdo->commit();
  echo json_encode(['ok'=>true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
