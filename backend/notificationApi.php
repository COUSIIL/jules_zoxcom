<?php
// backend/notificationApi.php
// Ce fichier centralise toute la logique de l'API pour les notifications.

// --- Headers & Initialisation ---
// Pour les actions JSON
if (!isset($_GET['action']) || $_GET['action'] !== 'sse') {
    header("Content-Type: application/json; charset=UTF-8");
}
// Pour l'action SSE
else {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');
}

header("Access-Control-Allow-Origin: *"); // ⚠️ restreindre en production
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion pre-flight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- Configuration ---
// Les chemins sont relatifs à ce fichier (backend/notificationApi.php)
$configPath = __DIR__ . '/config/dbConfig.php';
$notConfigPath = __DIR__ . '/../notification.config.php'; // Remonter d'un niveau

// Inclure les configurations
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'dbConfig.php not found.']);
    exit;
}
require_once $configPath;

if (!file_exists($notConfigPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'notification.config.php not found.']);
    exit;
}
require_once $notConfigPath;

// Vérifier que $mysqli existe et est bien une instance de mysqli
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection not initialized correctly.']);
    exit;
}


// --- Helpers ---
function send_json_response($success, $data = null, $error = null, $http_code = 200) {
    if (!headers_sent()) {
        http_response_code($http_code);
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


// --- Routage ---
$action = $_GET['action'] ?? null;
$auth = authenticate_request();

if (!$auth && !in_array($action, ['setup', 'listTags'])) { // 'setup' et 'listTags' pourraient être publics
    // En mode non-authentifié, on bloque tout sauf les actions autorisées.
    // send_json_response(false, null, 'Authentication required.', 401);
}


switch ($action) {

    case 'setup':
        $tables = [];
        $tables['notification_tags'] = "CREATE TABLE IF NOT EXISTS `notification_tags` (`id` INT AUTO_INCREMENT PRIMARY KEY, `slug` VARCHAR(100) NOT NULL UNIQUE, `label` VARCHAR(255) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $tables['notifications'] = "CREATE TABLE IF NOT EXISTS `notifications` (`id` BIGINT AUTO_INCREMENT PRIMARY KEY, `title` VARCHAR(255) NOT NULL, `body` TEXT, `tag_id` INT NULL, `type` VARCHAR(32) NOT NULL DEFAULT 'info', `priority` TINYINT NOT NULL DEFAULT 2, `channels` JSON NOT NULL, `meta` JSON NULL, `status` VARCHAR(32) NOT NULL DEFAULT 'draft', `visible_from` DATETIME NULL, `expires_at` DATETIME NULL, `created_by` INT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (`tag_id`) REFERENCES `notification_tags`(id) ON DELETE SET NULL, INDEX `idx_created_at` (`created_at`), INDEX `idx_status` (`status`), INDEX `idx_type` (`type`), INDEX `idx_visible_from` (`visible_from`), INDEX `idx_expires_at` (`expires_at`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $tables['user_notifications'] = "CREATE TABLE IF NOT EXISTS `user_notifications` (`id` BIGINT AUTO_INCREMENT PRIMARY KEY, `notification_id` BIGINT NOT NULL, `user_id` BIGINT NOT NULL, `delivered_at` DATETIME NULL, `is_read` TINYINT(1) NOT NULL DEFAULT 0, `read_at` DATETIME NULL, `channel_info` JSON NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY `uq_notification_user` (`notification_id`, `user_id`), FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE, INDEX `idx_user_id` (`user_id`), INDEX `idx_is_read` (`is_read`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $tables['push_subscriptions'] = "CREATE TABLE IF NOT EXISTS `push_subscriptions` (`id` BIGINT AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT NOT NULL, `subscription` JSON NOT NULL, `label` VARCHAR(255) NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `last_seen` TIMESTAMP NULL, INDEX `idx_user_id` (`user_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $tables['notification_targets'] = "CREATE TABLE IF NOT EXISTS `notification_targets` (`id` BIGINT AUTO_INCREMENT PRIMARY KEY, `notification_id` BIGINT NOT NULL, `target_type` VARCHAR(50) NOT NULL, `target_value` VARCHAR(100) NOT NULL, FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE, INDEX `idx_target` (`target_type`, `target_value`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $results = [];
        foreach ($tables as $name => $sql) {
            if ($mysqli->query($sql) === false) {
                $results[$name] = "Error: " . $mysqli->error;
            } else {
                $results[$name] = "OK";
            }
        }
        send_json_response(true, $results);
        break;

    case 'sse':
        $user_id = $auth['user_id'];
        if (!$user_id) {
            error_log("SSE Error: No user ID found.");
            exit;
        }

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

        while (true) {
            if (connection_aborted()) {
                break;
            }

            $sql = "SELECT n.id, n.title, n.body, n.type, n.priority, n.channels, n.meta, n.created_at, un.id as user_notification_id, un.is_read, un.read_at FROM notifications AS n INNER JOIN user_notifications AS un ON n.id = un.notification_id WHERE un.user_id = ? AND un.id > ? AND (n.status = 'sent' OR n.status = 'queued') ORDER BY un.id ASC";
            $stmt = $mysqli->prepare($sql);
            if (!$stmt) {
                error_log("SSE Prepare failed: " . $mysqli->error);
                sleep(10);
                continue;
            }

            $stmt->bind_param('ii', $user_id, $last_event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $notifications = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            foreach ($notifications as $notification) {
                $last_event_id = $notification['user_notification_id'];
                $notification['channels'] = json_decode($notification['channels'] ?? '[]', true);
                $notification['meta'] = $notification['meta'] ? json_decode($notification['meta'], true) : null;

                echo "id: " . $last_event_id . "\n";
                echo "event: notification\n";
                echo "data: " . json_encode($notification) . "\n\n";

                ob_flush();
                flush();
            }

            sleep(2);
        }
        break;

    case 'listNotifications':
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT) ?: $auth['user_id'];
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $per_page = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 20;
        $offset = ($page - 1) * $per_page;

        $sql = "SELECT n.id, n.title, n.body, n.type, n.priority, n.channels, n.meta, n.created_at, un.is_read, un.read_at FROM notifications AS n INNER JOIN user_notifications AS un ON n.id = un.notification_id WHERE un.user_id = ? AND (n.status = 'sent' OR n.status = 'queued') AND (n.visible_from IS NULL OR n.visible_from <= NOW()) AND (n.expires_at IS NULL OR n.expires_at > NOW()) ORDER BY n.priority DESC, n.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);

        $stmt->bind_param('iii', $user_id, $per_page, $offset);
        if (!$stmt->execute()) send_json_response(false, null, 'Execute failed: ' . $stmt->error, 500);

        $res = $stmt->get_result();
        $notifications = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
        $stmt->close();

        foreach ($notifications as &$n) {
            $n['channels'] = json_decode($n['channels'] ?? '[]', true);
            $n['meta'] = $n['meta'] ? json_decode($n['meta'], true) : null;
        }

        $countStmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM user_notifications WHERE user_id = ? AND is_read = 0");
        if (!$countStmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);
        $countStmt->bind_param('i', $user_id);
        $countStmt->execute();
        $cres = $countStmt->get_result();
        $total_unread = $cres->fetch_assoc()['cnt'] ?? 0;
        $countStmt->close();

        send_json_response(true, ['notifications' => $notifications, 'unread_count' => intval($total_unread)]);
        break;

    case 'markRead':
        $input = get_json_input();
        $notification_id = isset($input['notification_id']) ? intval($input['notification_id']) : null;
        if (!$notification_id) send_json_response(false, null, 'notification_id is required.', 400);

        $stmt = $mysqli->prepare("INSERT INTO user_notifications (notification_id, user_id, is_read, read_at) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()");
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);

        $stmt->bind_param('ii', $notification_id, $auth['user_id']);
        if (!$stmt->execute()) send_json_response(false, null, 'Execute failed: ' . $stmt->error, 500);

        $stmt->close();
        send_json_response(true, ['status' => 'marked as read']);
        break;

    case 'markAllRead':
        $stmt = $mysqli->prepare("UPDATE user_notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);
        $stmt->bind_param('i', $auth['user_id']);
        if (!$stmt->execute()) send_json_response(false, null, 'Execute failed: ' . $stmt->error, 500);
        $stmt->close();
        send_json_response(true, ['status' => 'all marked as read']);
        break;

    case 'subscribePush':
        $input = get_json_input();
        $subscription = $input['subscription'] ?? null;
        if (!$subscription || empty($subscription['endpoint'])) send_json_response(false, null, 'Invalid subscription object.', 400);

        $endpoint = $subscription['endpoint'];
        $sub_json = json_encode($subscription);
        $label = $input['label'] ?? null;

        $del = $mysqli->prepare("DELETE FROM push_subscriptions WHERE JSON_EXTRACT(subscription, '$.endpoint') = ?");
        if (!$del) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);
        $del->bind_param('s', $endpoint);
        if (!$del->execute()) send_json_response(false, null, 'Execute failed: ' . $del->error, 500);
        $del->close();

        $ins = $mysqli->prepare("INSERT INTO push_subscriptions (user_id, subscription, label) VALUES (?, ?, ?)");
        if (!$ins) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);
        $ins->bind_param('iss', $auth['user_id'], $sub_json, $label);
        if (!$ins->execute()) send_json_response(false, null, 'Execute failed: ' . $ins->error, 500);
        $ins->close();

        send_json_response(true, ['status' => 'subscribed']);
        break;

    case 'createTag':
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);
        $input = get_json_input();
        $slug = trim($input['slug'] ?? '');
        $label = trim($input['label'] ?? '');
        if ($slug === '' || $label === '') send_json_response(false, null, 'Slug and label are required.', 400);

        $stmt = $mysqli->prepare("INSERT INTO notification_tags (slug, label) VALUES (?, ?)");
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);
        $stmt->bind_param('ss', $slug, $label);
        if (!$stmt->execute()) {
            if ($mysqli->errno === 1062) send_json_response(false, null, 'Tag slug already exists.', 409);
            send_json_response(false, null, 'Failed to create tag: ' . $mysqli->error, 500);
        }
        $id = $mysqli->insert_id;
        $stmt->close();
        send_json_response(true, ['id' => $id], null, 201);
        break;

    case 'listTags':
        $res = $mysqli->query("SELECT id, slug, label FROM notification_tags ORDER BY label ASC");
        if (!$res) send_json_response(false, null, 'Query failed: ' . $mysqli->error, 500);
        $tags = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
        send_json_response(true, $tags);
        break;

    case 'createNotification':
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);
        $input = get_json_input();
        // Further validation...
        $result = create_and_enqueue_notification($mysqli, $input);
        if ($result === true) {
            send_json_response(true, ['status' => 'Notification created and enqueued.']);
        } else {
            send_json_response(false, null, 'Failed to create notification: ' . $result, 500);
        }
        break;

    case 'enqueueSend':
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);
        $input = get_json_input();
        $notification_id = isset($input['notification_id']) ? intval($input['notification_id']) : null;
        if (!$notification_id) send_json_response(false, null, 'notification_id is required.', 400);

        // This is a simplified version. The original logic from api.php should be used.
        // For now, just a placeholder:
        $stmt = $mysqli->prepare("UPDATE notifications SET status = 'queued' WHERE id = ?");
        $stmt->bind_param('i', $notification_id);
        $stmt->execute();
        send_json_response(true, ['status' => 'Notification enqueued for sending.']);
        break;

}


?>
