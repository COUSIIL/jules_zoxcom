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
$start = microtime(true);
$timeout = 1.0;              // ex 25s
$pollIntervalUs = 500000;   // 0.5s (ajuste)

echo "retry: 1000\n\n";
echo ": connected\n\n";
flush();

while (true) {
    if ((microtime(true) - $start) >= $timeout) {
        // Optionnel: envoyer un ping avant de sortir
        echo ": closing\n\n";
        flush();
        break;
    }

    clearstatcache(true, $file);
    $mtime = filemtime($file);

    if ($mtime !== false && $mtime > $lastModification) {
        $lastModification = $mtime;
        echo "data: " . json_encode(['update' => true, 'ts' => $lastModification]) . "\n\n";
        flush();
    } else {
        // ping comment pour éviter certains buffers/proxy
        echo ": ping\n\n";
        flush();
    }

    usleep($pollIntervalUs);
}

?>