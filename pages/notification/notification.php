<?php
// /SaveBite/pages/notification/notification.php
declare(strict_types=1);
require_once __DIR__ . '/../../config.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Notifications</title>
  <style>
    .topbar{}
    .title{}
    .top_right{display:flex;align-items:center;gap:12px;}
    .toolbar select{padding:6px 8px;border:1px solid #bbb;border-radius:8px;background:#eee;}
    .mini{border:1px solid #aaa;background:#ddd;border-radius:8px;padding:6px 10px;cursor:pointer;}
    .content{width:100%;max-width:820px;margin:0 auto;padding:16px;}
    .card{border:3px solid #222;border-radius:25px;padding:24px 24px 18px;margin-bottom:18px;background:#fff;cursor:pointer;}
    .card.unread{box-shadow:0 0 0 2px rgba(176,0,32,.1);}
    .card-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;}
    .subtitle{font-weight:700;}
    .row{display:grid;grid-template-columns:110px 1fr;gap:8px;padding:4px 0;border-top:1px dashed #eee;}
    .row:first-of-type{border-top:none;}
    .label{color:#333;}
    .value{color:#4d524f;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .meta{display:flex;align-items:center;gap:8px;font-size:12px;color:#666;margin-top:8px;}
    .badge{display:inline-block;padding:2px 6px;border-radius:999px;font-size:12px;border:1px solid #999;background:#f2f2f2;}
    .badge.unread{border-color:#b00020;color:#b00020;background:#ffecec;}
    .card:focus{outline:2px solid #222;}
    details summary{cursor:pointer;user-select:none;}
    .empty{color:#666;margin:24px 0;text-align:center;}
  </style>
</head>
<body>
  <header class="container-fluid p-4">
    <div class="d-flex mb-2 py-3 px-4 bg-light rounded shadow justify-content-between">
      <h1 class="title fw-bold">Notifications</h1>
      <div class="top_right">
        <div class="toolbar">
          <select id="filterSel">
            <option value="unread">Unread</option>
          <option value="all" selected>All</option>
        </select>
        <select id="typeSel" style="margin-left:8px;">
          <option value="all">All types</option>
          <option value="inventory">Inventory</option>
          <option value="donation">Donation</option>
          <option value="meal_plan">Meal Plan</option>
        </select>
      </div>
      <button id="markAll" class="mini" type="button">Mark all read</button>
    </div>
  </header>

  <main class="content" id="list"></main>
  <p id="emptyNotice" class="empty" style="display:none;">No new notifications</p>
</body>
<?php $___noti_js_v = @filemtime(__DIR__ . '/notification.js') ?: time(); ?>
<script src="/SaveBite/pages/notification/notification.js?v=<?= $___noti_js_v ?>"></script>
<script>
  if (window.initNotificationPage) window.initNotificationPage();
</script>
</html>
