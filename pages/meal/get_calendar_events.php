<?php
header('Content-Type: application/json');
require_once '../../config.php';

session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['id'];

try {
    // 1️⃣ Fetch all meal plans for current user
    $get_mealplan = $pdo->prepare("
        SELECT *
        FROM mealplan
        WHERE user_id = ?
        ORDER BY mealplan_date DESC
    ");
    $get_mealplan->execute([$user_id]);
    $mealplans = $get_mealplan->fetchAll(PDO::FETCH_ASSOC);

    // 2️⃣ Build structured array including ingredients
    $result = [];

    $get_mealplan_ingredients = $pdo->prepare("
        SELECT fi.*
        FROM mealplan_fooditem mf
        JOIN fooditem fi ON mf.fooditem_id = fi.foodItem_id
        WHERE mf.mealplan_id = ?
    ");

    foreach ($mealplans as $meal) {
        $get_mealplan_ingredients->execute([$meal['mealplan_id']]);
        $ingredients = $get_mealplan_ingredients->fetchAll(PDO::FETCH_ASSOC);

        $result[] = [
            'mealplan_id' => $meal['mealplan_id'],
            'title' => $meal['meal_name'],
            'description' => $meal['description'],
            'date' => $meal['mealplan_date'],
            'mealSlot' => $meal['meal_type'],
            'ingredients' => $ingredients
        ];
    }

    echo json_encode(["success" => true, "data" => $result]);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error on line " . $e->getLine() .
                     " in " . basename($e->getFile()) . ": " . $e->getMessage()
    ]);
}
