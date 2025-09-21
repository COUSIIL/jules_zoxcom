<?php
// backend/sse_notifications.php

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Important for Nginx proxy environments

// --- Configuration & Authentication ---

// Inclure la configuration de la base de données
$configPath = __DIR__ . '/config/dbConfig.php';
if (!file_exists($configPath)) {
    // Cannot send headers here as they are already sent. Log error instead.
    error_log("SSE Error: dbConfig.php not found.");
    exit;
}
require_once $configPath;

// Placeholder d'authentification (similaire à public/api.php)
function get_current_user_id() {
    // En production, vous implémenteriez une vraie logique de session/JWT
    return 1; // Pour le développement, on utilise l'user ID 1
}

$user_id = get_current_user_id();

if (!$user_id) {
    error_log("SSE Error: No user ID found.");
    exit;
}

// --- SSE Logic ---

// On récupère l'ID de la dernière notification lue par l'utilisateur pour ne pas renvoyer l'historique
$last_event_id = 0;
$stmt_latest = $mysqli->prepare("SELECT MAX(un.id) as max_id FROM user_notifications un WHERE un.user_id = ?");
if ($stmt_latest) {
    $stmt_latest->bind_param('i', $user_id);
    $stmt_latest->execute();
    $res = $stmt_latest->get_result();
    if ($row = $res->fetch_assoc()) {
        $last_event_id = (int)$row['max_id'];
    }
    $stmt_latest->close();
}


// Boucle pour envoyer les événements
while (true) {
    // 1. Vérifier si le client est toujours connecté
    if (connection_aborted()) {
        break;
    }

    // 2. Chercher les nouvelles notifications pour cet utilisateur
    $sql = "SELECT
                n.id, n.title, n.body, n.type, n.priority, n.channels, n.meta, n.created_at,
                un.id as user_notification_id, un.is_read, un.read_at
            FROM notifications AS n
            INNER JOIN user_notifications AS un ON n.id = un.notification_id
            WHERE un.user_id = ?
              AND un.id > ?
              AND (n.status = 'sent' OR n.status = 'queued')
            ORDER BY un.id ASC";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        // Log error, don't echo
        error_log("SSE Prepare failed: " . $mysqli->error);
        sleep(10); // Wait longer on error
        continue;
    }

    $stmt->bind_param('ii', $user_id, $last_event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // 3. Envoyer les nouvelles notifications
    foreach ($notifications as $notification) {
        // Mettre à jour le dernier ID vu
        $last_event_id = $notification['user_notification_id'];

        // Convertir les champs JSON en array PHP
        $notification['channels'] = json_decode($notification['channels'] ?? '[]', true);
        $notification['meta'] = $notification['meta'] ? json_decode($notification['meta'], true) : null;

        // Formatter et envoyer l'événement
        echo "id: " . $last_event_id . "\n";
        echo "event: notification\n";
        echo "data: " . json_encode($notification) . "\n\n";

        // Vider le buffer de sortie pour s'assurer que le message est envoyé immédiatement
        ob_flush();
        flush();
    }

    // 4. Attendre avant la prochaine vérification
    sleep(2); // Pause de 2 secondes
}

// Nettoyage
if ($mysqli) {
    $mysqli->close();
}
?>
