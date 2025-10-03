<div class="side-nav d-flex flex-column" style="width:80px; height: 100vh;">
    <a href="#" role="button" data-lucide="menu" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-menu" aria-controls="offcanvas-menu" class="nav-icon m-4 align-self-center"></a>
    <hr class="m-0 mx-2">

    <hr class="m-0 mx-2 mt-auto">
    <div class="align-self-center d-flex flex-column gap-4 m-4">
        <a href="#" role="button" data-lucide="settings" class="nav-icon"></a>
        <a href="#" role="button" data-lucide="log-out" class="nav-icon" id="nav-logout-btn"></a>
    </div>
</div>

<!-- offcanvas -->
<div class="side-nav shadow offcanvas offcanvas-start d-flex flex-column" tabindex="-1" id="offcanvas-menu" style="width: 300px; height: 100vh;">
    <div class="d-flex justify-content-between align-items-end p-3">
        <a href="#" role="button" data-lucide="circle-user-round" class="nav-icon"></a>
        <img src="../Img/savebite_icon&title.png" width="150px">
        <a href="#" role="button" data-lucide="arrow-left-to-line" data-bs-dismiss="offcanvas" class="nav-icon"></a>
    </div>

    <div class="p-4 pt-0 fs-4 fw-medium d-flex flex-column flex-grow-1 gap-3 overflow-auto">
        <hr class="m-0">
        <a href="#FI" role="button" class="nav-link"><span data-lucide="shopping-basket" class="me-3"></span>Food Inventory</a>
        <a href="#BFI" role="button" class="nav-link"><span data-lucide="file-search" class="me-3"></span>Browse Food Items</a>
        <a href="#PWM" role="button" class="nav-link"><span data-lucide="notebook-pen" class="me-3"></span>Plan Weekly Meals</a>
        <a href="#T&R" role="button" class="nav-link"><span data-lucide="chart-pie" class="me-3"></span>Track and Report</a>
        <a href="#N" role="button" class="nav-link mb-auto"><span data-lucide="bell" class="me-3"></span>Notifications</a>
        <hr class="m-0">
        <a href="#" role="button" class="nav-link border-topborder-bottom"><span data-lucide="settings" class="nav-icon me-3"></span>Settings</a>
        <a href="#" role="button" class="nav-link" id="nav-logout-btn"><span data-lucide="log-out" class="nav-icon me-3"></span>Logout</a>
    </div>
</div>
<script src="../Sidebar/sidebar.js"></script>
