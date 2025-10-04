// Sidebar Toggle Functionality
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");

    // Toggle the collapsed class
    sidebar.classList.toggle("collapsed");

    // Save the state to localStorage
    const isCollapsed = sidebar.classList.contains("collapsed");
    localStorage.setItem("sidebarCollapsed", isCollapsed);
}

// Initialize sidebar state on page load
document.addEventListener("DOMContentLoaded", function () {
    // Check if sidebar was previously collapsed
    const wasCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
    const sidebar = document.getElementById("sidebar");

    if (wasCollapsed) {
        sidebar.classList.add("collapsed");
    }

    // Initialize Lucide icons
    if (typeof lucide !== "undefined") {
        lucide.createIcons();
    }
});
