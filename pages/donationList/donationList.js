/* /SaveBite/assets/js/donationList.js
 * 读取我的捐赠列表（后端），编辑后落库，删除后落库
 * 只在有 #donation-list 的页面运行
 */
(function () {
  'use strict';

  const API_LIST   = '/SaveBite/pages/donationList/list_my_donations.php';
  const API_UPDATE = '/SaveBite/pages/donationList/update_donation.php';
  const API_DELETE = '/SaveBite/pages/donationList/delete_donation.php';

  const esc = (s) => String(s)
    .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
    .replaceAll('"','&quot;').replaceAll("'",'&#39;');
  
  const CATEGORIES = [
  'Produce',
  'Protein',
  'Dairy & Bakery',
  'Grains & Pantry',
  'Snacks & Beverages'
  ];


  function initDonationListPage() {
    if (window.__donationInited) return;

    const list = document.getElementById('donation-list');
    if (!list) return;  // 不是捐赠页，直接退出

    window.__donationInited = true;

    // ------- 读取并渲染 -------
    fetch(API_LIST, { method: 'GET' })
      .then(r => r.json())
      .then(json => {
        if (!json.success) throw new Error(json.error || 'Load failed');
        const items = json.items || [];
        render(items);

        // 统计缺失取件信息并提醒
        const missing = items.filter(it =>
          !String(it.pickup_location||'').trim() ||
          !String(it.availability||'').trim() ||
          !String(it.contact||'').trim()
        ).length;
        if (missing > 0) {
          alert(`There are ${missing} donated item(s) missing pickup info (pickup location / availability / contact). Please complete them soon.`);
        }
      })
      .catch(err => {
        console.error(err);
        list.innerHTML = `<p style="color:#c00">Failed to load donations: ${esc(err.message || err)}</p>`;
      });

    // ------- 事件委托 -------
    list.addEventListener('click', (e) => {
      const btn  = e.target.closest('button'); if (!btn) return;
      const card = btn.closest('.card');       if (!card) return;

      const act = btn.dataset.action;
      if (act === 'edit')        { enterEdit(card); return; }
      if (act === 'save')        { saveEdit(card);  return; }
      if (act === 'cancel-edit') { cancelEdit(card); return; }
      if (act === 'delete')      { del(card); return; }
    });

    // ------- 渲染 -------
    function render(data) {
      list.innerHTML = '';
      if (!data.length) { list.innerHTML = '<p class="empty-hint">No donated items yet.</p>'; return; }

      data.forEach(it => {
        const a = document.createElement('article');
        a.className = 'card';
        a.dataset.donationId = String(it.donation_id || '');
        // manageId 是对应 inventory 的 foodItem_id（纯数字）
        a.dataset.fooditemId = String(it.manageId || '');

        a.innerHTML = `
          <div class="card-head">
            <div class="card-title">
              <span class="label">Food Name:</span>
              <span class="value" data-field="food_name">${esc(it.name || 'Unknown')}</span>
              <span class="mini-id">(# ${esc(it.donation_id || '')})</span>

            </div>
            <div>
              <button class="mini" type="button" data-action="edit">Edit</button>
              <button class="mini" type="button" data-action="delete">Delete</button>
            </div>
          </div>

          <div class="row"><span class="label">Quantity:</span>
            <span class="value" data-field="quantity">${esc(it.quantity || '')}</span>
          </div>
          <div class="row"><span class="label">Category:</span>
            <span class="value" data-field="category">${esc(it.category || '')}</span>
          </div>
          <div class="row"><span class="label">Expiry date:</span>
            <span class="value" data-field="expiry">${esc(it.expiry || '')}</span>
          </div>

          <div class="row"><span class="label">Donation status:</span>
            <span class="value" data-field="donation_status">${esc(it.donation_status || 'pending')}</span>
          </div>
          <div class="row"><span class="label">Donated on:</span>
            <span class="value" data-field="donation_date">${esc(it.donation_date || '')}</span>
          </div>

          <div class="row"><span class="label">Description:</span>
            <span class="value" data-field="desc">${esc(it.desc || '')}</span>
          </div>

          <div class="row"><span class="label">Pickup location:</span>
            <span class="value" data-field="pickup_location">${esc(it.pickup_location || '')}</span>
          </div>
          <div class="row"><span class="label">Availability:</span>
            <span class="value" data-field="availability">${esc(it.availability || '')}</span>
          </div>
          <div class="row"><span class="label">Contact:</span>
            <span class="value" data-field="contact">${esc(it.contact || '')}</span>
          </div>
        `;
        list.appendChild(a);
      });
    }

    // ------- 编辑 -------
    function enterEdit(card){
      if (card.dataset.editing === '1') return;
      card.dataset.editing = '1';

      const get = f => (card.querySelector(`[data-field="${f}"]`)?.textContent || '').trim();
      const o = {
        food_name: get('food_name'),
        quantity:  get('quantity'),
        category:  get('category'),
        expiry:    get('expiry'),
        donation_status: get('donation_status'),
        donation_date:   get('donation_date'), // 只读显示
        desc:       get('desc'),
        pickup_location: get('pickup_location'),
        availability:    get('availability'),
        contact:         get('contact')
      };
      card.dataset.original = JSON.stringify(o);

      card.querySelector('[data-field="food_name"]').textContent = o.food_name;
      card.querySelector('[data-field="quantity"]').innerHTML   = `<input type="number" min="0" step="1" value="${esc(o.quantity)}">`;
      card.querySelector('[data-field="category"]').innerHTML = `
        <select>
          ${CATEGORIES.map(c => `<option value="${esc(c)}"${o.category===c?' selected':''}>${esc(c)}</option>`).join('')}
        </select>
      `;
      card.querySelector('[data-field="expiry"]').textContent = o.expiry;

      card.querySelector('[data-field="donation_status"]').innerHTML = `
        <select>
          <option value="pending"${o.donation_status==='pending' ? ' selected' : ''}>pending</option>
          <option value="picked_up"${o.donation_status==='picked_up' ? ' selected' : ''}>picked_up</option>
        </select>`;

      // donation_date 保持只读，不生成 input
      card.querySelector('[data-field="desc"]').innerHTML       = `<textarea rows="2">${esc(o.desc)}</textarea>`;
      card.querySelector('[data-field="pickup_location"]').innerHTML = `<input type="text" value="${esc(o.pickup_location)}">`;
      card.querySelector('[data-field="availability"]').innerHTML    = `<input type="text" value="${esc(o.availability)}">`;
      card.querySelector('[data-field="contact"]').innerHTML         = `<input type="text" value="${esc(o.contact)}">`;

      card.querySelector('.card-head div:last-child').innerHTML = `
        <button class="mini" type="button" data-action="save">Save</button>
        <button class="mini" type="button" data-action="cancel-edit">Cancel</button>
        <button class="mini" type="button" data-action="delete">Delete</button>`;
    }

    // ------- 保存（落库） -------
    function saveEdit(card){
      const v = sel => { const el = card.querySelector(sel); return el ? String(el.value || '').trim() : ''; };

      const payload = {
        donation_id: Number(card.dataset.donationId || 0),
        fooditem_id: Number(card.dataset.fooditemId || 0),

        // donation
        donation_status: v('[data-field="donation_status"] select'),
        pickup_location: v('[data-field="pickup_location"] input'),
        availability:    v('[data-field="availability"] input'),
        contact:         v('[data-field="contact"] input'),

        // donation_fooditem
        quantity: v('[data-field="quantity"] input'),

        // fooditem 
        category:  (card.querySelector('[data-field="category"] select')?.value ||
                    card.querySelector('[data-field="category"] input')?.value || '').trim(),
        desc:      (card.querySelector('[data-field="desc"] textarea')?.value || '').trim()
      };

      const errs=[];
      if (!CATEGORIES.includes(payload.category)) errs.push('Category must be one of: ' + CATEGORIES.join(', ') + '.');
      if (!payload.donation_id) errs.push('donation_id missing.');
      if (!['pending','picked_up'].includes(payload.donation_status)) errs.push('Donation status must be pending or picked_up.');
      if (!payload.pickup_location) errs.push('Pickup location is required.');
      if (!payload.availability)    errs.push('Availability is required.');
      if (!payload.contact)         errs.push('Contact is required.');
      if (!/^\d+$/.test(payload.quantity || '')) errs.push('Quantity must be an integer ≥ 0.');
      if (errs.length){ alert('Please fix:\n- ' + errs.join('\n- ')); return; }

      fetch(API_UPDATE, {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify(payload)
      })
      .then(r=>r.json())
      .then(json=>{
        if (!json.success) throw new Error(json.error || 'Update failed');

        // 同步 UI（donation_date 不会变）
        card.querySelector('[data-field="donation_status"]').textContent = payload.donation_status;
        card.querySelector('[data-field="pickup_location"]').textContent = payload.pickup_location;
        card.querySelector('[data-field="availability"]').textContent    = payload.availability;
        card.querySelector('[data-field="contact"]').textContent         = payload.contact;

        
        card.querySelector('[data-field="quantity"]').textContent  = payload.quantity;
        card.querySelector('[data-field="category"]').textContent  = payload.category;



        card.querySelector('[data-field="desc"]').textContent      = payload.desc;

        delete card.dataset.original; card.dataset.editing='0';
        card.querySelector('.card-head div:last-child').innerHTML = `
          <button class="mini" type="button" data-action="edit">Edit</button>
          <button class="mini" type="button" data-action="delete">Delete</button>`;
      })
      .catch(err=>{ console.error(err); alert(err.message || 'Update failed'); });
    }

    // ------- 取消 -------
    function cancelEdit(card){
      let o={}; try{ o=JSON.parse(card.dataset.original||'{}'); }catch(e){ o={}; }

      card.querySelector('[data-field="food_name"]').textContent = o.food_name || '';
      card.querySelector('[data-field="quantity"]').textContent  = o.quantity  || '';
      card.querySelector('[data-field="category"]').textContent  = o.category  || '';
      card.querySelector('[data-field="expiry"]').textContent    = o.expiry    || '';

      card.querySelector('[data-field="donation_status"]').textContent = o.donation_status || 'pending';
      card.querySelector('[data-field="donation_date"]').textContent   = o.donation_date   || '';

      card.querySelector('[data-field="desc"]').textContent      = o.desc || '';
      card.querySelector('[data-field="pickup_location"]').textContent = o.pickup_location || '';
      card.querySelector('[data-field="availability"]').textContent    = o.availability    || '';
      card.querySelector('[data-field="contact"]').textContent         = o.contact         || '';

      delete card.dataset.original; card.dataset.editing='0';
      card.querySelector('.card-head div:last-child').innerHTML = `
        <button class="mini" type="button" data-action="edit">Edit</button>
        <button class="mini" type="button" data-action="delete">Delete</button>`;
    }

    // ------- 删除（落库） -------
    function del(card){
      const donationId = Number(card.dataset.donationId||0);
      const foodItemId = Number(card.dataset.fooditemId||0);
      if(!donationId){ alert('Missing donation_id'); return; }
      if(!confirm('Delete this donation? This will return the food item back to inventory.')) return;

      fetch(API_DELETE, {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ donation_id: donationId, fooditem_id: foodItemId })
      })
      .then(r=>r.json())
      .then(json=>{
        if(!json.success) throw new Error(json.error||'Delete failed');
        card.remove();
      })
      .catch(err=>{ console.error(err); alert(err.message||'Delete failed'); });
    }
  }

  // 暴露 & 自动初始化
  window.initDonationListPage = initDonationListPage;
  if (document.readyState === 'complete' || document.readyState === 'interactive') initDonationListPage();
  else document.addEventListener('DOMContentLoaded', initDonationListPage, { once: true });
})();
