// Initialize variables
let yearlyChart, monthlyChart, categoryBarChart, dateRangeChart;
let currentFilter = "monthly"; // Default filter

// Sample data for demonstration
const sampleData = {
    monthly: {
        labels: ["June", "July", "August", "September", "October", "November"],
        donations: [12, 19, 15, 25, 22, 30],
        food: [45, 67, 52, 89, 78, 105],
    },
    yearly: {
        labels: ["2020", "2021", "2022", "2023", "2024", "2025"],
        donations: [45, 78, 92, 134, 156, 189],
        food: [234, 389, 456, 678, 734, 892],
    },
    categories: {
        labels: [
            "Produce",
            "Protein",
            "Dairy & Bakery",
            "Grains & Pantry",
            "Snacks & Beverages",
        ],
        data: [85, 62, 48, 73, 39],
    },
};

// Global data object to store fetched data
let reportData = {
    totalFoodSave: 307.5,
    totalDonations: 123,
    progress: 65,
    categories: {
        labels: [
            "Produce",
            "Protein",
            "Dairy & Bakery",
            "Grains & Pantry",
            "Snacks & Beverages",
        ],
        data: [85, 62, 48, 73, 39],
    },
    timeData: {
        labels: ["June", "July", "August", "September", "October", "November"],
        donations: [12, 19, 15, 25, 22, 30],
        food: [45, 67, 52, 89, 78, 105],
    },
};

// Main initialization function for page loader
function initReportPage() {
    console.log("🚀 Initializing Report Page...");

    // Check if Chart.js is loaded, if not load it first
    if (typeof Chart === "undefined") {
        console.log("📦 Loading Chart.js...");
        const script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/chart.js";
        script.onload = () => {
            console.log("✅ Chart.js loaded successfully");
            initializePageContent();
        };
        script.onerror = () => {
            console.error("❌ Failed to load Chart.js");
        };
        document.head.appendChild(script);
    } else {
        console.log("✅ Chart.js already loaded");
        initializePageContent();
    }
}

// Load sample data based on filter
function loadReportData(filter = "monthly", startDate = null, endDate = null) {
    console.log(`📊 Loading sample data for ${filter} view...`);

    switch (filter) {
        case "yearly":
            reportData.timeData = {
                labels: sampleData.yearly.labels,
                donations: sampleData.yearly.donations,
                food: sampleData.yearly.food,
            };
            break;
        case "category":
            reportData.timeData = {
                labels: [],
                donations: [],
                food: [],
            };
            break;
        case "dateRange":
            if (startDate && endDate) {
                // Generate sample data for date range
                const start = new Date(startDate);
                const end = new Date(endDate);
                const days =
                    Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
                const sampleDays = Math.min(days, 30); // Limit to 30 days for display

                reportData.timeData = {
                    labels: [],
                    donations: [],
                    food: [],
                };

                for (let i = 0; i < sampleDays; i++) {
                    const date = new Date(start);
                    date.setDate(date.getDate() + i);
                    reportData.timeData.labels.push(
                        date.toLocaleDateString("en-US", {
                            month: "short",
                            day: "numeric",
                        })
                    );
                    reportData.timeData.donations.push(
                        Math.floor(Math.random() * 10) + 1
                    );
                    reportData.timeData.food.push(
                        Math.floor(Math.random() * 30) + 10
                    );
                }
            }
            break;
        default: // monthly
            reportData.timeData = {
                labels: sampleData.monthly.labels,
                donations: sampleData.monthly.donations,
                food: sampleData.monthly.food,
            };
            break;
    }

    // Update charts and metrics
    if (monthlyChart && categoryBarChart && yearlyChart && dateRangeChart) {
        initializeMetrics();
        updateCharts();
        animateProgress();
        showChartSection(filter);
    } else {
        initializePageContent();
    }
}

// Initialize all page content
function initializePageContent() {
    initializeMetrics();
    initializeDatePicker();
    initializeChartFilter();
    initializeCharts();
    updateCharts(); // Update charts with current data
    animateProgress();
    showChartSection(currentFilter);
}

// Export to window for page loader
window.initReportPage = initReportPage;

// Initialize on page load (for standalone loading)
document.addEventListener("DOMContentLoaded", function () {
    if (document.getElementById("progressBar")) {
        initReportPage();
    }
});

// Initialize metrics display
function initializeMetrics() {
    animateValue("totalFoodSave", 0, reportData.totalFoodSave, 2000);
    animateValue("totalDonations", 0, reportData.totalDonations, 2000);
}

// Animate number counting
function animateValue(id, start, end, duration) {
    const element = document.getElementById(id);
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= end) {
            current = end;
            clearInterval(timer);
        }
        element.textContent =
            id === "totalFoodSave" ? current.toFixed(1) : Math.floor(current);
    }, 16);
}

// Initialize chart filter
function initializeChartFilter() {
    const chartFilter = document.getElementById("chartFilter");

    chartFilter.addEventListener("change", (e) => {
        currentFilter = e.target.value;
        console.log(`🔄 Switching to ${currentFilter} view`);

        // Reload data for the new filter
        if (currentFilter !== "dateRange") {
            loadReportData(currentFilter);
        } else {
            // Just show the date range section without loading data
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

// Animate progress bar
function animateProgress() {
    const progressBar = document.getElementById("progressBar");
    const targetProgress = reportData.progress;

    setTimeout(() => {
        progressBar.style.width = targetProgress + "%";
        progressBar.setAttribute("aria-valuenow", targetProgress);
        progressBar.querySelector("span").textContent = targetProgress + "%";
    }, 500);
}

// Initialize date picker
function initializeDatePicker() {
    const applyBtn = document.getElementById("applyDateRange");
    const startDateInput = document.getElementById("startDate");
    const endDateInput = document.getElementById("endDate");

    // Set default dates
    const today = new Date();
    const sixMonthsAgo = new Date(
        today.getFullYear(),
        today.getMonth() - 6,
        today.getDate()
    );

    startDateInput.valueAsDate = sixMonthsAgo;
    endDateInput.valueAsDate = today;

    // Apply date range
    applyBtn.addEventListener("click", () => {
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        if (startDate && endDate) {
            if (new Date(startDate) > new Date(endDate)) {
                alert("Start date must be before end date!");
                return;
            }

            console.log(`📅 Applying date range: ${startDate} to ${endDate}`);
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
    // Reload sample data
    loadReportData(currentFilter);
}
