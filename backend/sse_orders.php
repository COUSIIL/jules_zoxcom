<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Credentials: true');

// Close session to avoid locking other requests
if (session_status() === PHP_SESSION_NONE) {
    // Attempt to start session to access existing session data if needed, then close it immediately
    @session_start();
}
session_write_close();

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

$file = __DIR__ . '/data/last_order_update.txt';

if (!file_exists($file)) {
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0777, true);
    }
    touch($file);
}

// Initial state
clearstatcache();
$lastModification = filemtime($file);
$startTime = time();
$timeout = 25; // Restart connection every 25s to prevent PHP timeouts

echo "retry: 1000\n\n";
flush();

while (true) {
    if ((time() - $startTime) > $timeout) {
        break;
    }

    clearstatcache();
    $currentModification = filemtime($file);

    if ($currentModification > $lastModification) {
        $lastModification = $currentModification;
        echo "data: " . json_encode(['update' => true, 'ts' => $lastModification]) . "\n\n";
        flush();
    }

    // Tiny sleep to prevent CPU hogging
    usleep(500000); // 0.5s
}
?>