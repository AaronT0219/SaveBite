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
                alert('Event: ' + info.event.title);
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
            console.log('confirm button clicked!!!');
            e.preventDefault();

            const title = document.getElementById('mealTitle').value.trim();
            const date = document.getElementById('mealDate').value;
            const desc = document.getElementById('mealDescription').value.trim();

            if (!title || !date) return;

            // Add event to FullCalendar
            calendar.addEvent({
                title: title,
                start: date,
                description: desc
            });

            // Close modal
            addMealModal.hide();
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

        // You can replace this click event with your own modal or logic
        document.querySelectorAll('.food-card').forEach(card => {
            card.addEventListener('click', function () {
                const idx = this.getAttribute('data-idx');
                console.log('Card clicked:', items[idx]);
            });
        });
    }

    // ---------- RENDER SUGGESTED RECIPE ----------
    const recipes = [
    {
        name: "Chicken Sandwich",
        calories: 420,
        ingredients: [
        { name: "Bread", quantity: 2, unit: "slices" },
        { name: "Chicken Breast", quantity: 100, unit: "g" },
        { name: "Lettuce", quantity: 1, unit: "leaf" },
        { name: "Mayonnaise", quantity: 1, unit: "tbsp" }
        ]
    },
    {
        name: "Fruit Salad",
        calories: 210,
        ingredients: [
        { name: "Apple", quantity: 1, unit: "pcs" },
        { name: "Banana", quantity: 1, unit: "pcs" },
        { name: "Grapes", quantity: 10, unit: "pcs" }
        ]
    },
    {
        name: "Vegetable Fried Rice",
        image: "images/veg_fried_rice.jpg",
        calories: 380,
        ingredients: [
        { name: "Cooked Rice", quantity: 1, unit: "cup" },
        { name: "Carrot", quantity: 0.5, unit: "cup (diced)" },
        { name: "Peas", quantity: 0.25, unit: "cup" },
        { name: "Egg", quantity: 1, unit: "pcs" },
        { name: "Soy Sauce", quantity: 1, unit: "tbsp" },
        { name: "Garlic", quantity: 1, unit: "clove (minced)" },
        { name: "Oil", quantity: 1, unit: "tbsp" }
        ]
    }
    ];

    function renderSuggestedRecipe(recipes) {
        const container = document.getElementById('recipeCardContainer');
        container.innerHTML = '';

        recipes.forEach((recipe, idx) => {
            // Build full ingredient list in HTML format
            const ingredientsList = recipe.ingredients
                .map(i => `<tr><th>${i.name}</th> <td>${i.quantity}</td> <td>${i.unit}</td></tr>`)
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
        document.querySelectorAll('.recipe-card').forEach( (card) => {
            card.addEventListener('click', function () {
                const idx = this.getAttribute('data-idx');
                const recipe = recipes[idx];
                console.log('Selected recipe:', recipe);
            });
        });
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