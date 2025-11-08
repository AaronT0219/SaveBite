<?php
session_start();
require_once '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "User not logged in"
    ]);
    exit;
}

$user_id = $_SESSION['id'];
$filter = $_GET['filter'] ?? 'monthly';
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

try {
    $response = [
        "success" => true,
        "data" => []
    ];

    // Get total metrics
    $response['data']['metrics'] = getTotalMetrics($pdo, $user_id);
    
    // Get chart data based on filter
    switch ($filter) {
        case 'yearly':
            $response['data']['chart'] = getYearlyData($pdo, $user_id);
            break;
        case 'monthly':
            $response['data']['chart'] = getMonthlyData($pdo, $user_id);
            break;
        case 'weekly':
            $response['data']['chart'] = getWeeklyData($pdo, $user_id);
            break;
        case 'category':
            $response['data']['chart'] = getCategoryData($pdo, $user_id);
            break;
        case 'dateRange':
            $response['data']['chart'] = getDateRangeData($pdo, $user_id, $start_date, $end_date);
            break;
        default:
            $response['data']['chart'] = getMonthlyData($pdo, $user_id);
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error fetching data: " . $e->getMessage()
    ]);
}

function getTotalMetrics($pdo, $user_id) {
    // Debug: Log the user_id being used
    error_log("Getting metrics for user_id: " . $user_id);
    
    // Get total food items saved
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_food_save FROM fooditem WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $foodSave = $stmt->fetch()['total_food_save'];
    error_log("Food items found: " . $foodSave);

    // Get total donations made by this user
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_donations FROM donation WHERE donor_user_id = ?");
    $stmt->execute([$user_id]);
    $donations = $stmt->fetch()['total_donations'];
    error_log("Donations found: " . $donations);

    // Get total quantity of food saved
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total_quantity FROM fooditem WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $totalQuantity = $stmt->fetch()['total_quantity'] ?: 0;
    error_log("Total quantity: " . $totalQuantity);

    // Calculate progress (example: based on donation ratio)
    $progress = $donations > 0 ? min(($donations / max($foodSave, 1)) * 100, 100) : 0;

    return [
        'totalFoodSave' => (int)$foodSave,
        'totalDonations' => (int)$donations,
        'totalQuantity' => (int)$totalQuantity,
        'progress' => round($progress, 1)
    ];
}

function getYearlyData($pdo, $user_id) {
    // Note: Since created_at doesn't exist yet, we'll use donation_date for donations
    // and expiry_date for food items as a placeholder
    
    $current_year = date('Y');
    $years = [];
    for ($i = 5; $i >= 0; $i--) {
        $years[] = $current_year - $i;
    }

    $labels = array_map('strval', $years);
    $donations = [];
    $quantities = [];

    foreach ($years as $year) {
        // Get donations for this year
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM donation 
            WHERE donor_user_id = ? AND YEAR(donation_date) = ?
        ");
        $stmt->execute([$user_id, $year]);
        $donations[] = (int)$stmt->fetch()['count'];

        // Get sum of food quantities for this year (using expiry_date as placeholder)
        $stmt = $pdo->prepare("
            SELECT SUM(quantity) as total_quantity 
            FROM fooditem 
            WHERE user_id = ? AND YEAR(expiry_date) = ?
        ");
        $stmt->execute([$user_id, $year]);
        $quantities[] = (int)($stmt->fetch()['total_quantity'] ?: 0);
    }

    return [
        'labels' => $labels,
        'donations' => $donations,
        'quantity' => $quantities
    ];
}

function getMonthlyData($pdo, $user_id) {
    $current_year = date('Y');
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $donations = [];
    $quantities = [];

    for ($month = 1; $month <= 12; $month++) {
        // Get donations for this month
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM donation 
            WHERE donor_user_id = ? AND YEAR(donation_date) = ? AND MONTH(donation_date) = ?
        ");
        $stmt->execute([$user_id, $current_year, $month]);
        $donations[] = (int)$stmt->fetch()['count'];

        // Get sum of food quantities for this month (using expiry_date as placeholder)
        $stmt = $pdo->prepare("
            SELECT SUM(quantity) as total_quantity 
            FROM fooditem 
            WHERE user_id = ? AND YEAR(expiry_date) = ? AND MONTH(expiry_date) = ?
        ");
        $stmt->execute([$user_id, $current_year, $month]);
        $quantities[] = (int)($stmt->fetch()['total_quantity'] ?: 0);
    }

    return [
        'labels' => $months,
        'donations' => $donations,
        'quantity' => $quantities
    ];
}

function getWeeklyData($pdo, $user_id) {
    $labels = [];
    $donations = [];
    $quantities = [];

    // Get data for last 4 weeks
    for ($week = 3; $week >= 0; $week--) {
        $week_start = date('Y-m-d', strtotime("-$week weeks monday"));
        $week_end = date('Y-m-d', strtotime("-$week weeks sunday"));
        
        $labels[] = "Week " . (4 - $week);

        // Get donations for this week
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM donation 
            WHERE donor_user_id = ? AND donation_date BETWEEN ? AND ?
        ");
        $stmt->execute([$user_id, $week_start, $week_end]);
        $donations[] = (int)$stmt->fetch()['count'];

        // Get sum of food quantities for this week (using expiry_date as placeholder)
        $stmt = $pdo->prepare("
            SELECT SUM(quantity) as total_quantity 
            FROM fooditem 
            WHERE user_id = ? AND expiry_date BETWEEN ? AND ?
        ");
        $stmt->execute([$user_id, $week_start, $week_end]);
        $quantities[] = (int)($stmt->fetch()['total_quantity'] ?: 0);
    }

    return [
        'labels' => $labels,
        'donations' => $donations,
        'quantity' => $quantities
    ];
}

function getCategoryData($pdo, $user_id) {
    // Get food items by category
    $stmt = $pdo->prepare("
        SELECT category, COUNT(*) as count 
        FROM fooditem 
        WHERE user_id = ? 
        GROUP BY category 
        ORDER BY count DESC
    ");
    $stmt->execute([$user_id]);
    $results = $stmt->fetchAll();

    $labels = [];
    $data = [];

    foreach ($results as $row) {
        $labels[] = $row['category'] ?: 'Other';
        $data[] = (int)$row['count'];
    }

    // If no data, provide default categories
    if (empty($labels)) {
        $labels = ['No Data'];
        $data = [0];
    }

    return [
        'labels' => $labels,
        'data' => $data
    ];
}

function getDateRangeData($pdo, $user_id, $start_date, $end_date) {
    if (!$start_date || !$end_date) {
        return [
            'labels' => ['No Date Range'],
            'donations' => [0],
            'quantity' => [0]
        ];
    }

    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $days = $interval->days;

    $labels = [];
    $donations = [];
    $quantities = [];

    if ($days > 90) {
        // Monthly breakdown for longer ranges
        $current = clone $start;
        $current->modify('first day of this month');
        
        while ($current <= $end) {
            $month_start = $current->format('Y-m-01');
            $month_end = $current->format('Y-m-t');
            
            // Don't go beyond end date
            if ($month_end > $end_date) {
                $month_end = $end_date;
            }
            
            $labels[] = $current->format('M Y');

            // Get donations for this month
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM donation 
                WHERE donor_user_id = ? AND donation_date BETWEEN ? AND ?
            ");
            $stmt->execute([$user_id, $month_start, $month_end]);
            $donations[] = (int)$stmt->fetch()['count'];

            // Get sum of food quantities for this month
            $stmt = $pdo->prepare("
                SELECT SUM(quantity) as total_quantity 
                FROM fooditem 
                WHERE user_id = ? AND expiry_date BETWEEN ? AND ?
            ");
            $stmt->execute([$user_id, $month_start, $month_end]);
            $quantities[] = (int)($stmt->fetch()['total_quantity'] ?: 0);

            $current->modify('+1 month');
        }
    } else {
        // Weekly breakdown for shorter ranges
        $weeks = min(ceil($days / 7), 8);
        
        for ($week = 0; $week < $weeks; $week++) {
            $week_start = date('Y-m-d', strtotime($start_date . " +$week weeks"));
            $week_end = date('Y-m-d', strtotime($week_start . " +6 days"));
            
            // Don't go beyond end date
            if ($week_end > $end_date) {
                $week_end = $end_date;
            }
            
            $labels[] = "Week " . ($week + 1);

            // Get donations for this week
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM donation 
                WHERE donor_user_id = ? AND donation_date BETWEEN ? AND ?
            ");
            $stmt->execute([$user_id, $week_start, $week_end]);
            $donations[] = (int)$stmt->fetch()['count'];

            // Get sum of food quantities for this week
            $stmt = $pdo->prepare("
                SELECT SUM(quantity) as total_quantity 
                FROM fooditem 
                WHERE user_id = ? AND expiry_date BETWEEN ? AND ?
            ");
            $stmt->execute([$user_id, $week_start, $week_end]);
            $quantities[] = (int)($stmt->fetch()['total_quantity'] ?: 0);
        }
    }

    return [
        'labels' => $labels,
        'donations' => $donations,
        'quantity' => $quantities
    ];
}
?>