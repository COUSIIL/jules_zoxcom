<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Nginx

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
session_write_close();

require_once __DIR__ . '/config/dbConfig.php';

// Disable buffering
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', 1);
}
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
while (ob_get_level() > 0) {
    ob_end_flush();
}
ob_implicit_flush(1);

// Get Last ID from Client
$lastEventId = 0;
if (isset($_SERVER["HTTP_LAST_EVENT_ID"])) {
    $lastEventId = intval($_SERVER["HTTP_LAST_EVENT_ID"]);
} elseif (isset($_GET['lastEventId'])) {
    $lastEventId = intval($_GET['lastEventId']);
}

// File for quick check
$idFile = __DIR__ . '/data/latest_event_id.txt';

$start = microtime(true);
$timeout = 25.0; // Seconds before closing
$pollIntervalUs = 200000; // 0.2s

// Send initial connection comment
echo ": connected\n\n";
flush();

while (true) {
    if ((microtime(true) - $start) >= $timeout) {
        break;
    }

    $currentMaxId = 0;
    if (file_exists($idFile)) {
        $currentMaxId = intval(file_get_contents($idFile));
    }

    // If file ID > lastEventId, we have new events
    // OR if we suspect the file might be outdated but DB has more (e.g. multiple servers),
    // but here we assume single server or shared storage.
    // If $lastEventId is much smaller than $currentMaxId, we catch up.

    if ($currentMaxId > $lastEventId) {
        // Fetch events from DB
        $stmt = $mysqli->prepare("SELECT id, event_type, payload FROM system_events WHERE id > ? ORDER BY id ASC LIMIT 50");
        if ($stmt) {
            $stmt->bind_param("i", $lastEventId);
            $stmt->execute();
            $result = $stmt->get_result();

            $events = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if (!empty($events)) {
                foreach ($events as $row) {
                    echo "id: " . $row['id'] . "\n";
                    echo "event: " . $row['event_type'] . "\n";
                    echo "data: " . $row['payload'] . "\n\n";

                    $lastEventId = $row['id'];
                }
                flush();
            }
        }
    }

    usleep($pollIntervalUs);
}
?>