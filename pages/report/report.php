<?php 

// session_start();

// echo '<pre>';
// print_r($_SESSION);
// echo '</pre>';

?>

<link rel="stylesheet" href="../pages/report/report.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid p-4">
    <div class="d-flex mb-2 py-3 px-4 bg-light rounded shadow">
        <h1 class="fw-bold">Track and Report</h1>
    </div>

    <div class="mt-5 px-4">
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="metric-card shadow-sm">
                    <div class="metric-icon">
                        <i data-lucide="heart"></i>
                    </div>
                    <div class="metric-content">
                        <h5 class="metric-label">Total Food Save</h5>
                        <h2 class="metric-value" id="totalFoodSave">0</h2>
                        <p class="metric-unit">foods</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="metric-card shadow-sm">
                    <div class="metric-icon">
                        <i data-lucide="gift"></i>
                    </div>
                    <div class="metric-content">
                        <h5 class="metric-label">Total Donations Made</h5>
                        <h2 class="metric-value" id="totalDonations">0</h2>
                        <p class="metric-unit">donations</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center align-items-center gap-3 my-4 flex-wrap">
            <div class="filter-group">
                <label class="form-label fw-bold mb-2">View By:</label>
                <select class="form-select form-select-lg" id="chartFilter" style="min-width: 200px;">
                    <option value="yearly">Yearly</option>
                    <option value="monthly" selected>Monthly</option>
                    <option value="category">By Category</option>
                    <option value="dateRange">Date Range</option>
                </select>
            </div>
            <div id="dateRangeControls" class="align-items-center gap-3 flex-wrap" style="display: none;">
                <div class="filter-group">
                    <label class="form-label fw-bold mb-2">From:</label>
                    <input type="date" class="form-control form-control-lg" id="startDate" style="min-width: 180px;">
                </div>
                <div class="filter-group">
                    <label class="form-label fw-bold mb-2">To:</label>
                    <input type="date" class="form-control form-control-lg" id="endDate" style="min-width: 180px;">
                </div>
                <div class="filter-group align-self-end">
                    <button class="btn btn-dark btn-lg" id="applyDateRange">
                        <i class="bi bi-filter me-2"></i>
                        Apply Filter
                    </button>
                </div>
            </div>
        </div>

        <div class="charts-container shadow-sm p-4 bg-white rounded" style="min-height: 425px;">
            <!-- Empty State Message -->
            <div id="emptyStateMessage" class="text-center py-5" style="display: none;">
                <div class="mb-4">
                    <i data-lucide="bar-chart-3" style="width: 80px; height: 80px; color: #6c757d;"></i>
                </div>
                <h3 class="fw-bold mb-3">No Data Yet</h3>
                <p class="text-muted fs-5 mb-4">Start your journey to reduce food waste!</p>
                <p class="text-muted mb-4">
                    Begin logging food items in your inventory and making donations<br>
                    to track your progress and see your impact.
                </p>
                <div class="d-flex gap-3 justify-content-center">
                    <a href="#inventory" class="btn btn-dark btn-lg" onclick="loadPage(event, '../pages/inventory/inventory.php', 'inventory')">
                        <i data-lucide="package" class="me-2" style="width: 20px; height: 20px;"></i>
                        Add Food Items
                    </a>
                    <a href="#donationList" class="btn btn-outline-dark btn-lg" onclick="loadPage(event, '../pages/donationList/donationList.php', 'donationList')">
                        <i data-lucide="heart" class="me-2" style="width: 20px; height: 20px;"></i>
                        Make a Donation
                    </a>
                </div>
            </div>

            <div class="row g-4" id="chartsContent">
                <!-- Yearly Chart -->
                <div class="col-12 chart-section" id="yearlySection" style="display: none;">
                    <div class="chart-wrapper">
                        <h5 class="text-center mb-3">Yearly Overview</h5>
                        <canvas id="yearlyChart"></canvas>
                    </div>
                </div>

                <!-- Monthly Charts -->
                <div class="chart-section" id="monthlySection">
                    <div class="col-12">
                        <div class="chart-wrapper">
                            <h5 class="text-center mb-3">Monthly Overview</h5>
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Category Chart -->
                <div class="col-12 chart-section" id="categorySection" style="display: none;">
                    <div class="chart-wrapper">
                        <h5 class="text-center mb-3">Food Items by Category</h5>
                        <canvas id="categoryBarChart"></canvas>
                    </div>
                </div>

                <!-- Date Range Chart -->
                <div class="col-12 chart-section" id="dateRangeSection" style="display: none;">
                    <div class="chart-wrapper">
                        <h5 class="text-center mb-3">Date Range Overview</h5>
                        <canvas id="dateRangeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
