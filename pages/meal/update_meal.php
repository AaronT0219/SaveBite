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

$meal_id = mysqli_real_escape_string($conn, $data['meal_id']);
$title = mysqli_real_escape_string($conn, $data['title']);
$desc = mysqli_real_escape_string($conn, $data['desc']);
$date = mysqli_real_escape_string($conn, $data['date']);
$mealSlot = mysqli_real_escape_string($conn, $data['mealSlot']);

try {
    $update_meal = $pdo->prepare("
        UPDATE mealplan
        SET meal_name = ?, description = ?, mealplan_date = ?, meal_type = ?
        WHERE mealplan_id = ? AND user_id = ?
    ");

    $update_meal->execute([$title, $desc, $date, $mealSlot, $meal_id, $user_id]);

    echo json_encode(["success" => true, "message" => "Meal updated successfully"]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database error on line " . $e->getLine() .
                     " in " . basename($e->getFile()) . ": " . $e->getMessage()
    ]);
}
