/* /SaveBite/assets/js/inventory.js
 * 功能：编辑/保存（落库）、标记为捐赠（落库）、新建并保存（落库）、删除（落库）
 */
(function () {
  'use strict';

  // 后端接口
  const API_CREATE_FOOD   = '/SaveBite/pages/inventory/create_fooditem.php';
  const API_UPDATE_FOOD   = '/SaveBite/pages/inventory/update_fooditem.php';
  const API_DELETE_FOOD   = '/SaveBite/pages/inventory/delete_fooditem.php';
  const API_CREATE_DON    = '/SaveBite/pages/donationList/create_donation.php';

  const FIELD_ALIASES = {
    food_name:          ['food_name','name'],
    quantity:           ['quantity'],
    category:           ['category'],
    expiry_date:        ['expiry_date','expiry','expire','exp'],
    status:             ['status'],
    storage_location:   ['storage_location','storage','location','place'],
    description:        ['description','desc','detail']
  };
  const FIELD_KEYS = Object.keys(FIELD_ALIASES);

  const toUILabel = (v='') => (String(v).toLowerCase()==='donation' ? 'donated' : (v||''));

  function pickCell(card, mainKey) {
    const names = FIELD_ALIASES[mainKey] || [mainKey];
    for (const n of names) {
      const el = card.querySelector(`.value[data-field="${n}"]`);
      if (el) return el;
    }
    return null;
  }
  const setHTML = (el, html) => { if (!el) return false; el.innerHTML = html; return true; };
  const escapeAttr = (s = '') => String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;');
  const escapeHTML = (s = '') => String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;');

  function getCellValueSafe(el){
    if(!el) return '';
    const c=el.querySelector('input,select,textarea');
    return c ? (c.value??'').toString().trim() : (el.textContent||'').trim();
  }
  function collectDisplay(card){ const d={}; for(const k of FIELD_KEYS) d[k]=getCellValueSafe(pickCell(card,k)); return d; }
  function collectInputs(card){ const d={}; card.querySelectorAll('.edit-input').forEach(inp=>{ const n=inp.dataset.name; if(n) d[n]=inp.value; }); return d; }
  function renderDisplay(card, v){
    for(const k of FIELD_KEYS){
      const el=pickCell(card,k);
      if(!el) continue;
      el.textContent = (k==='status') ? toUILabel(v[k]) : (v[k]??'');
    }
  }

  function enterEdit(card){
    if(!card || card.dataset.editing==='1') return;
    card.__orig = collectDisplay(card);

    // 标题里的 Food Name
    const nameCell = pickCell(card, 'food_name');
    setHTML(nameCell, `<input class="edit-input title-name-input" data-name="food_name" type="text" value="${escapeAttr(card.__orig.food_name || '')}">`);

    const q = pickCell(card, 'quantity');
    setHTML(q, `<input class="edit-input" data-name="quantity" type="number" min="0" step="1" value="${card.__orig.quantity || ''}">`);

    const cat = pickCell(card, 'category');
    setHTML(cat, `<input class="edit-input" data-name="category" type="text" value="${escapeAttr(card.__orig.category)}">`);

    // 日期范围：今天 ~ +100年
    const today = new Date();
    const y = today.getFullYear(), m = String(today.getMonth()+1).padStart(2,'0'), d = String(today.getDate()).padStart(2,'0');
    const min = `${y}-${m}-${d}`, max = `${y+100}-${m}-${d}`;

    const exp = pickCell(card, 'expiry_date');
    setHTML(exp, `<input class="edit-input" data-name="expiry_date" type="date" min="${min}" max="${max}" value="${card.__orig.expiry_date || ''}">`);

    // 注意：这里只允许 '', used, reserved（donation 只能通过“Mark as donated”产生）
    const st = pickCell(card, 'status');
    setHTML(st, `
      <select class="edit-input" data-name="status">
        <option value="used">used</option>
        <option value="reserved">reserved</option>
        <option value="expired">expired</option>
      </select>
    `);
    const stSel = st?.querySelector('select'); 
    if (stSel) {
      const v = String(card.__orig.status || '');
      stSel.value = ['used','reserved','expired'].includes(v) ? v : 'used';
    }


    const loc = pickCell(card, 'storage_location');
    setHTML(loc, `<input class="edit-input" data-name="storage_location" type="text" value="${escapeAttr(card.__orig.storage_location)}">`);

    const desc = pickCell(card, 'description');
    setHTML(desc, `<textarea class="edit-input" data-name="description" rows="2">${escapeHTML(card.__orig.description)}</textarea>`);

    const btnWrap = card.querySelector('.card-head div:last-child');
    if (btnWrap && !card.__btnHTML) card.__btnHTML = btnWrap.innerHTML;
    if (btnWrap) btnWrap.innerHTML = `
      <button class="mini" type="button" data-action="edit">Save</button>
      <button class="mini" type="button" data-action="cancel">Cancel</button>
    `;

    card.dataset.editing='1';
  }

  function validateRequired(values){
    const errors=[];
    if(!values.food_name?.trim()) errors.push('Food Name cannot be empty.');
    if(!/^\d+$/.test((values.quantity||'').trim())) errors.push('Quantity is required and must be an integer ≥ 0.');
    if(!values.category?.trim()) errors.push('Category cannot be empty.');
    if(!values.expiry_date?.trim()) errors.push('Expiry date cannot be empty.');
    if(!['used','reserved','expired'].includes(String(values.status||''))) {
      errors.push('Status must be one of: used / reserved / expired.');}
    if(!values.storage_location?.trim()) errors.push('Storage location cannot be empty.');
    if(errors.length){ alert('Please fix:\n- ' + errors.join('\n- ')); return false; }
    return true;
  }

  // 保存（区分新建/更新，都会落库）
  async function saveEdit(card){
    if(!card || card.dataset.editing!=='1') return;
    const values = collectInputs(card);
    if(!validateRequired(values)) return;

    const isNew = !card.dataset.id || card.dataset.id === 'new';
    try{
      if (isNew){
        // —— 新建
        const res = await fetch(API_CREATE_FOOD, {
          method: 'POST',
          headers: { 'Content-Type':'application/json' },
          body: JSON.stringify({
            food_name: values.food_name,
            quantity:  Number(values.quantity),
            category:  values.category,
            expiry_date: values.expiry_date,
            status:    values.status,               // '' | used | reserved
            storage_location: values.storage_location,
            description: values.description || ''
          })
        });
        const data = await res.json();
        if(!data.success) throw new Error(data.error || 'Create failed');

        // 用返回的 id 固定卡片
        card.dataset.id = String(data.fooditem_id);
        const idBadge = card.querySelector('.mini-id');
        if (idBadge) idBadge.textContent = `(# ${data.fooditem_id})`;
        renderDisplay(card, values);
        cleanupEdit(card);
      }else{
        // —— 更新
        const id = Number(card.dataset.id);
        const res = await fetch(API_UPDATE_FOOD, {
          method: 'POST',
          headers: { 'Content-Type':'application/json' },
          body: JSON.stringify({
            fooditem_id: id,
            food_name: values.food_name,
            quantity:  Number(values.quantity),
            category:  values.category,
            expiry_date: values.expiry_date,
            status:    values.status,               // '' | used | reserved
            storage_location: values.storage_location,
            description: values.description || ''
          })
        });
        const data = await res.json();
        if(!data.success) throw new Error(data.error || 'Update failed');

        renderDisplay(card, values);
        cleanupEdit(card);
      }
    }catch(e){
      console.error(e);
      alert(e.message || 'Network error');
    }
  }

  function cancelEdit(card){
    const orig = card.__orig || collectDisplay(card);
    renderDisplay(card, orig);
    cleanupEdit(card);
  }

  function cleanupEdit(card){
    const btnWrap = card.querySelector('.card-head div:last-child');
    if (btnWrap) btnWrap.innerHTML = card.__btnHTML || `
      <button class="mini" type="button" data-action="edit">Edit</button>
      <button class="mini" type="button" data-action="donate">Mark as donated</button>
      <button class="mini" type="button" data-action="delete">Delete</button>
    `;
    card.__btnHTML = undefined;
    card.dataset.editing='0';
    card.__orig = undefined;
  }

  // Mark as donated：调创建 donation 接口 + 从页面移除
  async function markAsDonated(card){
    const id = Number(card?.dataset.id); if(!id) return;
    const getText = (k) => getCellValueSafe(pickCell(card, k));
    try{
      const res = await fetch(API_CREATE_DON, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({
          fooditem_id: id,
          quantity: Number(getText('quantity') || 1),
          donation_status: 'pending'
        })
      });
      const data = await res.json();
      if (!data.success) { alert(data.error || data.message || 'Update failed'); return; }
      card.remove();
      alert('Moved to Donation List.');
    }catch(e){
      console.error(e); alert('Network error');
    }
  }

  // + 新建（先插入一张可编辑卡片，保存时落库）
  function addNewCard(){
    const article=document.createElement('article');
    article.className='card'; article.dataset.id='new';

    const t = new Date(); const yy=t.getFullYear(), mm=String(t.getMonth()+1).padStart(2,'0'), dd=String(t.getDate()).padStart(2,'0');
    const min=`${yy}-${mm}-${dd}`, max=`${yy+100}-${mm}-${dd}`;

    article.innerHTML = `
      <div class="card-head">
        <div class="card-title">
          <span class="label">Food Name:</span>
          <span class="value" data-field="food_name">
            <input class="edit-input title-name-input" data-name="food_name" type="text" value="">
          </span>
          <span class="mini-id">(# new)</span>
        </div>
        <div>
          <button class="mini" type="button" data-action="edit">Save</button>
          <button class="mini" type="button" data-action="cancel">Cancel</button>
        </div>
      </div>
      <div class="row"><span class="label">Quantity:</span>
        <span class="value" data-field="quantity"><input class="edit-input" data-name="quantity" type="number" min="0" step="1" value=""></span>
      </div>
      <div class="row"><span class="label">Category:</span>
        <span class="value" data-field="category"><input class="edit-input" data-name="category" type="text" value=""></span>
      </div>
      <div class="row"><span class="label">Expiry date:</span>
        <span class="value" data-field="expiry_date"><input class="edit-input" data-name="expiry_date" type="date" min="${min}" max="${max}"></span>
      </div>
      <div class="row"><span class="label">Status:</span>
        <span class="value" data-field="status">
          <select class="edit-input" data-name="status">
            <option value="used">used</option>
            <option value="reserved">reserved</option>
            <option value="expired">expired</option>
          </select>
        </span>
      </div>
      <div class="row"><span class="label">Storage location:</span>
        <span class="value" data-field="storage_location"><input class="edit-input" data-name="storage_location" type="text" value=""></span>
      </div>
      <div class="row"><span class="label">Description:</span>
        <span class="value" data-field="description"><textarea class="edit-input" data-name="description" rows="2"></textarea></span>
      </div>
    `;
    article.dataset.editing='1';
    const containerEl = document.getElementById('list');
    if(containerEl.firstChild) containerEl.insertBefore(article, containerEl.firstChild);
    else containerEl.appendChild(article);
  }

  async function deleteCard(card){
    const id = Number(card?.dataset.id);
    if (!id || isNaN(id)) { card.remove(); return; } // 新卡或无 id：仅前端移除
    if (!confirm('Delete this item?')) return;
    try{
      const res = await fetch(API_DELETE_FOOD, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({ fooditem_id: id })
      });
      const data = await res.json();
      if (!data.success) throw new Error(data.error || 'Delete failed');
      card.remove();
    }catch(e){
      console.error(e); alert(e.message || 'Network error');
    }
  }

  function bindEvents(){
    document.addEventListener('click', (e)=>{
      const card = e.target.closest('.card');
      const btn = e.target.closest('button');
      const plus = e.target.closest('.plus');

      if (plus){ e.preventDefault(); addNewCard(); return; }
      if (!btn) return;

      const action = btn.dataset.action;
      if (!action) return;

      if (action === 'edit'){
        const label = btn.textContent.trim().toLowerCase();
        if (label === 'edit') enterEdit(card);
        else if (label === 'save'){ saveEdit(card); }
        return;
      }
      if (action === 'cancel'){
        if (!card.dataset.id || card.dataset.id === 'new') card.remove();
        else cancelEdit(card);
        return;
      }
      if (action === 'donate'){ markAsDonated(card); return; }
      if (action === 'delete'){ deleteCard(card); return; }
    }, false);

    const sel = document.getElementById('filterSel');
    if (sel){ sel.addEventListener('change', ()=>applyFilter(sel.value)); applyFilter(sel.value || 'all'); }else {
 console.warn('[inventory] #filterSel not found');}
  }

  function applyFilter(value){
    const cards = Array.from(document.querySelectorAll('.card'));
    const now = new Date();
    if (value === 'all'){ cards.forEach(c=>c.style.display=''); return; }
    if (value === 'recent'){
      const ids = cards.map(x=>Number(x.dataset.id)||0).sort((a,b)=>b-a).slice(0,10);
      cards.forEach(c=>{ c.style.display = ids.includes(Number(c.dataset.id)||0) ? '' : 'none'; });
      return;
    }
    if (value === 'near'){
      cards.forEach(c=>{
        const expStr=(c.dataset.expiry||'').trim();
        const exp = expStr? new Date(expStr) : null;
        let show=false;
        if(exp && !isNaN(exp.getTime())){
          const diff=Math.floor((exp - now)/(1000*60*60*24));
          show = diff >= 0 && diff <= 7;
        }
        c.style.display = show? '' : 'none';
      });
    }
  }

  function initInventoryPage(){
    if (window.__inventoryInited) return;
    bindEvents();
    window.__inventoryInited = true;
    console.log('[inventory] ready');
  }

  window.initInventoryPage = initInventoryPage;
  window.applyFilter = applyFilter;

  if (document.readyState === 'complete' || document.readyState === 'interactive') initInventoryPage();
  else document.addEventListener('DOMContentLoaded', initInventoryPage, { once: true });
})();
