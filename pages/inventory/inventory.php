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
      <a class="go_donation_btn"  data-page="donationList" title="Open donation list">View Donation List</a>
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
</body>
</html>
