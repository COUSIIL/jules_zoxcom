<?php
function triggerOrderUpdate() {
    $file = __DIR__ . '/data/last_order_update.txt';
    $dir = dirname($file);

    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    // Use 'a' to ensure file exists, but we just need to update timestamp
    if (!file_exists($file)) {
        touch($file);
    } else {
        touch($file);
    }
}
?>