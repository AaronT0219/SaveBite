<head>
    <link rel="stylesheet" href="../pages/browse/browse.css">
</head>
<body>
    <div id="BFI" class="container-fluid p-4">
        <!-- Top Nav -->
        <div class="d-flex justify-content-between align-items-center mb-2 py-3 px-4 bg-light rounded shadow">
            <h1 class="fw-bold">Browse Food Items</h1>
            <div class="dropdown">
                <button class="btn dropdown-toggle btn-lg px-4 filter-btn" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">Filter</button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown" id="filterMenu">
                    <li><a class="dropdown-item filter-option" data-filter="inventory" href="#">Inventory Only</a></li>
                    <li><a class="dropdown-item filter-option" data-filter="donation" href="#">Donation Listings</a></li>
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
        <div class="w-75 d-flex flex-wrap gap-4 px-4 my-4" id="filterTagContainer"></div>

        <!-- Food Item Container -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 px-3" id="foodCardContainer"></div>
 
        <!-- Food Item Modal -->
        <div class="modal fade" id="foodItemModal" tabindex="-1" aria-labelledby="foodItemModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header px-4">
                        <h5 class="modal-title" id="foodItemModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-0 fs-5 fw-medium" id="foodItemModalBody"></div>
                    <div class="modal-footer" id="foodItemModalFooter"></div>
                </div>
            </div>
        </div>
    
        <!-- Donation Form Modal -->
        <div class="modal fade" id="donationFormModal" tabindex="-1" aria-labelledby="donationFormModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">\
                <div class="modal-content">
                    <div class="modal-header px-4">
                        <div class="modal-title" id="donationFormModalLabel"><h3 class="fw-bold">Donation Form</h3></div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="donation-form">
                        <div class="modal-body py-0">
                            <div class="mb-3 mt-2">
                                <label for="pickup_location" class="form-label fs-5 fw-medium">Pickup Location</label>
                                <input type="text" name="pickup_location" class="form-control" id="pickup_location" placeholder="Enter pickup location" required>
                                <div class="invalid-feedback">
                                    Field can't be empty
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="availability" class="form-label fs-5 fw-medium">Availability</label>
                                <input type="date" name="availability" class="form-control" id="availability" required>
                                <div class="invalid-feedback">
                                    Field can't be empty
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-lg fw-medium w-100" id="donationForm-submit-btn" type="button">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>