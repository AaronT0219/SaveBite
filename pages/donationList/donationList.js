/* /SaveBite/assets/js/donationList.js
 * 从后端接口渲染（不再使用 localStorage）
 * 进入页面后统计：pickup_location / availability / contact 三项为空的卡片数量并提示
 */
(function () {
  'use strict';

  const API_LIST = '/SaveBite/pages/donationList/list_my_donations.php';
  const esc = s => String(s)
    .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
    .replaceAll('"','&quot;').replaceAll("'",'&#39;');
  const toUILabel = s => (String(s).toLowerCase()==='donation' ? 'donated' : (s||''));

  function initDonationListPage() {
    if (window.__donationInited) return;
    window.__donationInited = true;

    const list = document.getElementById('list');

    fetch(API_LIST, { method: 'GET' })
      .then(r => r.json())
      .then(json => {
        if (!json.success) throw new Error(json.error || 'Load failed');
        const items = json.items || [];
        render(items);

        // 进入页面后统计缺失数量并提示
        const missing = items.filter(it =>
          !it.pickup_location?.trim() || !it.availability?.trim() || !it.contact?.trim()
        ).length;
        if (missing > 0) {
          alert(`There are ${missing} donated item(s) missing pickup info (pickup location / availability / contact). Please complete them soon.`);
        }
      })
      .catch(err => {
        console.error(err);
        list.innerHTML = `<p style="color:#c00">Failed to load donations: ${esc(err.message)}</p>`;
      });

    list.addEventListener('click', (e) => {
      const btn = e.target.closest('button'); if (!btn) return;
      const card = btn.closest('.card'); if (!card) return;
      const act = btn.dataset.action;

      if (act === 'edit') { enterEdit(card); return; }
      if (act === 'save') { saveEdit(card); return; }
      if (act === 'cancel-edit') { cancelEdit(card); return; }
      if (act === 'delete') { card.remove(); return; } // 仅前端移除
    });

    function render(data) {
      list.innerHTML = '';
      if (!data.length) {
        list.innerHTML = '<p class="empty-hint">No donated items yet.</p>';
        return;
      }
      data.forEach(it => {
        const article = document.createElement('article');
        article.className = 'card';
        article.dataset.id = String(it.id);
        article.dataset.manageId = String(it.manageId || '');
        article.dataset.donationId = String(it.donation_id || '');
        article.dataset.expiry = it.expiry || '';

        article.innerHTML = `
          <div class="card-head">
            <div class="card-title">
              <span class="label">Food Name:</span>
              <span class="value" data-field="food_name">${esc(it.name || 'Unknown')}</span>
              <span style="color:#666;font-size:90%;">(# ${esc(it.id)})</span>
            </div>
            <div>
              <button class="mini" type="button" data-action="edit">Edit</button>
              <button class="mini" type="button" data-action="delete">Delete</button>
            </div>
          </div>

          <div class="row"><span class="label">Quantity:</span><span class="value" data-field="quantity">${esc(it.quantity || '')}</span></div>
          <div class="row"><span class="label">Category:</span><span class="value" data-field="category">${esc(it.category || '')}</span></div>
          <div class="row"><span class="label">Expiry date:</span><span class="value" data-field="expiry">${esc(it.expiry || '')}</span></div>
          <div class="row"><span class="label">Status:</span><span class="value" data-field="status">${esc(toUILabel(it.status || 'donation'))}</span></div>
          <div class="row"><span class="label">Description:</span><span class="value" data-field="desc">${esc(it.desc || '')}</span></div>

          <div class="row"><span class="label">Pickup location:</span><span class="value" data-field="pickup_location">${esc(it.pickup_location || '')}</span></div>
          <div class="row"><span class="label">Availability:</span><span class="value" data-field="availability">${esc(it.availability || '')}</span></div>
          <div class="row"><span class="label">Contact:</span><span class="value" data-field="contact">${esc(it.contact || '')}</span></div>
        `;
        list.appendChild(article);
      });
    }

    function enterEdit(card) {
      if (card.dataset.editing === '1') return;
      card.dataset.editing = '1';

      const get = f => (card.querySelector(`[data-field="${f}"]`)?.textContent || '').trim();
      const o = {
        food_name: get('food_name'),
        quantity: get('quantity'),
        category: get('category'),
        expiry:   get('expiry'),
        status:   get('status'),
        desc:     get('desc'),
        pickup_location: get('pickup_location'),
        availability:    get('availability'),
        contact:         get('contact')
      };
      card.dataset.original = JSON.stringify(o);

      card.querySelector('[data-field="food_name"]').innerHTML = `<input class="edit-input" type="text" value="${esc(o.food_name)}">`;
      card.querySelector('[data-field="quantity"]').innerHTML   = `<input type="number" min="0" step="1" value="${esc(o.quantity)}">`;
      card.querySelector('[data-field="category"]').innerHTML   = `<input type="text" value="${esc(o.category)}">`;
      card.querySelector('[data-field="expiry"]').innerHTML     = `<input type="date" value="${esc(o.expiry)}">`;
      card.querySelector('[data-field="status"]').innerHTML     = `<select>
        <option value="used"${o.status==='used'?' selected':''}>used</option>
        <option value="donated"${o.status==='donated'?' selected':''}>donated</option>
        <option value="reserved"${o.status==='reserved'?' selected':''}>reserved</option>
      </select>`;
      card.querySelector('[data-field="desc"]').innerHTML       = `<textarea rows="2">${esc(o.desc)}</textarea>`;

      card.querySelector('[data-field="pickup_location"]').innerHTML = `<input type="text" placeholder="Enter pickup location" value="${esc(o.pickup_location)}">`;
      card.querySelector('[data-field="availability"]').innerHTML    = `<input type="text" placeholder="Enter availability" value="${esc(o.availability)}">`;
      card.querySelector('[data-field="contact"]').innerHTML         = `<input type="text" placeholder="Enter contact" value="${esc(o.contact)}">`;

      card.querySelector('.card-head div:last-child').innerHTML = `
        <button class="mini" type="button" data-action="save">Save</button>
        <button class="mini" type="button" data-action="cancel-edit">Cancel</button>
        <button class="mini" type="button" data-action="delete">Delete</button>
      `;
    }

    function saveEdit(card) {
      const v = sel => card.querySelector(sel) ? card.querySelector(sel).value.trim() : '';
      const next = {
        food_name: v('[data-field="food_name"] input'),
        quantity:  v('[data-field="quantity"] input'),
        category:  v('[data-field="category"] input'),
        expiry:    v('[data-field="expiry"] input'),
        status:    (card.querySelector('[data-field="status"] select')?.value || 'donated'),
        desc:      (card.querySelector('[data-field="desc"] textarea')?.value || ''),
        pickup_location: v('[data-field="pickup_location"] input'),
        availability:    v('[data-field="availability"] input'),
        contact:         v('[data-field="contact"] input')
      };

      // 必填校验（除 Description）
      const errs = [];
      if (!/^\d+$/.test(next.quantity)) errs.push('Quantity is required and must be an integer ≥ 0.');
      if (!next.category) errs.push('Category is required.');
      if (!next.expiry) errs.push('Expiry date is required.');
      if (!['used','donated','reserved'].includes(next.status)) errs.push('Status must be used/donated/reserved.');
      if (!next.pickup_location) errs.push('Pickup location is required.');
      if (!next.availability) errs.push('Availability is required.');
      if (!next.contact) errs.push('Contact is required.');
      if (errs.length) { alert('Please fix:\n- ' + errs.join('\n- ')); return; }

      // 仅更新 UI（不落库）
      card.querySelector('[data-field="food_name"]').textContent = next.food_name;
      card.querySelector('[data-field="quantity"]').textContent  = next.quantity;
      card.querySelector('[data-field="category"]').textContent  = next.category;
      card.querySelector('[data-field="expiry"]').textContent    = next.expiry;
      card.querySelector('[data-field="status"]').textContent    = next.status;
      card.querySelector('[data-field="desc"]').textContent      = next.desc;
      card.querySelector('[data-field="pickup_location"]').textContent = next.pickup_location;
      card.querySelector('[data-field="availability"]').textContent    = next.availability;
      card.querySelector('[data-field="contact"]').textContent         = next.contact;

      delete card.dataset.original; card.dataset.editing = '0';
      card.querySelector('.card-head div:last-child').innerHTML = `
        <button class="mini" type="button" data-action="edit">Edit</button>
        <button class="mini" type="button" data-action="delete">Delete</button>
      `;
    }

    function cancelEdit(card) {
      let o = {};
      try { o = JSON.parse(card.dataset.original || '{}'); } catch(e) { o = {}; }

      card.querySelector('[data-field="food_name"]').textContent = (o.food_name ?? '').toString();
      card.querySelector('[data-field="quantity"]').textContent  = (o.quantity ?? '').toString();
      card.querySelector('[data-field="category"]').textContent  = (o.category ?? '').toString();
      card.querySelector('[data-field="expiry"]').textContent    = (o.expiry ?? '').toString();
      card.querySelector('[data-field="status"]').textContent    = (o.status ?? 'donated').toString();
      card.querySelector('[data-field="desc"]').textContent      = (o.desc ?? '').toString();
      card.querySelector('[data-field="pickup_location"]').textContent = (o.pickup_location ?? '').toString();
      card.querySelector('[data-field="availability"]').textContent    = (o.availability ?? '').toString();
      card.querySelector('[data-field="contact"]').textContent         = (o.contact ?? '').toString();

      delete card.dataset.original; card.dataset.editing = '0';
      card.querySelector('.card-head div:last-child').innerHTML = `
        <button class="mini" type="button" data-action="edit">Edit</button>
        <button class="mini" type="button" data-action="delete">Delete</button>
      `;
    }
  }

  window.initDonationListPage = initDonationListPage;
  if (document.readyState === 'complete' || document.readyState === 'interactive') initDonationListPage();
  else document.addEventListener('DOMContentLoaded', initDonationListPage, { once: true });
})();
