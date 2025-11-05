<head>
    <link rel="stylesheet" href="../pages/meals/meals.css">
</head>

<body>
    <div class="container-fluid p-4" id="PWM">
        <!-- Top Nav -->
        <div class="d-flex justify-content-between align-items-center mb-2 py-3 px-4 bg-light rounded shadow">
            <h1 class="fw-bold">Plan Weekly Meals</h1>

            <button class="btn btn-lg fw-medium addMeal-btn" data-bs-target="#addMealModal"  data-bs-toggle="modal">Add Meal</button>
        </div>

        <!-- Calendar -->
        <div class="w-100 h-100 px-3 mt-5" id="calendar"></div>

        <!-- Add Meal Modal -->
        <div class="modal fade" id="addMealModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="addMealModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-body fs-4 fw-medium d-flex flex-column">
                        <div class="w-100 text-end">
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="row row-cols-2 gy-2 gx-4 mx-2 mb-3">
                            <!-- Current Inventory -->
                            <div class="col">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-medium fs-3 fw-bolder">Current Inventory</div>

                                    <div class="dropdown">
                                        <button class="btn btn-lg dropdown-toggle px-3 filter-btn" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">Filter</button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown" id="filterMenu">
                                            <li class="dropdown-submenu">
                                                <span id="categoryDropdown" class="dropdown-item disabled">Categories</span>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item filter-option" data-filter="category" data-value="Produce" href="#">Produce</a></li>
                                                    <li><a class="dropdown-item filter-option" data-filter="category" data-value="Protein" href="#">Protein</a></li>
                                                    <li><a class="dropdown-item filter-option" data-filter="category" data-value="Dairy & Bakery" href="#">Dairy & Bakery</a></li>
                                                    <li><a class="dropdown-item filter-option" data-filter="category" data-value="Grains & Pantry" href="#">Grains & Pantry</a></li>
                                                    <li><a class="dropdown-item filter-option" data-filter="category" data-value="Snacks & Beverages" href="#">Snacks & Beverages</a></li>
                                                </ul>
                                            </li>
                                            <li><a class="dropdown-item filter-option" data-filter="expiry" href="#">Expiry Date (Soonest to Latest)</a></li>
                                            <li class="dropdown-submenu">
                                                <span id="storageDropdown" class="dropdown-item disabled">Storage Type</span>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item filter-option" data-filter="storage" data-value="Fridge" href="#">Fridge</a></li>
                                                    <li><a class="dropdown-item filter-option" data-filter="storage" data-value="Freezer" href="#">Freezer</a></li>
                                                    <li><a class="dropdown-item filter-option" data-filter="storage" data-value="Pantry" href="#">Pantry</a></li>
                                                    <li><a class="dropdown-item filter-option" data-filter="storage" data-value="Countertop" href="#">Countertop</a></li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Active Filter Container -->
                                <div class="w-75 d-flex flex-wrap gap-4 px-4 mt-2 mb-3" id="filterTagContainer"></div>

                                <!-- Food Item Container -->
                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 overflow-y-auto" id="foodCardContainer" style="max-height: 330px"></div>
                            </div>

                            <!-- Meal Form -->
                            <div class="col">
                                <form id="mealForm" class="input-fields h-100 d-flex flex-column gap-4 overflow-y-auto px-1 pb-1" style="max-height: 400px">
                                    <div class="flex-fill">
                                        <label for="mealTitle" class="form-label">Meal Name</label>
                                        <input type="text" class="form-control fs-5" id="mealTitle" placeholder="e.g. Chicken Salad" required>
                                    </div>
        
                                    <div class="flex-fill">
                                        <label for="mealDescription" class="form-label">Description</label>
                                        <textarea class="form-control fs-5" id="mealDescription" rows="3" placeholder="Optional" style="height: 150px"></textarea>
                                    </div>

                                    <div class="flex-fill d-flex align-items-center gap-3">
                                        <div class="flex-fill">
                                            <select id="mealSlot" class="form-select fw-medium fs-5" aria-label="Meal slot select">
                                                <option selected value="breakfast" class="fw-medium">Breakfast</option>
                                                <option value="lunch" class="fw-medium">Lunch</option>
                                                <option value="dinner" class="fw-medium">Dinner</option>
                                                <option value="snacks" class="fw-medium">Snacks</option>
                                            </select>
                                        </div>

                                        <div class="d-flex gap-2 align-items-center flex-fill">
                                            <label for="mealDate" class="fs-5">Date</label>
                                            <input type="date" class="form-control fs-5" id="mealDate" required>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Suggested Recipe -->
                            <div class="col">
                                <div class="fw-medium fs-3 fw-bolder">Suggested Recipe</div>

                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mt-auto" id="recipeCardContainer"></div>
                            </div>

                            <!-- Selected Food Item -->
                            <div class="col">
                                <div class="fw-medium fs-3 fw-bolder mb-3">Selected Ingredients</div>

                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3" id="selectedCardContainer"></div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-primary w-100 fs-5 fw-medium text-dark" id="mealConfirm-btn">Confirm</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quantity Input Modal -->
        <div class="modal fade" id="quantityModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <div class="modal-title fs-5 fw-bold">Enter Quantity</div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <form id="quantityForm" novalidate>
                            <input type="number" id="quantityInput" class="form-control" min="1" value="1">
                            <div class="invalid-feedback">
                                Please enter a valid quantity
                            </div>
    
                            <button type="button" id="quantityConfirm" class="btn btn-primary fw-medium text-dark w-100 mt-3">Confirm</button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>

        <!-- Missing Ingredients Modal -->
        <div class="modal fade" id="missingIngredientsModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Missing Ingredients</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p id="missingIngredientsMsg"></p>
                        <p>Do you want to continue and add the remaining ingredients?</p>

                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-secondary flex-fill" id="missingCancel" data-bs-dismiss="modal">No</button>
                            <button type="button" class="btn flex-fill" id="missingConfirm">Yes</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
     </div>
</body>