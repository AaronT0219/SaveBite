// Initialize variables
let yearlyChart, monthlyChart, categoryBarChart, dateRangeChart;
let currentFilter = "monthly"; // Default filter

// Global data object to store fetched data
let reportData = {
    totalFoodSave: 0,
    totalDonations: 0,
    progress: 0,
    categories: {
        labels: [],
        data: [],
    },
    timeData: {
        labels: [],
        donations: [],
        food: [],
    },
};

// Main initialization function for page loader
function initReportPage() {
    console.log("🚀 Initializing Report Page...");

    // Always destroy existing charts first to ensure clean state
    destroyAllCharts();

    // Restore saved filter state if exists
    const savedFilter = sessionStorage.getItem("reportFilter");
    if (savedFilter) {
        currentFilter = savedFilter;
        console.log(`📌 Restored filter: ${currentFilter}`);
    }

    // Check if Chart.js is loaded, if not load it first
    if (typeof Chart === "undefined") {
        console.log("📦 Loading Chart.js...");
        const script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/chart.js";
        script.onload = () => {
            console.log("✅ Chart.js loaded successfully");
            loadInitialData();
        };
        script.onerror = () => {
            console.error("❌ Failed to load Chart.js");
        };
        document.head.appendChild(script);
    } else {
        console.log("✅ Chart.js already loaded");
        loadInitialData();
    }
}

// Load initial data based on current filter
function loadInitialData() {
    if (currentFilter === "dateRange") {
        // Check if saved dates exist in session storage
        const savedStartDate = sessionStorage.getItem("reportStartDate");
        const savedEndDate = sessionStorage.getItem("reportEndDate");

        let startDate, endDate;

        if (savedStartDate && savedEndDate) {
            // Use saved dates
            startDate = savedStartDate;
            endDate = savedEndDate;
            console.log(
                `📅 Loading saved date range: ${startDate} to ${endDate}`
            );
        } else {
            // For date range, load default 7 days of data
            const today = new Date();
            const sevenDaysAgo = new Date(today);
            sevenDaysAgo.setDate(today.getDate() - 7);

            startDate = sevenDaysAgo.toISOString().split("T")[0];
            endDate = today.toISOString().split("T")[0];

            // Save default dates
            sessionStorage.setItem("reportStartDate", startDate);
            sessionStorage.setItem("reportEndDate", endDate);

            console.log(
                `📅 Loading default date range: ${startDate} to ${endDate}`
            );
        }

        loadReportData("dateRange", startDate, endDate);
    } else {
        loadReportData(currentFilter);
    }
}

// Fetch data from database
async function loadReportData(
    filter = "monthly",
    startDate = null,
    endDate = null
) {
    try {
        console.log("📊 Fetching report data from database...");

        let url = `../pages/report/get_report_data.php?filter=${filter}`;
        if (startDate && endDate) {
            url += `&start_date=${startDate}&end_date=${endDate}`;
        }

        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
            reportData = result.data;
            console.log("✅ Report data loaded successfully", reportData);

            // Always initialize page content to ensure everything is set up
            initializePageContent();
        } else {
            console.error("❌ Failed to fetch report data:", result.message);
            // Fall back to empty data
            reportData = {
                totalFoodSave: 0,
                totalDonations: 0,
                progress: 0,
                categories: { labels: ["No Data"], data: [0] },
                timeData: { labels: ["No Data"], donations: [0], food: [0] },
            };

            // Show empty state since there's no data
            initializePageContent();
        }
    } catch (error) {
        console.error("❌ Error fetching report data:", error);
        // Fall back to empty data
        reportData = {
            totalFoodSave: 0,
            totalDonations: 0,
            progress: 0,
            categories: { labels: ["No Data"], data: [0] },
            timeData: { labels: ["No Data"], donations: [0], food: [0] },
        };

        // Show empty state since there's no data
        initializePageContent();
    }
}

// Initialize all page content
function initializePageContent() {
    // Check if user has any data
    const hasData =
        reportData.totalFoodSave > 0 || reportData.totalDonations > 0;

    console.log(
        `📊 Data check: totalFoodSave=${reportData.totalFoodSave}, totalDonations=${reportData.totalDonations}, hasData=${hasData}`
    );

    if (!hasData) {
        // Show empty state message
        showEmptyState();
    } else {
        // Show charts and initialize everything
        hideEmptyState();
        initializeMetrics();
        initializeDatePicker();
        initializeChartFilter();
        initializeCharts();
        updateCharts(); // Update charts with current data
        showChartSection(currentFilter);
    }
}

// Show empty state message
function showEmptyState() {
    console.log("🔍 Showing empty state message");

    const emptyState = document.getElementById("emptyStateMessage");
    const chartsContent = document.getElementById("chartsContent");
    const filterControls = document.querySelector(
        ".d-flex.justify-content-center"
    );
    const metricsRow = document.querySelector(".row.g-4.mb-4");

    if (emptyState) {
        emptyState.style.display = "block";
        console.log("✅ Empty state message shown");
    }
    if (chartsContent) chartsContent.style.display = "none";
    if (filterControls) filterControls.style.display = "none";
    if (metricsRow) metricsRow.style.display = "none";

    // Reinitialize Lucide icons for the empty state
    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }
}

// Hide empty state message
function hideEmptyState() {
    console.log("🔍 Hiding empty state message");

    const emptyState = document.getElementById("emptyStateMessage");
    const chartsContent = document.getElementById("chartsContent");
    const filterControls = document.querySelector(
        ".d-flex.justify-content-center"
    );
    const metricsRow = document.querySelector(".row.g-4.mb-4");

    if (emptyState) emptyState.style.display = "none";
    if (chartsContent) {
        chartsContent.style.display = "block";
        console.log("✅ Charts content shown");
    }
    if (filterControls) filterControls.style.display = "flex";
    if (metricsRow) metricsRow.style.display = "flex";
}

// Export to window for page loader
window.initReportPage = initReportPage;

// Export refresh function for when page is loaded via navigation
window.refreshReportPage = function () {
    console.log("🔄 Refreshing report page via navigation...");
    if (document.getElementById("emptyStateMessage")) {
        // Destroy existing charts first
        destroyAllCharts();
        // Reinitialize everything
        initReportPage();
    }
};

// Initialize on page load (for standalone loading)
document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("emptyStateMessage")) {
        initReportPage();
    }
});

// Reinitialize when page becomes visible (e.g., when navigating back)
document.addEventListener("visibilitychange", function () {
    if (!document.hidden && document.getElementById("emptyStateMessage")) {
        console.log("🔄 Page visible again, reinitializing...");
        // Destroy existing charts first
        destroyAllCharts();
        // Reinitialize everything
        initReportPage();
    }
});

// Initialize metrics display
function initializeMetrics() {
    // Get current values or start from 0
    const currentFoodSave =
        parseFloat(document.getElementById("totalFoodSave")?.textContent) || 0;
    const currentDonations =
        parseInt(document.getElementById("totalDonations")?.textContent) || 0;

    animateValue(
        "totalFoodSave",
        currentFoodSave,
        reportData.totalFoodSave,
        1000
    );
    animateValue(
        "totalDonations",
        currentDonations,
        reportData.totalDonations,
        1000
    );
}

// Animate number counting
function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    if (!element) return;

    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

// Initialize chart filter
function initializeChartFilter() {
    const chartFilter = document.getElementById("chartFilter");

    // Remove existing event listener by cloning and replacing the element
    const newChartFilter = chartFilter.cloneNode(true);
    chartFilter.parentNode.replaceChild(newChartFilter, chartFilter);

    // Restore saved filter in dropdown AFTER replacing
    if (currentFilter) {
        newChartFilter.value = currentFilter;
    }

    newChartFilter.addEventListener("change", (e) => {
        currentFilter = e.target.value;
        console.log(`🔄 Switching to ${currentFilter} view`);

        // Save filter to session storage
        sessionStorage.setItem("reportFilter", currentFilter);

        // Reload data for the new filter
        if (currentFilter !== "dateRange") {
            loadReportData(currentFilter);
        } else {
            // For date range, check if saved dates exist
            const savedStartDate = sessionStorage.getItem("reportStartDate");
            const savedEndDate = sessionStorage.getItem("reportEndDate");

            let startDate, endDate;

            if (savedStartDate && savedEndDate) {
                // Use saved dates
                startDate = savedStartDate;
                endDate = savedEndDate;
                console.log(
                    `📅 Using saved date range: ${startDate} to ${endDate}`
                );
            } else {
                // Use default 7 days of data
                const today = new Date();
                const sevenDaysAgo = new Date(today);
                sevenDaysAgo.setDate(today.getDate() - 7);

                startDate = sevenDaysAgo.toISOString().split("T")[0];
                endDate = today.toISOString().split("T")[0];

                // Save default dates
                sessionStorage.setItem("reportStartDate", startDate);
                sessionStorage.setItem("reportEndDate", endDate);

                console.log(
                    `📅 Loading default date range: ${startDate} to ${endDate}`
                );
            }

            loadReportData("dateRange", startDate, endDate);
            showChartSection(currentFilter);
        }
    });
}

// Show/hide chart sections based on filter
function showChartSection(filter) {
    // Toggle Date Range controls
    const dateControls = document.getElementById("dateRangeControls");
    if (dateControls) {
        if (filter === "dateRange") {
            dateControls.classList.add("show");
        } else {
            dateControls.classList.remove("show");
        }
    }

    // Hide all sections
    document.getElementById("yearlySection").style.display = "none";
    document.getElementById("monthlySection").style.display = "none";
    document.getElementById("categorySection").style.display = "none";
    document.getElementById("dateRangeSection").style.display = "none";

    // Show selected section
    switch (filter) {
        case "yearly":
            document.getElementById("yearlySection").style.display = "block";
            break;
        case "monthly":
            document.getElementById("monthlySection").style.display = "block";
            break;
        case "category":
            document.getElementById("categorySection").style.display = "block";
            break;
        case "dateRange":
            document.getElementById("dateRangeSection").style.display = "block";
            break;
    }
}

// Initialize date picker
function initializeDatePicker() {
    const applyBtn = document.getElementById("applyDateRange");
    const startDateInput = document.getElementById("startDate");
    const endDateInput = document.getElementById("endDate");

    // Check if saved dates exist in session storage
    const savedStartDate = sessionStorage.getItem("reportStartDate");
    const savedEndDate = sessionStorage.getItem("reportEndDate");

    if (savedStartDate && savedEndDate) {
        // Use saved dates
        startDateInput.value = savedStartDate;
        endDateInput.value = savedEndDate;
        console.log(
            `📅 Restored saved dates: ${savedStartDate} to ${savedEndDate}`
        );
    } else {
        // Set default dates - 1 week from current day
        const today = new Date();
        const oneWeekAgo = new Date(today);
        oneWeekAgo.setDate(today.getDate() - 7);

        const defaultStartDate = oneWeekAgo.toISOString().split("T")[0];
        const defaultEndDate = today.toISOString().split("T")[0];

        startDateInput.value = defaultStartDate;
        endDateInput.value = defaultEndDate;

        // Save default dates to session storage
        sessionStorage.setItem("reportStartDate", defaultStartDate);
        sessionStorage.setItem("reportEndDate", defaultEndDate);
    }

    // Remove existing event listener by cloning and replacing the button
    const newApplyBtn = applyBtn.cloneNode(true);
    applyBtn.parentNode.replaceChild(newApplyBtn, applyBtn);

    // Apply date range
    newApplyBtn.addEventListener("click", () => {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (startDate && endDate) {
            if (new Date(startDate) > new Date(endDate)) {
                alert("Start date must be before end date!");
                return;
            }

            console.log(`📅 Applying date range: ${startDate} to ${endDate}`);

            // Save dates to session storage
            sessionStorage.setItem("reportStartDate", startDate);
            sessionStorage.setItem("reportEndDate", endDate);

            // Fetch data for the date range
            loadReportData("dateRange", startDate, endDate);
        } else {
            alert("Please select both start and end dates!");
        }
    });
}

// Initialize all charts
function initializeCharts() {
    // Destroy existing charts if they exist
    destroyAllCharts();

    createMonthlyChart();
    createCategoryBarChart();
    createYearlyChart();
    createDateRangeChart();
}

// Destroy all existing charts
function destroyAllCharts() {
    if (monthlyChart) {
        monthlyChart.destroy();
        monthlyChart = null;
    }
    if (categoryBarChart) {
        categoryBarChart.destroy();
        categoryBarChart = null;
    }
    if (yearlyChart) {
        yearlyChart.destroy();
        yearlyChart = null;
    }
    if (dateRangeChart) {
        dateRangeChart.destroy();
        dateRangeChart = null;
    }
}

// Create Donations Over Time Chart
function createDonationsChart() {
    const ctx = document.getElementById("donationsChart").getContext("2d");
    donationsChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: sampleData.donationsOverTime.labels,
            datasets: [
                {
                    label: "Donations",
                    data: sampleData.donationsOverTime.data,
                    borderColor: "#212529",
                    backgroundColor: "rgba(33, 37, 41, 0.1)",
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: "#212529",
                    pointRadius: 5,
                    pointHoverRadius: 7,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                },
                tooltip: {
                    backgroundColor: "rgba(33, 37, 41, 0.9)",
                    padding: 12,
                    titleColor: "#fff",
                    bodyColor: "#fff",
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                    },
                },
            },
        },
    });
}

// Create Monthly Chart
function createMonthlyChart() {
    const ctx = document.getElementById("monthlyChart").getContext("2d");
    monthlyChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: reportData.timeData.labels,
            datasets: [
                {
                    label: "Donations Made",
                    data: reportData.timeData.donations,
                    backgroundColor: "rgba(33, 37, 41, 0.8)",
                    borderColor: "#212529",
                    borderWidth: 1,
                    yAxisID: "y",
                },
                {
                    label: "Food Save",
                    data: reportData.timeData.food,
                    backgroundColor: "rgba(40, 167, 69, 0.8)",
                    borderColor: "#28a745",
                    borderWidth: 1,
                    yAxisID: "y1",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                },
                tooltip: {
                    backgroundColor: "rgba(33, 37, 41, 0.9)",
                    padding: 12,
                    titleColor: "#fff",
                    bodyColor: "#fff",
                },
            },
            scales: {
                y: {
                    type: "linear",
                    display: true,
                    position: "left",
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                    title: {
                        display: true,
                        text: "Donations Made",
                    },
                },
                y1: {
                    type: "linear",
                    display: true,
                    position: "right",
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    title: {
                        display: true,
                        text: "Food Save",
                    },
                },
            },
        },
    });
}

// Create Category Bar Chart
function createCategoryBarChart() {
    const ctx = document.getElementById("categoryBarChart").getContext("2d");

    // Define colors for different categories
    const categoryColors = {
        Produce: "#28a745",
        Protein: "#dc3545",
        "Dairy & Bakery": "#ffc107",
        "Grains & Pantry": "#17a2b8",
        "Snacks & Beverages": "#fd7e14",
    };

    const backgroundColors = reportData.categories.labels.map(
        (label) => categoryColors[label] || "#6c757d"
    );

    categoryBarChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: reportData.categories.labels,
            datasets: [
                {
                    label: "Items by Category",
                    data: reportData.categories.data,
                    backgroundColor: backgroundColors,
                    borderWidth: 2,
                    borderColor: "#fff",
                    borderRadius: 8,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: "y",
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: "rgba(33, 37, 41, 0.9)",
                    padding: 12,
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                },
            },
        },
    });
}

// Create Yearly Chart
function createYearlyChart() {
    const ctx = document.getElementById("yearlyChart").getContext("2d");
    yearlyChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: reportData.timeData.labels,
            datasets: [
                {
                    label: "Donations Made",
                    data: reportData.timeData.donations,
                    backgroundColor: "rgba(33, 37, 41, 0.8)",
                    borderColor: "#212529",
                    borderWidth: 1,
                    yAxisID: "y",
                },
                {
                    label: "Food Save",
                    data: reportData.timeData.food,
                    backgroundColor: "rgba(40, 167, 69, 0.8)",
                    borderColor: "#28a745",
                    borderWidth: 1,
                    yAxisID: "y1",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                },
                tooltip: {
                    backgroundColor: "rgba(33, 37, 41, 0.9)",
                    padding: 12,
                },
            },
            scales: {
                y: {
                    type: "linear",
                    display: true,
                    position: "left",
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                    title: {
                        display: true,
                        text: "Donations Made",
                    },
                },
                y1: {
                    type: "linear",
                    display: true,
                    position: "right",
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    title: {
                        display: true,
                        text: "Food Save",
                    },
                },
            },
        },
    });
}

// Create Date Range Chart
function createDateRangeChart() {
    const ctx = document.getElementById("dateRangeChart").getContext("2d");
    dateRangeChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: reportData.timeData.labels,
            datasets: [
                {
                    label: "Donations Made",
                    data: reportData.timeData.donations,
                    backgroundColor: "rgba(33, 37, 41, 0.8)",
                    borderColor: "#212529",
                    borderWidth: 1,
                    yAxisID: "y",
                },
                {
                    label: "Food Save",
                    data: reportData.timeData.food,
                    backgroundColor: "rgba(40, 167, 69, 0.8)",
                    borderColor: "#28a745",
                    borderWidth: 1,
                    yAxisID: "y1",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: "top",
                },
                tooltip: {
                    backgroundColor: "rgba(33, 37, 41, 0.9)",
                    padding: 12,
                    titleColor: "#fff",
                    bodyColor: "#fff",
                },
            },
            scales: {
                y: {
                    type: "linear",
                    display: true,
                    position: "left",
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                    title: {
                        display: true,
                        text: "Donations Made",
                    },
                },
                y1: {
                    type: "linear",
                    display: true,
                    position: "right",
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    title: {
                        display: true,
                        text: "Food Save",
                    },
                },
            },
        },
    });
}

// Update all charts with current data
function updateCharts() {
    console.log("🔄 Updating all charts with new data");

    // Verify canvas elements exist before updating
    if (!document.getElementById("monthlyChart")) {
        console.warn("⚠️ Chart canvases not found in DOM");
        return;
    }

    // Update Monthly Chart
    if (monthlyChart) {
        monthlyChart.data.labels = reportData.timeData.labels;
        monthlyChart.data.datasets[0].data = reportData.timeData.donations;
        monthlyChart.data.datasets[1].data = reportData.timeData.food;
        monthlyChart.update();
    }

    // Update Category Chart
    if (categoryBarChart) {
        categoryBarChart.data.labels = reportData.categories.labels;
        categoryBarChart.data.datasets[0].data = reportData.categories.data;

        // Update colors for new categories
        const categoryColors = {
            Produce: "#28a745",
            Protein: "#dc3545",
            "Dairy & Bakery": "#ffc107",
            "Grains & Pantry": "#17a2b8",
            "Snacks & Beverages": "#fd7e14",
        };

        const backgroundColors = reportData.categories.labels.map(
            (label) => categoryColors[label] || "#6c757d"
        );
        categoryBarChart.data.datasets[0].backgroundColor = backgroundColors;
        categoryBarChart.update();
    }

    // Update Yearly Chart
    if (yearlyChart) {
        yearlyChart.data.labels = reportData.timeData.labels;
        yearlyChart.data.datasets[0].data = reportData.timeData.donations;
        yearlyChart.data.datasets[1].data = reportData.timeData.food;
        yearlyChart.update();
    }

    // Update Date Range Chart
    if (dateRangeChart) {
        dateRangeChart.data.labels = reportData.timeData.labels;
        dateRangeChart.data.datasets[0].data = reportData.timeData.donations;
        dateRangeChart.data.datasets[1].data = reportData.timeData.food;
        dateRangeChart.update();
    }
}

// Export function to refresh all data
function refreshReportData() {
    console.log("🔄 Refreshing all report data...");
    // Reload data from the backend
    loadReportData(currentFilter);
}
