/* /SaveBite/assets/js/inventory.js
 * 去掉 inventory 的 donated 选项；其他逻辑同前
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

    const nameCell = pickCell(card, 'food_name');
    setHTML(nameCell, `<input class="edit-input title-name-input" data-name="food_name" type="text" value="${escapeAttr(card.__orig.food_name || '')}">`);

    const q = pickCell(card, 'quantity');
    setHTML(q, `<input class="edit-input" data-name="quantity" type="number" min="0" step="1" value="${card.__orig.quantity || ''}">`);

    const cat = pickCell(card, 'category');
    setHTML(cat, `<input class="edit-input" data-name="category" type="text" value="${escapeAttr(card.__orig.category)}">`);

    const exp = pickCell(card, 'expiry_date');
    setHTML(exp, `<input class="edit-input" data-name="expiry_date" type="date" value="${card.__orig.expiry_date || ''}">`);

    // ⚠️ 去掉 donated 选项（由按钮 Mark as donated 触发，不在这里选）
    const st = pickCell(card, 'status');
    setHTML(st, `
      <select class="edit-input" data-name="status">
        <option value="available"${card.__orig.status==='available'?' selected':''}>available</option>
        <option value="used"${card.__orig.status==='used'?' selected':''}>used</option>
        <option value="reserved"${card.__orig.status==='reserved'?' selected':''}>reserved</option>
      </select>
    `);

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

  async function markAsDonated(card){
    const id = Number(card?.dataset.id); if(!id) return;
    try{
      const res = await fetch(API_STATUS, {
        method: 'POST',
        headers: { 'Content-Type':'application/json' },
        body: JSON.stringify({ fooditem_id:id, tagClassName:'.donation-tag-modal', status:true })
      });
      const data = await res.json();
      if (!data.success) { alert(data.error || data.message || 'Update failed'); return; }
      card.remove();
      alert('Moved to Donation List.');
    }catch(e){ console.error(e); alert('Network error'); }
  }

  function bindEvents(){
    document.addEventListener('click', (e)=>{
      const card = e.target.closest('.card');
      const btn = e.target.closest('button');
      const plus = e.target.closest('.plus');

      if (plus){ e.preventDefault(); /* 新卡创建逻辑保留或删除均可 */ return; }
      if (!btn) return;

      const action = btn.dataset.action;
      if (action === 'edit'){
        const label = btn.textContent.trim().toLowerCase();
        if (label === 'edit') enterEdit(card);
        else if (label === 'save') saveEdit(card);
        return;
      }
      if (action === 'cancel'){ cancelEdit(card); return; }
      if (action === 'donate'){ markAsDonated(card); return; }
      if (action === 'delete'){ card.remove(); return; } // 仅前端移除
    }, false);
  }

  function initInventoryPage(){
    if (window.__inventoryInited) return;
    bindEvents();
    window.__inventoryInited = true;
  }

  window.initInventoryPage = initInventoryPage;
  if (document.readyState === 'complete' || document.readyState === 'interactive') initInventoryPage();
  else document.addEventListener('DOMContentLoaded', initInventoryPage, { once: true });
})();
