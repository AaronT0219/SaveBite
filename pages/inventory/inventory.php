<?php
// /SaveBite/pages/inventory/inventory.php
// 仅输出页面与卡片列表，数据来自 DB；编辑/保存/删除由 /assets/js/inventory.js 处理
require_once __DIR__ . '/../../config.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// 兼容两种会话键名：拿不到就 0（不默认 7）
$uid = (int)($_SESSION['user_id'] ?? $_SESSION['id'] ?? 0);

// 拉取当前用户的库存；不再因 donation 而隐藏，
// 但同时查出是否已存在 pending 的 donation，用于禁用按钮
$rows = [];
try {
  $st = $pdo->prepare("
    SELECT 
      f.foodItem_id, f.food_name, f.quantity, f.category, f.expiry_date, f.status, f.storage_location, f.description,
      EXISTS(
        SELECT 1
        FROM donation_fooditem df
        JOIN donation d ON d.donation_id = df.donation_id
        WHERE df.fooditem_id = f.foodItem_id
          AND d.donor_user_id = f.user_id
          AND d.status = 'pending'
      ) AS has_donation
    FROM fooditem f
    WHERE f.user_id = :uid
    ORDER BY f.foodItem_id DESC
  ");
  $st->execute([':uid' => $uid]);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $rows = [];
}

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Food Inventory</title>
  <style>
    .topbar{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:20px 32px;}
    .title{font-size:28px;font-weight:700;margin:0;}
    .actions{display:flex;align-items:center;gap:12px;}
    .btn,.plus{padding:8px 12px;border:1px solid #bbb;background:#eee;border-radius:22px;cursor:pointer;text-decoration:none;color:#222;}
    .btn.mini{padding:6px 10px;border-radius:16px;}
    .content{width:100%;max-width:920px;margin:0 auto;padding:16px;}
    .card{border:3px solid #222;border-radius:25px;padding:28px;margin-bottom:18px;background:#fff;}
    .card-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;}
    .card-title{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .mini-id{color:#666;font-size:90%;}
    .row{display:grid;grid-template-columns:180px 1fr;gap:8px;padding:8px 0;border-top:1px dashed #eee;}
    .row:first-of-type{border-top:none;}
    .label{font-weight:600;}
    .value{color:#333;}
    .row input,.row select,.row textarea{width:100%;padding:6px 8px;border:1px solid #bbb;border-radius:6px;background:#f3f3f3;box-sizing:border-box;}
    .controls select{padding:6px 10px;border:1px solid #bbb;border-radius:22px;background:#fff;}
  </style>
</head>
<body>
  <header class="topbar">
    <h1 class="title">Food Inventory</h1>
    <div class="actions">
      <!-- 过滤下拉 -->
      <div class="controls">
        <select id="filterSel">
          <option value="all" selected>All</option>
          <option value="recent">Recent</option>
          <option value="near">Near expiry (7d)</option>
        </select>
      </div>

      <!-- 跳到 Donation List -->
      <a class="btn" href="/SaveBite/templates/base.php?page=donationList">View Donation List</a>

      <!-- 新建卡片（前端插入一张可编辑卡片） -->
      <a class="plus" href="#" title="Add new">＋</a>
    </div>
  </header>

  <main class="content" id="list">
    <?php if (!$rows): ?>
      <p id="empty-hint" style="color:#666">No items yet. Click “＋” to add one.</p>
    <?php else: ?>
      <?php foreach ($rows as $r):
        $id   = (int)$r['foodItem_id'];
        $exp  = (string)($r['expiry_date'] ?? '');
        $name = (string)$r['food_name'];
        $qty  = (string)$r['quantity'];
        $cat  = (string)$r['category'];
        $st   = (string)($r['status'] ?? '');
        $loc  = (string)$r['storage_location'];
        $desc = (string)($r['description'] ?? '');
        $has  = !empty($r['has_donation']);
      ?>
      <!-- data-donated 由服务器给出，刷新后也能保持按钮禁用 -->
      <article class="card" id="item-<?= $id ?>" data-id="<?= $id ?>" data-expiry="<?= e($exp) ?>" data-donated="<?= $has ? '1' : '0' ?>">
        <div class="card-head">
          <div class="card-title">
            <span class="label">Food Name:</span>
            <span class="value" data-field="food_name"><?= e($name) ?></span>
            <span class="mini-id">(# <?= $id ?>)</span>
          </div>
          <div>
            <button class="btn mini" type="button" data-action="edit">Edit</button>
            <button
              class="btn mini"
              type="button"
              data-action="donate"
              <?= $has ? 'disabled style="opacity:.65;cursor:not-allowed"' : '' ?>
            ><?= $has ? 'Added to Donation List' : 'Mark as donated' ?></button>
            <button class="btn mini" type="button" data-action="delete">Delete</button>
          </div>
        </div>

        <div class="row">
          <span class="label">Quantity:</span>
          <span class="value" data-field="quantity"><?= e($qty) ?></span>
        </div>
        <div class="row">
          <span class="label">Category:</span>
          <span class="value" data-field="category"><?= e($cat) ?></span>
        </div>
        <div class="row">
          <span class="label">Expiry date:</span>
          <span class="value" data-field="expiry_date"><?= e($exp) ?></span>
        </div>
        <div class="row">
          <span class="label">Status:</span>
          <span class="value" data-field="status"><?= e($st) ?></span>
        </div>
        <div class="row">
          <span class="label">Storage location:</span>
          <span class="value" data-field="storage_location"><?= e($loc) ?></span>
        </div>
        <div class="row">
          <span class="label">Description:</span>
          <span class="value" data-field="description"><?= e($desc) ?></span>
        </div>
      </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <!-- 若你的 JS 实际在 /pages/inventory/ 请把路径改成对应位置 -->
  <script src="/SaveBite/assets/js/inventory.js"></script>
  <script>
    if (window.initInventoryPage) window.initInventoryPage();
  </script>
</body>
</html>
