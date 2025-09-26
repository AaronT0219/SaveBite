<div class="flex-grow-1 pt-3" style="min-height: 30vh;" id="BFI">
    <!-- Top Nav -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h1 class="fw-bold">Browse Food Items</h1>
        <div class="d-flex">
            <div class="dropdown">
                <button class="btn dropdown-toggle btn-lg px-4 filter-btn" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    Filter
                </button>
                <ul class="dropdown-menu" aria-labelledby="filterDropdown" id="filterMenu">
                    <li><a class="dropdown-item filter-option" data-filter="inventory" href="#">Inventory Only</a></li>
                    <li><a class="dropdown-item filter-option" data-filter="donation" href="#">Donation Listings</a></li>
                    <li class="dropdown-submenu">
                        <span class="dropdown-item disabled" id="categoryDropdown" aria-expanded="false">Categories</span>
                        <ul class="dropdown-menu" aria-labelledby="categoryDropdown">
                            <li><a class="dropdown-item filter-option" data-filter="category" data-value="Fruit" href="#">Fruit</a></li>
                            <li><a class="dropdown-item filter-option" data-filter="category" data-value="Dairy" href="#">Dairy</a></li>
                            <li><a class="dropdown-item filter-option" data-filter="category" data-value="Bakery" href="#">Bakery</a></li>
                        </ul>
                    </li>
                    <li><a class="dropdown-item filter-option" data-filter="expiry" href="#">Expiry Date (Soonest First)</a></li>
                    <li class="dropdown-submenu">
                        <span class="dropdown-item disabled" id="storageDropdown" aria-expanded="false">Storage Type</span>
                        <ul class="dropdown-menu" aria-labelledby="storageDropdown">
                            <li><a class="dropdown-item filter-option" data-filter="storage" data-value="Fridge" href="#">Fridge</a></li>
                            <li><a class="dropdown-item filter-option" data-filter="storage" data-value="Freezer" href="#">Freezer</a></li>
                            <li><a class="dropdown-item filter-option" data-filter="storage" data-value="Pantry" href="#">Pantry</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Filter Tags -->
    <div class="w-75 d-flex flex-wrap gap-4" id="filterTagContainer">
        <!-- Filter tags will be dynamically inserted here by JS -->
    </div>

    <!-- Food Card Items -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 py-3" id="foodCardContainer">
            <!-- cards will be dynamically inserted here by javaScript -->
    </div>

    <!-- Food Item Modal -->
    <div class="modal fade" id="foodItemModal" tabindex="-1" aria-labelledby="foodItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header px-4">
                    <h5 class="modal-title" id="foodItemModalLabel"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-0 fs-5 fw-medium" id="foodItemModalBody">
                    <!-- Details will be filled by JS -->
                </div>
                <div class="modal-footer" id="foodItemModalFooter">
                    <!-- Action buttons will be filled by JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../BFI/BFI.js"></script>