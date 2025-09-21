<?php
// backend/notificationApi.php

header("Content-Type: application/json; charset=UTF-8");

// --- Configuration & Helpers ---
require_once __DIR__ . '/config/dbConfig.php';

session_start();

function send_json_response($success, $data = null, $error = null, $http_code = 200) {
    http_response_code($http_code);
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

function get_current_user_id() {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
        return (int)$_SESSION['user']['id'];
    }
    return null;
}

function get_current_user_role() {
    if (isset($_SESSION['user']) && isset($_SESSION['user']['role'])) {
        return $_SESSION['user']['role'];
    }
    return 'guest';
}

// --- Table Creation ---
$tables = [];
$tables['notification_tags'] = "CREATE TABLE IF NOT EXISTS `notification_tags` (`id` INT AUTO_INCREMENT PRIMARY KEY, `slug` VARCHAR(100) NOT NULL UNIQUE, `label` VARCHAR(255) NOT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$tables['notifications'] = "CREATE TABLE IF NOT EXISTS `notifications` (`id` BIGINT AUTO_INCREMENT PRIMARY KEY, `title` VARCHAR(255) NOT NULL, `body` TEXT, `tag_id` INT NULL, `type` VARCHAR(32) NOT NULL DEFAULT 'info', `priority` TINYINT NOT NULL DEFAULT 2, `channels` JSON NOT NULL, `meta` JSON NULL, `status` VARCHAR(32) NOT NULL DEFAULT 'draft', `visible_from` DATETIME NULL, `expires_at` DATETIME NULL, `created_by` INT NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, FOREIGN KEY (`tag_id`) REFERENCES `notification_tags`(id) ON DELETE SET NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$tables['user_notifications'] = "CREATE TABLE IF NOT EXISTS `user_notifications` (`id` BIGINT AUTO_INCREMENT PRIMARY KEY, `notification_id` BIGINT NOT NULL, `user_id` BIGINT NOT NULL, `delivered_at` DATETIME NULL, `is_read` TINYINT(1) NOT NULL DEFAULT 0, `read_at` DATETIME NULL, `channel_info` JSON NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY `uq_notification_user` (`notification_id`, `user_id`), FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$tables['push_subscriptions'] = "CREATE TABLE IF NOT EXISTS `push_subscriptions` (`id` BIGINT AUTO_INCREMENT PRIMARY KEY, `user_id` BIGINT NOT NULL, `subscription` JSON NOT NULL, `label` VARCHAR(255) NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `last_seen` TIMESTAMP NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
$tables['notification_targets'] = "CREATE TABLE IF NOT EXISTS `notification_targets` (`id` BIGINT AUTO_INCREMENT PRIMARY KEY, `notification_id` BIGINT NOT NULL, `target_type` VARCHAR(50) NOT NULL, `target_value` VARCHAR(100) NOT NULL, FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

foreach ($tables as $sql) {
    if ($mysqli->query($sql) === false) {
        send_json_response(false, null, "Table creation error: " . $mysqli->error, 500);
    }
}

// --- Main Router ---
$action = $_GET['action'] ?? '';
$user_id = get_current_user_id();
$user_role = get_current_user_role();

$authenticated_routes = ['sse', 'listNotifications', 'markRead', 'markAllRead', 'subscribePush'];
if (in_array($action, $authenticated_routes) && !$user_id) {
    send_json_response(false, null, 'Authentication required.', 401);
}

switch ($action) {
    case 'sse':
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        while (true) {
            if (connection_aborted()) break;
            echo "event: new-notification\n";
            echo "data: " . json_encode(['message' => 'check for new notifications']) . "\n\n";
            ob_flush();
            flush();
            sleep(5);
        }
        break;

    case 'listNotifications':
        $stmt = $mysqli->prepare("SELECT n.id, n.title, n.body, n.type, n.priority, n.channels, n.meta, n.created_at, un.is_read, un.read_at FROM notifications AS n INNER JOIN user_notifications AS un ON n.id = un.notification_id WHERE un.user_id = ? ORDER BY n.created_at DESC LIMIT 50");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($notifications as &$n) {
            $n['channels'] = json_decode($n['channels'] ?? '[]', true);
            $n['meta'] = $n['meta'] ? json_decode($n['meta'], true) : null;
        }

        $countStmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM user_notifications WHERE user_id = ? AND is_read = 0");
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

        $stmt = $mysqli->prepare("UPDATE user_notifications SET is_read = 1, read_at = NOW() WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param('ii', $notification_id, $user_id);
        $stmt->execute();
        $stmt->close();
        send_json_response(true);
        break;

    case 'markAllAsRead':
        $stmt = $mysqli->prepare("UPDATE user_notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
        send_json_response(true);
        break;

    // Other cases from original api.php can be kept here if they are not notification related
    // For now, assuming all were notification related and have been covered or are deprecated.

    default:
        send_json_response(false, null, 'Invalid action.', 404);
        break;
}

// --- Internal Functions ---
function create_order_notification(mysqli $db, int $orderId): bool {
    $title = "Nouvelle commande reçue";
    $body = "Une nouvelle commande a été passée (#$orderId).";
    $type = "system";
    $priority = 3;
    $channels = json_encode(["inapp", "push"]);
    $meta = json_encode(["route" => "/orders/$orderId", "icon" => "/icons/order.png"]);
    $status = "queued";

    // 1. Insert the main notification
    $stmt = $db->prepare("INSERT INTO notifications (title, body, type, priority, channels, meta, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) { error_log("Prepare failed: " . $db->error); return false; }
    $stmt->bind_param('sssisss', $title, $body, $type, $priority, $channels, $meta, $status);
    if (!$stmt->execute()) { error_log("Execute failed: " . $stmt->error); return false; }
    $notificationId = $stmt->insert_id;
    $stmt->close();

    if (!$notificationId) {
        error_log("Failed to create notification for order #$orderId");
        return false;
    }

    // 2. Get all user IDs
    $usersResult = $db->query("SELECT id FROM users");
    if (!$usersResult) {
        error_log("Failed to get users for notification: " . $db->error);
        return false;
    }

    // 3. Create a user-specific notification entry for each user
    $stmt = $db->prepare("INSERT INTO user_notifications (notification_id, user_id) VALUES (?, ?)");
    if (!$stmt) { error_log("Prepare failed: " . $db->error); return false; }
    while ($user = $usersResult->fetch_assoc()) {
        $userId = $user['id'];
        $stmt->bind_param('ii', $notificationId, $userId);
        if (!$stmt->execute()) { error_log("Execute failed for user $userId: " . $stmt->error); }
    }
    $stmt->close();

    return true;
}
?>
