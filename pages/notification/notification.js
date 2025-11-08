// /SaveBite/pages/notification/notification.js

// —�?配置 —�?
// 建议用绝对路径，避免 SPA 相对路径问题
const API_BASE = '/SaveBite/pages/notification';

function smallTitleOf(type){
  if(type==='inventory') return 'Inventory alerts';
  if(type==='donation')  return 'Donation updates';
  if(type==='meal_plan') return 'Meal Planning Reminders';
  return 'Notifications';
}
// Button labels (top-right). We now show explicit View + Mark-as-read buttons.
function actionLabelOf(type){
  if(type==='inventory') return 'View details';
  if(type==='donation')  return 'View details';
  if(type==='meal_plan') return 'View details';
  return 'View details';
}
function fmtTs(s){
  const d = new Date(String(s).replace(' ','T'));
  const p = n => (n<10?'0':'')+n;
  return `${d.getFullYear()}-${p(d.getMonth()+1)}-${p(d.getDate())} ${p(d.getHours())}:${p(d.getMinutes())}`;
}
function escapeHtml(s){
  return String(s).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

// 这些变量�?init 函数里赋值，避免在页面尚未注入时为空
let $list, $filter, $typeSel, $empty, $markAll;

// Coerce backend is_read (0/1 strings) to boolean
function isRead(n){
  const v = (n && (n.is_read ?? n.status));
  if (v === 1 || v === true) return true;
  const s = String(v).toLowerCase();
  return s === '1' || s === 'true' || s === 'seen';
}

async function load(){
  if (!$list || !$filter || !$empty) return;
  $list.innerHTML = 'Loading...';
  $empty.style.display = 'none';

  const filter = $filter.value || 'all';
  const type   = ($typeSel && $typeSel.value && $typeSel.value !== 'all') ? $typeSel.value : '';
  let data = {};
  try {
    const q = new URLSearchParams({ filter });
    if (type) q.set('type', type);
    const res = await fetch(`${API_BASE}/list.php?${q.toString()}`);
    data = await res.json();
  } catch (e) {
    data = { ok:false, error: 'Network or JSON error' };
  }

  if (!data.ok) {
    $list.innerHTML = `<div class="empty">Failed to load: ${escapeHtml(data.error || 'Unknown error')}</div>`;
    return;
  }

  if ($markAll && typeof data.unread_count === 'number') {
    const none = data.unread_count === 0;
    $markAll.disabled = none;
    $markAll.textContent = none ? 'All read' : 'Mark all read';
  }

  const items = Array.isArray(data.items) ? data.items : [];
  if (items.length === 0){
    $list.innerHTML = '';
    $empty.style.display = '';
    return;
  }

  $list.innerHTML = '';
  items.forEach(n => {
    const read = isRead(n);
    const card = document.createElement('article');
    card.className = 'card' + (read ? '' : ' unread');
    card.setAttribute('tabindex', '0');

    const head = document.createElement('div');
    head.className = 'card-head';
    head.innerHTML = `
      <div class="subtitle">
        ${smallTitleOf(n.type)}
        ${read ? '' : '<span class="badge unread">Unread</span>'}
      </div>
      <div>
        <button class="mini" type="button" data-action="view">${actionLabelOf(n.type)}</button>
        ${read ? '' : '<button class="mini" type="button" data-action="mark">Mark as read</button>'}
      </div>
    `;
    card.appendChild(head);

    const rowMsg = document.createElement('div');
    rowMsg.className = 'row';
    rowMsg.innerHTML = `<span class="label">Message</span><span class="value">${escapeHtml(n.message || n.title || '')}</span>`;
    card.appendChild(rowMsg);

    const rowTs = document.createElement('div');
    rowTs.className = 'row';
    rowTs.innerHTML = `<span class="label">Timestamp</span><span class="value">${fmtTs(n.created_at)}</span>`;
    card.appendChild(rowTs);

    // 不渲染详情块，保�?Timestamp 下方空白，仅保留 meta（ID/Type�?
    const meta = document.createElement('div');
    meta.className = 'meta';
    meta.innerHTML = `<span class="badge">#${n.id}</span><span>Type: ${n.type}</span>`;
    card.appendChild(meta);

    // Only buttons drive actions now
    const btnView = head.querySelector('[data-action="view"]');
    if (btnView) btnView.addEventListener('click', e => { e.stopPropagation(); openDetails(n); });
    const btnMark = head.querySelector('[data-action="mark"]');
    if (btnMark) btnMark.addEventListener('click', e => { e.stopPropagation(); markOneRead(n, card); });
    // no delete action

    $list.appendChild(card);
  });
}

async function openDetails(n){
  let url = '#';
  if (n.type === 'inventory') url = '/SaveBite/?page=inventory#food-' + (n.ref_id || '');
  if (n.type === 'donation')  url = '/SaveBite/?page=donationList#donation-' + (n.ref_id || '');
  if (n.type === 'meal_plan') url = '/SaveBite/?page=mealplan#plan-' + (n.ref_id || '');
  window.location.href = url;
}

async function markOneRead(n, card){
  try {
    await fetch(`${API_BASE}/mark_read.php`, {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:`id=${encodeURIComponent(n.id)}`
    });
  } catch(_){}
  // Optimistic UI: remove card or downgrade style
  if ($filter && $filter.value === 'unread') {
    card.remove();
    // If list becomes empty, show empty state
    if ($list && !$list.children.length) {
      $empty.style.display = '';
    }
  } else {
    card.classList.remove('unread');
    const badge = card.querySelector('.badge.unread');
    if (badge) badge.remove();
    const btnMark = card.querySelector('[data-action="mark"]');
    if (btnMark) btnMark.remove();
  }
}

// delete removed per request

async function markAllRead(){
  try { await fetch(`${API_BASE}/mark_all.php`, {method:'POST'}); } catch(_){}
  load();
}

// —�?�?PageLoader 调用的入�?—�?
// 注意：不要用 DOMContentLoaded，这个页面是�?SPA 注入�?
window.initNotificationPage = function initNotificationPage(){
  if (window.__notificationInited) return;
  window.__notificationInited = true;
  $list    = document.getElementById('list');
  $filter  = document.getElementById('filterSel');
  $typeSel = document.getElementById('typeSel');
  $empty   = document.getElementById('emptyNotice');
  $markAll = document.getElementById('markAll');

  if ($filter)  $filter.onchange  = load;
  if ($typeSel) $typeSel.onchange = load;
  if ($markAll) $markAll.onclick  = markAllRead;

  // 先补齐通知，再加载
  (async () => {
    try { await fetch(`${API_BASE}/generate.php`, {method:'POST'}); } catch(_){}
    load();
  })();
};


