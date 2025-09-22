<?php
// backend/notificationFunction.php
// Ce fichier contient les fonctions pour le système de notification.

// --- Helpers ---
function send_json_response($success, $data = null, $error = null, $http_code = 200) {
    if (!headers_sent()) {
        http_response_code($http_code);
        header("Content-Type: application/json; charset=UTF-8");
    }
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $error]);
    exit;
}

function get_json_input() {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        send_json_response(false, null, 'Invalid JSON payload: ' . json_last_error_msg(), 400);
    }
    return $input;
}

function authenticate_request() {
    // En production, implémenter une vraie logique de session/JWT
    // Pour le développement, on simule un utilisateur authentifié.
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        return ['user_id' => 2, 'role' => 'admin'];
    }
    return ['user_id' => 1, 'role' => 'admin']; // ID utilisateur par défaut pour le dev
}

function create_and_enqueue_notification(mysqli $db, array $params) {
    // Validation des paramètres de base
    if (empty($params['title']) || empty($params['targets'])) {
        return "Title and targets are required.";
    }

    $db->begin_transaction();
    try {
        // 1. Créer la notification principale (status 'draft')
        $stmt = $db->prepare(
            "INSERT INTO notifications (title, body, type, priority, channels, meta, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, 'draft', ?)"
        );
        if (!$stmt) throw new Exception('Prepare failed: ' . $db->error);

        $title = $params['title'];
        $body = $params['body'] ?? null;
        $type = $params['type'] ?? 'info';
        $priority = $params['priority'] ?? 2;
        $channels = json_encode($params['channels'] ?? ['inapp']);
        $meta = isset($params['meta']) ? json_encode($params['meta']) : null;
        $created_by = 0; // 0 pour système

        $stmt->bind_param('sssisss', $title, $body, $type, $priority, $channels, $meta, $created_by);
        if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);

        $notificationId = $db->insert_id;
        $stmt->close();

        // 2. Ajouter les cibles (targets)
        $tstmt = $db->prepare("INSERT INTO notification_targets (notification_id, target_type, target_value) VALUES (?, ?, ?)");
        if (!$tstmt) throw new Exception('Prepare target failed: ' . $db->error);
        foreach ($params['targets'] as $target) {
            $tstmt->bind_param('iss', $notificationId, $target['type'], $target['value']);
            if (!$tstmt->execute()) throw new Exception('Insert target failed: ' . $tstmt->error);
        }
        $tstmt->close();

        // 3. Mettre la notification en file d'attente (status 'queued')
        $u = $db->prepare("UPDATE notifications SET status = 'queued' WHERE id = ?");
        if (!$u) throw new Exception('Update status failed: ' . $db->error);
        $u->bind_param('i', $notificationId);
        if (!$u->execute()) throw new Exception('Execute update status failed: ' . $u->error);
        $u->close();

        // 4. Insérer dans user_notifications pour les cibles directes
        foreach ($params['targets'] as $target) {
            if ($target['type'] === 'user_id') {
                $ins = $db->prepare("INSERT IGNORE INTO user_notifications (notification_id, user_id) VALUES (?, ?)");
                if (!$ins) throw new Exception('Prepare user_notification failed: ' . $db->error);
                $uid = intval($target['value']);
                $ins->bind_param('ii', $notificationId, $uid);
                if (!$ins->execute()) throw new Exception('Execute user_notification failed: ' . $ins->error);
                $ins->close();
            }
        }

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        error_log("Notification creation failed: " . $e->getMessage());
        return $e->getMessage();
    }
}
?>
