<?php
/* 示例数据（可换成数据库查询） */
$items = [
  ['id'=>101,'name'=>'Apples','quantity'=>12,'category'=>'Fruit','expiry'=>'2025-11-02','status'=>'Fresh','desc'=>'Red apples','storage'=>'Fridge A-1'],
  ['id'=>102,'name'=>'Milk 1L','quantity'=>6 ,'category'=>'Dairy','expiry'=>'2025-10-25','status'=>'Refrigerated','desc'=>'Low-fat milk','storage'=>'Fridge B-2'],
  ['id'=>103,'name'=>'Rice 5kg','quantity'=>3 ,'category'=>'Grain','expiry'=>'2026-03-15','status'=>'Dry','desc'=>'Jasmine rice','storage'=>'Room Temp Shelf'],
];
?>
<style>
  /* 仅本页样式；如有全局CSS，可移走 */
  #main-content{ padding-left:32px; padding-right:32px; }

  .inventory-root > header,
  .inventory-root > main{ max-width: 900px; margin: 0 auto; }

  .topbar{ display:flex; justify-content:space-between; align-items:center; gap:12px; padding:16px 0 8px; }
  .title{ font-size:28px; font-weight:700; margin:0; }
  .top_botton{ display:flex; align-items:center; gap:12px; }
  .go_donation_btn{
    padding:8px 12px; font-size:14px; border:1px solid #bbb; background:#ddd; border-radius:20px;
    text-decoration:none; color:#222; cursor:pointer;
  }
  .plus{
    width:40px; height:40px; display:flex; align-items:center; justify-content:center;
    border:3px solid #222; border-radius:50%; font-size:24px; background:#ddd; color:#222; text-decoration:none;
  }

  .content{ width:100%; margin:0; padding:0 0 24px; box-sizing:border-box; }
  .toolbar{ display:flex; align-items:center; gap:8px; }
  .toolbar label{ font-weight:600; }
  .toolbar select{ padding:6px 8px; border:1px solid #bbb; border-radius:8px; background:#eee; }

  .card{ border:3px solid #222; border-radius:20px; padding:18px; margin:16px 0; background:#fff; }
  .card-head{ display:flex; align-items:center; justify-content:space-between; gap:8px; margin-bottom:10px; }
  .mini{ margin-left:4px; }

  .row{
    display:grid; grid-template-columns:180px 1fr;
    align-items:center; gap:8px; padding:8px 0; border-top:1px dashed #eee;
  }
  .row:first-of-type{ border-top:none; }
  .row .label{ white-space:nowrap; }
  .row .value{ color:#4d524f; word-break:break-word; }

  .add-panel{
    width:100%; border:3px solid #222; border-radius:20px; padding:18px; margin:16px 0; background:#fff;
    box-sizing:border-box;
  }
  .add-panel h3{ text-align:center; margin:4px 0 10px; font-size:16px; }
  .add-form .row{ display:grid; grid-template-columns:160px 1fr; align-items:center; gap:8px; padding:6px 0; }
  .add-form .row input, .add-form .row textarea, .add-form .row select{
    width:100%; padding:6px 8px; border:1px solid #bbb; border-radius:6px; background:#eee; box-sizing:border-box;
  }
  .add-form .actions{ display:flex; gap:8px; justify-content:center; margin-top:8px; }
  .add-form .btn{ padding:8px 14px; border:1px solid #aaa; background:#ddd; border-radius:8px; cursor:pointer; }

  .hidden{ display:none; }

  @media (max-width:560px){
    #main-content{ padding-left:16px; padding-right:16px; }
    .row{ grid-template-columns:140px 1fr; }
    .add-form .row{ grid-template-columns:130px 1fr; }
  }
</style>

<div class="inventory-root">

  <header class="topbar">
    <h1 class="title">Food Inventory</h1>

    <div class="toolbar" id="toolbar">
      <label for="filterSel">Filter:</label>
      <select id="filterSel">
        <option value="all">All</option>
        <option value="recent">Recent</option>
        <option value="near">Near expiry (7 days)</option>
      </select>
    </div>

    <div class="top_botton">
      <a class="go_donation_btn" data-page="donationList">View Donation List</a>
      <a class="plus" href="#" aria-label="Add new food item" title="Add new item">+</a>
    </div>
  </header>

  <main class="content" id="list">
    <!-- 新增表单（默认隐藏） -->
    <section id="addPanel" class="add-panel hidden">
      <h3>Item Details</h3>
      <form id="addForm" class="add-form" novalidate>
        <div class="row">
          <span class="label"><label for="f_name">Food name:</label></span>
          <input id="f_name" name="name" type="text" required />
        </div>
        <div class="row">
          <span class="label"><label for="f_qty">Quantity:</label></span>
          <input id="f_qty" name="quantity" type="number" min="0" step="1" required />
        </div>
        <div class="row">
          <span class="label"><label for="f_cat">Category:</label></span>
          <input id="f_cat" name="category" type="text" required />
        </div>
        <div class="row">
          <span class="label"><label for="f_exp">Expiry date:</label></span>
          <input id="f_exp" name="expiry" type="date" required />
        </div>
        <div class="row">
          <span class="label"><label for="f_status">Status:</label></span>
          <select id="f_status" name="status" required>
            <option value="used">used</option>
            <option value="donated">donated</option>
            <option value="reserved">reserved</option>
          </select>
        </div>
        <div class="row">
          <span class="label"><label for="f_storage">Storage location:</label></span>
          <input id="f_storage" name="storage" type="text" />
        </div>
        <div class="row">
          <span class="label"><label for="f_desc">Description:</label></span>
          <textarea id="f_desc" name="desc" rows="2"></textarea>
        </div>
        <div class="actions">
          <button type="submit" class="btn">Save</button>
          <button type="button" class="btn" id="btnCancelAdd">Cancel</button>
        </div>
      </form>
    </section>

    <!-- 现有条目渲染 -->
    <?php if (!empty($items)) { foreach ($items as $it) {
      $mid = 'M-' . htmlspecialchars($it['id']);
    ?>
      <article class="card"
               data-id="<?php echo $mid; ?>"
               data-expiry="<?php echo htmlspecialchars($it['expiry']); ?>"
               data-created="">
        <div class="card-head">
          <div class="card-title">FoodItem · <?php echo htmlspecialchars($it['name']); ?> (# <?php echo $mid; ?>)</div>
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
        <div class="row"><span class="label">Storage location:</span><span class="value" data-field="storage"><?php echo htmlspecialchars($it['storage'] ?? ''); ?></span></div>
        <div class="row"><span class="label">Description:</span><span class="value" data-field="desc"><?php echo htmlspecialchars($it['desc']); ?></span></div>
      </article>
    <?php } } else { ?>
      <p style="color:#666;margin:8px 0;">No items yet.</p>
    <?php } ?>
  </main>

</div>
