<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config.php';
session_start();

/** 先做鉴权，禁止默认 user_id=1 的“伪登录” */
$userId = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
if (!$userId) {
  http_response_code(401);
  echo json_encode(['ok'=>false,'error'=>'unauthorized'], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $filter = $_GET['filter'] ?? 'all';
  $type   = $_GET['type']   ?? null; // optional: inventory | donation | meal_plan

  // 根据你的真实字段名改这里
  $sql = "SELECT
            notification_id      AS id,
            target_type          AS type,
            target_id            AS ref_id,
            title,
            description          AS summary,
            NULL                 AS details,
            CASE WHEN status='seen' THEN 1 ELSE 0 END AS is_read,
            CASE WHEN TIME(notification_date) = '00:00:00' THEN NOW() ELSE notification_date END AS created_at
          FROM notification
          WHERE user_id = :uid";
  if ($filter === 'unread') {
    // Treat any non-'seen' value as unread (covers legacy/invalid values)
    $sql .= " AND status <> 'seen'";
  }
  if (is_string($type)) {
    $type = trim($type);
    if (in_array($type, ['inventory','donation','meal_plan'], true)) {
      $sql .= " AND target_type = :type";
    } else {
      $type = null; // ignore invalid value
    }
  }
  $sql .= " ORDER BY notification_date DESC";

  $stmt = $pdo->prepare($sql);
  $params = [':uid' => $userId];
  if ($type) { $params[':type'] = $type; }
  $stmt->execute($params);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Build pretty messages per type
  $fiIds = [];
  $donIds = [];
  // removed: $donFoodIds used for 'Moved to donation'
  $mpIds = [];
  foreach ($rows as $r) {
    $t = (string)($r['type'] ?? '');
    $ref = (int)($r['ref_id'] ?? 0);
    if ($t === 'inventory' && $ref > 0) $fiIds[$ref] = true;
    if ($t === 'donation' && $ref > 0) { $donIds[$ref] = true; }
    if ($t === 'meal_plan' && $ref > 0) $mpIds[$ref] = true;
  }

  // Prefetch maps
  $foodMap = [];
  $allFoodIds = array_keys($fiIds);
  if ($allFoodIds) {
    $ph = implode(',', array_fill(0, count($allFoodIds), '?'));
    $q = $pdo->prepare("SELECT foodItem_id, food_name, expiry_date FROM fooditem WHERE user_id=? AND foodItem_id IN ($ph)");
    $args = array_merge([$userId], $allFoodIds);
    $q->execute($args);
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
      $foodMap[(int)$row['foodItem_id']] = $row;
    }
  }

  $donMap = [];
  if ($donIds) {
    $ids = array_keys($donIds);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $q = $pdo->prepare("SELECT donation_id, donation_date FROM donation WHERE donor_user_id=? AND donation_id IN ($ph)");
    $args = array_merge([$userId], $ids);
    $q->execute($args);
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
      $donMap[(int)$row['donation_id']] = $row;
    }
  }

  $donNameMap = [];
  if ($donIds) {
    $ids = array_keys($donIds);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $q = $pdo->prepare(
      "SELECT d.donation_id, f.food_name
         FROM donation d
         JOIN donation_fooditem df ON df.donation_id = d.donation_id
         JOIN fooditem f ON f.foodItem_id = df.fooditem_id
        WHERE d.donor_user_id = ? AND d.donation_id IN ($ph)");
    $args = array_merge([$userId], $ids);
    $q->execute($args);
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
      $did = (int)$row['donation_id'];
      if (!isset($donNameMap[$did])) $donNameMap[$did] = (string)$row['food_name'];
    }
  }

  $mealMap = [];
  if ($mpIds) {
    $ids = array_keys($mpIds);
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $q = $pdo->prepare("SELECT mealplan_id, meal_name, mealplan_date FROM mealplan WHERE user_id=? AND mealplan_id IN ($ph)");
    $args = array_merge([$userId], $ids);
    $q->execute($args);
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
      $mealMap[(int)$row['mealplan_id']] = $row;
    }
  }

  // Compose final messages (single line)
  foreach ($rows as &$r) {
    $t = (string)($r['type'] ?? '');
    $ref = (int)($r['ref_id'] ?? 0);
    $msg = (string)($r['title'] ?? '');
    if ($t === 'inventory') {
      $title = (string)($r['title'] ?? '');
      if ($title === 'Donation blocked') {
        // Specific message when donating is blocked due to meal plan conflict
        if ($ref && isset($foodMap[$ref])) {
          $name = (string)($foodMap[$ref]['food_name'] ?? 'Item');
          $msg = sprintf('Cannot split %s between meal plan and donation. Create two separate items.', $name);
        } else {
          $msg = 'Cannot split item between meal plan and donation. Create two separate items.';
        }
      } else if ($ref && isset($foodMap[$ref])) {
        $name = (string)($foodMap[$ref]['food_name'] ?? 'Item');
        $date = (string)($foodMap[$ref]['expiry_date'] ?? '');
        if ($date !== '') $msg = sprintf('%s will expire on %s', $name, $date);
        else $msg = sprintf('%s will expire soon', $name);
      }
    } elseif ($t === 'donation') {
      $title = (string)($r['title'] ?? '');
      if ($title === 'Donation created') {
        if ($ref && isset($donMap[$ref])) {
          $date = (string)($donMap[$ref]['donation_date'] ?? '');
          $name = (string)($donNameMap[$ref] ?? 'Donation');
          $msg = $date ? sprintf('%s marked as donated (Donation Date: %s)', $name, $date)
                       : sprintf('%s marked as donated', $name);
        }
      } elseif ($title === 'Donation picked up') {
        if ($ref && isset($donMap[$ref])) {
          $date = (string)($donMap[$ref]['donation_date'] ?? '');
          $name = (string)($donNameMap[$ref] ?? 'Donation');
          $msg = $date ? sprintf('%s picked up (Donation Date: %s)', $name, $date)
                       : sprintf('%s picked up', $name);
        } else {
          $msg = 'Donation picked up';
        }
      }
    } elseif ($t === 'meal_plan') {
      if ($ref && isset($mealMap[$ref])) {
        $name = (string)($mealMap[$ref]['meal_name'] ?? 'Meal');
        $date = (string)($mealMap[$ref]['mealplan_date'] ?? '');
        $msg = $date
          ? sprintf('%s planned on %s', $name, $date)
          : sprintf('A new Meal Plan %s has been created', $name);
      }
    }
    $r['message'] = $msg;
  }
  unset($r);

  // Only show meal_plan notifications for plans scheduled for tomorrow
  if ($rows) {
    $tomorrow = (new DateTime('tomorrow'))->format('Y-m-d');
    $rows = array_values(array_filter($rows, function($r) use ($mealMap, $tomorrow) {
      if ((string)($r['type'] ?? '') !== 'meal_plan') return true;
      $ref = (int)($r['ref_id'] ?? 0);
      $date = isset($mealMap[$ref]) ? (string)$mealMap[$ref]['mealplan_date'] : '';
      return $date === $tomorrow;
    }));
  }

  // Deduplicate meal_plan notifications caused by two sources
  // Keep the newest per (type='meal_plan', ref_id)
  if ($rows) {
    $seen = [];
    $unique = [];
    foreach ($rows as $row) {
      if (($row['type'] ?? '') === 'meal_plan') {
        $k = 'meal_plan#' . (int)($row['ref_id'] ?? 0);
        if (isset($seen[$k])) continue;
        $seen[$k] = true;
      }
      $unique[] = $row;
    }
    $rows = $unique;
  }

  // Unread count based on filtered result set
  $unread = 0;
  foreach ($rows as $r) { if (!(int)($r['is_read'] ?? 0)) $unread++; }

  echo json_encode(['ok'=>true, 'unread_count'=>$unread, 'items'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
