<?php
// /SaveBite/pages/notifications/index.php
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

    .topbar{display:flex;justify-content:space-between;gap:12px;padding:20px 32px;}
    .title{font-size:28px;font-weight:700;}
    .top_right{display:flex;align-items:center;gap:12px;}
    .toolbar{display:flex;align-items:center;gap:8px;}
    .toolbar label{font-weight:600;}
    .toolbar select{padding:6px 8px;border:1px solid #bbb;border-radius:8px;background:#eee;}
    #bell{position:relative;display:flex;align-items:center;}
    #bell>span:first-child{font-size:20px;}
    #badge{
      position:absolute;right:-6px;top:-6px;min-width:18px;height:18px;line-height:18px;text-align:center;
      background:#b00020;color:#fff;border-radius:9px;font-size:12px;padding:0 4px;display:none;
    }
    .mini{border:1px solid #aaa;background:#ddd;border-radius:8px;padding:6px 10px;cursor:pointer;}

    .content{width:100%;max-width:820px;margin:0 auto;padding:16px;}
    .card{border:3px solid #222;border-radius:25px;padding:24px 24px 18px;margin-bottom:18px;background:#fff;cursor:pointer;}
    .card.unread{box-shadow:0 0 0 2px rgba(176,0,32,.1);}
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
    details summary{cursor:pointer;user-select:none;}
    .empty{color:#666;margin:24px 0;text-align:center;}
  </style>
</head>
<body>
  <header class="topbar">
    <h1 class="title">Notifications</h1>
    <div class="top_right">
      <div class="toolbar">
        <label for="filterSel">Filter:</label>
        <select id="filterSel">
          <option value="unread">Unread</option>
          <option value="all">All</option>
        </select>
      </div>
      <button id="markAll" class="mini" type="button">Mark all read</button>
    </div>
  </header>

  <main class="content" id="list"></main>
  <p id="emptyNotice" class="empty" style="display:none;">No new notifications</p>

<script>
const $list   = document.getElementById('list');
const $filter = document.getElementById('filterSel');
const $badge  = document.getElementById('badge');
const $empty  = document.getElementById('emptyNotice');
const $markAll= document.getElementById('markAll');

document.addEventListener('DOMContentLoaded', load);
$filter.addEventListener('change', load);
$markAll.addEventListener('click', markAllRead);

function smallTitleOf(type){
  if(type==='inventory') return 'Inventory alerts';
  if(type==='donation')  return 'Donation updates';
  if(type==='meal_plan')      return 'Meal Planning Reminders';
  return 'Notifications';
}
function actionLabelOf(type){
  if(type==='inventory') return 'Donate';
  if(type==='donation')  return 'View status';
  if(type==='meal_plan')      return 'Open Planner';
  return 'Open';
}
function fmtTs(s){
  const d = new Date(s.replace(' ','T'));
  const p = n => (n<10?'0':'')+n;
  return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`;
}

async function load(){
  $list.innerHTML = 'Loading...';
  $empty.style.display = 'none';
  const filter = $filter.value; // unread | all
  const res = await fetch(`./list.php?filter=${encodeURIComponent(filter)}`);
  const data = await res.json();

  if(!data.ok){
    $list.innerHTML = `<div class="empty">Failed to load: ${data.error || 'Unknown error'}</div>`;
    $badge.style.display = 'none';
    return;
  }

  // 角标
  if (data.unread_count > 0) {
    $badge.style.display = '';
    $badge.textContent = data.unread_count;
  } else {
    $badge.style.display = 'none';
  }

  // 渲染
  const items = Array.isArray(data.items) ? data.items : [];
  if(items.length === 0){
    $list.innerHTML = '';
    $empty.style.display = '';
    return;
  }
  $list.innerHTML = '';
  items.forEach(n => {
    const card = document.createElement('article');
    card.className = 'card' + (n.is_read ? '' : ' unread');
    card.setAttribute('tabindex', '0');

    // 头部 + 未读徽章
    const head = document.createElement('div');
    head.className = 'card-head';
    head.innerHTML = `
      <div class="subtitle">
        ${smallTitleOf(n.type)}
        ${n.is_read ? '' : '<span class="badge unread">Unread</span>'}
      </div>
      <div>
        <button class="mini" type="button" data-action="do">${actionLabelOf(n.type)}</button>
      </div>
    `;
    card.appendChild(head);

    // 极简信息（message + timestamp）
    const rowMsg = document.createElement('div');
    rowMsg.className = 'row';
    rowMsg.innerHTML = `<span class="label">Message:</span><span class="value">${escapeHtml(n.title || n.message || '')}</span>`;
    card.appendChild(rowMsg);

    const rowTs = document.createElement('div');
    rowTs.className = 'row';
    rowTs.innerHTML = `<span class="label">Timestamp:</span><span class="value">${fmtTs(n.created_at)}</span>`;
    card.appendChild(rowTs);

    // 详情折叠（来自 summary / details）
    if (n.summary || n.details){
      const details = document.createElement('details');
      details.style.marginTop = '8px';
      const sum = document.createElement('summary');
      sum.textContent = 'View details';
      details.appendChild(sum);

      const box = document.createElement('div');
      box.style.marginTop = '6px';
      if (n.summary) box.innerHTML += `<div>${escapeHtml(n.summary)}</div>`;
      if (n.details) box.innerHTML += `<div style="color:#4b5563;margin-top:4px;">${escapeHtml(n.details)}</div>`;
      details.appendChild(box);
      card.appendChild(details);
    }

    // meta + 按钮
    const meta = document.createElement('div');
    meta.className = 'meta';
    meta.innerHTML = `<span class="badge">#${n.id}</span><span>Type: ${n.type}</span>`;
    card.appendChild(meta);

    // 交互：整卡点击 -> track，右上按钮同样 track；未读时先标记已读
    card.addEventListener('click', e => {
      if (e.target.closest('button')) return;
      trackIt(n);
    });
    head.querySelector('[data-action="do"]').addEventListener('click', e => {
      e.stopPropagation();
      trackIt(n);
    });

    $list.appendChild(card);
  });
}

function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

async function trackIt(n){
  // 标记已读（如果未读）
  if (!n.is_read){
    await fetch('./mark_read.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`id=${encodeURIComponent(n.id)}`});
  }
  // 跳转（占位路由——按你们实际页面改）
  let url = '#';
  if (n.type === 'inventory') url = '/SaveBite/pages/inventory/view.php?id=' + (n.ref_id || '');
  if (n.type === 'donation')  url = '/SaveBite/pages/donationList/view.php?id=' + (n.ref_id || '');
  if (n.type === 'meal_plan')      url = '/SaveBite/pages/mealplan/view.php?id=' + (n.ref_id || '');
  window.location.href = url;
}

async function markAllRead(){
  await fetch('./mark_all.php', {method:'POST'});
  load();
}
</script>
</body>
</html>
