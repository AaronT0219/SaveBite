<?php
// /SaveBite/pages/inventory/inventory.php
require_once __DIR__ . '/../../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// 登录体系存在时：只显示当前用户物品
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 7;

// 隐藏已 donation 的记录（DB 中 status='donation'）
$sql = "
  SELECT foodItem_id, food_name, quantity, category, expiry_date, status, storage_location, description
  FROM fooditem
  WHERE COALESCE(TRIM(LOWER(status)),'') <> 'donation'
    AND user_id = :uid
  ORDER BY foodItem_id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([':uid' => $uid]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Food Inventory</title>
  <style>
    #main-content{ padding-left:32px; padding-right:32px; }
    .inventory-root > header, .inventory-root > main{ max-width: 900px; margin: 0 auto; }
    .topbar{ display:flex; justify-content:space-between; align-items:center; gap:12px; padding:16px 0 8px; }
    .title{ font-size:28px; font-weight:700; margin:0; }
    .top_botton{ display:flex; align-items:center; gap:12px; }
    .go_donation_btn{
      padding:8px 12px; font-size:14px; border:1px solid #bbb; background:#ddd; border-radius:20px;
      text-decoration:none; color:#222; cursor:pointer;
    }
    .plus{
      width:40px; height:40px; display:flex; align-items:center; justify-content:center;
      border:3px solid #222; border-radius:50%; font-size:24px; background:#ddd; color:#222; text-decoration:none;
    }
    .content{ width:100%; margin:0; padding:0 0 24px; box-sizing:border-box; }
    .toolbar{ display:flex; align-items:center; gap:8px; }
    .toolbar label{ font-weight:600; }
    .toolbar select{ padding:6px 8px; border:1px solid #bbb; border-radius:8px; background:#eee; }
    .card{ border:3px solid #222; border-radius:20px; padding:18px; margin:16px 0; background:#fff; }
    .card-head{ display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; flex-wrap:wrap; }
    .card-title{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .mini-id{ color:#666; font-size:90%; }
    .mini{ margin-left:4px; }
    .row{ display:grid; grid-template-columns:180px 1fr; align-items:center; gap:8px; padding:8px 0; border-top:1px dashed #eee; }
    .row:first-of-type{ border-top:none; }
    .row .label{ white-space:nowrap; }
    .row .value{ color:#4d524f; word-break:break-word; }
    .hidden{ display:none; }
    @media (max-width:560px){ #main-content{ padding-left:16px; padding-right:16px; } .row{ grid-template-columns:140px 1fr; } }
    .edit-input{ width:100%; padding:6px 8px; border:1px solid #bbb; border-radius:6px; background:#eee; box-sizing:border-box; }
    .title-name-input{ width:260px; }
  </style>
</head>
<body>
<div class="inventory-root">

  <header class="topbar">
    <h1 class="title">Food Inventory</h1>

    <div class="toolbar" id="toolbar">
      <label for="filterSel">Filter:</label>
      <select id="filterSel">
        <option value="all">All</option>
        <option value="recent">Recent</option>
        <option value="near">Near expiry (7 days)</option>
      </select>
    </div>

    <div class="top_botton">
      <a class="go_donation_btn" href="/SaveBite/templates/base.php?page=donationList">View Donation List</a>
      <a class="plus" href="#" aria-label="Add new food item" title="Add new item">+</a>
    </div>
  </header>

  <main class="content" id="list">
    <?php if (!empty($items)) : ?>
      <?php foreach ($items as $it): 
        $id = (int)$it['foodItem_id'];
      ?>
        <article id="item-<?= (int)$it['foodItem_id'] ?>" class="card"
                 data-id="<?= (int)$it['foodItem_id'] ?>"
                 data-expiry="<?= htmlspecialchars($it['expiry_date'] ?? '') ?>">
          <div class="card-head">
            <div class="card-title">
              <span class="label">Food Name:</span>
              <span class="value" data-field="food_name"><?= htmlspecialchars($it['food_name'] ?? 'Unknown') ?></span>
              <span class="mini-id">(# <?= $id ?>)</span>
            </div>
            <div>
              <button class="mini" type="button" data-action="edit">Edit</button>
              <button class="mini" type="button" data-action="donate">Mark as donated</button>
              <button class="mini" type="button" data-action="delete">Delete</button>
            </div>
          </div>

          <div class="row"><span class="label">Quantity:</span>
            <span class="value" data-field="quantity"><?= (int)$it['quantity'] ?></span>
          </div>
          <div class="row"><span class="label">Category:</span>
            <span class="value" data-field="category"><?= htmlspecialchars($it['category'] ?? '') ?></span>
          </div>
          <div class="row"><span class="label">Expiry date:</span>
            <span class="value" data-field="expiry_date"><?= htmlspecialchars($it['expiry_date'] ?? '') ?></span>
          </div>
          <div class="row"><span class="label">Status:</span>
            <span class="value status" data-field="status"><?= htmlspecialchars($it['status'] ?? '') ?></span>
          </div>
          <div class="row"><span class="label">Storage location:</span>
            <span class="value" data-field="storage_location"><?= htmlspecialchars($it['storage_location'] ?? '') ?></span>
          </div>
          <div class="row"><span class="label">Description:</span>
            <span class="value" data-field="description"><?= htmlspecialchars($it['description'] ?? '') ?></span>
          </div>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="color:#666;margin:8px 0;">No items yet.</p>
    <?php endif; ?>
  </main>

</div>

<script src="/SaveBite/assets/js/inventory.js"></script>
</body>
</html>
