<?php /* 本页从 localStorage.donationItems 读取数据并渲染 */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Donation List</title>
  <style>
    .topbar{display:flex;justify-content:space-between;gap:12px;padding:20px 32px;}
    .title{font-size:28px;font-weight:700;}
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
    <div class="top_botton">
      <a class="go_manage_btn" data-page="inventory">Back to Manage</a>
    </div>
  </header>

  <main class="content" id="list"></main>
</body>
</html>
