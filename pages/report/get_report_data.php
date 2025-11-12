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

    // Get total food items saved (sum of quantity from food_items)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as total_food_save FROM fooditem WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $totalFoodSave = $stmt->fetch()['total_food_save'];

    // Get total donations made by user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_donations FROM donation WHERE donor_user_id = ?");
    $stmt->execute([$user_id]);
    $totalDonations = $stmt->fetch()['total_donations'];

    // Calculate progress (percentage of donations picked up vs total)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN status = 'picked up' THEN 1 END) as picked_up,
            COUNT(*) as total 
        FROM donation 
        WHERE donor_user_id = ?
    ");
    $stmt->execute([$user_id]);
    $progressData = $stmt->fetch();
    $progress = $progressData['total'] > 0 ? 
        round(($progressData['picked_up'] / $progressData['total']) * 100) : 0;

    // Get category distribution (only for specific categories)
    $validCategories = ['Produce', 'Protein', 'Dairy & Bakery', 'Grains & Pantry', 'Snacks & Beverages'];
    $placeholders = str_repeat('?,', count($validCategories) - 1) . '?';
    
    $stmt = $pdo->prepare("
        SELECT 
            category, 
            COALESCE(SUM(quantity), 0) as total_quantity 
        FROM fooditem 
        WHERE user_id = ? AND category IN ($placeholders)
        GROUP BY category 
        ORDER BY FIELD(category, 'Produce', 'Protein', 'Dairy & Bakery', 'Grains & Pantry', 'Snacks & Beverages')
    ");
    $stmt->execute(array_merge([$user_id], $validCategories));
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
            // Get donations count and food save per year
            $stmtDonations = $pdo->prepare("
                SELECT 
                    YEAR(donation_date) as year,
                    COUNT(*) as count
                FROM donation 
                WHERE donor_user_id = ? 
                GROUP BY YEAR(donation_date) 
                ORDER BY year DESC 
                LIMIT 6
            ");
            $stmtDonations->execute([$user_id]);
            $donationsData = $stmtDonations->fetchAll(PDO::FETCH_ASSOC);
            
            $stmtFood = $pdo->prepare("
                SELECT 
                    YEAR(expiry_date) as year,
                    COALESCE(SUM(quantity), 0) as total
                FROM fooditem 
                WHERE user_id = ? 
                GROUP BY YEAR(expiry_date) 
                ORDER BY year DESC 
                LIMIT 6
            ");
            $stmtFood->execute([$user_id]);
            $foodData = $stmtFood->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge data by year
            $yearlyData = [];
            foreach ($donationsData as $row) {
                $yearlyData[$row['year']]['donations'] = (int)$row['count'];
            }
            foreach ($foodData as $row) {
                $yearlyData[$row['year']]['food'] = (int)$row['total'];
            }
            
            krsort($yearlyData);
            $yearlyData = array_slice($yearlyData, 0, 6, true);
            $yearlyData = array_reverse($yearlyData, true);
            
            $timeData = ['labels' => [], 'donations' => [], 'food' => []];
            foreach ($yearlyData as $year => $data) {
                $timeData['labels'][] = (string)$year;
                $timeData['donations'][] = $data['donations'] ?? 0;
                $timeData['food'][] = $data['food'] ?? 0;
            }
            break;
            
        case 'category':
            // For category view, return empty time data
            $timeData = ['labels' => [], 'donations' => [], 'food' => []];
            break;
            
        case 'dateRange':
            if ($startDate && $endDate) {
                $stmtDonations = $pdo->prepare("
                    SELECT 
                        DATE(donation_date) as date,
                        COUNT(*) as count
                    FROM donation 
                    WHERE donor_user_id = ? AND DATE(donation_date) BETWEEN ? AND ?
                    GROUP BY DATE(donation_date) 
                    ORDER BY date ASC
                ");
                $stmtDonations->execute([$user_id, $startDate, $endDate]);
                $donationsData = $stmtDonations->fetchAll(PDO::FETCH_ASSOC);
                
                $stmtFood = $pdo->prepare("
                    SELECT 
                        DATE(expiry_date) as date,
                        COALESCE(SUM(quantity), 0) as total
                    FROM fooditem 
                    WHERE user_id = ? AND DATE(expiry_date) BETWEEN ? AND ?
                    GROUP BY DATE(expiry_date) 
                    ORDER BY date ASC
                ");
                $stmtFood->execute([$user_id, $startDate, $endDate]);
                $foodData = $stmtFood->fetchAll(PDO::FETCH_ASSOC);
                
                // Merge data by date
                $dateData = [];
                foreach ($donationsData as $row) {
                    $dateData[$row['date']]['donations'] = (int)$row['count'];
                }
                foreach ($foodData as $row) {
                    $dateData[$row['date']]['food'] = (int)$row['total'];
                }
                
                ksort($dateData);
                
                $timeData = ['labels' => [], 'donations' => [], 'food' => []];
                foreach ($dateData as $date => $data) {
                    $timeData['labels'][] = date('M d', strtotime($date));
                    $timeData['donations'][] = $data['donations'] ?? 0;
                    $timeData['food'][] = $data['food'] ?? 0;
                }
            } else {
                $timeData = ['labels' => [], 'donations' => [], 'food' => []];
            }
            break;
            
        default: // monthly
            // Get donations count and food save per month for current year
            $stmtDonations = $pdo->prepare("
                SELECT 
                    MONTH(donation_date) as month,
                    MONTHNAME(donation_date) as month_name,
                    COUNT(*) as count
                FROM donation 
                WHERE donor_user_id = ? AND YEAR(donation_date) = YEAR(CURDATE())
                GROUP BY MONTH(donation_date), MONTHNAME(donation_date)
                ORDER BY MONTH(donation_date) ASC
            ");
            $stmtDonations->execute([$user_id]);
            $donationsData = $stmtDonations->fetchAll(PDO::FETCH_ASSOC);
            
            $stmtFood = $pdo->prepare("
                SELECT 
                    MONTH(expiry_date) as month,
                    MONTHNAME(expiry_date) as month_name,
                    COALESCE(SUM(quantity), 0) as total
                FROM fooditem 
                WHERE user_id = ? AND YEAR(expiry_date) = YEAR(CURDATE())
                GROUP BY MONTH(expiry_date), MONTHNAME(expiry_date)
                ORDER BY MONTH(expiry_date) ASC
            ");
            $stmtFood->execute([$user_id]);
            $foodData = $stmtFood->fetchAll(PDO::FETCH_ASSOC);
            
            // Merge data by month
            $monthlyData = [];
            foreach ($donationsData as $row) {
                $monthlyData[$row['month']]['name'] = $row['month_name'];
                $monthlyData[$row['month']]['donations'] = (int)$row['count'];
            }
            foreach ($foodData as $row) {
                if (!isset($monthlyData[$row['month']]['name'])) {
                    $monthlyData[$row['month']]['name'] = $row['month_name'];
                }
                $monthlyData[$row['month']]['food'] = (int)$row['total'];
            }
            
            ksort($monthlyData);
            $monthlyData = array_slice($monthlyData, -6, 6, true);
            
            $timeData = ['labels' => [], 'donations' => [], 'food' => []];
            foreach ($monthlyData as $data) {
                $timeData['labels'][] = $data['name'];
                $timeData['donations'][] = $data['donations'] ?? 0;
                $timeData['food'][] = $data['food'] ?? 0;
            }
            break;
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