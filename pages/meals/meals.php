<head>
    <link rel="stylesheet" href="../pages/meals/meals.css">
</head>

<body>
    <div class="container-fluid p-4">
        <!-- Top Nav -->
        <div class="d-flex justify-content-between align-items-center mb-2 py-3 px-4 bg-light rounded shadow">
            <h1 class="fw-bold">Plan Weekly Meals</h1>

            <button class="btn btn-lg fw-medium fs addMeal-btn" data-bs-target="#addMealModal"  data-bs-toggle="modal">Add Meal</button>
        </div>

        <!-- Date Picker -->
        <!-- <div class="w-100 my-5">
            <div class="date-container d-flex position-relative mx-auto">
                <input type="date" class="date-picker form-control form-control-lg px-4">
                <i data-lucide="calendar-days" class="icon date-icon position-absolute translate-middle"></i>
            </div>
        </div> -->

        <!-- Calendar -->
        <div class="w-100 px-3 mt-5" id="calendar"></div>

        <!-- Add Meal Modal -->
        <div class="modal fade" id="addMealModal" tabindex="-1" aria-labelledby="addMealModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content">
                    <div class="modal-body">
                        <form id="mealForm">
                            <div class="container-fluid d-flex gap-3">
                                <div class="foodItems-container">
                                    foods here.
                                </div>
    
                                <div class="input-fields flex-fill">
                                    <div class="mb-3">
                                        <label for="mealTitle" class="form-label">Meal Name</label>
                                        <input type="text" class="form-control" id="mealTitle" placeholder="e.g. Chicken Salad" required>
                                    </div>
        
                                    <div class="mb-3">
                                        <label for="mealDescription" class="form-label">Description</label>
                                        <textarea class="form-control" id="mealDescription" rows="3" placeholder="Optional"></textarea>
                                    </div>

                                    <div class="d-flex mb-3 gap-3">
                                        <select class="form-select w-25" aria-label="Meal slot select">
                                                <option selected>Breakfast</option>
                                                <option value="1">Lunch</option>
                                                <option value="2">Dinner</option>
                                                <option value="3">TeaLunch</option>
                                        </select>
                                        <div class="d-flex gap-2 align-items-center flex-grow-1">
                                            <label for="mealDate">Date</label>
                                            <input type="date" class="form-control" id="mealDate" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary w-100" id="mealConfirm-btn">Confirm</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
     </div>
</body>