<?php
header('Content-Type: application/json');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}

require_once $configPath;

// --- Helpers ---
function getDateRange($period) {
    $now = new DateTime();
    $start = clone $now;
    $end = clone $now;

    switch ($period) {
        case 'today':
            break;
        case 'week':
            $start->modify('monday this week');
            $end->modify('sunday this week');
            break;
        case 'month':
            $start->modify('first day of this month');
            $end->modify('last day of this month');
            break;
    }
    return [
        'start' => $start->format('Y-m-d 00:00:00'),
        'end' => $end->format('Y-m-d 23:59:59')
    ];
}

// --- Inputs ---
$data = json_decode(file_get_contents('php://input'), true);

// Global Analysis Range
$startDateInput = $_GET['startDate'] ?? $data['startDate'] ?? null;
$endDateInput = $_GET['endDate'] ?? $data['endDate'] ?? null;
$granularity = $_GET['granularity'] ?? $data['granularity'] ?? 'day';

// Card Filters (Expects YYYY-MM-DD representing the period)
$filterDayInput = $_GET['filterDay'] ?? $data['filterDay'] ?? date('Y-m-d');
$filterWeekInput = $_GET['filterWeek'] ?? $data['filterWeek'] ?? date('Y-m-d');
$filterMonthInput = $_GET['filterMonth'] ?? $data['filterMonth'] ?? date('Y-m-d');

// Analysis Range Fallback
if (!$startDateInput || !$endDateInput) {
    $range = getDateRange('month');
    $startDate = $range['start'];
    $endDate = $range['end'];
} else {
    $startDate = date('Y-m-d 00:00:00', strtotime($startDateInput));
    $endDate = date('Y-m-d 23:59:59', strtotime($endDateInput));
}

// Escape inputs for Stats
$fDay = $mysqli->real_escape_string($filterDayInput);
$fWeek = $mysqli->real_escape_string($filterWeekInput);
$fMonth = $mysqli->real_escape_string($filterMonthInput);

// --- 1. Dashboard Counts (Dynamic filters) ---
// We use the passed dates to filter.
// Day: DATE(created_at) = DATE('$fDay')
// Week: YEARWEEK(created_at, 1) = YEARWEEK('$fWeek', 1)
// Month: DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT('$fMonth', '%Y-%m')

$sqlCounts = "
SELECT
    -- DAY STATS
    SUM(CASE WHEN DATE(created_at) = DATE('$fDay') THEN 1 ELSE 0 END) as total_today,
    SUM(CASE WHEN DATE(created_at) = DATE('$fDay') AND status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_today,
    SUM(CASE WHEN DATE(created_at) = DATE('$fDay') AND status = 'canceled' THEN 1 ELSE 0 END) as canceled_today,
    SUM(CASE WHEN DATE(created_at) = DATE('$fDay') AND status = 'unreaching' THEN 1 ELSE 0 END) as unreaching_today,
    SUM(CASE WHEN DATE(created_at) = DATE('$fDay') AND status IN ('waiting', 'pending') THEN 1 ELSE 0 END) as awaiting_today,
    SUM(CASE WHEN DATE(created_at) = DATE('$fDay') AND status = 'shipping' THEN 1 ELSE 0 END) as delivered_today,
    SUM(CASE WHEN DATE(created_at) = DATE('$fDay') AND status = 'completed' THEN 1 ELSE 0 END) as completed_today,

    -- WEEK STATS
    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK('$fWeek', 1) THEN 1 ELSE 0 END) as total_week,
    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK('$fWeek', 1) AND status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_week,
    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK('$fWeek', 1) AND status = 'canceled' THEN 1 ELSE 0 END) as canceled_week,
    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK('$fWeek', 1) AND status = 'unreaching' THEN 1 ELSE 0 END) as unreaching_week,
    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK('$fWeek', 1) AND status IN ('waiting', 'pending') THEN 1 ELSE 0 END) as awaiting_week,
    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK('$fWeek', 1) AND status = 'shipping' THEN 1 ELSE 0 END) as delivered_week,
    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK('$fWeek', 1) AND status = 'completed' THEN 1 ELSE 0 END) as completed_week,

    -- MONTH STATS
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT('$fMonth', '%Y-%m') THEN 1 ELSE 0 END) as total_month,
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT('$fMonth', '%Y-%m') AND status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_month,
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT('$fMonth', '%Y-%m') AND status = 'canceled' THEN 1 ELSE 0 END) as canceled_month,
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT('$fMonth', '%Y-%m') AND status = 'unreaching' THEN 1 ELSE 0 END) as unreaching_month,
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT('$fMonth', '%Y-%m') AND status IN ('waiting', 'pending') THEN 1 ELSE 0 END) as awaiting_month,
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT('$fMonth', '%Y-%m') AND status = 'shipping' THEN 1 ELSE 0 END) as delivered_month,
    SUM(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT('$fMonth', '%Y-%m') AND status = 'completed' THEN 1 ELSE 0 END) as completed_month

FROM orders
";

$resultCounts = $mysqli->query($sqlCounts);
$counts = $resultCounts ? $resultCounts->fetch_assoc() : [];
foreach ($counts as $key => $val) $counts[$key] = (int)$val;

// --- 2. Top Wilayas (Completed Only) ---
$sDate = $mysqli->real_escape_string($startDate);
$eDate = $mysqli->real_escape_string($endDate);

$sqlWilayas = "
    SELECT delivery_zone, COUNT(*) as count
    FROM orders
    WHERE created_at BETWEEN '$sDate' AND '$eDate'
    AND delivery_zone IS NOT NULL AND delivery_zone != ''
    AND status = 'completed'
    GROUP BY delivery_zone
    ORDER BY count DESC
    LIMIT 6
";
$resultWilayas = $mysqli->query($sqlWilayas);
$topWilayas = $resultWilayas ? $resultWilayas->fetch_all(MYSQLI_ASSOC) : [];

// --- 3. Top Products (Standard range) ---
$sqlProducts = "
    SELECT oi.product_name, SUM(oi.qty) as count
    FROM order_items oi
    JOIN orders o ON o.id = oi.order_id
    WHERE o.created_at BETWEEN '$sDate' AND '$eDate'
    GROUP BY oi.product_name
    ORDER BY count DESC
    LIMIT 10
";
$resultProducts = $mysqli->query($sqlProducts);
$topProducts = $resultProducts ? $resultProducts->fetch_all(MYSQLI_ASSOC) : [];

// --- 4. Trend (Granularity) ---
$sqlTrend = "";
switch ($granularity) {
    case 'week':
        $sqlTrend = "SELECT DATE_FORMAT(created_at, '%Y-W%u') as date_key, MIN(DATE(created_at)) as date_label, COUNT(*) as count FROM orders WHERE created_at BETWEEN '$sDate' AND '$eDate' GROUP BY date_key ORDER BY date_key";
        break;
    case 'month':
        $sqlTrend = "SELECT DATE_FORMAT(created_at, '%Y-%m') as date_key, DATE_FORMAT(created_at, '%Y-%m') as date_label, COUNT(*) as count FROM orders WHERE created_at BETWEEN '$sDate' AND '$eDate' GROUP BY date_key ORDER BY date_key";
        break;
    case 'day':
    default:
        $sqlTrend = "SELECT DATE(created_at) as date_key, DATE(created_at) as date_label, COUNT(*) as count FROM orders WHERE created_at BETWEEN '$sDate' AND '$eDate' GROUP BY date_key ORDER BY date_key";
        break;
}

$resultTrend = $mysqli->query($sqlTrend);
$trendData = $resultTrend ? $resultTrend->fetch_all(MYSQLI_ASSOC) : [];

echo json_encode([
    'success' => true,
    'dashboard' => [
        'today' => [
            'total' => $counts['total_today'],
            'confirmed' => $counts['confirmed_today'],
            'canceled' => $counts['canceled_today'],
            'unreachable' => $counts['unreaching_today'],
            'awaiting' => $counts['awaiting_today'],
            'delivered' => $counts['delivered_today'],
            'completed' => $counts['completed_today'],
        ],
        'week' => [
            'total' => $counts['total_week'],
            'confirmed' => $counts['confirmed_week'],
            'canceled' => $counts['canceled_week'],
            'unreachable' => $counts['unreaching_week'],
            'awaiting' => $counts['awaiting_week'],
            'delivered' => $counts['delivered_week'],
            'completed' => $counts['completed_week'],
        ],
        'month' => [
            'total' => $counts['total_month'],
            'confirmed' => $counts['confirmed_month'],
            'canceled' => $counts['canceled_month'],
            'unreachable' => $counts['unreaching_month'],
            'awaiting' => $counts['awaiting_month'],
            'delivered' => $counts['delivered_month'],
            'completed' => $counts['completed_month'],
        ],
    ],
    'analysis' => [
        'period' => ['start' => $startDate, 'end' => $endDate],
        'granularity' => $granularity,
        'trend' => $trendData,
        'topWilayas' => $topWilayas,
        'topProducts' => $topProducts
    ]
]);

$mysqli->close();
?>
