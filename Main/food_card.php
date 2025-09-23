<?php
// food_card.php
// Usage: include 'food_card.php'; echo food_card('Apple', 10, 'Fruit');

function food_card($name, $quantity, $category, $donated = false, $expiry = '', $storage = '') {
    $donatedTag = $donated ? '<span class="badge bg-success ms-2">Donated</span>' : '';
    $expiryText = $expiry ? '<p class="card-text mb-1"><strong>Expiry:</strong> ' . htmlspecialchars($expiry) . '</p>' : '';
    $storageText = $storage ? '<p class="card-text"><strong>Storage:</strong> ' . htmlspecialchars($storage) . '</p>' : '';
    return '
    <div class="col">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">' . htmlspecialchars($name) . ' ' . $donatedTag . '</h5>
                <p class="card-text mb-1"><strong>Quantity:</strong> ' . htmlspecialchars($quantity) . '</p>
                <p class="card-text mb-1"><strong>Category:</strong> ' . htmlspecialchars($category) . '</p>
                ' . $expiryText . '
                ' . $storageText . '
            </div>
        </div>
    </div>
    ';
}
