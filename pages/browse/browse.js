// Browse Page JavaScript
class BrowsePage {
    constructor() {
        this.initializeEventListeners();
        this.loadFoodItems();
    }

    initializeEventListeners() {
        // Add item button
        const addButton = document.querySelector(".btn-primary");
        if (addButton) {
            addButton.addEventListener("click", () => {
                this.showAddItemModal();
            });
        }

        // Edit buttons
        document.addEventListener("click", (e) => {
            if (e.target.closest(".btn-outline-primary")) {
                e.preventDefault();
                this.editItem(e.target.closest("tr"));
            }
        });

        // Delete buttons
        document.addEventListener("click", (e) => {
            if (e.target.closest(".btn-outline-danger")) {
                e.preventDefault();
                this.deleteItem(e.target.closest("tr"));
            }
        });

        // Search and filter functionality
        this.initializeFilters();
    }

    initializeFilters() {
        // Add search input if it doesn't exist
        const cardHeader = document.querySelector(".card-header");
        if (cardHeader && !document.querySelector(".browse-search")) {
            const searchDiv = document.createElement("div");
            searchDiv.className = "browse-search me-3";
            searchDiv.innerHTML = `
                <input type="text" class="form-control" placeholder="Search food items..." 
                       style="width: 250px;">
            `;
            cardHeader.insertBefore(searchDiv, cardHeader.lastElementChild);

            // Add search functionality
            const searchInput = searchDiv.querySelector("input");
            searchInput.addEventListener("input", (e) => {
                this.filterItems(e.target.value);
            });
        }
    }

    filterItems(searchTerm) {
        const rows = document.querySelectorAll("tbody tr");
        rows.forEach((row) => {
            const itemName = row.cells[0].textContent.toLowerCase();
            const category = row.cells[1].textContent.toLowerCase();
            const isVisible =
                itemName.includes(searchTerm.toLowerCase()) ||
                category.includes(searchTerm.toLowerCase());
            row.style.display = isVisible ? "" : "none";
        });
    }

    showAddItemModal() {
        // For now, just show an alert - you can implement a proper modal later
        alert("Add Item functionality will be implemented here");
        console.log("Opening add item modal...");
    }

    editItem(row) {
        const itemName = row.cells[0].textContent;
        alert(`Edit functionality for "${itemName}" will be implemented here`);
        console.log("Editing item:", itemName);
    }

    deleteItem(row) {
        const itemName = row.cells[0].textContent;
        if (confirm(`Are you sure you want to delete "${itemName}"?`)) {
            row.remove();
            console.log("Deleted item:", itemName);
        }
    }

    loadFoodItems() {
        // Apply CSS classes to existing elements
        const table = document.querySelector(".table");
        if (table) {
            table.classList.add("browse-table");
        }

        const card = document.querySelector(".card");
        if (card) {
            card.classList.add("browse-card");
        }

        // Update badges with custom class
        const badges = document.querySelectorAll(".badge");
        badges.forEach((badge) => {
            badge.classList.add("browse-status-badge");
        });

        // Update action buttons
        const actionBtns = document.querySelectorAll(".btn-sm");
        actionBtns.forEach((btn) => {
            btn.classList.add("browse-action-btn");
        });

        console.log("Browse page initialized successfully");
    }
}

// Initialize the browse page when the content is loaded
document.addEventListener("DOMContentLoaded", () => {
    // Small delay to ensure the content is fully rendered
    setTimeout(() => {
        new BrowsePage();
    }, 100);
});

// Also initialize if the page is loaded via AJAX
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        setTimeout(() => new BrowsePage(), 100);
    });
} else {
    setTimeout(() => new BrowsePage(), 100);
}
