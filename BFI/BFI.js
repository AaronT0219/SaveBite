// BFI.js - Handles rendering and filtering of food cards

// Sample food items data in JS
const foodItems = [
    {
        name: 'Apple',
        quantity: 10,
        category: 'Fruit',
        donation: false,
        reserved: false,
        used: false,
        expiry: '2025-09-25',
        storage: 'Fridge',
        description: 'Fresh and crisp apples, perfect for snacking or baking.'
    },
    {
        name: 'Milk',
        quantity: 2,
        category: 'Dairy',
        donation: true,
        reserved: false,
        used: false,
        expiry: '2025-09-21',
        storage: 'Fridge',
        description: 'Whole milk, great for drinking or cooking.'
    },
    {
        name: 'Bread',
        quantity: 5,
        category: 'Bakery',
        donation: false,
        reserved: true,
        used: false,
        expiry: '2025-09-22',
        storage: 'Pantry',
        description: 'Soft bakery bread, ideal for sandwiches.'
    },
    {
        name: 'Banana',
        quantity: 8,
        category: 'Fruit',
        donation: false,
        reserved: false,
        used: true,
        expiry: '2025-09-23',
        storage: 'Pantry',
        description: 'Ripe bananas, perfect for smoothies or snacks.'
    },
    {
        name: 'Cheese',
        quantity: 3,
        category: 'Dairy',
        donation: false,
        reserved: false,
        used: false,
        expiry: '2025-09-28',
        storage: 'Fridge',
        description: 'Block of cheese, great for grating or slicing.'
    },
    {
        name: 'Frozen Peas',
        quantity: 6,
        category: 'Vegetable',
        donation: false,
        reserved: false,
        used: false,
        expiry: '2025-12-01',
        storage: 'Freezer',
        description: 'Frozen green peas, ideal for soups and stir-fries.'
    },
    {
        name: 'Yogurt',
        quantity: 4,
        category: 'Dairy',
        donation: true,
        reserved: false,
        used: false,
        expiry: '2025-09-24',
        storage: 'Fridge',
        description: 'Creamy yogurt, delicious as a snack or breakfast.'
    },
    {
        name: 'Orange',
        quantity: 12,
        category: 'Fruit',
        donation: false,
        reserved: false,
        used: false,
        expiry: '2025-09-27',
        storage: 'Fridge',
        description: 'Juicy oranges, packed with vitamin C.'
    },
    {
        name: 'Rice',
        quantity: 1,
        category: 'Grain',
        donation: false,
        reserved: false,
        used: false,
        expiry: '2026-01-15',
        storage: 'Pantry',
        description: 'Bag of rice, a staple for many meals.'
    },
];

function renderCards(items) {
    const container = document.getElementById('foodCardContainer');
    container.innerHTML = '';
    if (!items.length) {
        const wrapper = document.createElement('div');
        wrapper.className = 'text-center w-100 my-5';

        const h3 = document.createElement('h3');
        h3.className = 'opacity-25 mb-0';
        h3.textContent = 'No Match Items';

        const frownIcon = lucide.createElement(lucide.Frown);
        frownIcon.classList.add('ms-2');
        frownIcon.style.width = '1em';
        frownIcon.style.height = '1em';

        h3.appendChild(frownIcon);
        wrapper.appendChild(h3);
        container.appendChild(wrapper);
    } else {
        items.forEach((item, idx) => {
            let tags = '';
            if (item.donation) {
                tags = '<span class="badge">Donation</span>';
            } else if (item.reserved) {
                tags = '<span class="badge reserved-tag">Reserved</span>';
            } else if (item.used) {
                tags = '<span class="badge bg-secondary">Used</span>';
            }
            let expiry = item.expiry ? `<p class=\"card-text mb-1\"><strong>Expiry:</strong> ${item.expiry}</p>` : '';
            let storage = item.storage ? `<p class=\"card-text\"><strong>Storage:</strong> ${item.storage}</p>` : '';
            const cardHtml = `
            <div class=\"col\">
                <div class=\"card h-100 food-card\" data-idx=\"${idx}\">
                    <div class=\"card-header\">
                        <h5 class=\"d-flex justify-content-between mb-0\">${item.name} ${tags}</h5>
                    </div>
                    <div class=\"card-body fw-medium\">
                        <p class=\"card-text mb-1\"><strong>Quantity:</strong> ${item.quantity}</p>
                        <p class=\"card-text mb-1\"><strong>Category:</strong> ${item.category}</p>
                        ${expiry}
                        ${storage}
                    </div>
                </div>
            </div>
            `;
            container.innerHTML += cardHtml;
        });

        // Add click event to each card to show modal
        document.querySelectorAll('.food-card').forEach(card => {
            card.addEventListener('click', function() {
                const idx = this.getAttribute('data-idx');
                showFoodModal(items[idx]);
            });
        });
    }
}

// Show modal with food item details
function showFoodModal(item) {
    const modalTitle = document.getElementById('foodItemModalLabel');
    const modalBody = document.getElementById('foodItemModalBody');
    const modalFooter = document.getElementById('foodItemModalFooter');

    //icons
    const usedIcon = lucide.createElement(lucide.CheckCircle);
    const mealIcon = lucide.createElement(lucide.Calendar);
    const flagIcon = lucide.createElement(lucide.Flag);

    
    let tags = '';
    if (item.donation) {
        tags = '<span class="badge ms-4">Donation</span>';
    } else if (item.reserved) {
        tags = '<span class="badge reserved-tag ms-4">Reserved</span>';
    } else if (item.used) {
        tags = '<span class="badge bg-secondary ms-4">Used</span>';
    }

    modalTitle.innerHTML = `<h3 class="d-flex align-items-center mb-0 fw-bold">${item.name} ${tags}</h3>`;
    modalBody.innerHTML = `
    <ul class="list-group list-group-flush">
    <li class="list-group-item">Quantity: ${item.quantity}</li>
    <li class="list-group-item">Category: ${item.category}</li>
    <li class="list-group-item">Expiry: ${item.expiry || 'N/A'}</li>
    <li class="list-group-item">Storage: ${item.storage || 'N/A'}</li>
    <li class="list-group-item">
        <div class="d-flex flex-column gap-2 mb-2">
            <strong>Description:</strong>
            <text class="border rounded p-2">${item.description || 'No description available.'}</text>
        </div>
    </li>
    </ul>
    `;
    // Modal footer: 3 action buttons with icons
    if (modalFooter) {
        modalFooter.innerHTML = '';
        // Create a flex container for horizontal layout
        const btnContainer = document.createElement('div');
        btnContainer.className = 'd-flex justify-content-center gap-3 px-5 w-100';
    
        // Button 1: Mark as Used
        const usedBtn = document.createElement('button');
        usedBtn.type = 'button';
        usedBtn.className = 'btn btn-lg flex-fill d-flex justify-content-center align-items-center fw-medium markUsed-btn';
        usedBtn.appendChild(usedIcon.cloneNode(true));
        usedBtn.innerHTML += '<span class="ms-2 text-nowrap">Mark as Used</span>';
    
        // Button 2: Add to Meal Plan
        const planMealBtn = document.createElement('button');
        planMealBtn.type = 'button';
        planMealBtn.className = 'btn btn-lg flex-fill d-flex justify-content-center align-items-center fw-medium planMeal-btn';
        planMealBtn.appendChild(mealIcon.cloneNode(true));
        planMealBtn.innerHTML += '<span class="ms-2 text-nowrap">Plan Meal</span>';
    
        // Button 3: Report Issue
        const donationBtn = document.createElement('button');
        donationBtn.type = 'button';
        donationBtn.className = 'btn btn-lg flex-fill d-flex justify-content-center align-items-center fw-medium flagDonation-btn';
        donationBtn.appendChild(flagIcon.cloneNode(true));
        donationBtn.innerHTML += '<span class="ms-2 text-nowrap">Flag Donation</span>';
    
        btnContainer.appendChild(usedBtn);
        btnContainer.appendChild(planMealBtn);
        btnContainer.appendChild(donationBtn);
        modalFooter.appendChild(btnContainer);
    }
    
    // Show modal using Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('foodItemModal'));
    modal.show();
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

    // Render filter tags with smart add/remove and animation
    function renderTags() {
        const tagContainer = document.getElementById('filterTagContainer');
        // Get current tags in DOM
        const currentTags = Array.from(tagContainer.children);
        // Build a map for quick lookup
        const currentMap = new Map();
        currentTags.forEach(tag => {
            currentMap.set(tag.dataset.key, tag);
        });

        // Build a set of keys for active filters
        const activeKeys = new Set(activeFilters.map(f => `${f.filter}:${f.value}`));

        // Remove tags that are no longer active
        currentTags.forEach(tag => {
            if (!activeKeys.has(tag.dataset.key)) {
                tag.classList.remove('show');
                setTimeout(() => {
                    tag.remove();
                }, 300);
            }
        });

        // Add new tags
        activeFilters.forEach((f, idx) => {
            const key = `${f.filter}:${f.value}`;
            if (!currentMap.has(key)) {
                const tag = document.createElement('div');
                tag.className = 'badge fs-6 d-flex align-items-center filter-tag';
                tag.style.backgroundColor = '#b2c180ff';
                tag.innerHTML = `${getFilterLabel(f.filter, f.value)}`;
                tag.dataset.key = key;
                const closeIcon = lucide.createElement(lucide.X);
                closeIcon.classList.add('ms-2');
                closeIcon.style.cursor = 'pointer';
                closeIcon.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Remove filter and update
                    activeFilters = activeFilters.filter((ff, i) => `${ff.filter}:${ff.value}` !== key);
                    renderCards(getFilteredItems());
                    renderTags();
                });
                tag.appendChild(closeIcon);
                tagContainer.appendChild(tag);
                // Animate in
                requestAnimationFrame(() => {
                    tag.classList.add('show');
                });
            }
        });
    }

    // Apply all active filters to foodItems
    function getFilteredItems() {
        let filtered = foodItems.slice();
        activeFilters.forEach(f => {
            if (f.filter === 'inventory') {
                filtered = filtered.filter(item => !item.donation);
            } else if (f.filter === 'donation') {
                filtered = filtered.filter(item => item.donation);
            } else if (f.filter === 'reserved') {
                filtered = filtered.filter(item => item.reserved);
            } else if (f.filter === 'used') {
                filtered = filtered.filter(item => item.used);
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
