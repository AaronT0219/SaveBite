/* /SaveBite/assets/js/inventory.js
 * Edit / Save / Cancel / Mark as donated / + 新建（仅前端）
 * Mark as donated：调用接口更新DB + 写入 localStorage.donationItems + 从页面移除
 */
(function () {
  'use strict';

  const API_STATUS = '/SaveBite/pages/browse/update_fooditem_status.php';

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
  function setHTML(el, html) { if (!el) return false; el.innerHTML = html; return true; }
  function escapeAttr(s = '') { return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
  function escapeHTML(s = '') { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;'); }

  function getCellValueSafe(el){ if(!el) return ''; const c=el.querySelector('input,select,textarea'); return c ? (c.value??'').toString().trim() : (el.textContent||'').trim(); }
  function collectDisplay(card){ const d={}; for(const k of FIELD_KEYS) d[k]=getCellValueSafe(pickCell(card,k)); return d; }
  function collectInputs(card){ const d={}; card.querySelectorAll('.edit-input').forEach(inp=>{ const n=inp.dataset.name; if(n) d[n]=inp.value; }); return d; }
  function renderDisplay(card, v){ for(const k of FIELD_KEYS){ const el=pickCell(card,k); if(!el) continue; el.textContent = (k==='status') ? toUILabel(v[k]) : (v[k]??''); } }

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

    const exp = pickCell(card, 'expiry_date');
    setHTML(exp, `<input class="edit-input" data-name="expiry_date" type="date" value="${card.__orig.expiry_date || ''}">`);

    const st = pickCell(card, 'status');
    setHTML(st, `
      <select class="edit-input" data-name="status">
        <option value="donated">donated</option>
        <option value="used">used</option>
        <option value="reserved">reserved</option>
      </select>
    `);
    const stSel = st?.querySelector('select'); if(stSel) stSel.value = toUILabel(card.__orig.status || '');

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
    if(!values.status?.trim()) errors.push('Status cannot be empty.');
    if(!values.storage_location?.trim()) errors.push('Storage location cannot be empty.');
    if(errors.length){ alert('Please fix:\n- ' + errors.join('\n- ')); return false; }
    return true;
  }

  function saveEdit(card){
    if(!card || card.dataset.editing!=='1') return;
    const values = collectInputs(card);
    if(!validateRequired(values)) return;
    renderDisplay(card, values);
    cleanupEdit(card);
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

  // Mark as donated：调接口 + 写 localStorage + 移除卡片
  async function markAsDonated(card){
    const id = Number(card?.dataset.id); if(!id) return;

    // localStorage 工具
    const readLS = (k, def) => { try { return JSON.parse(localStorage.getItem(k) || JSON.stringify(def)); } catch(e){ return def; } };
    const writeLS = (k, v) => localStorage.setItem(k, JSON.stringify(v));

    try{
      const res = await fetch(API_STATUS, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({
          fooditem_id: id,
          tagClassName: '.donation-tag-modal',
          status: true
        })
      });
      const data = await res.json();
      if (!data.success) { alert(data.error || data.message || 'Update failed'); return; }

      // 收集信息写入 donationItems（供 donationList 页面使用）
      const getText = (k) => getCellValueSafe(pickCell(card, k));
      const donationItem = {
        id: 'D-' + id,
        manageId: 'M-' + id,
        name: getText('food_name'),
        quantity: getText('quantity'),
        category: getText('category'),
        expiry: getText('expiry_date'),
        status: 'donation',
        desc: getText('description'),
        pickup_location: '',
        availability: '',
        contact: ''
      };
      const LS_KEY = 'donationItems';
      const arr = readLS(LS_KEY, []);
      const pos = arr.findIndex(x => String(x.manageId) === donationItem.manageId || String(x.id) === donationItem.id);
      if (pos >= 0) arr[pos] = { ...arr[pos], ...donationItem };
      else arr.push(donationItem);
      writeLS(LS_KEY, arr);

      // 从页面移除
      card.remove();
      alert('Moved to Donation List.');
    }catch(e){
      console.error(e); alert('Network error');
    }
  }

  // + 新建（仅前端）
  function addNewCard(){
    const container=document.getElementById('list');
    const article=document.createElement('article');
    article.className='card'; article.dataset.id='new';
    article.innerHTML = `
      <div class="card-head">
        <div class="card-title">
          <span class="label">Food Name:</span>
          <span class="value" data-field="food_name">
            <input class="edit-input title-name-input" data-name="food_name" type="text" value="">
          </span>
          <span class="mini-id">(# M-new)</span>
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
        <span class="value" data-field="expiry_date"><input class="edit-input" data-name="expiry_date" type="date"></span>
      </div>
      <div class="row"><span class="label">Status:</span>
        <span class="value" data-field="status">
          <select class="edit-input" data-name="status">
            <option value="donated">donated</option>
            <option value="used">used</option>
            <option value="reserved">reserved</option>
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
        else if (label === 'save'){
          // 新卡片仅前端：校验后直接切回展示
          const values = collectInputs(card);
          if(!validateRequired(values)) return;
          renderDisplay(card, values);
          card.dataset.editing='0';
          const btnWrap = card.querySelector('.card-head div:last-child');
          if (btnWrap) btnWrap.innerHTML = `
            <button class="mini" type="button" data-action="edit">Edit</button>
            <button class="mini" type="button" data-action="donate">Mark as donated</button>
            <button class="mini" type="button" data-action="delete">Delete</button>
          `;
        }
        return;
      }
      if (action === 'cancel'){
        if (!card.dataset.id || card.dataset.id === 'new') card.remove();
        else cancelEdit(card);
        return;
      }
      if (action === 'donate'){ markAsDonated(card); return; }
      if (action === 'delete'){ card.remove(); return; } // 仅前端删除
    }, false);

    const sel = document.getElementById('filterSel');
    if (sel){ sel.addEventListener('change', ()=>applyFilter(sel.value)); applyFilter(sel.value || 'all'); }
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
  if (document.readyState === 'complete' || document.readyState === 'interactive') initInventoryPage();
  else document.addEventListener('DOMContentLoaded', initInventoryPage, { once: true });
})();
