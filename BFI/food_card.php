<?php
// food_card.php
// Usage: include 'food_card.php'; echo food_card('Apple', 10, 'Fruit');

function food_card($name, $quantity, $category, $donated = false, $expiry = '', $storage = '') {
    $donatedTag = $donated ? '<span class="badge bg-success ms-2">Donated</span>' : '';
    $expiryText = $expiry ? '<p class="card-text mb-1"><strong>Expiry:</strong> ' . htmlspecialchars($expiry) . '</p>' : '';
    $storageText = $storage ? '<p class="card-text"><strong>Storage:</strong> ' . htmlspecialchars($storage) . '</p>' : '';
    return '
    <div class="col">
        <div class="food-card card">
            <h4 class="card-header d-flex justify-content-between">' . htmlspecialchars($name) . ' ' . $donatedTag . '</h5>
            <div class="card-body">
                <p class="card-text mb-1"><strong>Quantity:</strong> ' . htmlspecialchars($quantity) . '</p>
                <p class="card-text mb-1"><strong>Category:</strong> ' . htmlspecialchars($category) . '</p>
                ' . $expiryText . '
                ' . $storageText . '
                <div class="btn-container">
                    <hr>
                    <div class="row row-cols-1 row-cols-xxl-3 g-4">
                        <div class="col">
                            <a href="#" class="btn btn-primary card-btn">
                                <div class="d-flex justify-content-center">
                                    <span class="btn-icon me-2" data-lucide="check-check"></span>
                                    Mark Used
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="#" class="btn btn-primary card-btn">
                                <div class="d-flex justify-content-center">
                                    <span class="btn-icon me-2" data-lucide="cooking-pot"></span>
                                    Plan Meal
                                </div>
                            </a>
                        </div>
                        <div class="col">
                            <a href="#" class="btn btn-primary card-btn">
                                <div class="d-flex justify-content-center">
                                    <span class="btn-icon me-2" data-lucide="flag"></span>
                                    Donation
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    ';
}
?>
