(function () {
    function initializeCalendar() {
        const calendarEl = document.getElementById("calendar");

        const calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                right: 'prev,next today',
                center: 'title',
                left: ''
            },
            customButtons: {
                today: {
                text: '',
                click: function() {
                    calendar.today();
                }
                }
            },
            views: {
                timeGridWeek: {
                    expandRows: true,
                    nowIndicator: true,
                    allDaySlot: false
                }
            },
            initialView: "dayGridMonth",
            editable: false,
            eventDisplay: "block",
            eventTimeFormat: {
                hour: "2-digit",
                minute: "2-digit",
            },

            // Fix grid height & limit visible events
            height: 'auto',
            dayMaxEventRows: 2,   // show max 2 events
            moreLinkClick: "popover",
            moreLinkContent: (args) => `+${args.num} more meals`,

            eventContent: function(arg) {
                const mealSlot = arg.event.extendedProps.mealSlot;
                const desc = arg.event.extendedProps.description;

                // Create custom event-content's inner HTML
                let innerHtml = `
                    <div class="fc-event-time text-muted small">${mealSlot || ''}</div>
                    <div class="fc-event-title text-dark fw-bolder text-truncate">${arg.event.title}</div>
                `;

                if (desc) {
                    innerHtml += `<div class="mt-1 small text-muted text-truncate">${desc}</div>`;
                }

                return { html: innerHtml };
            },

            eventClick: function(info) {
                // show modal that contain event details
                const event = info.event;
                const meal = event.extendedProps;

                const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
                const modalTitle = document.getElementById('eventTitle');
                const modalDesc = document.getElementById('eventDesc');
                const modalIngredients = document.getElementById('eventIngredients');

                modalTitle.textContent = event.title;
                modalDesc.textContent = meal.description || 'No description';

                modalIngredients.innerHTML = meal.selectedCards && meal.selectedCards.length
                ? meal.selectedCards.map(i => `
                    <tr"><th class="ps-3">${i.food_name}</th><td class="pe-4 text-end">${i.quantity}</td></tr>
                `).join('')
                : '<li>No ingredients</li>';

                eventModal.show();

                // close popover if it's opened (with animation)
                const popover = document.querySelector('.fc-popover');
                if (popover) {
                   setTimeout(() => {
                        popover.classList.add('closing');
                        setTimeout(() => popover.remove(), 200);
                    }, 100);
                }

                // edit & delete button event listenr
                const editBtn = document.getElementById('eventEdit-btn');
                const dltBtn = document.getElementById('eventDlt-btn');

                editBtn.onclick = () => {
                    const editModal = new bootstrap.Modal(document.getElementById('eventEditModal'));
                    document.getElementById('eventEditTitle').value = event.title;
                    document.getElementById('eventEditDate').value = event.startStr;
                    document.getElementById('eventEditDesc').value = event.extendedProps.description;
                    document.getElementById('eventEditSlot').value = event.extendedProps.mealSlot;

                    eventModal.hide();
                    editModal.show();

                    document.getElementById('eventEditConfirm').onclick = () => {
                        const form = document.getElementById('eventEditForm');
                        
                        if (!form.checkValidity()) {
                            form.classList.add('was-validated');
                            return;
                        }

                        const updated = {
                            meal_id: event.id,
                            title: document.getElementById('eventEditTitle').value,
                            date: document.getElementById('eventEditDate').value,
                            desc: document.getElementById('eventEditDesc').value,
                            mealSlot: document.getElementById('eventEditSlot').value
                        };

                        fetch('../pages/meals/update_meal.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(updated)
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) {
                                showToast("Update failed: " + data.message, "danger");
                                return;
                            }

                            editModal.hide();
                            showToast("Meal updated successfully!", "success");
                            fetchAndRender_CalendarEvents(calendar);
                        });

                        form.reset();
                        form.classList.remove('was-validated');
                    };
                };

                dltBtn.onclick = () => {
                    if (!confirm("Are you sure you want to delete this meal plan?")) return;

                    fetch('../pages/meals/delete_meal.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ meal_id: event.id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            showToast(`Delete failed: ${data.message}`, "danger");
                            return;
                        }

                        eventModal.hide();
                        showToast("Meal plan deleted successfully!", "success");

                        // Refresh UI
                        fetchFoodItemsAndInit();
                        fetchAndRender_CalendarEvents(calendar);
                    })
                    .catch(() => showToast("Error deleting meal plan.", "danger"));
                };
            }
        });

        calendar.render();

        // retrive and load events from database
        fetchAndRender_CalendarEvents(calendar);

        const todayBtn = document.querySelector('.fc-today-button');
        if (todayBtn) {
            const iconEl = lucide.createElement(lucide.CalendarSync);
            todayBtn.appendChild(iconEl);
        }

        addMealEventListener(calendar);
    }

    function addMealEventListener(calendar) {
        // Open modal on "Add Meal" button click
        const addMealBtn = document.querySelector('.addMeal-btn');
        const addMealModal = new bootstrap.Modal(document.getElementById('addMealModal'));
        const mealConfirmBtn = document.getElementById('mealConfirm-btn');

        addMealBtn.addEventListener('click', () => {
            addMealModal.show();
        });

        // Handle form submission
        mealConfirmBtn.addEventListener('click', function (e) {
            const form = document.getElementById('mealForm');
            
            if (form.checkValidity()) {
                e.preventDefault();
                
                if (!selectedCards.length) {
                    const container = document.getElementById('selectedCardContainer');
                    const alertHtml = `
                        <div class="selectedCards-alert d-flex justify-content-between align-items-center alert alert-danger fs-5 py-2" role="alert">
                            Please select at least one ingredient.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                        </div>
                    `
                    container.insertAdjacentHTML('beforebegin', alertHtml);
                    return;
                }

                const title = document.getElementById('mealTitle').value.trim();
                const date = document.getElementById('mealDate').value;
                const desc = document.getElementById('mealDescription').value.trim();
                const mealSlot = document.getElementById('mealSlot').value;

                // EXPIRY CHECK SECTION
                const mealDate = new Date(date);
                const expiredItems = selectedCards.filter(card => {
                    if (!card.item.expiry) return false;
                    return new Date(card.item.expiry) < mealDate;
                });

                if (expiredItems.length) {
                    // show modal before proceeding
                    showExpiryWarningModal(expiredItems, () => {
                        // Proceed after confirmation
                        submitMealPlan(selectedCards, title, desc, date, mealSlot, form, addMealModal, calendar);
                    });
                    return;
                }

                // No expiry conflict — proceed directly
                submitMealPlan(selectedCards, title, desc, date, mealSlot, form, addMealModal, calendar);

            } else {
                form.classList.add('was-validated');
            }
        });
    }

    function submitMealPlan(selectedCards, title, desc, date, mealSlot, form, addMealModal, calendar) {
        fetch('../pages/meals/post_meal.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ selectedCards, title, desc, date, mealSlot })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert("Failed to store meal.");
                return;
            }

            // update food items and calendar
            fetchFoodItemsAndInit();
            fetchAndRender_CalendarEvents(calendar);

            // show success toast
            const toastEl = document.getElementById('mealToast');
            const toast = new bootstrap.Toast(toastEl);
            toast.show();

            // reset form and modal
            form.reset();
            form.classList.remove('was-validated');
            selectedCards = [];
            renderSelectedCard();
            const alertMsg = document.querySelector('.selectedCards-alert');
            if (alertMsg) alertMsg.remove();

            addMealModal.hide();
        })
        .catch(() => {
            alert('Error: Failed to create meal plan.');
        });
    }

    function showExpiryWarningModal(expiredItems, onProceed) {
        const modalEl = document.getElementById('expiryWarningModal');
        const listEl = document.getElementById('expiryWarningList');
        const proceedBtn = document.getElementById('expiryProceedBtn');

        // populate list
        listEl.innerHTML = expiredItems
            .map(card => `<li class="list-group-item d-flex justify-content-between fw-medium">
                            <span>${card.item.name}</span>
                            <small class="text-danger">Expires ${card.item.expiry}</small>
                        </li>`)
            .join('');

        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        // handle proceed
        proceedBtn.onclick = () => {
            modal.hide();
            onProceed();
        };
    }

    // ===== FILTER + CARD RENDERING MODULE =====

    // Holds all items fetched externally
    let allItems = [];
    let activeFilters = [];

    // ---------- FILTER HELPERS ----------
    function getFilterLabel(filter, value) {
        switch (filter) {
            case 'category': return value;
            case 'expiry': return 'Expiry Date (Soonest to Latest)';
            case 'storage': return value;
            default: return filter;
        }
    }

    // Apply active filters to dataset
    function getFilteredItems(items) {
        let filtered = items.slice();

        activeFilters.forEach(f => {
            if (f.filter === 'category' && f.value) {
                filtered = filtered.filter(item => item.category === f.value);
            } else if (f.filter === 'expiry') {
                filtered = filtered.slice().sort((a, b) => new Date(a.expiry) - new Date(b.expiry));
            } else if (f.filter === 'storage' && f.value) {
                filtered = filtered.filter(item => item.storage === f.value);
            }
        });

        return filtered;
    }

    // ---------- FILTER TAG RENDERING ----------
    function renderTags() {
        const tagContainer = document.getElementById('filterTagContainer');
        if (!tagContainer) return;

        const currentTags = Array.from(tagContainer.children);
        const currentMap = new Map();
        currentTags.forEach(tag => currentMap.set(tag.dataset.key, tag));
        const activeKeys = new Set(activeFilters.map(f => `${f.filter}:${f.value}`));

        // Remove old tags
        currentTags.forEach(tag => {
            if (!activeKeys.has(tag.dataset.key)) {
                tag.classList.remove('show');
                setTimeout(() => tag.remove(), 300);
            }
        });

        // Add new tags
        activeFilters.forEach(f => {
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
                    activeFilters = activeFilters.filter(ff => `${ff.filter}:${ff.value}` !== key);
                    UpdateView();
                });

                tag.appendChild(closeIcon);
                tagContainer.appendChild(tag);
                requestAnimationFrame(() => tag.classList.add('show'));
            }
        });
    }

    let selectedCards = []; // store selected cards

    // ---------- CARD RENDERING ----------
    function renderCards(items) {
        const container = document.getElementById('foodCardContainer');
        if (!container) return;

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
            return;
        }

        items.forEach((item, idx) => {
            let expiry = item.expiry
                ? `<p class="card-text fs-6 mb-1"><strong>Expiry:</strong> ${item.expiry}</p>`
                : '';
            let storage = item.storage
                ? `<p class="card-text fs-6"><strong>Storage:</strong> ${item.storage}</p>`
                : '';

            const cardHtml = `
                <div class="col">
                    <div class="food-card card h-100" data-idx="${idx}">
                        <div class="card-header">
                            <h5 class="d-flex justify-content-between mb-0">${item.name}</h5>
                        </div>
                        <div class="card-body fw-medium">
                            <p class="card-text fs-6 mb-1"><strong>Quantity:</strong> ${item.quantity}</p>
                            <p class="card-text fs-6 mb-1"><strong>Category:</strong> ${item.category}</p>
                            ${expiry}
                            ${storage}
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += cardHtml;

        });
        
        document.querySelectorAll('.food-card').forEach(card => {
            card.addEventListener('click', function () {
                const idx = this.getAttribute('data-idx');
                const item = items[idx];

                // prevent duplicates
                if (selectedCards.some(card => card.item.foodItem_id === item.foodItem_id)) {
                    showToast(`${item.name} has already been added.`, 'danger');
                    return;
                }

                showQuantityModal(item);
            });
        });
    }

    function showQuantityModal(item) {
        const quantityModal = new bootstrap.Modal(document.getElementById('quantityModal'));
        const quantityInput = document.getElementById('quantityInput');

        // open quantity modal
        quantityInput.value = 1; // default value
        quantityModal.show();

        // confirm button behavior
        document.getElementById('quantityConfirm').onclick = function () {
            const quantity = Number(quantityInput.value);
            const quantityForm = document.getElementById('quantityForm');

            if (!quantity || isNaN(quantity) || quantity <= 0 || quantity>item.quantity) {
                quantityInput.setCustomValidity("Invalid");
            } else {
                quantityInput.setCustomValidity("");
            }

            if (!quantityForm.checkValidity()) {
                quantityForm.classList.add('was-validated');
                return;
            }
            
            selectedCards.push({
                item,
                quantity:Number(quantity)
            });

            quantityForm.classList.remove('was-validated');
            quantityForm.reset();
            renderSelectedCard();

            quantityModal.hide();
        };
    }

    // ---------- SELECTED CARD RENDERING ----------
    function renderSelectedCard() {
        const container = document.getElementById("selectedCardContainer");
        container.innerHTML = '';

        // build ingredient cards
        selectedCards.forEach((card, idx) => {
            const item = card.item;
            const cardHtml = `
                <div class="col">
                    <div class="selected-food-card card h-100" data-idx="${idx}">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${item.name}</h5>
                            <button type="button" class="btn-close selectedCardBtn-close fs-6" aria-label="Remove"></button>
                        </div>
                        <div class="card-body fw-medium">
                            <p class="card-text fs-6 mb-1"><strong>Quantity:</strong> ${card.quantity}</p>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += cardHtml;
        });

        container.querySelectorAll('.selectedCardBtn-close').forEach(closeBtn => {
            closeBtn.addEventListener('click', function () {
                const cardEl = this.closest('.selected-food-card');
                const idx = cardEl.getAttribute('data-idx');
                const tobe_removed = selectedCards[idx];

                // remove from array
                selectedCards = selectedCards.filter(
                    card => card.item.foodItem_id != tobe_removed.item.foodItem_id
                );

                // re-render
                renderSelectedCard();
            });
        });
    }

    // ---------- RENDER SUGGESTED RECIPE ----------
    const recipes = [
        {
            id: "R01",
            name: "Chicken Sandwich",
            calories: 420,
            ingredients: [
            { name: "Bread", quantity: 2 },          
            { name: "Chicken Breast", quantity: 2 },
            { name: "Lettuce", quantity: 1 },     
            { name: "Mayonnaise", quantity: 1 }
            ]
        },
        {
            id: "R02",
            name: "Fruit Salad",
            calories: 210,
            ingredients: [
            { name: "Apple", quantity: 1 },
            { name: "Banana", quantity: 1 },
            { name: "Grapes", quantity: 10 }
            ]
        },
        {
            id: "R03",
            name: "Vegetable Fried Rice",
            calories: 380,
            ingredients: [
            { name: "Cooked Rice", quantity: 1 }, 
            { name: "Carrot", quantity: 1 },     
            { name: "Peas", quantity: 1 },      
            { name: "Egg", quantity: 1 },
            { name: "Soy Sauce", quantity: 1 },    
            { name: "Garlic", quantity: 1 },      
            { name: "Oil", quantity: 1 }       
            ]
        }
    ];


    function renderSuggestedRecipe(recipes) {
        const container = document.getElementById('recipeCardContainer');
        container.innerHTML = '';

        recipes.forEach((recipe, idx) => {
            // Build full ingredient list in HTML format
            const ingredientsList = recipe.ingredients
                .map(i => `<tr><th class="ps-3">${i.name}</th> <td class="text-end pe-4">${i.quantity}</td></tr>`)
                .join('');

            const cardHtml = `
                <div class="col">
                    <div class="recipe-card card h-100" data-idx="${idx}" style="cursor: pointer;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${recipe.name}</h5>
                            <span class="badge calories-badge fs-6">${recipe.calories} kcal</span>
                        </div>
                        <div class="card-body fw-medium">
                            <table class="table table-sm table-striped fs-6 mb-0">
                                <tbody>${ingredientsList}</tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += cardHtml;
        });

        // Add click handler to “Select Recipe” buttons
        document.querySelectorAll('.recipe-card').forEach((card, idx) => {
            card.addEventListener('click', function () {
                const recipe = recipes[idx];
                
                fetch("../pages/meals/checkIngredients.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        ingredients: recipe.ingredients
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const matchedItems = data.matchedItems;
                    const missingItems = data.missingItems;          

                    if (data.success) {
                        addRecipeDetails(matchedItems, recipe);
                        return;
                    }

                    if (!matchedItems.length) {
                        showToast("None of the ingredients are available in your inventory", 'danger');
                        return;
                    }

                    // Show modal if there's misssing ingredients
                    show_MissingIngredients_Modal(matchedItems, missingItems, recipe);
                })
                .catch(() => {
                    alert('Error: Failed to check ingredients.');
                });
            });
        });
    }

    function show_MissingIngredients_Modal(matchedItems, missingItems, recipe) {
        const missingModal = new bootstrap.Modal(document.getElementById('missingIngredientsModal'));
        const missingMsg = document.getElementById('missingIngredientsMsg');
        const btnMissingConfirm = document.getElementById('missingConfirm');

        // 🟥 Missing ingredients → show modal
        missingMsg.innerHTML = "<strong>Missing:</strong> <br>" + missingItems.join("<br>");

        missingModal.show();

        // ✅ If YES → add only matched items
        btnMissingConfirm.onclick = () => {
            addRecipeDetails(matchedItems, recipe);
            missingModal.hide();
        };

        // ❌ NO → do nothing
        document.getElementById('missingCancel').onclick = () => {
            missingModal.hide();
        };
    }

    function addRecipeDetails(matchedItems, recipe) {
        const title = document.getElementById('mealTitle');
        title.value = recipe.name;

        // Loop add only those NOT already in selectedCards
        matchedItems.forEach((item, index) => {
            const exists = selectedCards.some(c => c.item.foodItem_id === item.foodItem_id);
            if (exists) return; // skip duplicates

            selectedCards.push({
                item,
                quantity: recipe.ingredients[index].quantity
            });
        });

        renderSelectedCard();
    }

    function showToast(message, type = "info") {
        const toastContainer = document.querySelector(".toast-container")

        const toastEl = document.createElement("div");
        toastEl.className = `toast align-items-center text-bg-${type} border-0 show`;
        toastEl.role = "alert";
        toastEl.innerHTML = `
            <div class="d-flex px-3">
                <div class="toast-body fs-6 fw-medium">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);

        setTimeout(() => toastEl.remove(), 5000);
    }

    // ---------- COMBINED UPDATE ----------
    function UpdateView() {
        renderTags();
        const filteredItems = getFilteredItems(allItems);
        renderCards(filteredItems);
    }

    // ---------- SETUP ----------
    function setupFilterListeners() {
        document.querySelectorAll('.filter-option').forEach(el => {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                const filter = this.getAttribute('data-filter');
                const value = this.getAttribute('data-value');
                if (!activeFilters.some(f => f.filter === filter && (f.value === value || !value))) {
                    activeFilters.push({ filter, value });
                    UpdateView();
                }
            });
        });
    }

    // Example initializer — call this after you load your items
    function initFilterAndCards(items) {
        allItems = items;
        setupFilterListeners();
        UpdateView();
    }

    function fetchFoodItemsAndInit() {
        fetch('../pages/meals/get_available_fooditems.php')
        .then(response => response.json())
        .then(data => {
            if (Array.isArray(data)) {
                initFilterAndCards(data);
            } else {
                console.error("Unexpected data format:", data);
                foodItems = [];
            }
        })
        .catch(err => {
            console.error('Failed to fetch food items:', err);
        });
    }

    function fetchAndRender_CalendarEvents(calendar) {
        fetch('../pages/meals/get_calendar_events.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                console.error("Failed to fetch events:", data.message);
                return;
            }

            // clear existing events before addition
            calendar.getEvents().forEach(event => event.remove());

            const eventColor = {
                breakfast: '#e9edc9',
                lunch: '#ccd5ae',
                dinner: '#d4a373',
                snack: '#d6b18bff'
            };

            const events = data.data;

            events.forEach(event => {
                calendar.addEvent({
                    id: event.mealplan_id,
                    title: event.title,
                    start: event.date,
                    description: event.description,
                    mealSlot: event.mealSlot,
                    selectedCards: event.ingredients,
                    color: eventColor[event.mealSlot] || 'gray'
                });
            });
        })
        .catch(err => {
            console.error('Failed to fetch events:', err);
        });
    }

    function initMealsPage() {
        initializeCalendar();
        renderSuggestedRecipe(recipes);
        fetchFoodItemsAndInit();
    }

    window.initMealsPage = initMealsPage;
})();