<?php
header('Content-Type: application/json');
require_once '../../config.php';

session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

$meal_id = $data['meal_id'] ?? null;

if (!$meal_id) {
    echo json_encode(["success" => false, "message" => "Meal ID missing."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1️⃣ Fetch all reserved fooditems linked to this meal plan
    $get_reserved_items = $pdo->prepare("
        SELECT mf.fooditem_id, mf.quantity, f.food_name
        FROM mealplan_fooditem mf
        JOIN fooditem f ON mf.fooditem_id = f.foodItem_id
        WHERE mf.mealplan_id = ? AND f.user_id = ? AND f.status = 'reserved'
    ");
    $get_reserved_items->execute([$meal_id, $user_id]);
    $reserved_items = $get_reserved_items->fetchAll(PDO::FETCH_ASSOC);

    // 2️⃣ Prepare reusable statements
    $get_original = $pdo->prepare("
        SELECT foodItem_id FROM fooditem 
        WHERE user_id = ? AND food_name = ? AND status != 'reserved' LIMIT 1
    ");

    $update_stock = $pdo->prepare("
        UPDATE fooditem 
        SET quantity = quantity + ? 
        WHERE foodItem_id = ? AND user_id = ?
    ");

    $update_reserved_to_available = $pdo->prepare("
        UPDATE fooditem 
        SET status = 'available'
        WHERE foodItem_id = ? AND user_id = ? AND status = 'reserved'
    ");

    $delete_reserved_item = $pdo->prepare("
        DELETE FROM fooditem 
        WHERE foodItem_id = ? AND user_id = ? AND status = 'reserved'
    ");

    // 3️⃣ Process each reserved item
    foreach ($reserved_items as $item) {
        $get_original->execute([$user_id, $item['food_name']]);
        $original = $get_original->fetch(PDO::FETCH_ASSOC);

        if ($original) {
            // ✅ Original exists → restore its stock
            $update_stock->execute([$item['quantity'], $original['foodItem_id'], $user_id]);
            // Then delete the reserved copy
            $delete_reserved_item->execute([$item['fooditem_id'], $user_id]);
        } else {
            // ⚠ Original deleted → just convert reserved to available
            $update_reserved_to_available->execute([$item['fooditem_id'], $user_id]);
        }
    }

    // 4️⃣ Delete junction entries
    $delete_junction = $pdo->prepare("
        DELETE FROM mealplan_fooditem WHERE mealplan_id = ?
    ");
    $delete_junction->execute([$meal_id]);

    // 5️⃣ Delete notification linked to this meal plan
    $delete_notification = $pdo->prepare("
        DELETE FROM notification
        WHERE user_id = ? AND target_type = 'meal_plan' AND target_id = ?
    ");
    $delete_notification->execute([$user_id, $meal_id]);

    // 6️⃣ Delete the meal plan itself
    $delete_meal = $pdo->prepare("
        DELETE FROM mealplan
        WHERE mealplan_id = ? AND user_id = ?
    ");
    $delete_meal->execute([$meal_id, $user_id]);

    // ✅ Commit all changes
    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Meal plan deleted successfully and stock restored."
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "Database error on line " . $e->getLine() .
                     " in " . basename($e->getFile()) . ": " . $e->getMessage()
    ]);
}
