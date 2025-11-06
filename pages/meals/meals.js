(function () {
    function initializeCalendar() {
        const calendarEl = document.getElementById("calendar");

        const calendar = new FullCalendar.Calendar(calendarEl, {
            headerToolbar: {
                right: 'prev,next today',
                center: 'title',
                left: 'dayGridMonth,timeGridWeek'
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
            editable: true,
            eventColor: "#D4A373",
            eventDisplay: "block",
            events: [],
            eventTimeFormat: {
                hour: "2-digit",
                minute: "2-digit",
            },
            eventClick: function (info) {
                console.log(info.event.extendedProps.mealSlot);
                console.log(info.event.extendedProps.selectedCards);
            }
        });

        calendar.render();

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
                    
                    fetchFoodItemsAndInit();

                    // Add event to FullCalendar
                    calendar.addEvent({
                        title: title,
                        start: date,
                        description: desc,
                        mealSlot: mealSlot,
                        selectedCards: selectedCards
                    });

                    // Reset Add Meal Modal
                    form.reset();
                    form.classList.remove('was-validated');
                    selectedCards = [];
                    renderSelectedCard();
                    const alertMsg = document.querySelector('.selectedCards-alert');
                    if (alertMsg) alertMsg.remove();

                    // Close modal
                    addMealModal.hide();
                })
                .catch(() => {
                    alert('Error: Failed to create meal plan.');
                });
            } else {
                form.classList.add('was-validated');
            }
        });
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
                    alert(`${item.name} has already been added.`);
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
                .map(i => `<tr><th>${i.name}</th> <td class="text-end">${i.quantity}</td></tr>`)
                .join('');

            const cardHtml = `
                <div class="col">
                    <div class="recipe-card card h-100" data-idx="${idx}" style="cursor: pointer;">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">${recipe.name}</h5>
                            <span class="badge calories-badge fs-6">${recipe.calories} kcal</span>
                        </div>
                        <div class="card-body fw-medium">
                            <table class="table table-sm table-striped fs-6">
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
                        addRecipeIngredients(matchedItems, recipe);
                        return;
                    }

                    if (!matchedItems.length) {
                        alert("None of the ingredients are available in your inventory");
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
            addRecipeIngredients(matchedItems, recipe);
            missingModal.hide();
        };

        // ❌ NO → do nothing
        document.getElementById('missingCancel').onclick = () => {
            missingModal.hide();
        };
    }

    function addRecipeIngredients(matchedItems, recipe) {
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

    function initMealsPage() {
        initializeCalendar();
        renderSuggestedRecipe(recipes);
        fetchFoodItemsAndInit();
    }

    window.initMealsPage = initMealsPage;
})();