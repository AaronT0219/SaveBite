<?php
header("Content-Type: application/json");
require_once '../../config.php';

session_start();
if(isset($_SESSION['id'])) {
    $id = $_SESSION['id'];

    $data = json_decode(file_get_contents("php://input"), true);
    $ingredients = $data['ingredients'];

    $errors = [];
    $foodItems = [];

    foreach ($ingredients as $ing) {
        $name = mysqli_real_escape_string($conn, $ing['name']);
        $neededQty = (int)$ing['quantity'];

        $sql = "
            SELECT *
            FROM fooditem 
            WHERE user_id = '$id'
            AND food_name = '$name'
            AND status = 'available'
            LIMIT 1
        ";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 0) {
            $errors[] = "$name not found in your inventory";
            continue;
        }

        $row = mysqli_fetch_assoc($result);

        if ($row['quantity'] < $neededQty) {
            $errors[] = "$name only has {$row['quantity']} but needs $neededQty";
            continue;
        }

        $mappedItem = [
            'foodItem_id' => $row['foodItem_id'],
            'name' => $row['food_name'],
            'quantity' => (int)$row['quantity'],
            'category' => $row['category'],
            'donation' => $row['status'] === 'donation',
            'reserved' => $row['status'] === 'reserved',
            'used' => $row['status'] === 'used',
            'expiry' => $row['expiry_date'],
            'storage' => $row['storage_location'],
            'description' => $row['description'] ?? ''
        ];

        $foodItems[] = $mappedItem;
    }

    if (!empty($errors)) {
        echo json_encode([
            "success" => false,
            "message" => "Ingredients missing",
            "matchedItems" => $foodItems,
            "missingItems" => $errors
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "Ingredients valid",
            "matchedItems" => $foodItems
        ]);
    }
}
