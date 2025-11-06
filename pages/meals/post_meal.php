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

$title = mysqli_real_escape_string($conn, $data['title']);
$desc = mysqli_real_escape_string($conn, $data['desc']);
$date = mysqli_real_escape_string($conn, $data['date']);
$mealSlot = mysqli_real_escape_string($conn, $data['mealSlot']);
$selectedCards = $data['selectedCards'];

if (empty($selectedCards)) {
    echo json_encode(["success" => false, "message" => "No ingredients selected."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1️⃣ Insert meal record
    $post_meal = $pdo->prepare("
        INSERT INTO mealplan (user_id, meal_name, description, mealplan_date, meal_type)
        VALUES (?, ?, ?, ?, ?)
    ");
    $post_meal->execute([$user_id, $title, $desc, $date, $mealSlot]);

    $meal_id = $pdo->lastInsertId();

    // 2️⃣ Insert each selected ingredient
    $post_mealplan_fooditem = $pdo->prepare("
        INSERT INTO mealplan_fooditem (mealplan_id, fooditem_id, quantity)
        VALUES (?, ?, ?)
    ");

    $update_fooditem_quantity = $pdo->prepare("
        UPDATE fooditem
        SET quantity = quantity - ?
        WHERE foodItem_id = ? AND user_id = ?
    ");

    $updatedItems = []; // track all updated food items

    foreach ($selectedCards as $card) {
        $fooditem_id = $card['item']['foodItem_id'] ?? null;
        $quantity = $card['quantity'] ?? 0;

        if (!$fooditem_id || $quantity <= 0) continue;

        $update_fooditem_quantity->execute([$quantity, $fooditem_id, $user_id]);

        // 3️⃣ Fetch the updated item info for insertion
        $get_fooditem = $pdo->prepare("SELECT * FROM fooditem WHERE foodItem_id = ? AND user_id = ?");
        $get_fooditem->execute([$fooditem_id, $user_id]);
        $foodItem = $get_fooditem->fetch(PDO::FETCH_ASSOC);

         // 4️⃣ Check remaining stock
        $remaining = $foodItem ? $foodItem['quantity'] : 0;

        // 5️⃣ Delete if stock <= 0
        if ($remaining <= 0) {
            $delete_fooditem = $pdo->prepare("
                DELETE FROM fooditem WHERE foodItem_id = ? AND user_id = ?
            ");
            $delete_fooditem->execute([$fooditem_id, $user_id]);
        }

        // track updates on each loop
        $updatedItems[] = [
            'foodItem_id' => $fooditem_id,
            'quantity' => max(0, $remaining)
        ];

        // 6️⃣ Create a new fooditem object with reserved tag, follow with quantity accordingly
        if ($foodItem) {
            $insert_reserved_fooditem = $pdo->prepare("
                INSERT INTO fooditem (
                    user_id, food_name, quantity, category, storage_location, expiry_date, description, status
                ) VALUES (
                    :user_id, :food_name, :quantity, :category, :storage_location, :expiry_date, :description, :status
                )
            ");

            $insert_reserved_fooditem->execute([
                ':user_id' => $user_id,
                ':food_name' => $foodItem['food_name'],
                ':quantity' => $quantity, // quantity reserved
                ':category' => $foodItem['category'] ?? null,
                ':storage_location' => $foodItem['storage_location'] ?? null,
                ':expiry_date' => $foodItem['expiry_date'] ?? null,
                ':description' => $foodItem['description'] ?? null,
                ':status' => 'reserved'
            ]);
        }

        $reserved_fooditem_id = $pdo->lastInsertId();

        $post_mealplan_fooditem->execute([$meal_id, $reserved_fooditem_id, $quantity]);
    }

    // ✅ Commit if everything succeeds
    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Meal stored successfully", "updatedItems" => $updatedItems]);

} catch (Exception $e) {
    // ❌ Rollback on error
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode([
        "success" => false,
        "message" => "Database error on line " . $e->getLine() . 
        " in " . basename($e->getFile()) . ": " . $e->getMessage()
    ]);
}