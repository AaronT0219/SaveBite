// Initialize variables
let yearlyChart, monthlyChart, weeklyChart, categoryBarChart, dateRangeChart;
let currentFilter = "monthly"; // Default filter

// Sample data (replace with actual data from backend)
const sampleData = {
    totalFoodSave: 245,
    totalDonations: 87,
    progress: 65,
    categories: {
        labels: ["Vegetables", "Fruits", "Dairy", "Bakery", "Meat", "Others"],
        data: [30, 25, 15, 12, 10, 8],
    },
    monthly: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun"],
        donations: [12, 19, 15, 25, 22, 30],
        quantity: [35, 48, 42, 55, 51, 62],
    },
    yearly: {
        labels: ["2020", "2021", "2022", "2023", "2024", "2025"],
        donations: [145, 189, 234, 298, 356, 412],
        quantity: [543, 678, 812, 945, 1102, 1289],
    },
    weekly: {
        labels: ["Week 1", "Week 2", "Week 3", "Week 4"],
        donations: [8, 12, 10, 15],
        quantity: [28, 36, 31, 42],
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

// Initialize all page content
function initializePageContent() {
    initializeMetrics();
    initializeDatePicker();
    initializeChartFilter();
    initializeCharts();
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
    animateValue("totalFoodSave", 0, sampleData.totalFoodSave, 2000);
    animateValue("totalDonations", 0, sampleData.totalDonations, 2000);
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
        showChartSection(currentFilter);
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
    document.getElementById("weeklySection").style.display = "none";
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
        case "weekly":
            document.getElementById("weeklySection").style.display = "block";
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
    const targetProgress = sampleData.progress;

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

            // Update charts with filtered data
            updateChartsWithDateRange(startDate, endDate);
        } else {
            alert("Please select both start and end dates!");
        }
    });
}

// Initialize all charts
function initializeCharts() {
    createMonthlyChart();
    createCategoryBarChart();
    createYearlyChart();
    createWeeklyChart();
    createDateRangeChart();
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
            labels: sampleData.monthly.labels,
            datasets: [
                {
                    label: "Total Donations",
                    data: sampleData.monthly.donations,
                    backgroundColor: "rgba(33, 37, 41, 0.8)",
                    borderColor: "#212529",
                    borderWidth: 1,
                    yAxisID: "y",
                },
                {
                    label: "Quantity",
                    data: sampleData.monthly.quantity,
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
                        text: "Donations",
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
                        text: "Quantity",
                    },
                },
            },
        },
    });
}

// Create Category Bar Chart
function createCategoryBarChart() {
    const ctx = document.getElementById("categoryBarChart").getContext("2d");
    categoryBarChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: sampleData.categories.labels,
            datasets: [
                {
                    label: "Items by Category",
                    data: sampleData.categories.data,
                    backgroundColor: [
                        "#28a745",
                        "#ffc107",
                        "#17a2b8",
                        "#fd7e14",
                        "#dc3545",
                        "#6c757d",
                    ],
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
            labels: sampleData.yearly.labels,
            datasets: [
                {
                    label: "Total Donations",
                    data: sampleData.yearly.donations,
                    backgroundColor: "rgba(33, 37, 41, 0.8)",
                    borderColor: "#212529",
                    borderWidth: 1,
                    yAxisID: "y",
                },
                {
                    label: "Quantity",
                    data: sampleData.yearly.quantity,
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
                        text: "Donations",
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
                        text: "Quantity",
                    },
                },
            },
        },
    });
}

// Create Weekly Chart
function createWeeklyChart() {
    const ctx = document.getElementById("weeklyChart").getContext("2d");
    weeklyChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: sampleData.weekly.labels,
            datasets: [
                {
                    label: "Total Donations",
                    data: sampleData.weekly.donations,
                    backgroundColor: "rgba(33, 37, 41, 0.8)",
                    borderColor: "#212529",
                    borderWidth: 1,
                    yAxisID: "y",
                },
                {
                    label: "Quantity",
                    data: sampleData.weekly.quantity,
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
                        text: "Donations",
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
                        text: "Quantity",
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
            labels: ["No Data"],
            datasets: [
                {
                    label: "Total Donations",
                    data: [0],
                    backgroundColor: "rgba(33, 37, 41, 0.8)",
                    borderColor: "#212529",
                    borderWidth: 1,
                    yAxisID: "y",
                },
                {
                    label: "Quantity",
                    data: [0],
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
                        text: "Donations",
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
                        text: "Quantity",
                    },
                },
            },
        },
    });
}

// Update charts with date range
function updateChartsWithDateRange(startDate, endDate) {
    console.log("Filtering data from", startDate, "to", endDate);

    // In a real application, you would fetch filtered data from the backend here
    // For now, we'll simulate data based on the date range

    if (currentFilter === "dateRange" && dateRangeChart) {
        // Generate sample data for the date range
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        // Create labels based on date range (simplified - showing months or days)
        const labels = [];
        const donations = [];
        const quantities = [];

        if (diffDays > 90) {
            // Show monthly data for longer ranges
            const months = [
                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun",
                "Jul",
                "Aug",
                "Sep",
                "Oct",
                "Nov",
                "Dec",
            ];
            const startMonth = start.getMonth();
            const endMonth = end.getMonth();
            const monthsInRange =
                endMonth >= startMonth
                    ? endMonth - startMonth + 1
                    : 12 - startMonth + endMonth + 1;

            for (let i = 0; i < Math.min(monthsInRange, 6); i++) {
                const monthIndex = (startMonth + i) % 12;
                labels.push(months[monthIndex]);
                donations.push(Math.floor(Math.random() * 30) + 10);
                quantities.push(Math.floor(Math.random() * 50) + 20);
            }
        } else {
            // Show weekly data for shorter ranges
            const weeks = Math.min(Math.ceil(diffDays / 7), 8);
            for (let i = 1; i <= weeks; i++) {
                labels.push(`Week ${i}`);
                donations.push(Math.floor(Math.random() * 15) + 5);
                quantities.push(Math.floor(Math.random() * 25) + 10);
            }
        }

        dateRangeChart.data.labels = labels;
        dateRangeChart.data.datasets[0].data = donations;
        dateRangeChart.data.datasets[1].data = quantities;
        dateRangeChart.update();
    } else if (currentFilter === "monthly" && monthlyChart) {
        monthlyChart.data.datasets[0].data = sampleData.monthly.donations.map(
            (val) => val + Math.floor(Math.random() * 5)
        );
        monthlyChart.data.datasets[1].data = sampleData.monthly.quantity.map(
            (val) => val + Math.floor(Math.random() * 10)
        );
        monthlyChart.update();
    } else if (currentFilter === "yearly" && yearlyChart) {
        yearlyChart.update();
    } else if (currentFilter === "weekly" && weeklyChart) {
        weeklyChart.update();
    }
}

// Export function to refresh all data
function refreshReportData() {
    // This function can be called to refresh all data from the backend
    initializeMetrics();
    animateProgress();

    // Update all charts
    if (monthlyChart) monthlyChart.update();
    if (categoryBarChart) categoryBarChart.update();
    if (yearlyChart) yearlyChart.update();
    if (weeklyChart) weeklyChart.update();
    if (dateRangeChart) dateRangeChart.update();
}