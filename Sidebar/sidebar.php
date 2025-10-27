<link rel="stylesheet" href="../Sidebar/sidebar.css">
<script src="../Sidebar/sidebar.js"></script>

<div id="sidebar" class="sidebar d-flex flex-column shadow" style="height: 100vh;">
    <div class="sidebar-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-secondary" style="height: 55px;">
        <img src="../assets/images/logo.png" alt="Logo" class="logo" style="height: 35px;">
        <i data-lucide="menu" class="menu-icon" onclick="toggleSidebar()"></i>
    </div>

    <div class="sidebar-content py-3 fs-4 fw-medium d-flex flex-column flex-grow-1 overflow-auto">
        <div class="mb-auto">
            <a class="nav-link" data-page="inventory" data-tooltip="Food Inventory">
                <i data-lucide="shopping-cart" class="me-3 icon"></i>
                <span class="nav-text">Food Inventory</span>
            </a>
            <a class="nav-link" data-page="browse" data-tooltip="Browse Food Items">
                <i data-lucide="search" class="me-3 icon"></i>
                <span class="nav-text">Browse Food Items</span>
            </a>
            <a class="nav-link" data-page="meal" data-tooltip="Plan Weekly Meals">
                <i data-lucide="calendar" class="me-3 icon"></i>
                <span class="nav-text">Plan Weekly Meals</span>
            </a>
            <a class="nav-link" data-page="report" data-tooltip="Track and Report">
                <i data-lucide="chart-no-axes-column" class="me-3 icon"></i>
                <span class="nav-text">Track and Report</span>
            </a>
            <a class="nav-link" data-page="notification" data-tooltip="Notifications">
                <i data-lucide="bell" class="me-3 icon"></i>
                <span class="nav-text">Notifications</span>
            </a>
        </div>
        <div>
            <a class="nav-link" data-page="settings" data-tooltip="Settings">
                <i data-lucide="settings" class="me-3 icon"></i>
                <span class="nav-text">Settings</span>
            </a>
            <a href="../Logout/logout.php" class="nav-link" data-tooltip="Logout">
                <i data-lucide="log-out" class="me-3 icon"></i>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>
</div>
