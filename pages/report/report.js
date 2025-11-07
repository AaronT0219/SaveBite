// =============== globals ===============
let yearlyChart, monthlyChart, weeklyChart, categoryBarChart, dateRangeChart;
let currentFilter = "monthly"; // default

// fallback sample (首次渲染骨架，随后用后端数据覆盖)
const sampleData = {
  totalFoodSave: 0,
  totalDonations: 0,
  progress: 0,
  categories: { labels: ["No Data"], data: [0] },
  monthly:   { labels: ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"], donations: Array(12).fill(0), quantity: Array(12).fill(0) },
  yearly:    { labels: [], donations: [], quantity: [] },
  weekly:    { labels: ["Week 1","Week 2","Week 3","Week 4"], donations:[0,0,0,0], quantity:[0,0,0,0] },
};

// 入口
function initReportPage() {
  // Chart.js 若未加载则先加载
  if (typeof Chart === "undefined") {
    const s = document.createElement("script");
    s.src = "https://cdn.jsdelivr.net/npm/chart.js";
    s.onload = initializePageContent;
    s.onerror = () => console.error("Chart.js failed to load");
    document.head.appendChild(s);
  } else {
    initializePageContent();
  }
}
window.initReportPage = initReportPage;

document.addEventListener("DOMContentLoaded", () => {
  if (document.getElementById("progressBar")) initReportPage();
});

// 初始化
function initializePageContent() {
  initializeMetrics();
  initializeDatePicker();
  initializeChartFilter();
  initializeCharts();
  animateProgress();
  showChartSection(currentFilter);
  // 拉后端数据覆盖
  refreshFromBackend();
}

/* ---------- UI 数值 ---------- */
function initializeMetrics() {
  animateValue("totalFoodSave", 0, sampleData.totalFoodSave, 800);
  animateValue("totalDonations", 0, sampleData.totalDonations, 800);
}
function animateValue(id, start, end, duration) {
  const el = document.getElementById(id);
  const range = end - start;
  const step  = range / (duration / 16);
  let cur = start;
  const t = setInterval(() => {
    cur += step;
    if ((step >= 0 && cur >= end) || (step < 0 && cur <= end)) { cur = end; clearInterval(t); }
    el.textContent = id === "totalFoodSave" ? Math.floor(cur) : Math.floor(cur);
  }, 16);
}
function animateProgress() {
  const bar = document.getElementById("progressBar");
  const target = sampleData.progress || 0;
  setTimeout(() => {
    bar.style.width = target + "%";
    bar.setAttribute("aria-valuenow", target);
    bar.querySelector("span").textContent = target + "%";
  }, 300);
}

/* ---------- filter ---------- */
function initializeChartFilter() {
  const sel = document.getElementById("chartFilter");
  sel.addEventListener("change", (e) => {
    currentFilter = e.target.value;
    showChartSection(currentFilter);
    refreshFromBackend();
  });
}
function showChartSection(filter) {
  const ids = ["yearlySection","monthlySection","weeklySection","categorySection","dateRangeSection"];
  ids.forEach(id => document.getElementById(id).style.display = "none");
  const dateControls = document.getElementById("dateRangeControls");
  if (filter === "dateRange") dateControls.classList.add("show"); else dateControls.classList.remove("show");
  const map = {
    yearly: "yearlySection",
    monthly: "monthlySection",
    weekly: "weeklySection",
    category: "categorySection",
    dateRange: "dateRangeSection",
  };
  document.getElementById(map[filter]).style.display = "block";
}

/* ---------- 日期区间 ---------- */
function initializeDatePicker() {
  const applyBtn = document.getElementById("applyDateRange");
  const startInput = document.getElementById("startDate");
  const endInput   = document.getElementById("endDate");

  const today = new Date();
  const sixMonthsAgo = new Date(today.getFullYear(), today.getMonth()-6, today.getDate());
  startInput.valueAsDate = sixMonthsAgo;
  endInput.valueAsDate = today;

  applyBtn.addEventListener("click", () => {
    const s = startInput.value, e = endInput.value;
    if (!s || !e) { alert("Please select both start and end dates!"); return; }
    if (new Date(s) > new Date(e)) { alert("Start date must be before end date!"); return; }
    currentFilter = "dateRange";
    showChartSection(currentFilter);
    refreshFromBackend();
  });
}

/* ---------- 拉数据并灌入 ---------- */
async function fetchReport(filter = 'monthly', startDate = '', endDate = '') {
  const params = new URLSearchParams({ filter });
  if (filter === 'dateRange') {
    if (startDate) params.set('start_date', startDate);
    if (endDate)   params.set('end_date', endDate);
  }
  const res = await fetch(`./get_report_data.php?` + params.toString(), { headers: { 'Accept':'application/json' }});
  return await res.json();
}

async function refreshFromBackend() {
  let s = '', e = '';
  if (currentFilter === 'dateRange') {
    s = document.getElementById('startDate')?.value || '';
    e = document.getElementById('endDate')?.value   || '';
  }
  try {
    const data = await fetchReport(currentFilter, s, e);
    hydrateFromApi(data);
  } catch (err) {
    console.error(err);
  }
}

function hydrateFromApi(payload) {
  if (!payload?.success) return;

  // metrics
  const m = payload.data.metrics || {};
  sampleData.totalFoodSave  = m.totalFoodSave  ?? 0;
  sampleData.totalDonations = m.totalDonations ?? 0;
  sampleData.progress       = m.progress       ?? 0;
  initializeMetrics();
  animateProgress();

  const c = payload.data.chart || {};
  switch (currentFilter) {
    case 'monthly':
      sampleData.monthly.labels    = c.labels || sampleData.monthly.labels;
      sampleData.monthly.donations = c.donations || Array(sampleData.monthly.labels.length).fill(0);
      sampleData.monthly.quantity  = c.quantity  || Array(sampleData.monthly.labels.length).fill(0);
      if (monthlyChart) monthlyChart.destroy();
      createMonthlyChart();
      break;
    case 'yearly':
      sampleData.yearly.labels    = c.labels || [];
      sampleData.yearly.donations = c.donations || [];
      sampleData.yearly.quantity  = c.quantity  || [];
      if (yearlyChart) yearlyChart.destroy();
      createYearlyChart();
      break;
    case 'weekly':
      sampleData.weekly.labels    = c.labels || ["Week 1","Week 2","Week 3","Week 4"];
      sampleData.weekly.donations = c.donations || [0,0,0,0];
      sampleData.weekly.quantity  = c.quantity  || [0,0,0,0];
      if (weeklyChart) weeklyChart.destroy();
      createWeeklyChart();
      break;
    case 'category':
      sampleData.categories.labels = c.labels || ["No Data"];
      sampleData.categories.data   = c.data   || [0];
      if (categoryBarChart) categoryBarChart.destroy();
      createCategoryBarChart();
      break;
    case 'dateRange':
      if (dateRangeChart) {
        dateRangeChart.data.labels = c.labels || ['No Data'];
        dateRangeChart.data.datasets[0].data = c.donations || [0];
        dateRangeChart.data.datasets[1].data = c.quantity  || [0];
        dateRangeChart.update();
      }
      break;
  }
}

/* ---------- 图表 ---------- */
function createMonthlyChart() {
  const ctx = document.getElementById("monthlyChart").getContext("2d");
  monthlyChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: sampleData.monthly.labels,
      datasets: [
        { label:"Total Donations", data: sampleData.monthly.donations, backgroundColor:"rgba(33,37,41,.8)", borderColor:"#212529", borderWidth:1, yAxisID:"y" },
        { label:"Quantity", data: sampleData.monthly.quantity, backgroundColor:"rgba(40,167,69,.8)", borderColor:"#28a745", borderWidth:1, yAxisID:"y1" },
      ],
    },
    options: commonDualAxisOptions("Donations","Quantity"),
  });
}

function createYearlyChart() {
  const ctx = document.getElementById("yearlyChart").getContext("2d");
  yearlyChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: sampleData.yearly.labels,
      datasets: [
        { label:"Total Donations", data: sampleData.yearly.donations, backgroundColor:"rgba(33,37,41,.8)", borderColor:"#212529", borderWidth:1, yAxisID:"y" },
        { label:"Quantity", data: sampleData.yearly.quantity, backgroundColor:"rgba(40,167,69,.8)", borderColor:"#28a745", borderWidth:1, yAxisID:"y1" },
      ],
    },
    options: commonDualAxisOptions("Donations","Quantity"),
  });
}

function createWeeklyChart() {
  const ctx = document.getElementById("weeklyChart").getContext("2d");
  weeklyChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: sampleData.weekly.labels,
      datasets: [
        { label:"Total Donations", data: sampleData.weekly.donations, backgroundColor:"rgba(33,37,41,.8)", borderColor:"#212529", borderWidth:1, yAxisID:"y" },
        { label:"Quantity", data: sampleData.weekly.quantity, backgroundColor:"rgba(40,167,69,.8)", borderColor:"#28a745", borderWidth:1, yAxisID:"y1" },
      ],
    },
    options: commonDualAxisOptions("Donations","Quantity"),
  });
}

function createCategoryBarChart() {
  const ctx = document.getElementById("categoryBarChart").getContext("2d");
  categoryBarChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: sampleData.categories.labels,
      datasets: [{ label:"Items by Category", data: sampleData.categories.data, backgroundColor:["#28a745","#ffc107","#17a2b8","#fd7e14","#dc3545","#6c757d"], borderWidth:2, borderColor:"#fff", borderRadius:8 }],
    },
    options: {
      responsive: true, maintainAspectRatio: true, indexAxis: "y",
      plugins: { legend: { display:false }, tooltip: { backgroundColor:"rgba(33,37,41,.9)", padding:12 } },
      scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
    },
  });
}

function createDateRangeChart() {
  const ctx = document.getElementById("dateRangeChart").getContext("2d");
  dateRangeChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["No Data"],
      datasets: [
        { label:"Total Donations", data:[0], backgroundColor:"rgba(33,37,41,.8)", borderColor:"#212529", borderWidth:1, yAxisID:"y" },
        { label:"Quantity", data:[0], backgroundColor:"rgba(40,167,69,.8)", borderColor:"#28a745", borderWidth:1, yAxisID:"y1" },
      ],
    },
    options: commonDualAxisOptions("Donations","Quantity"),
  });
}

function initializeCharts() {
  createMonthlyChart();
  createCategoryBarChart();
  createYearlyChart();
  createWeeklyChart();
  createDateRangeChart();
}

function commonDualAxisOptions(leftTitle, rightTitle) {
  return {
    responsive: true,
    maintainAspectRatio: true,
    plugins: {
      legend: { display: true, position: "top" },
      tooltip: { backgroundColor: "rgba(33,37,41,.9)", padding: 12, titleColor:"#fff", bodyColor:"#fff" },
    },
    scales: {
      y:  { type:"linear", display:true, position:"left",  beginAtZero:true, title:{display:true, text:leftTitle} },
      y1: { type:"linear", display:true, position:"right", beginAtZero:true, grid:{ drawOnChartArea:false }, title:{display:true, text:rightTitle} },
    },
  };
}
