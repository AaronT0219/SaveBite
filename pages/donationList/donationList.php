<?php /* 本页从 localStorage.donationItems 读取数据并渲染 */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Donation List</title>
  <style>
    .topbar{display:flex;justify-content:space-between;gap:12px;padding:20px 32px;}
    .title{font-size:28px;font-weight:700;/* bold */}
    .top_botton{display:flex;align-items:center;gap:16px;}
    .go_manage_btn{padding:10px 14px;font-size:14px;border:1px solid #bbb;background:#ddd;border-radius:25px;text-decoration:none;color:#222;cursor:pointer;}
    .content{width:100%;max-width:820px;margin:0 auto;padding:16px;}
    .card{border:3px solid #222;border-radius:25px;padding:40px;margin-bottom:18px;background:#fff;}
    .card-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;}
    .row{display:grid;grid-template-columns:160px 1fr;gap:8px;padding:4px 0;border-top:1px dashed #eee;}
    .row:first-of-type{border-top:none;}
    .value{color:#4d524fff;}
    .mini{margin-left:4px;}
    .row input,.row textarea,.row select{width:100%;padding:6px 8px;border:1px solid #bbb;border-radius:6px;background:#eee;box-sizing:border-box;}
  </style>
</head>
<body>
  <header class="topbar">
    <h1 class="title">Donation List</h1>
    <div class="top_botton"><a class="go_manage_btn" data-page="inventory">Back to Manage</a></div>
  </header>

  <main class="content" id="list"></main>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    const list=document.getElementById('list');
    const LS_KEY='donationItems', LS_REMOVED='removedFromManage';
    const readLS=(k,def)=>{try{return JSON.parse(localStorage.getItem(k)||JSON.stringify(def));}catch(e){return def;}};
    const writeLS=(k,v)=>localStorage.setItem(k,JSON.stringify(v));
    const esc=s=>String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#39;');

    let items=readLS(LS_KEY, []);

    // 迁移旧数据：统一 id 形如 D-xxx（优先从 manageId 推导）
    let changed=false;
    items=items.map(it=>{
      if(!/^D-/.test(String(it.id))){
        const base=it.manageId?String(it.manageId).replace(/^M-/,''):String(it.id);
        it.id='D-'+base; changed=true;
      }
      return it;
    });
    if(changed) writeLS(LS_KEY, items);

    render(items);

    // 进入时提示缺失信息
    const missing=items.filter(x=>!x.pickup_location||!x.availability||!x.contact).length;
    if(missing>0){ alert(`There are ${missing} item(s) missing pickup info (pickup location / availability / contact). Please complete them soon.`); }

    // 事件委托
    list.addEventListener('click', e=>{
      const btn=e.target.closest('button'); if(!btn) return;
      const card=btn.closest('.card'); if(!card) return;
      const id=card.dataset.id;
      const act=btn.dataset.action;

      if(act==='delete'){
        items=items.filter(x=>String(x.id)!==String(id));
        writeLS(LS_KEY, items);
        // 同步到 manage：记录被删除的 manageId
        const manageId=card.dataset.manageId;
        const removed=readLS(LS_REMOVED,[]);
        if(manageId && !removed.includes(manageId)){ removed.push(manageId); writeLS(LS_REMOVED, removed); }
        card.remove();
        return;
      }
      if(act==='edit'){ enterEdit(card); return; }
      if(act==='save'){ saveEdit(card); return; }
      if(act==='cancel-edit'){ cancelEdit(card); return; }
    });

    function render(data){
      list.innerHTML='';
      if(!data.length){ list.innerHTML='<p style="color:#666;margin:8px 0;">No donated items yet.</p>'; return; }
      data.forEach(it=>{
        const article=document.createElement('article');
        article.className='card';
        article.dataset.id=String(it.id);
        article.dataset.manageId=String(it.manageId||'');
        article.dataset.expiry=it.expiry||'';
        article.innerHTML=`
          <div class="card-head">
            <div class="card-title">FoodItem · ${esc(it.name)} (# ${esc(it.id)})</div>
            <div>
              <button class="mini" type="button" data-action="edit">Edit</button>
              <button class="mini" type="button" data-action="delete">Delete</button>
            </div>
          </div>
          <div class="row"><span class="label">Quantity:</span><span class="value" data-field="quantity">${esc(it.quantity)}</span></div>
          <div class="row"><span class="label">Category:</span><span class="value" data-field="category">${esc(it.category)}</span></div>
          <div class="row"><span class="label">Expiry date:</span><span class="value" data-field="expiry">${esc(it.expiry)}</span></div>
          <div class="row"><span class="label">Status:</span><span class="value" data-field="status">${esc(it.status)}</span></div>
          <div class="row"><span class="label">Description:</span><span class="value" data-field="desc">${esc(it.desc)}</span></div>
          <div class="row"><span class="label">Pickup location:</span><span class="value" data-field="pickup_location">${esc(it.pickup_location||'')}</span></div>
          <div class="row"><span class="label">Availability:</span><span class="value" data-field="availability">${esc(it.availability||'')}</span></div>
          <div class="row"><span class="label">Contact:</span><span class="value" data-field="contact">${esc(it.contact||'')}</span></div>`;
        list.appendChild(article);
      });
    }

    function enterEdit(card){
      if(card.dataset.editing==='1') return; card.dataset.editing='1';
      const get=f=>(card.querySelector(`[data-field="${f}"]`)?.textContent||'').trim();
      const o={quantity:get('quantity'),category:get('category'),expiry:get('expiry'),status:get('status'),desc:get('desc'),
               pickup_location:get('pickup_location'),availability:get('availability'),contact:get('contact')};
      card.dataset.original=JSON.stringify(o);
      card.querySelector('[data-field="quantity"]').innerHTML=`<input type="number" min="0" step="1" value="${esc(o.quantity)}">`;
      card.querySelector('[data-field="category"]').innerHTML=`<input type="text" value="${esc(o.category)}">`;
      card.querySelector('[data-field="expiry"]').innerHTML=`<input type="date" value="${esc(o.expiry)}">`;
      card.querySelector('[data-field="status"]').innerHTML=`<select>
        <option value="used"${o.status==='used'?' selected':''}>used</option>
        <option value="donated"${o.status==='donated'?' selected':''}>donated</option>
        <option value="reserved"${o.status==='reserved'?' selected':''}>reserved</option></select>`;
      card.querySelector('[data-field="desc"]').innerHTML=`<textarea rows="2">${esc(o.desc)}</textarea>`;
      card.querySelector('[data-field="pickup_location"]').innerHTML=`<input type="text" placeholder="Enter pickup location" value="${esc(o.pickup_location)}">`;
      card.querySelector('[data-field="availability"]').innerHTML=`<input type="text" placeholder="Enter availability" value="${esc(o.availability)}">`;
      card.querySelector('[data-field="contact"]').innerHTML=`<input type="text" placeholder="Enter contact" value="${esc(o.contact)}">`;
      card.querySelector('.card-head div:last-child').innerHTML=`
        <button class="mini" type="button" data-action="save">Save</button>
        <button class="mini" type="button" data-action="cancel-edit">Cancel</button>
        <button class="mini" type="button" data-action="delete">Delete</button>`;
    }

    function saveEdit(card){
      const v=sel=>card.querySelector(sel)?card.querySelector(sel).value.trim():'';
      const next={quantity:v('[data-field="quantity"] input'),
                  category:v('[data-field="category"] input'),
                  expiry:v('[data-field="expiry"] input'),
                  status:(card.querySelector('[data-field="status"] select')?.value||'donated'),
                  desc:(card.querySelector('[data-field="desc"] textarea')?.value||''),
                  pickup_location:v('[data-field="pickup_location"] input'),
                  availability:v('[data-field="availability"] input'),
                  contact:v('[data-field="contact"] input')};
      const errs=[];
      if(!/^\d+$/.test(next.quantity)) errs.push('Quantity is required and must be an integer ≥ 0.');
      if(!next.category) errs.push('Category is required.');
      if(!next.expiry) errs.push('Expiry date is required.');
      if(!['used','donated','reserved'].includes(next.status)) errs.push('Status must be used/donated/reserved.');
      if(!next.pickup_location) errs.push('Pickup location is required.');
      if(!next.availability) errs.push('Availability is required.');
      if(!next.contact) errs.push('Contact is required.');
      if(errs.length){ alert('Please fix:\n- '+errs.join('\n- ')); return; }

      // 写回 UI
      card.querySelector('[data-field="quantity"]').textContent=next.quantity;
      card.querySelector('[data-field="category"]').textContent=next.category;
      card.querySelector('[data-field="expiry"]').textContent=next.expiry;
      card.querySelector('[data-field="status"]').textContent=next.status;
      card.querySelector('[data-field="desc"]').textContent=next.desc;
      card.querySelector('[data-field="pickup_location"]').textContent=next.pickup_location;
      card.querySelector('[data-field="availability"]').textContent=next.availability;
      card.querySelector('[data-field="contact"]').textContent=next.contact;

      // 同步到 localStorage
      const id=card.dataset.id;
      let arr=readLS(LS_KEY,[]);
      const i=arr.findIndex(x=>String(x.id)===String(id));
      if(i>=0){ arr[i]={...arr[i],...next}; writeLS(LS_KEY, arr); }

      delete card.dataset.original; card.dataset.editing='0';
      card.querySelector('.card-head div:last-child').innerHTML=
        `<button class="mini" type="button" data-action="edit">Edit</button>
         <button class="mini" type="button" data-action="delete">Delete</button>`;
    }

    function cancelEdit(card){
      let o={}; try{o=JSON.parse(card.dataset.original||'{}')}catch(e){o={}};
      card.querySelector('[data-field="quantity"]').textContent=(o.quantity??'').toString();
      card.querySelector('[data-field="category"]').textContent=(o.category??'').toString();
      card.querySelector('[data-field="expiry"]').textContent=(o.expiry??'').toString();
      card.querySelector('[data-field="status"]').textContent=(o.status??'donated').toString();
      card.querySelector('[data-field="desc"]').textContent=(o.desc??'').toString();
      card.querySelector('[data-field="pickup_location"]').textContent=(o.pickup_location??'').toString();
      card.querySelector('[data-field="availability"]').textContent=(o.availability??'').toString();
      card.querySelector('[data-field="contact"]').textContent=(o.contact??'').toString();
      delete card.dataset.original; card.dataset.editing='0';
      card.querySelector('.card-head div:last-child').innerHTML=
        `<button class="mini" type="button" data-action="edit">Edit</button>
         <button class="mini" type="button" data-action="delete">Delete</button>`;
    }
  });
  </script>
</body>
</html>
