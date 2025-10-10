// Helper for backend update
function updateFoodStatus(fooditem_id, tagClassName, status) {
    return fetch('../pages/browse/update_fooditem_status.php' , {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ fooditem_id, tagClassName, status })
    }).then(res => res.json());
}

// Show modal with food item details
function showFoodModal(item) {
    // replace to the current opened modal
    currentItem = item;

    const modalTitle = document.getElementById('foodItemModalLabel');
    const modalBody = document.getElementById('foodItemModalBody');
    const modalFooter = document.getElementById('foodItemModalFooter');

    const usedIcon = lucide.createElement(lucide.CheckCircle);
    const mealIcon = lucide.createElement(lucide.Calendar);
    const flagIcon = lucide.createElement(lucide.Flag);

    // tags template
    let tags = item.donation ? '<span class="d-flex align-items-center badge donation-tag ms-4 donation-tag-modal">Donation</span>' : 
    item.reserved ? '<span class="badge reserved-tag ms-4">Reserved</span>' : 
    item.used ? `<span class="d-flex align-items-center badge bg-secondary ms-4 used-tag-modal">Used</span>` : '';

    modalTitle.innerHTML = `<h3 class="d-flex align-items-center mb-0 ms-3 fw-bold">${item.name} ${tags}</h3>`;

    // tag removing operation handler
    function tag_Remove_UpdateStatus(tagClassName) {
        const removeBtn = document.querySelector(tagClassName);

        if (removeBtn) {
            const xIcon = lucide.createElement(lucide.X);
            xIcon.classList.add('ms-2', 'tag_removeBtn');
            xIcon.style.cursor = 'pointer';
            removeBtn.appendChild(xIcon);
            xIcon.addEventListener('click', function(e) {
                e.stopPropagation();
                updateFoodStatus(item.foodItem_id, tagClassName, false)
                .then(data => {
                    if (data.success) {
                        if (tagClassName === '.used-tag-modal') {
                            item.used = false;
                        } else {
                            item.donation = false;
                        }

                        const modalEl = document.getElementById('foodItemModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                        setTimeout(() => showFoodModal(item), 300);
                        updateView();
                    } else {
                        alert('Failed to remove tag.');
                    }
                })
                .catch(() => alert('Failed to remove tag.'));
            });
        }
    }

    tag_Remove_UpdateStatus('.used-tag-modal');
    tag_Remove_UpdateStatus('.donation-tag-modal');


    modalBody.innerHTML = `
    <ul class="list-group list-group-flush">
    <li class="list-group-item"><strong>Quantity:</strong> ${item.quantity}</li>
    <li class="list-group-item"><strong>Category:</strong> ${item.category}</li>
    <li class="list-group-item"><strong>Expiry:</strong> ${item.expiry || 'N/A'}</li>
    <li class="list-group-item"><strong>Storage:</strong> ${item.storage || 'N/A'}</li>
    <li class="list-group-item">
        <div class="d-flex flex-column gap-2 mb-2">
            <strong>Description:</strong>
            <text class="border rounded p-2 text-wrap">${item.description || 'No description available.'}</text>
        </div>
    </li>
    </ul>
    `;

    if (modalFooter) {
        modalFooter.innerHTML = '';

        // Elements creation
        const btnContainer = document.createElement('div');
        btnContainer.className = 'd-flex justify-content-center gap-3 px-5 w-100';

        const usedBtn = document.createElement('button');
        usedBtn.type = 'button';
        usedBtn.className = 'btn btn-lg flex-fill d-flex justify-content-center align-items-center fw-medium markUsed-btn';
        usedBtn.appendChild(usedIcon.cloneNode(true));
        usedBtn.innerHTML += '<span class="ms-2 text-nowrap">Mark as Used</span>';

        const planMealBtn = document.createElement('button');
        planMealBtn.type = 'button';
        planMealBtn.className = 'btn btn-lg flex-fill d-flex justify-content-center align-items-center fw-medium planMeal-btn';
        planMealBtn.appendChild(mealIcon.cloneNode(true));
        planMealBtn.innerHTML += '<span class="ms-2 text-nowrap">Plan Meal</span>';

        const donationBtn = document.createElement('button');
        donationBtn.type = 'button';
        donationBtn.className = 'btn btn-lg flex-fill d-flex justify-content-center align-items-center fw-medium flagDonation-btn';
        donationBtn.setAttribute('data-bs-target', '#donationFormModal');
        donationBtn.setAttribute('data-bs-toggle', 'modal');
        donationBtn.appendChild(flagIcon.cloneNode(true));
        donationBtn.innerHTML += '<span class="ms-2 text-nowrap">Flag Donation</span>';

        const usedBtnWrapper = document.createElement('span');
        const donationBtnWrapper = document.createElement('span');

        function appendBtn(btn, btn2, btn3) {
            for (const b of [btn, btn2, btn3]) {
                btnContainer.appendChild(b);
            }
        }

        let disableReason = item.used ? 'Item Already Marked as Used' : item.reserved ? 'Item Already Marked as Reserved' : item.donation ? 'Item Is a Donation Listing' : '';
        if (disableReason) {
            // Disable used and donation buttons
            usedBtn.disabled = true;
            usedBtn.classList.add('btn-secondary');
            donationBtn.disabled = true;
            donationBtn.classList.add('btn-secondary');

            const wrapper_attrs = {
                class: 'd-flex flex-fill',
                tabindex: '0',
                'data-bs-toggle': 'popover',
                'data-bs-content': disableReason,
                'data-bs-trigger': 'hover focus',
                'data-bs-placement': 'bottom',
                'data-bs-custom-class': 'custom-popover fw-semibold'
            };

            for (const [k, v] of Object.entries(wrapper_attrs)) {
                usedBtnWrapper.setAttribute(k, v);
                donationBtnWrapper.setAttribute(k, v);
            }

            usedBtnWrapper.appendChild(usedBtn);
            donationBtnWrapper.appendChild(donationBtn);

            appendBtn(usedBtnWrapper, planMealBtn, donationBtnWrapper);

            if (window.bootstrap && window.bootstrap.Popover) {
                new bootstrap.Popover(usedBtnWrapper);
                new bootstrap.Popover(donationBtnWrapper);
            }
        } else {
            appendBtn(usedBtn, planMealBtn, donationBtn);

            usedBtn.addEventListener('click', function(e) {
                usedBtn.disabled = true;
                usedBtn.classList.add('btn-secondary');
                updateFoodStatus(item.foodItem_id, '.used-tag-modal', true)
                .then(data => {
                    if (data.success) {
                        item.used = true;
                        updateView();
                        const modalEl = document.getElementById('foodItemModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                        setTimeout(() => showFoodModal(item), 300);
                    } else {
                        usedBtn.disabled = false;
                        usedBtn.classList.remove('btn-secondary');
                        alert('Failed to mark as used.');
                    }
                })
                .catch(() => {
                    usedBtn.disabled = false;
                    usedBtn.classList.remove('btn-secondary');
                    alert('Failed to mark as used.');
                });
            });
        }

        modalFooter.appendChild(btnContainer);
    }
    const modal = new bootstrap.Modal(document.getElementById('foodItemModal'));
    modal.show();
}

// Render food item cards
function renderCards(items) {
    const container = document.getElementById('foodCardContainer');
    container.innerHTML = '';
    if (!items.length) {
        const wrapper = document.createElement('div');
        wrapper.className = 'text-center w-100 my-5';
        const h3 = document.createElement('h3');
        h3.className = 'opacity-25 mb-0';
        h3.textContent = 'No Items Found';
        const frownIcon = lucide.createElement(lucide.Frown);
        frownIcon.classList.add('ms-2');
        frownIcon.style.width = '1em';
        frownIcon.style.height = '1em';
        h3.appendChild(frownIcon);
        wrapper.appendChild(h3);
        container.appendChild(wrapper);
    } else {
        items.forEach((item, idx) => {
            let tags = item.donation ? '<span class="badge donation-tag">Donation</span>' : item.reserved ? '<span class="badge reserved-tag">Reserved</span>' : item.used ? `<span class="badge bg-secondary used-tag">Used</span>` : '';
            let expiry = item.expiry ? `<p class=\"card-text mb-1\"><strong>Expiry:</strong> ${item.expiry}</p>` : '';
            let storage = item.storage ? `<p class=\"card-text\"><strong>Storage:</strong> ${item.storage}</p>` : '';
            const cardHtml = `
            <div class=\"col\">
                <div class=\"food-card card h-100\" data-idx=\"${idx}\">
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
        document.querySelectorAll('.food-card').forEach(card => {
            card.addEventListener('click', function(e) {
                const idx = this.getAttribute('data-idx');
                showFoodModal(items[idx]);
            });
        });
    }
}

// Helper to get display name for filter
function getFilterLabel(filter, value) {
    switch (filter) {
        case 'inventory': return 'Inventory Only';
        case 'donation': return 'Donation Listings';
        case 'category': return value;
        case 'expiry': return 'Expiry Date (Soonest to Latest)';
        case 'storage': return value;
        default: return filter;
    }
}

// Render filter tags with smart add/remove
let activeFilters = [];

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

    // Add new tags and styling
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
                updateView();
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

// Fetch food items from the backend and initialize the app
let foodItems = [];

function fetchFoodItemsAndInit() {
    fetch('../pages/browse/get_fooditems.php')
    .then(response => response.json())
    .then(data => {
        if (Array.isArray(data)) {
            foodItems = data;
            if (typeof updateView === 'function') {
                updateView();
            } else {
                console.warn('updateView function is not defined.');
            }
        } else {
            console.error("Unexpected data format:", data);
            foodItems = [];
        }
    })
    .catch(err => {
        console.error('Failed to fetch food items:', err);
    });
}

// Apply all active filters to foodItems
function getFilteredItems() {
    let filtered = foodItems.slice();
    activeFilters.forEach(f => {
        if (f.filter === 'inventory') {
            filtered = filtered.filter(item => !item.donation && !item.used);
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

function setupFilterListeners() {
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
}

// use to keep track current opened modal
let currentItem = null;

function donationSubmitBtn_Listener() {
    const donationForm_submit_btn = document.getElementById('donationForm-submit-btn');

    donationForm_submit_btn.addEventListener('click', function(e) {
        const form = document.getElementById('donation-form');
        if (!currentItem) return alert("No food item selected.");

        if (form.checkValidity()) {
            e.preventDefault();

            const pickup_location = document.getElementById('pickup_location').value;
            const availability = document.getElementById('availability').value;
            const fooditem_id = currentItem.foodItem_id;
            const quantity = currentItem.quantity;
            const donation = true;

            fetch('../pages/browse/post_donation.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ fooditem_id, donation, quantity, pickup_location, availability })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentItem.donation = true;
                    updateView();
                    const modalEl = document.getElementById('donationFormModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                } else {
                    alert("Failed to store donation.");
                }
            })
            .catch(() => {
                alert('Error: Failed to send donation data.');
            });
        } else {
            form.classList.add('was-validated');
        }
    });
}

// init function for page loader to run
function initBrowsePage() {
    fetchFoodItemsAndInit();
    donationSubmitBtn_Listener();
    setupFilterListeners();
}
window.initBrowsePage = initBrowsePage;