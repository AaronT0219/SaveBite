<div id="BFI" class="pt-3" style="min-height:30vh;">
    <div class="d-flex justify-content-between align-items-center mb-2">
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
                <li><a class="dropdown-item filter-option" data-filter="expiry" href="#">Expiry Date (Soonest First)</a></li>
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
    <div class="w-75 d-flex flex-wrap gap-4" id="filterTagContainer"></div>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 py-3" id="foodCardContainer"></div>
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
</div>
<script src="../BFI/BFI.js"></script>