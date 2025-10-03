<?php
/* ====== 示例数据（可换成数据库查询结果）====== */
$items = [
  ['id'=>101,'name'=>'Apples','quantity'=>12,'category'=>'Fruit','expiry'=>'2025-11-02','status'=>'Fresh','desc'=>'Red apples'],
  ['id'=>102,'name'=>'Milk 1L','quantity'=>6 ,'category'=>'Dairy','expiry'=>'2025-10-25','status'=>'Refrigerated','desc'=>'Low-fat milk'],
  ['id'=>103,'name'=>'Rice 5kg','quantity'=>3 ,'category'=>'Grain','expiry'=>'2026-03-15','status'=>'Dry','desc'=>'Jasmine rice'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Food Inventory</title>

  <style>
    /* 顶栏 */
    .topbar{ display:flex; justify-content:space-between; gap:12px; padding:20px 32px; }
    .title{ font-size:28px; font-weight:700; }
    .top_botton{ display:flex; align-items:center; gap:16px; }
    .go_donation_btn{ padding:10px 14px; font-size:14px; border:1px solid #bbb; background:#ddd; border-radius:25px; text-decoration:none; color:#222; cursor:pointer; }
    .plus{ width:40px; height:40px; display:flex; align-items:center; justify-content:center; border:3px solid #222; border-radius:50%; font-size:40px; background:#ddd; color:#222; text-decoration:none; }

    /* 主体内容区：等宽居中 */
    .content{ width:100%; max-width:820px; margin:0 auto; padding:16px; }

    /* 卡片外观 */
    .card{ border:3px solid #222; border-radius:25px; padding:40px; margin-bottom:18px; background:#fff; }
    .card-head{ display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:8px; }
    .row{ display:grid; grid-template-columns:110px 1fr; gap:8px; padding:4px 0; border-top:1px dashed #eee; }
    .row:first-of-type{ border-top:none; }
    .value{ color:#4d524fff; }
    .mini{ margin-left:4px; }

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
    <div class="top_botton">
      <a class="go_donation_btn" href="#" title="Open donation list">View Donation List</a>
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
          <input id="f_cat" name="category" type="text" />
        </div>
        <div class="row">
          <label for="f_exp">Expiry date:</label>
          <input id="f_exp" name="expiry" type="date" />
        </div>
        <div class="row">
          <label for="f_status">Status:</label>
          <!-- 只允许这三种 -->
          <select id="f_status" name="status" required>
            <option value="used">used</option>
            <option value="donated">donated</option>
            <option value="reserved">reserved</option>
          </select>
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
      <?php foreach ($items as $it) { ?>
        <article class="card" data-id="<?php echo htmlspecialchars($it['id']); ?>">
          <div class="card-head">
            <div class="card-title">FoodItem · <?php echo htmlspecialchars($it['name']); ?></div>
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

    // 文本转义（避免插入危险字符）
    const esc = (str='') => String(str)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'",'&#39;');

    // 展开/收起新增表单
    plusBtn.addEventListener('click', function(e){
      e.preventDefault();
      addPanel.classList.toggle('hidden');
      if (!addPanel.classList.contains('hidden')) {
        document.getElementById('f_name').focus();
        addPanel.scrollIntoView({behavior:'smooth', block:'center'});
      }
    });

    // 取消新增
    cancelBtn.addEventListener('click', function(){
      addPanel.classList.add('hidden');
      addForm.reset();
    });

    // 保存新增 → 插入到列表顶部
    addForm.addEventListener('submit', function(e){
      e.preventDefault();
      const fd = new FormData(addForm);
      const item = {
        id: Date.now(),
        name: fd.get('name'),
        quantity: fd.get('quantity'),
        category: fd.get('category'),
        expiry: fd.get('expiry'),
        status: fd.get('status'), // used/donated/reserved
        desc: fd.get('desc')
      };

      const article = document.createElement('article');
      article.className = 'card';
      article.dataset.id = String(item.id);
      article.innerHTML = `
        <div class="card-head">
          <div class="card-title">FoodItem · ${esc(item.name)}</div>
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
        <div class="row"><span class="label">Description:</span><span class="value" data-field="desc">${esc(item.desc)}</span></div>
      `;
      list.insertBefore(article, list.firstChild);

      addPanel.classList.add('hidden');
      addForm.reset();
    });

    // 事件委托：Edit / Save / Cancel / Delete / Donate
    list.addEventListener('click', function(e){
      const btn = e.target.closest('button');
      if(!btn) return;
      const action = btn.dataset.action;
      const card = btn.closest('.card');
      if(!card) return;

      if(action === 'delete'){ card.remove(); return; }
      if(action === 'donate'){
        const st = card.querySelector('[data-field="status"]');
        if(st) st.textContent = 'donated';
        return;
      }
      if(action === 'edit'){ enterEdit(card); return; }
      if(action === 'save'){ saveEdit(card); return; }
      if(action === 'cancel-edit'){ cancelEdit(card); return; }
    });

    /* —— 进入编辑态：缓存原值到 data-original —— */
    function enterEdit(card){
      if(card.dataset.editing === '1') return;
      card.dataset.editing = '1';

      const get = f => (card.querySelector(`[data-field="${f}"]`)?.textContent || '').trim();
      const curr = {
        quantity: get('quantity'),
        category: get('category'),
        expiry:   get('expiry'),
        status:   get('status'),
        desc:     get('desc')
      };
      card.dataset.original = JSON.stringify(curr);

      card.querySelector(`[data-field="quantity"]`).innerHTML =
        `<input type="number" min="0" step="1" value="${esc(curr.quantity)}" style="width:100%;box-sizing:border-box;">`;
      card.querySelector(`[data-field="category"]`).innerHTML =
        `<input type="text" value="${esc(curr.category)}" style="width:100%;box-sizing:border-box;">`;
      card.querySelector(`[data-field="expiry"]`).innerHTML =
        `<input type="date" value="${esc(curr.expiry)}" style="width:100%;box-sizing:border-box;">`;
      card.querySelector(`[data-field="status"]`).innerHTML =
        `<select style="width:100%;box-sizing:border-box;">
           <option value="used"${curr.status==='used'?' selected':''}>used</option>
           <option value="donated"${curr.status==='donated'?' selected':''}>donated</option>
           <option value="reserved"${curr.status==='reserved'?' selected':''}>reserved</option>
         </select>`;
      card.querySelector(`[data-field="desc"]`).innerHTML =
        `<textarea rows="2" style="width:100%;box-sizing:border-box;">${esc(curr.desc)}</textarea>`;

      // 按钮切换
      const btnBox = card.querySelector('.card-head div:last-child');
      btnBox.innerHTML = `
        <button class="mini" type="button" data-action="save">Save</button>
        <button class="mini" type="button" data-action="cancel-edit">Cancel</button>
        <button class="mini" type="button" data-action="delete">Delete</button>
      `;
    }

    /* —— 保存编辑：写回并清缓存 —— */
    function saveEdit(card){
      const qEl = card.querySelector('[data-field="quantity"] input');
      const cEl = card.querySelector('[data-field="category"] input');
      const eEl = card.querySelector('[data-field="expiry"] input');
      const sEl = card.querySelector('[data-field="status"] select');
      const dEl = card.querySelector('[data-field="desc"] textarea');

      const next = {
        quantity: qEl ? qEl.value : '',
        category: cEl ? cEl.value : '',
        expiry:   eEl ? eEl.value : '',
        status:   sEl ? sEl.value : 'used',
        desc:     dEl ? dEl.value : ''
      };

      card.querySelector('[data-field="quantity"]').textContent = next.quantity;
      card.querySelector('[data-field="category"]').textContent = next.category;
      card.querySelector('[data-field="expiry"]').textContent   = next.expiry;
      card.querySelector('[data-field="status"]').textContent   = next.status;
      card.querySelector('[data-field="desc"]').textContent     = next.desc;

      delete card.dataset.original;
      card.dataset.editing = '0';

      const btnBox = card.querySelector('.card-head div:last-child');
      btnBox.innerHTML = `
        <button class="mini" type="button" data-action="edit">Edit</button>
        <button class="mini" type="button" data-action="donate">Mark as donated</button>
        <button class="mini" type="button" data-action="delete">Delete</button>
      `;
    }

    /* —— 取消编辑：按缓存恢复 —— */
    function cancelEdit(card){
      let orig = {};
      try { orig = JSON.parse(card.dataset.original || '{}'); } catch(e){ orig = {}; }

      card.querySelector('[data-field="quantity"]').textContent = (orig.quantity ?? '').toString();
      card.querySelector('[data-field="category"]').textContent = (orig.category ?? '').toString();
      card.querySelector('[data-field="expiry"]').textContent   = (orig.expiry   ?? '').toString();
      card.querySelector('[data-field="status"]').textContent   = (orig.status   ?? 'used').toString();
      card.querySelector('[data-field="desc"]').textContent     = (orig.desc     ?? '').toString();

      delete card.dataset.original;
      card.dataset.editing = '0';

      const btnBox = card.querySelector('.card-head div:last-child');
      btnBox.innerHTML = `
        <button class="mini" type="button" data-action="edit">Edit</button>
        <button class="mini" type="button" data-action="donate">Mark as donated</button>
        <button class="mini" type="button" data-action="delete">Delete</button>
      `;
    }
  });
  </script>
</body>
</html>
