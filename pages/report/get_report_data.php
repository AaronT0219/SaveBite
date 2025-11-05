<?php
require_once '../../config.php';

header('Content-Type: application/json');

// Check if user is logged in
session_start();
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

$user_id = $_SESSION['id'];

try {
    // Get filter parameters from GET request
    $filter = $_GET['filter'] ?? 'monthly';
    $startDate = $_GET['start_date'] ?? null;
    $endDate = $_GET['end_date'] ?? null;

    // Initialize response array
    $response = [
        "success" => true,
        "data" => []
    ];

    // Get total food items saved (total quantity of all food items)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as total_food_save FROM food_items WHERE created_by = ?");
    $stmt->execute([$user_id]);
    $totalFoodSave = $stmt->fetch()['total_food_save'];

    // Get total donations made by user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_donations FROM donation WHERE donor_user_id = ?");
    $stmt->execute([$user_id]);
    $totalDonations = $stmt->fetch()['total_donations'];

    // Calculate progress (example: percentage of food items that were used vs total)
    $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(CASE WHEN status = 'used' THEN quantity ELSE 0 END), 0) as used_quantity,
            COALESCE(SUM(quantity), 0) as total_quantity 
        FROM food_items 
        WHERE created_by = ?
    ");
    $stmt->execute([$user_id]);
    $progressData = $stmt->fetch();
    $progress = $progressData['total_quantity'] > 0 ? 
        round(($progressData['used_quantity'] / $progressData['total_quantity']) * 100) : 0;

    // Get category distribution
    $stmt = $pdo->prepare("
        SELECT 
            category, 
            COALESCE(SUM(quantity), 0) as total_quantity 
        FROM food_items 
        WHERE created_by = ? 
        GROUP BY category 
        ORDER BY total_quantity DESC
    ");
    $stmt->execute([$user_id]);
    $categoryData = $stmt->fetchAll();

    $categories = [
        'labels' => [],
        'data' => []
    ];
    
    foreach ($categoryData as $row) {
        $categories['labels'][] = $row['category'];
        $categories['data'][] = (int)$row['total_quantity'];
    }

    // Get time-based data based on filter
    $timeData = [];
    
    switch ($filter) {
        case 'yearly':
            $stmt = $pdo->prepare("
                SELECT 
                    YEAR(created_at) as period,
                    COUNT(*) as donations,
                    COALESCE(SUM(quantity), 0) as quantity
                FROM food_items 
                WHERE created_by = ? 
                GROUP BY YEAR(created_at) 
                ORDER BY period DESC 
                LIMIT 6
            ");
            $stmt->execute([$user_id]);
            break;
            
        case 'weekly':
            $stmt = $pdo->prepare("
                SELECT 
                    CONCAT('Week ', WEEK(created_at, 1) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at) - 1 DAY), 1) + 1) as period,
                    COUNT(*) as donations,
                    COALESCE(SUM(quantity), 0) as quantity
                FROM food_items 
                WHERE created_by = ? AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())
                GROUP BY WEEK(created_at, 1) 
                ORDER BY WEEK(created_at, 1) DESC 
                LIMIT 4
            ");
            $stmt->execute([$user_id]);
            break;
            
        case 'dateRange':
            if ($startDate && $endDate) {
                $stmt = $pdo->prepare("
                    SELECT 
                        DATE(created_at) as period,
                        COUNT(*) as donations,
                        COALESCE(SUM(quantity), 0) as quantity
                    FROM food_items 
                    WHERE created_by = ? AND DATE(created_at) BETWEEN ? AND ?
                    GROUP BY DATE(created_at) 
                    ORDER BY period DESC
                ");
                $stmt->execute([$user_id, $startDate, $endDate]);
            } else {
                $timeData = ['labels' => [], 'donations' => [], 'quantity' => []];
                break;
            }
            break;
            
        default: // monthly
            $stmt = $pdo->prepare("
                SELECT 
                    MONTHNAME(created_at) as period,
                    COUNT(*) as donations,
                    COALESCE(SUM(quantity), 0) as quantity
                FROM food_items 
                WHERE created_by = ? AND YEAR(created_at) = YEAR(CURDATE())
                GROUP BY MONTH(created_at), MONTHNAME(created_at)
                ORDER BY MONTH(created_at) DESC 
                LIMIT 6
            ");
            $stmt->execute([$user_id]);
            break;
    }
    
    if ($filter !== 'dateRange' || ($startDate && $endDate)) {
        $timeResults = $stmt->fetchAll();
        
        $timeData = [
            'labels' => [],
            'donations' => [],
            'quantity' => []
        ];
        
        foreach (array_reverse($timeResults) as $row) {
            $timeData['labels'][] = $row['period'];
            $timeData['donations'][] = (int)$row['donations'];
            $timeData['quantity'][] = (int)$row['quantity'];
        }
    }

    // Build response
    $response['data'] = [
        'totalFoodSave' => (float)$totalFoodSave,
        'totalDonations' => (int)$totalDonations,
        'progress' => (int)$progress,
        'categories' => $categories,
        'timeData' => $timeData
    ];

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error fetching report data: " . $e->getMessage()
    ]);
}
?>