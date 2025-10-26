<?php
// backend/notificationApi.php
// Ce fichier centralise toute la logique de l'API pour les notifications.

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

// --- Headers & Initialisation ---
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // ⚠️ restreindre en production
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion pre-flight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// --- Configuration & Functions ---
require_once __DIR__ . '/config/dbConfig.php';
require_once __DIR__ . '/notificationFunction.php';
require __DIR__ . '/../notification.config.php';


// Vérifier que $mysqli existe et est bien une instance de mysqli
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection not initialized correctly.']);
    exit;
}

// --- Routage ---
$action = $_GET['action'] ?? null;
$auth = $_GET['user_id'] ?? null;
$notification_id = $_GET['notification_id'] ?? null;


if (!$auth && !in_array($action, ['setup', 'listTags'])) { // 'setup' et 'listTags' pourraient être publics
    // En mode non-authentifié, on bloque tout sauf les actions autorisées.
    send_json_response(false, null, 'Authentication required.', 401);
    exit;
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

    case 'listNotifications':
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT) ?: $auth;
        $since_id = filter_input(INPUT_GET, 'since_id', FILTER_VALIDATE_INT);
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $per_page = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 20;
        $offset = ($page - 1) * $per_page;

        $sql = "SELECT n.id, n.title, n.body, n.type, n.priority, n.channels, n.meta, n.created_at, un.is_read, un.read_at
                FROM notifications AS n
                INNER JOIN user_notifications AS un ON n.id = un.notification_id
                WHERE un.user_id = ?
                AND (n.status = 'sent' OR n.status = 'queued')
                AND (n.visible_from IS NULL OR n.visible_from <= NOW())
                AND (n.expires_at IS NULL OR n.expires_at > NOW())";

        $params = [$user_id];
        $types = "i";

        if ($since_id) {
            $sql .= " AND un.id > ?";
            $params[] = $since_id;
            $types .= "i";
            $sql .= " ORDER BY un.id ASC";
        } else {
            $sql .= " ORDER BY n.priority DESC, n.created_at DESC LIMIT ? OFFSET ?";
            $params[] = $per_page;
            $params[] = $offset;
            $types .= "ii";
        }

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);

        $stmt->bind_param($types, ...$params);
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
        $notification_id = isset($notification_id) ? intval($notification_id) : null;
        if (!$notification_id) send_json_response(false, null, 'notification_id is required.', 400);

        $stmt = $mysqli->prepare("INSERT INTO user_notifications (notification_id, user_id, is_read, read_at) VALUES (?, ?, 1, NOW()) ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()");
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);

        $stmt->bind_param('ii', $notification_id, $auth);
        if (!$stmt->execute()) send_json_response(false, null, 'Execute failed: ' . $stmt->error, 500);

        $stmt->close();
        send_json_response(true, ['status' => 'marked as read']);
        break;

    case 'markAllRead':
        $stmt = $mysqli->prepare("UPDATE user_notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);
        $stmt->bind_param('i', $auth);
        if (!$stmt->execute()) send_json_response(false, null, 'Execute failed: ' . $stmt->error, 500);
        $stmt->close();
        send_json_response(true, $auth, 'all marked as read');
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
        $ins->bind_param('iss', $auth, $sub_json, $label);
        if (!$ins->execute()) send_json_response(false, null, 'Execute failed: ' . $ins->error, 500);
        $ins->close();

        send_json_response(true, ['status' => 'subscribed']);
        break;

    case 'createTag':

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

        $notification_id = isset($notification_id) ? intval($notification_id) : null;
        if (!$notification_id) send_json_response(false, null, 'notification_id is required.', 400);

        // This is a simplified version. The original logic from api.php should be used.
        // For now, just a placeholder:
        $stmt = $mysqli->prepare("UPDATE notifications SET status = 'queued' WHERE id = ?");
        $stmt->bind_param('i', $notification_id);
        $stmt->execute();
        send_json_response(true, ['status' => 'Notification enqueued for sending.']);
        break;

    case 'swPush':

    // 🔹 Récupération des abonnements de l'utilisateur
    $userId = (int)$auth;
    $subscriptions = getUserSubscriptions($mysqli, $userId);

    if (empty($subscriptions)) {
        send_json_response(false, null, 'Aucune subscription trouvée pour cet utilisateur.', 404);
    }

    // 🔹 Contenu de la notification
    $payload = json_encode([
        'title' => 'Nouvelle notification 🎉',
        'message' => 'Une nouvelle notification vient d’arriver.',
        'icon' => '/icons/notification-icon.png',
        'link' => '/notifications'
    ]);

    // 🔹 Chargement des clés VAPID depuis config
    require_once __DIR__ . '/config/vapid_keys.php'; // (à adapter)
    // vapid_keys.php :
    // define('VAPID_PUBLIC_KEY', '...');
    // define('VAPID_PRIVATE_KEY', '...');

    // 🔹 Instanciation du client WebPush
    $webPush = new WebPush([
        'VAPID' => [
            'subject' => VAPID_SUBJECT,
            'publicKey' => VAPID_PUBLIC_KEY,
            'privateKey' => VAPID_PRIVATE_KEY,
        ],
    ]);

    $successCount = 0;
    $failCount = 0;

    // 🔹 Envoi à chaque abonnement
    foreach ($subscriptions as $sub) {
        try {
            $subscription = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'keys' => [
                    'p256dh' => $sub['p256dh'],
                    'auth' => $sub['auth'],
                ],
            ]);

            $report = $webPush->sendOneNotification($subscription, $payload);

            if ($report->isSuccess()) {
                $successCount++;
            } else {
                $failCount++;
                error_log("[WebPush] Échec pour endpoint: " . $sub['endpoint']);
            }

        } catch (Exception $e) {
            $failCount++;
            error_log("[WebPush] Exception: " . $e->getMessage());
        }
    }

    send_json_response(true, [
        'sent' => $successCount,
        'failed' => $failCount,
        'total' => count($subscriptions),
    ]);

    break;

    case 'testPush':
    $userId = (int)$auth;
    $subscriptions = getUserSubscriptions($mysqli, $userId);

    if (empty($subscriptions)) {
        send_json_response(false, null, 'Aucune subscription trouvée pour cet utilisateur.', 404);
    }

    $webPush = new WebPush([
        'VAPID' => [
            'subject' => VAPID_SUBJECT,
            'publicKey' => VAPID_PUBLIC_KEY,
            'privateKey' => VAPID_PRIVATE_KEY,
        ],
    ]);

    $payload = json_encode([
        'title' => 'Test de notification 🎯',
        'message' => 'Si tu vois ceci, ton système de notifications push fonctionne ✅',
        'icon' => '/icons/notification-icon.png',
        'link' => '/'
    ]);

    $success = 0; $fail = 0;
    foreach ($subscriptions as $sub) {
        try {
            $subscription = Subscription::create([
                'endpoint' => $sub['endpoint'],
                'keys' => [
                    'p256dh' => $sub['p256dh'],
                    'auth' => $sub['auth'],
                ],
            ]);

            $report = $webPush->sendOneNotification($subscription, $payload);
            if ($report->isSuccess()) $success++;
            else $fail++;
        } catch (Exception $e) {
            $fail++;
        }
    }

    send_json_response(true, [
        'sent' => $success,
        'failed' => $fail,
        'total' => count($subscriptions),
    ]);
    break;





}
?>
