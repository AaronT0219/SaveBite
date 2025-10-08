<?php
/* sample data（using array variable） */
$items = [
  ['id'=>101,'name'=>'Apples','quantity'=>12,'category'=>'Fruit','expiry'=>'2025-11-02','status'=>'Fresh','desc'=>'Red apples', 'storage'=>'Fridge A-1'], // [NEW] 示例含 storage
  ['id'=>102,'name'=>'Milk 1L','quantity'=>6 ,'category'=>'Dairy','expiry'=>'2025-10-25','status'=>'Refrigerated','desc'=>'Low-fat milk', 'storage'=>'Fridge B-2'], // [NEW]
  ['id'=>103,'name'=>'Rice 5kg','quantity'=>3 ,'category'=>'Grain','expiry'=>'2026-03-15','status'=>'Dry','desc'=>'Jasmine rice', 'storage'=>'Room Temp Shelf'], // [NEW]
];
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Food Inventory</title>

  <style>
    /* top bar setting */
    .topbar{ 
      display:flex; 
      justify-content:space-between; /* 左右两端对齐，中间自动留白 */
      gap:12px; 
      padding:20px 32px; }

    .title{ 
      font-size:28px; 
      font-weight:700; /* bold */}
    .top_botton{ 
      display:flex; 
      align-items:center; 
      gap:16px; }
    .go_donation_btn{ 
      padding:10px 14px; 
      font-size:14px; 
      border:1px solid #bbb; 
      background:#ddd; 
      border-radius:25px; 
      text-decoration:none; 
      color:#222; 
      cursor:pointer; /* item to show can be click */}

    .plus{ 
      width:40px; /* same width and height setting(circle)  */
      height:40px; 
      display:flex; 
      align-items:center; 
      justify-content:center; 
      border:3px solid #222; 
      border-radius:50%; 
      font-size:40px; 
      background:#ddd; 
      color:#222; 
      text-decoration:none; /* romove underline */}

    /* main content */
    .content{ 
      width:100%; 
      max-width:820px; 
      margin:0 auto; 
      padding:16px; }

    .toolbar{ display:flex; align-items:center; gap:12px; margin-bottom:12px; }
    .toolbar label{ font-weight:600; }
    .toolbar select{ padding:6px 8px; border:1px solid #bbb; border-radius:8px; background:#eee; }

    /* [NEW] 顶栏里的筛选器对齐 */
    .topbar .toolbar{ display:flex; align-items:center; gap:12px; margin:0; }
    .topbar .toolbar select{ padding:6px 8px; border:1px solid #bbb; border-radius:8px; background:#eee; }

    /* Card appearance */
    .card{ border:3px solid #222; border-radius:25px; padding:40px; margin-bottom:18px; background:#fff; }
    .card-head{ display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:8px; }
    .row{ 
      display:grid; 
      grid-template-columns:110px 1fr;/* left part is fixed, right is flex */
      gap:8px; 
      padding:4px 0; 
      border-top:1px dashed #eee; /* dash line between row */}
    .row:first-of-type{ border-top:none; }
    .value{ color:#4d524fff; }
    .mini{ margin-left:4px; /* card btn gap */}

    /* 新增表单面板：与卡片同样尺寸与自适应 */
    .add-panel{
      width:100%; max-width:100%;
      border:3px solid #222; border-radius:25px; padding:40px; margin-bottom:18px; background:#fff;
      box-sizing:border-box;
    }
    .add-panel h3{ text-align:center; margin:4px 0 10px; font-size:16px; }
    .add-form .row{ display:grid; grid-template-columns:120px 1fr; align-items:center; gap:8px; padding:6px 0; }
    .add-form .row label{ white-space:nowrap; }
    .add-form .row input, .add-form .row textarea, .add-form .row select{
      width:100%; padding:6px 8px; border:1px solid #bbb; border-radius:6px; background:#eee; box-sizing:border-box;
    }
    .add-form .actions{ display:flex; gap:8px; justify-content:center; margin-top:8px; }
    .add-form .btn{ padding:8px 14px; border:1px solid #aaa; background:#ddd; border-radius:8px; cursor:pointer; }

    .hidden{ display:none; }
  </style>
</head>

<body>
  <header class="topbar">
    <h1 class="title">Food Inventory</h1>

    <!-- [NEW] 顶栏里的筛选器（放在 View Donation List 左边） -->
    <div class="toolbar" id="toolbar">
      <label for="filterSel">Filter:</label>
      <select id="filterSel">
        <option value="all">All</option>
        <option value="recent">Recent</option>
        <option value="near">Near expiry (7 days)</option>
      </select>
    </div>

    <div class="top_botton">
      <a class="go_donation_btn" href="donationList.php" title="Open donation list">View Donation List</a>
      <a class="plus" href="#" aria-label="Add new food item" title="Add new item">+</a>
    </div>
  </header>

  <main class="content" id="list">

    <!-- 新增表单（默认隐藏） -->
    <section id="addPanel" class="add-panel hidden">
      <h3>Item Details</h3>
      <form id="addForm" class="add-form">
        <div class="row">
          <label for="f_name">food name:</label>
          <input id="f_name" name="name" type="text" required />
        </div>
        <div class="row">
          <label for="f_qty">Quantity:</label>
          <input id="f_qty" name="quantity" type="number" min="0" step="1" required />
        </div>
        <div class="row">
          <label for="f_cat">Category:</label>
          <input id="f_cat" name="category" type="text" required /> <!-- [CHANGED] 必填 -->
        </div>
        <div class="row">
          <label for="f_exp">Expiry date:</label>
          <input id="f_exp" name="expiry" type="date" required />  <!-- [CHANGED] 必填 -->
        </div>
        <div class="row">
          <label for="f_status">Status:</label>
          <select id="f_status" name="status" required>
            <option value="used">used</option>
            <option value="donated">donated</option>
            <option value="reserved">reserved</option>
          </select>
        </div>

        <!-- [NEW] Storage location（在 Description 之上） -->
        <div class="row">
          <label for="f_storage">Storage location:</label>
          <input id="f_storage" name="storage" type="text" />
        </div>

        <div class="row">
          <label for="f_desc">Description:</label>
          <textarea id="f_desc" name="desc" rows="2"></textarea>
        </div>
        <div class="actions">
          <button type="submit" class="btn">Save</button>
          <button type="button" class="btn" id="btnCancelAdd">Cancel</button>
        </div>
      </form>
    </section>

    <!-- 现有条目渲染 -->
    <?php if (!empty($items)) { ?>
      <?php foreach ($items as $it) { 
        $mid = 'M-' . htmlspecialchars($it['id']); // [NEW] 管理页专用显示ID
      ?>
        <!-- 每个管理卡片都有自己的 ID（M-xxx） -->
        <article class="card"
                 data-id="<?php echo $mid; ?>"
                 data-expiry="<?php echo htmlspecialchars($it['expiry']); ?>"
                 data-created="">
          <div class="card-head">
            <div class="card-title">
              FoodItem · <?php echo htmlspecialchars($it['name']); ?> (# <?php echo $mid; ?>)
            </div>
            <div>
              <button class="mini" type="button" data-action="edit">Edit</button>
              <button class="mini" type="button" data-action="donate">Mark as donated</button>
              <button class="mini" type="button" data-action="delete">Delete</button>
            </div>
          </div>

          <div class="row"><span class="label">Quantity:</span><span class="value" data-field="quantity"><?php echo htmlspecialchars($it['quantity']); ?></span></div>
          <div class="row"><span class="label">Category:</span><span class="value" data-field="category"><?php echo htmlspecialchars($it['category']); ?></span></div>
          <div class="row"><span class="label">Expiry date:</span><span class="value" data-field="expiry"><?php echo htmlspecialchars($it['expiry']); ?></span></div>
          <div class="row"><span class="label">Status:</span><span class="value" data-field="status"><?php echo htmlspecialchars($it['status']); ?></span></div>

          <!-- [NEW] Storage location：位于 Description 之上 -->
          <div class="row"><span class="label">Storage location:</span>
            <span class="value" data-field="storage"><?php echo htmlspecialchars($it['storage'] ?? ''); ?></span>
          </div>

          <div class="row"><span class="label">Description:</span><span class="value" data-field="desc"><?php echo htmlspecialchars($it['desc']); ?></span></div>
        </article>
      <?php } ?>
    <?php } else { ?>
      <p style="color:#666;margin:8px 0;">No items yet.</p>
    <?php } ?>
  </main>

  <script>
  document.addEventListener('DOMContentLoaded', function(){
    const plusBtn   = document.querySelector('.plus');
    const addPanel  = document.getElementById('addPanel');
    const addForm   = document.getElementById('addForm');
    const cancelBtn = document.getElementById('btnCancelAdd');
    const list      = document.getElementById('list');

    const NEAR_DAYS = 7;  
    const RECENT_DAYS = 3; 

    // 小工具
    const esc = (s='') => String(s)
      .replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
      .replaceAll('"','&quot;').replaceAll("'",'&#39;');
    const getLS = (k,def)=>{try{return JSON.parse(localStorage.getItem(k)||JSON.stringify(def));}catch(e){return def;}};
    const setLS = (k,v)=>localStorage.setItem(k,JSON.stringify(v));
    const now0 = ()=>{const d=new Date();d.setHours(0,0,0,0);return d.getTime();};
    const LS_DONATION='donationItems', LS_REMOVED='removedFromManage';

    // 初始卡片标记为旧
    document.querySelectorAll('.card[data-created=""]').forEach(c=>{c.dataset.created=String(Date.now()-30*24*60*60*1000);});

    // 同步 donationList 删除
    const removed = new Set(getLS(LS_REMOVED,[]));
    if(removed.size){ document.querySelectorAll('.card').forEach(c=>{ if(removed.has(c.dataset.id)) c.remove(); }); setLS(LS_REMOVED,[]); }

    // 新增表单展开/收起
    plusBtn.addEventListener('click',function(e){
      e.preventDefault();
      addPanel.classList.toggle('hidden');
      if (!addPanel.classList.contains('hidden')) {
        document.getElementById('f_name').focus();
        addPanel.scrollIntoView({behavior:'smooth', block:'center'});
      }
    });
    cancelBtn.addEventListener('click',()=>{ addPanel.classList.add('hidden'); addForm.reset(); });

    // 新增保存
    addForm.addEventListener('submit',function(e){
      e.preventDefault();
      if(!addForm.checkValidity()){ addForm.reportValidity(); return; } // 原生必填

      const fd = new FormData(addForm);
      const id = 'M-'+Date.now();
      const item = {
        id,
        name: fd.get('name'),
        quantity: fd.get('quantity'),
        category: fd.get('category'),
        expiry: fd.get('expiry'),
        status: fd.get('status'),
        storage: fd.get('storage') || '',   // [NEW]
        desc: fd.get('desc') || ''
      };

      const art = document.createElement('article');
      art.className='card';
      art.dataset.id = item.id;
      art.dataset.created = String(Date.now());
      art.dataset.expiry = item.expiry || '';
      art.innerHTML = `
        <div class="card-head">
          <div class="card-title">FoodItem · ${esc(item.name)} (# ${esc(item.id)})</div>
          <div>
            <button class="mini" type="button" data-action="edit">Edit</button>
            <button class="mini" type="button" data-action="donate">Mark as donated</button>
            <button class="mini" type="button" data-action="delete">Delete</button>
          </div>
        </div>
        <div class="row"><span class="label">Quantity:</span><span class="value" data-field="quantity">${esc(item.quantity)}</span></div>
        <div class="row"><span class="label">Category:</span><span class="value" data-field="category">${esc(item.category)}</span></div>
        <div class="row"><span class="label">Expiry date:</span><span class="value" data-field="expiry">${esc(item.expiry)}</span></div>
        <div class="row"><span class="label">Status:</span><span class="value" data-field="status">${esc(item.status)}</span></div>

        <!-- [NEW] Storage location（在 Description 之上） -->
        <div class="row"><span class="label">Storage location:</span>
          <span class="value" data-field="storage">${esc(item.storage)}</span>
        </div>

        <div class="row"><span class="label">Description:</span><span class="value" data-field="desc">${esc(item.desc)}</span></div>
      `;
      const afterPanel = document.getElementById('addPanel').nextElementSibling;
      list.insertBefore(art, afterPanel);

      addPanel.classList.add('hidden');
      addForm.reset();
    });

    // 卡片按钮事件
    list.addEventListener('click',function(e){
      const btn = e.target.closest('button'); if(!btn) return;
      const card = btn.closest('.card'); if(!card) return;
      const act = btn.dataset.action;

      if(act==='delete'){ card.remove(); return; }

      if(act==='donate'){
        if(confirm('Mark this item as donated?')){
          const st = card.querySelector('[data-field="status"]'); if(st) st.textContent='donated';
          // 捐赠ID：把 M-xxx 转成 D-xxx
          const dId='D-'+String(card.dataset.id||'').replace(/^M-/,'');
          const name=(card.querySelector('.card-title')?.textContent||'').replace(/^FoodItem ·\s*/,'').replace(/\(#.*\)\s*$/,'');
          const grab=f=>(card.querySelector(`[data-field="${f}"]`)?.textContent||'').trim();
          const donating={
            id:dId,
            manageId:card.dataset.id,
            name,
            quantity:grab('quantity'),
            category:grab('category'),
            expiry:grab('expiry'),
            status:'donated',
            storage:grab('storage')||'',  // [NEW] 一并写入（可选）
            desc:grab('desc'),
            pickup_location:'',
            availability:'',
            contact:''
          };
          const arr=getLS(LS_DONATION,[]); if(!arr.some(x=>x.manageId===donating.manageId)){arr.unshift(donating); setLS(LS_DONATION,arr);}
          alert('Added to Donation List.');
        }
        return;
      }

      if(act==='edit'){ enterEdit(card); return; }
      if(act==='save'){ saveEdit(card); return; }
      if(act==='cancel-edit'){ cancelEdit(card); return; }
    });

    // —— 编辑：进入/保存/取消 —— //
    function enterEdit(card){
      if(card.dataset.editing==='1') return; card.dataset.editing='1';

      const get=f=>(card.querySelector(`[data-field="${f}"]`)?.textContent||'').trim();
      const orig={
        quantity:get('quantity'),
        category:get('category'),
        expiry:get('expiry'),
        status:get('status'),
        storage:get('storage'), // [NEW]
        desc:get('desc')
      };
      card.dataset.original=JSON.stringify(orig);

      card.querySelector('[data-field="quantity"]').innerHTML=`<input type="number" min="0" step="1" value="${esc(orig.quantity)}" style="width:100%;box-sizing:border-box;">`;
      card.querySelector('[data-field="category"]').innerHTML=`<input type="text" value="${esc(orig.category)}" style="width:100%;box-sizing:border-box;">`;
      card.querySelector('[data-field="expiry"]').innerHTML=`<input type="date" value="${esc(orig.expiry)}" style="width:100%;box-sizing:border-box;">`;
      card.querySelector('[data-field="status"]').innerHTML=`<select style="width:100%;box-sizing:border-box;">
        <option value="used"${orig.status==='used'?' selected':''}>used</option>
        <option value="donated"${orig.status==='donated'?' selected':''}>donated</option>
        <option value="reserved"${orig.status==='reserved'?' selected':''}>reserved</option></select>`;
      /* [NEW] storage 可编辑 */
      card.querySelector('[data-field="storage"]').innerHTML=`<input type="text" value="${esc(orig.storage)}" style="width:100%;box-sizing:border-box;">`;
      card.querySelector('[data-field="desc"]').innerHTML=`<textarea rows="2" style="width:100%;box-sizing:border-box;">${esc(orig.desc)}</textarea>`;

      card.querySelector('.card-head div:last-child').innerHTML=`
        <button class="mini" type="button" data-action="save">Save</button>
        <button class="mini" type="button" data-action="cancel-edit">Cancel</button>
        <button class="mini" type="button" data-action="delete">Delete</button>`;
    }

    function saveEdit(card){
      const q=card.querySelector('[data-field="quantity"] input')?.value.trim()||'';
      const c=card.querySelector('[data-field="category"] input')?.value.trim()||'';
      const e=card.querySelector('[data-field="expiry"] input')?.value.trim()||'';
      const s=card.querySelector('[data-field="status"] select')?.value||'used';
      const t=card.querySelector('[data-field="storage"] input')?.value.trim()||''; // [NEW]
      const d=card.querySelector('[data-field="desc"] textarea')?.value||'';

      // 必填（除 desc 与 storage）
      const errs=[]; 
      if(!/^\d+$/.test(q)) errs.push('Quantity is required and must be an integer ≥ 0.');
      if(!c) errs.push('Category is required.');
      if(!e) errs.push('Expiry date is required.');
      if(!['used','donated','reserved'].includes(s)) errs.push('Status must be used/donated/reserved.');
      if(errs.length){ alert('Please fix:\n- '+errs.join('\n- ')); return; }

      card.querySelector('[data-field="quantity"]').textContent=q;
      card.querySelector('[data-field="category"]').textContent=c;
      card.querySelector('[data-field="expiry"]').textContent=e;
      card.querySelector('[data-field="status"]').textContent=s;
      card.querySelector('[data-field="storage"]').textContent=t; // [NEW]
      card.querySelector('[data-field="desc"]').textContent=d;

      delete card.dataset.original; card.dataset.editing='0';
      card.querySelector('.card-head div:last-child').innerHTML=`
        <button class="mini" type="button" data-action="edit">Edit</button>
        <button class="mini" type="button" data-action="donate">Mark as donated</button>
        <button class="mini" type="button" data-action="delete">Delete</button>`;
      card.dataset.expiry=e||'';
    }

    function cancelEdit(card){
      let o={}; try{o=JSON.parse(card.dataset.original||'{}')}catch(e){o={}};
      card.querySelector('[data-field="quantity"]').textContent=(o.quantity??'').toString();
      card.querySelector('[data-field="category"]').textContent=(o.category??'').toString();
      card.querySelector('[data-field="expiry"]').textContent=(o.expiry??'').toString();
      card.querySelector('[data-field="status"]').textContent=(o.status??'used').toString();
      card.querySelector('[data-field="storage"]').textContent=(o.storage??'').toString(); // [NEW]
      card.querySelector('[data-field="desc"]').textContent=(o.desc??'').toString();
      delete card.dataset.original; card.dataset.editing='0';
      card.querySelector('.card-head div:last-child').innerHTML=`
        <button class="mini" type="button" data-action="edit">Edit</button>
        <button class="mini" type="button" data-action="donate">Mark as donated</button>
        <button class="mini" type="button" data-action="delete">Delete</button>`;
    }

    // 简易筛选
    const filterSel=document.getElementById('filterSel'); 
    filterSel.addEventListener('change',applyFilter); 
    applyFilter();

    function applyFilter(){
      const mode=filterSel.value, today=now0(), nearCut=today+NEAR_DAYS*24*60*60*1000, recentCut=Date.now()-RECENT_DAYS*24*60*60*1000;
      document.querySelectorAll('.card').forEach(card=>{
        const created=Number(card.dataset.created||0);
        const expStr=card.dataset.expiry||''; 
        const exp=expStr?new Date(expStr):null; 
        const expMs=exp?(exp.setHours(0,0,0,0),exp.getTime()):null;
        let show=true; 
        if(mode==='recent') show=created>=recentCut; 
        else if(mode==='near') show=(expMs!==null&&expMs>=today&&expMs<=nearCut);
        card.style.display=show?'':'none';
      });
    }
  });
  </script>
</body>
</html>
