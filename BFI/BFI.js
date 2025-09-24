// BFI.js - Handles rendering and filtering of food cards

// Sample food items data in JS
const foodItems = [
    {
        name: 'Apple',
        quantity: 10,
        category: 'Fruit',
        donated: false,
        expiry: '2025-09-25',
        storage: 'Fridge',
    },
    {
        name: 'Milk',
        quantity: 2,
        category: 'Dairy',
        donated: true,
        expiry: '2025-09-21',
        storage: 'Fridge',
    },
    {
        name: 'Bread',
        quantity: 5,
        category: 'Bakery',
        donated: false,
        expiry: '2025-09-22',
        storage: 'Pantry',
    },
    {
        name: 'Banana',
        quantity: 8,
        category: 'Fruit',
        donated: true,
        expiry: '2025-09-23',
        storage: 'Pantry',
    },
    {
        name: 'Cheese',
        quantity: 3,
        category: 'Dairy',
        donated: false,
        expiry: '2025-09-28',
        storage: 'Fridge',
    },
    {
        name: 'Frozen Peas',
        quantity: 6,
        category: 'Vegetable',
        donated: false,
        expiry: '2025-12-01',
        storage: 'Freezer',
    },
    {
        name: 'Yogurt',
        quantity: 4,
        category: 'Dairy',
        donated: true,
        expiry: '2025-09-24',
        storage: 'Fridge',
    },
    {
        name: 'Orange',
        quantity: 12,
        category: 'Fruit',
        donated: false,
        expiry: '2025-09-27',
        storage: 'Fridge',
    },
    {
        name: 'Rice',
        quantity: 1,
        category: 'Grain',
        donated: false,
        expiry: '2026-01-15',
        storage: 'Pantry',
    },
];

function renderCards(items) {
    const container = document.getElementById('foodCardContainer');
    container.innerHTML = '';
    if (!items.length) {
        container.innerHTML = `
            <div class="d-flex justify-content-center w-100 my-5">
                <h3 class="opacity-25 mb-0">No Match Items<span class="noMatch-icon ms-2" data-lucide="frown"></span></h3>
            </div>
        `;
    } else {
        items.forEach(item => {
            let donatedTag = item.donated ? '<span class="badge">Donated</span>' : '';
            let expiry = item.expiry ? `<p class=\"card-text mb-1\"><strong>Expiry:</strong> ${item.expiry}</p>` : '';
            let storage = item.storage ? `<p class=\"card-text\"><strong>Storage:</strong> ${item.storage}</p>` : '';
            container.innerHTML += `
            <div class=\"col\">
                <div class=\"card h-100\">
                    <div class=\"card-header\">
                        <h5 class=\"d-flex justify-content-between mb-0\">${item.name} ${donatedTag}</h5>
                    </div>
                    <div class=\"card-body\">
                        <p class=\"card-text mb-1\"><strong>Quantity:</strong> ${item.quantity}</p>
                        <p class=\"card-text mb-1\"><strong>Category:</strong> ${item.category}</p>
                        ${expiry}
                        ${storage}
                    </div>
                </div>
            </div>
            `;
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // State for active filters
    let activeFilters = [];

    // Helper to get display name for filter
    function getFilterLabel(filter, value) {
        switch (filter) {
            case 'inventory': return 'Inventory Only';
            case 'donation': return 'Donation Listings';
            case 'category': return value;
            case 'expiry': return 'Expiry Date (Soonest First)';
            case 'storage': return value;
            default: return filter;
        }
    }

    // Render filter tags
    function renderTags() {
        const tagContainer = document.getElementById('filterTagContainer');
        tagContainer.innerHTML = '';
        activeFilters.forEach((f, idx) => {
            const tag = document.createElement('div');
            tag.className = 'badge fs-6 d-flex align-items-center';
            tag.style.backgroundColor = '#b2c180ff';
            tag.innerHTML = `${getFilterLabel(f.filter, f.value)}`;
            const closeIcon = lucide.createElement(lucide.X);
            closeIcon.classList.add('ms-2');
            closeIcon.style.cursor = 'pointer';
            closeIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                activeFilters.splice(idx, 1);
                updateView();
            });
            tag.appendChild(closeIcon);
            tagContainer.appendChild(tag);
        });
    }

    // Apply all active filters to foodItems
    function getFilteredItems() {
        let filtered = foodItems.slice();
        activeFilters.forEach(f => {
            if (f.filter === 'inventory') {
                filtered = filtered.filter(item => !item.donated);
            } else if (f.filter === 'donation') {
                filtered = filtered.filter(item => item.donated);
            } else if (f.filter === 'category' && f.value) {
                filtered = filtered.filter(item => item.category === f.value);
            } else if (f.filter === 'expiry') {
                filtered = filtered.slice().sort((a, b) => new Date(a.expiry) - new Date(b.expiry));
            } else if (f.filter === 'storage' && f.value) {
                filtered = filtered.filter(item => item.storage === f.value);
            }
        });
        return filtered;
    }

    // Update both tags and cards
    function updateView() {
        renderTags();
        renderCards(getFilteredItems());
    }

    // Initial render
    updateView();

    // Add filter on click
    document.querySelectorAll('.filter-option').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.getAttribute('data-filter');
            const value = this.getAttribute('data-value');
            // Prevent duplicate filters (except expiry, which can be toggled)
            if (!activeFilters.some(f => f.filter === filter && (f.value === value || !value))) {
                activeFilters.push({ filter, value });
                updateView();
            }
        });
    });
});
