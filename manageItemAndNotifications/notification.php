<?php
/* ====== 示例通知数据（实际可来自数据库）====== 
 * 字段：
 * - id: 唯一编号
 * - type: inventory | donation | meal
 * - message: 通知正文
 * - ts: Y-m-d H:i:s
 * - unread: 是否未读（true/false）
 * - link: 点击后跳转链接
 */
$notifications = [
  [
    'id' => 501,
    'type' => 'inventory',
    'message' => 'Apples will expire in 2 days. Consider donating them.',
    'ts' => '2025-10-10 09:12:00',
    'unread' => true,
    'link' => 'manageFoodItem.php#M-101'
  ],
  [
    'id' => 502,
    'type' => 'donation',
    'message' => 'Your donation “Canned Beans” is now published successfully.',
    'ts' => '2025-10-09 16:25:00',
    'unread' => false,
    'link' => 'donationList.php'
  ],
  [
    'id' => 503,
    'type' => 'donation',
    'message' => '“Milk 1L” has been claimed. Please arrange pickup.',
    'ts' => '2025-10-11 10:05:00',
    'unread' => true,
    'link' => 'donationList.php'
  ],
  [
    'id' => 504,
    'type' => 'meal',
    'message' => 'Meal plan reminder for Wednesday. Suggested menu uses “Rice 5kg”.',
    'ts' => '2025-10-08 08:00:00',
    'unread' => false,
    'link' => 'mealPlanner.php'
  ],
  [
    'id' => 505,
    'type' => 'donation',
    'message' => 'Pickup reminder for “Apples” tomorrow 10:00 at Warehouse A.',
    'ts' => '2025-10-12 08:30:00',
    'unread' => true,
    'link' => 'donationList.php'
  ],
];

/* —— 按时间倒序排序 —— */
usort($notifications, function($a, $b){
  return strtotime($b['ts']) <=> strtotime($a['ts']);
});

function smallTitleOf($type){
  if ($type === 'inventory') return 'Inventory alerts';
  if ($type === 'donation')  return 'Donation updates';
  if ($type === 'meal')      return 'Meal Planning Reminders';
  return 'Notifications';
}
function actionLabelOf($type){
  if ($type === 'inventory') return 'Donate';
  if ($type === 'donation')  return 'View status';
  if ($type === 'meal')      return 'Open Planner';
  return 'Open';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Notifications</title>
  <style>
    /* top bar */
    .topbar{display:flex;justify-content:space-between;gap:12px;padding:20px 32px;}
    .title{font-size:28px;font-weight:700;}
    .top_right{display:flex;align-items:center;gap:12px;}
    .toolbar{display:flex;align-items:center;gap:8px;}
    .toolbar label{font-weight:600;}
    .toolbar select{padding:6px 8px;border:1px solid #bbb;border-radius:8px;background:#eee;}
    /* bell badge */
    #bell{position:relative;display:flex;align-items:center;}
    #bell>span:first-child{font-size:20px;}
    #badge{
      position:absolute;right:-6px;top:-6px;min-width:18px;height:18px;line-height:18px;text-align:center;
      background:#b00020;color:#fff;border-radius:9px;font-size:12px;padding:0 4px;display:none;
    }
    .mini{border:1px solid #aaa;background:#ddd;border-radius:8px;padding:6px 10px;cursor:pointer;}

    /* content & card */
    .content{width:100%;max-width:820px;margin:0 auto;padding:16px;}
    .card{border:3px solid #222;border-radius:25px;padding:24px 24px 18px;margin-bottom:18px;background:#fff;cursor:pointer;}
    .card-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;}
    .subtitle{font-weight:700;}
    .row{display:grid;grid-template-columns:110px 1fr;gap:8px;padding:4px 0;border-top:1px dashed #eee;}
    .row:first-of-type{border-top:none;}
    .label{color:#333;}
    .value{color:#4d524f;}
    .meta{display:flex;align-items:center;gap:8px;font-size:12px;color:#666;margin-top:8px;}
    .badge{display:inline-block;padding:2px 6px;border-radius:999px;font-size:12px;border:1px solid #999;background:#f2f2f2;}
    .badge.unread{border-color:#b00020;color:#b00020;background:#ffecec;}
    .card:focus{outline:2px solid #222;}
    /* 让按钮不触发卡片整体点击 */
    .card-head .mini{pointer-events:auto;}
  </style>
</head>
<body>
  <header class="topbar">
    <h1 class="title">Notifications</h1>
    <div class="top_right">
      <div class="toolbar">
        <label for="filterSel">Filter:</label>
        <select id="filterSel">
          <option value="recent">Recent</option>
          <option value="unread">Unread</option>
          <option value="all">All</option>
        </select>
      </div>

      <!-- 铃铛 + 未读徽章 -->
      <div id="bell" title="Notifications">
        <span>🔔</span>
        <span id="badge">0</span>
      </div>

      <!-- 一键标记已读 -->
      <button id="markAll" class="mini" type="button">Mark all read</button>
    </div>
  </header>

  <main class="content" id="list">
    <?php foreach($notifications as $n): 
      $subtitle = smallTitleOf($n['type']);
      $action   = actionLabelOf($n['type']);
    ?>
      <article class="card"
               tabindex="0"
               data-id="<?php echo htmlspecialchars($n['id']); ?>"
               data-type="<?php echo htmlspecialchars($n['type']); ?>"
               data-ts="<?php echo htmlspecialchars($n['ts']); ?>"
               data-unread="<?php echo $n['unread'] ? '1':'0'; ?>"
               data-link="<?php echo htmlspecialchars($n['link']); ?>">
        <div class="card-head">
          <div class="subtitle">
            <?php echo htmlspecialchars($subtitle); ?>
            <?php if ($n['unread']) { ?><span class="badge unread">Unread</span><?php } ?>
          </div>
          <div>
            <button class="mini" type="button" data-action="do">
              <?php echo htmlspecialchars($action); ?>
            </button>
          </div>
        </div>

        <div class="row">
          <span class="label">Message:</span>
          <span class="value"><?php echo htmlspecialchars($n['message']); ?></span>
        </div>
        <div class="row">
          <span class="label">Timestamp:</span>
          <span class="value" data-field="ts"><?php echo htmlspecialchars($n['ts']); ?></span>
        </div>

        <div class="meta">
          <span class="badge">#<?php echo htmlspecialchars($n['id']); ?></span>
          <span>Type: <?php echo htmlspecialchars($n['type']); ?></span>
        </div>
      </article>
    <?php endforeach; ?>
  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    const list = document.getElementById('list');
    const filterSel = document.getElementById('filterSel');
    const badge = document.getElementById('badge');
    const markAllBtn = document.getElementById('markAll');
    const RECENT_DAYS = 3;

    // 计算未读条数并刷新徽章
    function updateBadge(){
      const unreadCount = [...document.querySelectorAll('.card')]
        .filter(c => c.dataset.unread === '1').length;
      if (badge) {
        badge.textContent = unreadCount;
        badge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
      }
    }
    updateBadge();

    // 点击卡片或右上按钮：跳转，并将该条设为已读
    list.addEventListener('click', function(e){
      const btn = e.target.closest('button');
      const card = e.target.closest('.card');
      if(!card) return;

      if(btn && btn.dataset.action === 'do'){
        e.stopPropagation();
        goto(card);
        return;
      }
      goto(card);
    });

    function goto(card){
      // 标记这条为已读
      card.dataset.unread = '0';
      const u = card.querySelector('.badge.unread');
      if (u) u.remove();

      // 更新铃铛徽章数量
      updateBadge();

      // 跳转
      const link = card.dataset.link || '#';
      window.location.href = link;
    }

    // 一键标记全部为已读（不跳转）
    if (markAllBtn) {
      markAllBtn.addEventListener('click', () => {
        document.querySelectorAll('.card').forEach(card=>{
          if (card.dataset.unread === '1') {
            card.dataset.unread = '0';
            const u = card.querySelector('.badge.unread');
            if (u) u.remove();
          }
        });
        updateBadge();
      });
    }

    // 过滤：recent / unread / all
    filterSel.addEventListener('change', applyFilter);
    applyFilter();

    function applyFilter(){
      const mode = filterSel.value;
      const now = Date.now();
      const recentCut = now - RECENT_DAYS*24*60*60*1000;
      let shown = 0;

      document.querySelectorAll('.card').forEach(card=>{
        const unread = card.dataset.unread === '1';
        const ts = new Date(card.dataset.ts).getTime();
        let show = true;

        if(mode === 'recent'){ show = !isNaN(ts) && ts >= recentCut; }
        else if(mode === 'unread'){ show = unread; }
        // all: show = true;

        card.style.display = show ? '' : 'none';
        if (show) shown++;
      });

      // （可选）没有结果时友好提示
      let empty = document.getElementById('emptyNotice');
      if(!empty){
        empty = document.createElement('p');
        empty.id = 'emptyNotice';
        empty.style.cssText = 'color:#666;margin:8px 0;display:none;';
        empty.textContent = '暂无符合条件的通知';
        document.querySelector('main.content').appendChild(empty);
      }
      empty.style.display = shown === 0 ? '' : 'none';
    }
  });
  </script>
</body>
</html>
