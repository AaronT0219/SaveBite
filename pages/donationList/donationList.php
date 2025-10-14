<?php /* /SaveBite/pages/donationList/donationList.php */ ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Donation List</title>
  <style>
    .topbar{display:flex;justify-content:space-between;gap:12px;padding:20px 32px;}
    .title{font-size:28px;font-weight:700;margin:0;}
    .top_botton{display:flex;align-items:center;gap:16px;}
    .go_manage_btn{padding:10px 14px;font-size:14px;border:1px solid #bbb;background:#ddd;border-radius:25px;text-decoration:none;color:#222;cursor:pointer;}
    .content{width:100%;max-width:820px;margin:0 auto;padding:16px;}
    .card{border:3px solid #222;border-radius:25px;padding:40px;margin-bottom:18px;background:#fff;}
    .card-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;}
    .mini{margin-left:4px;}
    .row{display:grid;grid-template-columns:160px 1fr;gap:8px;padding:6px 0;border-top:1px dashed #eee;}
    .row:first-of-type{border-top:none;}
    .value{color:#4d524f;}
    .row input,.row textarea,.row select{width:100%;padding:6px 8px;border:1px solid #bbb;border-radius:6px;background:#eee;box-sizing:border-box;}
    .empty-hint{color:#666;margin:8px 0;}
    .card-title{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .card-title .label{ font-weight:600; }
    .card-title .value{ display:inline-block; }
    .card-title .value .edit-input{ width:260px; max-width:60vw; }
    .card-title .mini-id{ margin-left:auto; color:#666; font-size:90%; }
  </style>
</head>
<body>
  <header class="topbar">
    <h1 class="title">Donation List</h1>
    <div class="top_botton">
      <a class="go_manage_btn" href="/SaveBite/templates/base.php?page=inventory">Back to Manage</a>
    </div>
  </header>

  <main class="content" id="donation-list"></main>

  <script src="/SaveBite/pages/donationList/donationList.js"></script>
  <script> if (window.initDonationListPage) window.initDonationListPage(); </script>
</body>
</html>
