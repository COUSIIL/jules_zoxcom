<?php
// api.php (routeur principal) - utilise mysqli (objet) depuis backend/config/dbConfig.php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // ⚠️ restreindre en production
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Chemins config
$configPath = __DIR__ . '/backend/config/dbConfig.php';
$notConfigPath = __DIR__ . '/notification.config.php';

// Gestion pre-flight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Inclure les configurations
if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Le fichier de configuration dbConfig.php est manquant.']);
    exit;
}
require_once $configPath;

if (!file_exists($notConfigPath)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Le fichier de configuration notification.config.php est manquant.']);
    exit;
}
require_once $notConfigPath;

// Vérifier que $mysqli existe et est bien une instance de mysqli
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'La connexion $mysqli n\'est pas initialisée correctement.']);
    exit;
}

// Création des tables si elles n'existent pas
$tables = [];

// Table notification_tags
$tables['notification_tags'] = <<<SQL
CREATE TABLE IF NOT EXISTS `notification_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `label` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

// Table notifications
$tables['notifications'] = <<<SQL
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `body` TEXT,
  `tag_id` INT NULL,
  `type` VARCHAR(32) NOT NULL DEFAULT 'info' COMMENT 'Ex: info, success, warning, error, promo, system',
  `priority` TINYINT NOT NULL DEFAULT 2 COMMENT '1=low, 2=normal, 3=high, 4=urgent',
  `channels` JSON NOT NULL COMMENT 'Ex: ["inapp","email","push"]',
  `meta` JSON NULL COMMENT 'Objet libre pour données additionnelles (route, resourceId, etc.)',
  `status` VARCHAR(32) NOT NULL DEFAULT 'draft' COMMENT 'draft, queued, sent, failed, archived',
  `visible_from` DATETIME NULL,
  `expires_at` DATETIME NULL,
  `created_by` INT NULL COMMENT 'ID de l''utilisateur ou système ayant créé la notif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`tag_id`) REFERENCES `notification_tags`(id) ON DELETE SET NULL,
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_status` (`status`),
  INDEX `idx_type` (`type`),
  INDEX `idx_visible_from` (`visible_from`),
  INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

// Table user_notifications
$tables['user_notifications'] = <<<SQL
CREATE TABLE IF NOT EXISTS `user_notifications` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `notification_id` BIGINT NOT NULL,
  `user_id` BIGINT NOT NULL,
  `delivered_at` DATETIME NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME NULL,
  `channel_info` JSON NULL COMMENT 'Stocke les métadonnées par canal (ex: email_id, push_id)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_notification_user` (`notification_id`, `user_id`),
  FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

// Table push_subscriptions
$tables['push_subscriptions'] = <<<SQL
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
  `user_id` BIGINT NOT NULL,
  `subscription` JSON NOT NULL,
  `label` VARCHAR(255) NULL COMMENT 'Ex: "Chrome sur Windows", "iPhone de John"',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_seen` TIMESTAMP NULL,
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

// Table notification_targets
$tables['notification_targets'] = <<<SQL
CREATE TABLE IF NOT EXISTS `notification_targets` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `notification_id` BIGINT NOT NULL,
    `target_type` VARCHAR(50) NOT NULL COMMENT 'Ex: "role", "group", "segment"',
    `target_value` VARCHAR(100) NOT NULL,
    FOREIGN KEY (`notification_id`) REFERENCES `notifications`(`id`) ON DELETE CASCADE,
    INDEX `idx_target` (`target_type`, `target_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

// Exécution
foreach ($tables as $name => $sql) {
    if ($mysqli->query($sql) === false) {
        http_response_code(500);
        exit(json_encode([
            'success' => false,
            'message' => "Erreur création table `$name`: " . $mysqli->error
        ]));
    }
}


// --- Helpers ---
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

/**
 * Crée et met en file d'attente une notification système.
 * C'est une fonction interne, pas une route API.
 *
 * @param mysqli $db - L'instance de la connexion mysqli.
 * @param array $params - Tableau contenant les détails de la notification.
 *   - 'title' (string) - Requis.
 *   - 'body' (string) - Optionnel.
 *   - 'targets' (array) - Requis. Ex: [['type' => 'user_id', 'value' => 1]]
 *   - 'type' (string) - Optionnel, défaut 'info'.
 *   - 'priority' (int) - Optionnel, défaut 2.
 *   - 'channels' (array) - Optionnel, défaut ['inapp'].
 *   - 'meta' (array) - Optionnel.
 * @return bool|string - true en cas de succès, message d'erreur en cas d'échec.
 */
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
            // Vous pouvez étendre ici pour 'role', 'broadcast', etc.
        }

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        // Log l'erreur au lieu de l'afficher directement
        error_log("Notification creation failed: " . $e->getMessage());
        return $e->getMessage();
    }
}

/**
 * Placeholder d'authentification.
 * Retourne ['user_id' => int, 'role' => 'admin'|'user'|...] ou false
 * À remplacer par ta logique réelle (JWT/session).
 */
function authenticate_request() {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        return ['user_id' => 2, 'role' => 'admin'];
    }

    // IMPLEMENTER ICI la lecture du header Authorization et la vérification du token
    // Exemple minimal (danger : NE PAS UTILISER EN PRODUCTION) :
    // return ['user_id' => 1, 'role' => 'admin'];

    return ['user_id' => 1, 'role' => 'admin']; // temporaire pour développement
}

// Sanity check pour éviter notices
$action = $_GET['action'] ?? '';
$auth = authenticate_request();

// Si action publique, tu peux ajuster la condition
if (!$auth && !in_array($action, ['listTags', 'listPermissions'])) {
    // send_json_response(false, null, 'Authentication required.', 401);
    // pour l'instant on laisse continuer en mode dev
}

// --- ROUTES ---
switch ($action) {

    // ---------- Tags ----------
    case 'createTag':
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);

        $input = get_json_input();
        $slug = trim($input['slug'] ?? '');
        $label = trim($input['label'] ?? '');

        if ($slug === '' || $label === '') {
            send_json_response(false, null, 'Slug and label are required.', 400);
        }

        $stmt = $mysqli->prepare("INSERT INTO notification_tags (slug, label) VALUES (?, ?)");
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);

        $stmt->bind_param('ss', $slug, $label);
        if (!$stmt->execute()) {
            // duplicate entry check (MySQL error code 1062)
            if ($mysqli->errno === 1062) {
                send_json_response(false, null, 'Tag slug already exists.', 409);
            }
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

    // ---------- Create Notification ----------
    case 'createNotification':
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);

        $input = get_json_input();
        $title = trim($input['title'] ?? '');
        if ($title === '') send_json_response(false, null, 'Title is required.', 400);

        $body = $input['body'] ?? null;
        $tag_id = !empty($input['tag_id']) ? intval($input['tag_id']) : null;
        $type = $input['type'] ?? 'info';
        $priority = isset($input['priority']) ? intval($input['priority']) : 2;
        $channels = isset($input['channels']) ? json_encode($input['channels']) : json_encode(['inapp']);
        $meta = isset($input['meta']) ? json_encode($input['meta']) : null;
        $status = $input['status'] ?? 'draft';
        $visible_from = $input['visible_from'] ?? null;
        $expires_at = $input['expires_at'] ?? null;
        $created_by = $auth['user_id'];

        // Transaction
        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare(
                "INSERT INTO notifications
                 (title, body, tag_id, type, priority, channels, meta, status, visible_from, expires_at, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if (!$stmt) throw new Exception('Prepare failed: ' . $mysqli->error);

            // Bind params: s (title), s (body or null), i (tag_id or null), s(type), i(priority), s(channels), s(meta), s(status), s(visible_from), s(expires_at), i(created_by)
            // When binding NULL for integers, set variable to null and use 'i': mysqli accepts null for bind_param if variable is null.
            $bind_tag = $tag_id;
            $bind_body = $body;
            $bind_meta = $meta;
            $bind_visible = $visible_from;
            $bind_expires = $expires_at;

            $stmt->bind_param(
                'sssisissssi',
                $title,
                $bind_body,
                $bind_tag,
                $type,
                $priority,
                $channels,
                $bind_meta,
                $status,
                $bind_visible,
                $bind_expires,
                $created_by
            );


            // Note: bind_param requires variables (can't pass expressions directly). Above we passed variables.

            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            $notificationId = $mysqli->insert_id;
            $stmt->close();

            // targets (optionnel)
            if (!empty($input['targets']) && is_array($input['targets'])) {
                $tstmt = $mysqli->prepare("INSERT INTO notification_targets (notification_id, target_type, target_value) VALUES (?, ?, ?)");
                if (!$tstmt) throw new Exception('Prepare target failed: ' . $mysqli->error);

                foreach ($input['targets'] as $target) {
                    $ttype = $target['type'] ?? null;
                    $tvalue = $target['value'] ?? null;
                    if (!$ttype) continue;
                    $tstmt->bind_param('iss', $notificationId, $ttype, $tvalue);
                    if (!$tstmt->execute()) {
                        throw new Exception('Insert target failed: ' . $tstmt->error);
                    }
                }
                $tstmt->close();
            }

            $mysqli->commit();
            send_json_response(true, ['id' => $notificationId], null, 201);
        } catch (Exception $e) {
            $mysqli->rollback();
            send_json_response(false, null, 'Failed to create notification: ' . $e->getMessage(), 500);
        }
        break;

    // ---------- List Notifications for a user ----------
    case 'listNotifications':
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT) ?: $auth['user_id'];
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $per_page = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 20;
        $offset = ($page - 1) * $per_page;

        $sql = "SELECT
                    n.id, n.title, n.body, n.type, n.priority, n.channels, n.meta, n.created_at,
                    un.is_read, un.read_at
                FROM notifications AS n
                INNER JOIN user_notifications AS un ON n.id = un.notification_id
                WHERE un.user_id = ?
                  AND (n.status = 'sent' OR n.status = 'queued')
                  AND (n.visible_from IS NULL OR n.visible_from <= NOW())
                  AND (n.expires_at IS NULL OR n.expires_at > NOW())
                ORDER BY n.priority DESC, n.created_at DESC
                LIMIT ? OFFSET ?";

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);

        $stmt->bind_param('iii', $user_id, $per_page, $offset);
        if (!$stmt->execute()) send_json_response(false, null, 'Execute failed: ' . $stmt->error, 500);

        $res = $stmt->get_result();
        $notifications = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
        $stmt->close();

        // Décoder channels/meta pour chaque notification pour retourner des structures JS-friendly
        foreach ($notifications as &$n) {
            $n['channels'] = json_decode($n['channels'] ?? '[]', true);
            $n['meta'] = $n['meta'] ? json_decode($n['meta'], true) : null;
        }

        // Compter non-lus
        $countStmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM user_notifications WHERE user_id = ? AND is_read = 0");
        if (!$countStmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);
        $countStmt->bind_param('i', $user_id);
        $countStmt->execute();
        $cres = $countStmt->get_result();
        $total_unread = $cres->fetch_assoc()['cnt'] ?? 0;
        $countStmt->close();

        send_json_response(true, ['notifications' => $notifications, 'unread_count' => intval($total_unread)]);
        break;

    // ---------- Mark single notification as read ----------
    case 'markRead':
        $input = get_json_input();
        $notification_id = isset($input['notification_id']) ? intval($input['notification_id']) : null;
        if (!$notification_id) send_json_response(false, null, 'notification_id is required.', 400);

        $stmt = $mysqli->prepare(
            "INSERT INTO user_notifications (notification_id, user_id, is_read, read_at)
             VALUES (?, ?, 1, NOW())
             ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()"
        );
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);

        $stmt->bind_param('ii', $notification_id, $auth['user_id']);
        if (!$stmt->execute()) send_json_response(false, null, 'Execute failed: ' . $stmt->error, 500);

        $stmt->close();
        send_json_response(true, ['status' => 'marked as read']);
        break;

    // ---------- Mark all read ----------
    case 'markAllRead':
        $stmt = $mysqli->prepare("UPDATE user_notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
        if (!$stmt) send_json_response(false, null, 'Prepare failed: ' . $mysqli->error, 500);
        $stmt->bind_param('i', $auth['user_id']);
        if (!$stmt->execute()) send_json_response(false, null, 'Execute failed: ' . $stmt->error, 500);
        $stmt->close();
        send_json_response(true, ['status' => 'all marked as read']);
        break;

    // ---------- Subscribe Push ----------
    case 'subscribePush':
        $input = get_json_input();
        $subscription = $input['subscription'] ?? null;
        if (!$subscription || empty($subscription['endpoint'])) send_json_response(false, null, 'Invalid subscription object.', 400);

        $endpoint = $subscription['endpoint'];
        $sub_json = json_encode($subscription);
        $label = $input['label'] ?? null;

        // Supprimer anciennes subscriptions ayant le même endpoint
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

    // ---------- Enqueue Send ----------
    case 'enqueueSend':
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);
        $input = get_json_input();
        $notification_id = isset($input['notification_id']) ? intval($input['notification_id']) : null;
        if (!$notification_id) send_json_response(false, null, 'notification_id is required.', 400);

        $mysqli->begin_transaction();
        try {
            $stmt = $mysqli->prepare("SELECT * FROM notifications WHERE id = ? AND status IN ('draft', 'failed')");
            if (!$stmt) throw new Exception('Prepare failed: ' . $mysqli->error);
            $stmt->bind_param('i', $notification_id);
            if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);
            $res = $stmt->get_result();
            $notification = $res->fetch_assoc();
            $res->free();
            $stmt->close();

            if (!$notification) {
                throw new Exception("Notification not found or already processed.");
            }

            // Mettre à queued
            $u = $mysqli->prepare("UPDATE notifications SET status = 'queued' WHERE id = ?");
            if (!$u) throw new Exception('Prepare failed: ' . $mysqli->error);
            $u->bind_param('i', $notification_id);
            if (!$u->execute()) throw new Exception('Execute failed: ' . $u->error);
            $u->close();

            // Récupérer targets
            $t = $mysqli->prepare("SELECT target_type, target_value FROM notification_targets WHERE notification_id = ?");
            if (!$t) throw new Exception('Prepare failed: ' . $mysqli->error);
            $t->bind_param('i', $notification_id);
            if (!$t->execute()) throw new Exception('Execute failed: ' . $t->error);
            $tres = $t->get_result();
            $targets = $tres->fetch_all(MYSQLI_ASSOC);
            $tres->free();
            $t->close();

            // Insérer entries user_notifications selon targets
            if (!empty($targets)) {
                foreach ($targets as $target) {
                    if ($target['target_type'] === 'user_id') {
                        $ins = $mysqli->prepare("INSERT IGNORE INTO user_notifications (notification_id, user_id) VALUES (?, ?)");
                        if (!$ins) throw new Exception('Prepare failed: ' . $mysqli->error);
                        $uid = intval($target['target_value']);
                        $ins->bind_param('ii', $notification_id, $uid);
                        if (!$ins->execute()) throw new Exception('Execute failed: ' . $ins->error);
                        $ins->close();
                    } elseif ($target['target_type'] === 'broadcast') {
                        // Remplacez 'users' par le nom réel de votre table utilisateurs si différent
                        $sql = "INSERT IGNORE INTO user_notifications (notification_id, user_id) SELECT ?, id FROM users";
                        if (!$mysqli->query($sql)) throw new Exception('Broadcast insert failed: ' . $mysqli->error);
                    } else {
                        // TODO: gérer roles/groups/segments si nécessaire
                    }
                }
            }

            $mysqli->commit();
            send_json_response(true, ['status' => 'Notification enqueued for sending.']);
        } catch (Exception $e) {
            $mysqli->rollback();
            send_json_response(false, null, 'Failed to enqueue: ' . $e->getMessage(), 500);
        }
        break;

    default:
        send_json_response(false, null, 'Invalid action.', 404);
        break;
}
