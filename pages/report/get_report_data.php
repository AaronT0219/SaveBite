<?php
declare(strict_types=1);
session_start();
require_once '../../config.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * 统一用 $_SESSION['user_id']
 */
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "User not logged in"], JSON_UNESCAPED_UNICODE);
    exit;
}
$user_id   = (int)$_SESSION['user_id'];
$filter    = $_GET['filter']      ?? 'monthly';
$startDate = $_GET['start_date']  ?? null;
$endDate   = $_GET['end_date']    ?? null;

try {
    $payload = [
        "success" => true,
        "data" => [
            "metrics" => getTotalMetrics($pdo, $user_id),
            "chart"   => getChartByFilter($pdo, $user_id, $filter, $startDate, $endDate),
        ],
    ];
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success"=>false, "message"=>"Error fetching data: ".$e->getMessage()], JSON_UNESCAPED_UNICODE);
}

/* ---------------- helpers ---------------- */

function getChartByFilter(PDO $pdo, int $user_id, string $filter, ?string $start, ?string $end): array {
    switch ($filter) {
        case 'yearly':    return getYearlyData($pdo, $user_id);
        case 'weekly':    return getWeeklyData($pdo, $user_id);
        case 'category':  return getCategoryData($pdo, $user_id);
        case 'dateRange': return getDateRangeData($pdo, $user_id, $start, $end);
        case 'monthly':
        default:          return getMonthlyData($pdo, $user_id);
    }
}

function getTotalMetrics(PDO $pdo, int $user_id): array {
    // total food items saved
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM fooditem WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $totalFoodSave = (int)$stmt->fetchColumn();

    // total donations
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM donation WHERE donor_user_id = ?");
    $stmt->execute([$user_id]);
    $totalDonations = (int)$stmt->fetchColumn();

    // total quantity
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM fooditem WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $totalQuantity = (int)$stmt->fetchColumn();

    // a simple progress metric (donations/foodSave capped at 100)
    $progress = $totalFoodSave > 0 ? min(100, ($totalDonations / $totalFoodSave) * 100) : 0;

    return [
        'totalFoodSave'  => $totalFoodSave,
        'totalDonations' => $totalDonations,
        'totalQuantity'  => $totalQuantity,
        'progress'       => round($progress, 1),
    ];
}

function getYearlyData(PDO $pdo, int $user_id): array {
    $currentYear = (int)date('Y');
    $years = [];
    for ($i = 5; $i >= 0; $i--) { $years[] = $currentYear - $i; }

    $labels = array_map('strval', $years);
    $donations = [];
    $quantities = [];

    foreach ($years as $y) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM donation WHERE donor_user_id=? AND YEAR(donation_date)=?");
        $stmt->execute([$user_id, $y]);
        $donations[] = (int)$stmt->fetchColumn();

        // 以 expiry_date 近似统计数量
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM fooditem WHERE user_id=? AND YEAR(expiry_date)=?");
        $stmt->execute([$user_id, $y]);
        $quantities[] = (int)$stmt->fetchColumn();
    }

    return ['labels'=>$labels, 'donations'=>$donations, 'quantity'=>$quantities];
}

function getMonthlyData(PDO $pdo, int $user_id): array {
    $currentYear = (int)date('Y');
    $monthsText = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    $donations = [];
    $quantities = [];

    for ($m = 1; $m <= 12; $m++) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM donation WHERE donor_user_id=? AND YEAR(donation_date)=? AND MONTH(donation_date)=?");
        $stmt->execute([$user_id, $currentYear, $m]);
        $donations[] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM fooditem WHERE user_id=? AND YEAR(expiry_date)=? AND MONTH(expiry_date)=?");
        $stmt->execute([$user_id, $currentYear, $m]);
        $quantities[] = (int)$stmt->fetchColumn();
    }

    return ['labels'=>$monthsText, 'donations'=>$donations, 'quantity'=>$quantities];
}

function getWeeklyData(PDO $pdo, int $user_id): array {
    $labels = [];
    $donations = [];
    $quantities = [];

    // 最近4周（以周一-周日为一周）
    for ($w = 3; $w >= 0; $w--) {
        $weekStart = date('Y-m-d', strtotime("-$w weeks monday"));
        $weekEnd   = date('Y-m-d 23:59:59', strtotime("-$w weeks sunday"));
        $labels[] = 'Week ' . (4 - $w);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM donation WHERE donor_user_id=? AND donation_date BETWEEN ? AND ?");
        $stmt->execute([$user_id, $weekStart, $weekEnd]);
        $donations[] = (int)$stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM fooditem WHERE user_id=? AND expiry_date BETWEEN ? AND ?");
        $stmt->execute([$user_id, $weekStart, $weekEnd]);
        $quantities[] = (int)$stmt->fetchColumn();
    }

    return ['labels'=>$labels, 'donations'=>$donations, 'quantity'=>$quantities];
}

function getCategoryData(PDO $pdo, int $user_id): array {
    $stmt = $pdo->prepare("SELECT COALESCE(category,'Other') AS category, COUNT(*) AS c FROM fooditem WHERE user_id=? GROUP BY category ORDER BY c DESC");
    $stmt->execute([$user_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $data = [];
    foreach ($rows as $r) {
        $labels[] = $r['category'];
        $data[]   = (int)$r['c'];
    }
    if (!$labels) { $labels=['No Data']; $data=[0]; }

    return ['labels'=>$labels, 'data'=>$data];
}

function getDateRangeData(PDO $pdo, int $user_id, ?string $start, ?string $end): array {
    if (!$start || !$end) {
        return ['labels'=>['No Date Range'],'donations'=>[0],'quantity'=>[0]];
    }
    $startDate = new DateTime($start);
    $endDate   = new DateTime($end.' 23:59:59'); // 包含当天

    $days = (int)$startDate->diff($endDate)->days;
    $labels = [];
    $donations = [];
    $quantities = [];

    if ($days > 90) {
        // 按月
        $cursor = new DateTime($startDate->format('Y-m-01'));
        while ($cursor <= $endDate) {
            $monthStart = $cursor->format('Y-m-01');
            $monthEnd   = $cursor->format('Y-m-t').' 23:59:59';
            if ($monthEnd > $endDate->format('Y-m-d H:i:s')) $monthEnd = $endDate->format('Y-m-d H:i:s');

            $labels[] = $cursor->format('M Y');

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM donation WHERE donor_user_id=? AND donation_date BETWEEN ? AND ?");
            $stmt->execute([$user_id, $monthStart, $monthEnd]);
            $donations[] = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM fooditem WHERE user_id=? AND expiry_date BETWEEN ? AND ?");
            $stmt->execute([$user_id, $monthStart, $monthEnd]);
            $quantities[] = (int)$stmt->fetchColumn();

            $cursor->modify('+1 month');
        }
    } else {
        // 按周（最多8周）
        $weeks = min(8, (int)ceil(($days + 1) / 7));
        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = (new DateTime($start))->modify("+$i week")->format('Y-m-d');
            $weekEnd   = (new DateTime($weekStart))->modify('+6 day')->format('Y-m-d 23:59:59');
            if (new DateTime($weekEnd) > $endDate) $weekEnd = $endDate->format('Y-m-d H:i:s');

            $labels[] = 'Week '.($i+1);

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM donation WHERE donor_user_id=? AND donation_date BETWEEN ? AND ?");
            $stmt->execute([$user_id, $weekStart, $weekEnd]);
            $donations[] = (int)$stmt->fetchColumn();

            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM fooditem WHERE user_id=? AND expiry_date BETWEEN ? AND ?");
            $stmt->execute([$user_id, $weekStart, $weekEnd]);
            $quantities[] = (int)$stmt->fetchColumn();
        }
    }

    return ['labels'=>$labels,'donations'=>$donations,'quantity'=>$quantities];
}
