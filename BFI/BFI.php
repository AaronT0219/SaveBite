<div class="flex-grow-1 pt-3" style="min-height: 100vh;" id="BFI">
    <!-- Top Nav -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Browse Food Items</h1>
        <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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

    <hr class="mb-5">

    <!-- Food Card Items -->
    <?php
        include 'food_card.php';
        //food items sample data
        $food_items = [
            [
                'name' => 'Apple',
                'quantity' => 10,
                'category' => 'Fruit',
                'donated' => false,
                'expiry' => '2025-09-25',
                'storage' => 'Fridge',
            ],
            [
                'name' => 'Milk',
                'quantity' => 2,
                'category' => 'Dairy',
                'donated' => true,
                'expiry' => '2025-09-21',
                'storage' => 'Fridge',
            ],
            [
                'name' => 'Bread',
                'quantity' => 5,
                'category' => 'Bakery',
                'donated' => false,
                'expiry' => '2025-09-22',
                'storage' => 'Pantry',
            ],
            [
                'name' => 'Banana',
                'quantity' => 8,
                'category' => 'Fruit',
                'donated' => true,
                'expiry' => '2025-09-23',
                'storage' => 'Pantry',
            ],
            [
                'name' => 'Cheese',
                'quantity' => 3,
                'category' => 'Dairy',
                'donated' => false,
                'expiry' => '2025-09-28',
                'storage' => 'Fridge',
            ],
            [
                'name' => 'Frozen Peas',
                'quantity' => 6,
                'category' => 'Vegetable',
                'donated' => false,
                'expiry' => '2025-12-01',
                'storage' => 'Freezer',
            ],
            [
                'name' => 'Yogurt',
                'quantity' => 4,
                'category' => 'Dairy',
                'donated' => true,
                'expiry' => '2025-09-24',
                'storage' => 'Fridge',
            ],
            [
                'name' => 'Orange',
                'quantity' => 12,
                'category' => 'Fruit',
                'donated' => false,
                'expiry' => '2025-09-27',
                'storage' => 'Fridge',
            ],
            [
                'name' => 'Rice',
                'quantity' => 1,
                'category' => 'Grain',
                'donated' => false,
                'expiry' => '2026-01-15',
                'storage' => 'Pantry',
            ],
        ];
    ?>
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="foodCardContainer">
        <?php
            //write out sample data
            foreach ($food_items as $item) {
                echo food_card(
                    $item['name'],
                    $item['quantity'],
                    $item['category'],
                    $item['donated'],
                    $item['expiry'],
                    $item['storage']
                );
            }
        ?>
    </div>
</div>

<script>
    // Get all food items from PHP as JS array
    const foodItems = <?php echo json_encode($food_items); ?>;

    function renderCards(items) {
        const container = document.getElementById('foodCardContainer');
        container.innerHTML = '';
        items.forEach(item => {
            let donatedTag = item.donated ? '<span class="badge bg-success ms-2">Donated</span>' : '';
            let expiry = item.expiry ? `<p class=\"card-text mb-1\"><strong>Expiry:</strong> ${item.expiry}</p>` : '';
            let storage = item.storage ? `<p class=\"card-text\"><strong>Storage:</strong> ${item.storage}</p>` : '';
            container.innerHTML += `
            <div class=\"col\">
                <div class=\"card h-100\">
                    <div class=\"card-body\">
                        <h5 class=\"card-title\">${item.name} ${donatedTag}</h5>
                        <p class=\"card-text mb-1\"><strong>Quantity:</strong> ${item.quantity}</p>
                        <p class=\"card-text mb-1\"><strong>Category:</strong> ${item.category}</p>
                        ${expiry}
                        ${storage}
                    </div>
                </div>
            </div>
            `;
        });
    }

    document.querySelectorAll('.filter-option').forEach(el => {
        el.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.getAttribute('data-filter');
            const value = this.getAttribute('data-value');
            let filtered = foodItems.slice();
            if (filter === 'inventory') {
                filtered = foodItems.filter(item => !item.donated);
            } else if (filter === 'donation') {
                filtered = foodItems.filter(item => item.donated);
            } else if (filter === 'category' && value) {
                filtered = foodItems.filter(item => item.category === value);
            } else if (filter === 'expiry') {
                filtered = foodItems.slice().sort((a, b) => new Date(a.expiry) - new Date(b.expiry));
            } else if (filter === 'storage' && value) {
                filtered = foodItems.filter(item => item.storage === value);
            }
            renderCards(filtered);
        });
    });
</script>