// Page Loader - Loads content without refreshing sidebar
class PageLoader {
    constructor() {
        this.contentArea = document.getElementById("main-content");
        this.currentPage = "";
        this.initializeNavigation();
    }

    // Initialize navigation event listeners
    initializeNavigation() {
        document.addEventListener("click", (e) => {
            const link = e.target.closest("[data-page]");
            if (link) {
                e.preventDefault();
                const page = link.getAttribute("data-page");
                this.loadPage(page);
            }
        });

        // Handle browser back/forward buttons
        window.addEventListener("popstate", (e) => {
            if (e.state && e.state.page) {
                this.loadPage(e.state.page, false);
            }
        });
    }

    // Load page content via AJAX
    async loadPage(page, updateHistory = true) {
        if (page === this.currentPage) return;

        try {
            // Show loading indicator
            this.showLoading();

            // Fetch page content
            const response = await fetch(`../pages/${page}.php`);
            if (!response.ok) {
                throw new Error(`Failed to load page: ${response.status}`);
            }

            const content = await response.text();

            // Update content area
            this.contentArea.innerHTML = content;

            // Update browser history
            if (updateHistory) {
                history.pushState({ page }, "", `?page=${page}`);
            }

            // Update current page
            this.currentPage = page;

            // Reinitialize any scripts needed for the new content
            this.reinitializePageScripts();

            // Update active navigation link
            this.updateActiveNavLink(page);
        } catch (error) {
            console.error("Error loading page:", error);
            this.showError("Failed to load page. Please try again.");
        }
    }

    // Show loading indicator
    showLoading() {
        this.contentArea.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="height: 50vh;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `;
    }

    // Show error message
    showError(message) {
        this.contentArea.innerHTML = `
            <div class="alert alert-danger m-4" role="alert">
                <h4 class="alert-heading">Error</h4>
                <p>${message}</p>
            </div>
        `;
    }

    // Reinitialize scripts for new content
    reinitializePageScripts() {
        // Reinitialize Lucide icons
        if (typeof lucide !== "undefined") {
            lucide.createIcons();
        }

        // Reinitialize Bootstrap components
        if (typeof bootstrap !== "undefined") {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize modals
            const modalList = [].slice.call(
                document.querySelectorAll(".modal")
            );
            modalList.map(function (modalEl) {
                return new bootstrap.Modal(modalEl);
            });
        }
    }

    // Update active navigation link
    updateActiveNavLink(page) {
        // Remove active class from all nav links
        document.querySelectorAll(".nav-link").forEach((link) => {
            link.classList.remove("active");
        });

        // Add active class to current page link
        const currentLink = document.querySelector(`[data-page="${page}"]`);
        if (currentLink) {
            currentLink.classList.add("active");
        }
    }

    // Load initial page based on URL parameter
    loadInitialPage() {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get("page") || "dashboard"; // default page
        this.loadPage(page, false);
    }
}

// Initialize page loader when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    const pageLoader = new PageLoader();
    pageLoader.loadInitialPage();
});
