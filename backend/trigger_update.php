<?php
function triggerOrderUpdate($data = null) {
    global $mysqli;

    // Default payload
    if ($data === null) {
        $data = ['update' => true];
    }

    // Ensure we have an event type
    $eventType = isset($data['event_type']) ? $data['event_type'] : 'order_update';

    // Remove event_type from payload to avoid redundancy if desired, or keep it.
    // Let's keep data as payload.
    $payload = json_encode($data);

    $eventId = 0;

    if ($mysqli) {
        $stmt = $mysqli->prepare("INSERT INTO system_events (event_type, payload) VALUES (?, ?)");
        if ($stmt) {
            $stmt->bind_param("ss", $eventType, $payload);
            $stmt->execute();
            $eventId = $stmt->insert_id;
            $stmt->close();
        }
    }

    // Fallback/Legacy: Touch file
    $file = __DIR__ . '/data/last_order_update.txt';
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    touch($file);

    // New Mechanism: Write Event ID
    if ($eventId > 0) {
        $idFile = __DIR__ . '/data/latest_event_id.txt';
        file_put_contents($idFile, $eventId);
    }
}
?>