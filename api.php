<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // En développement, soyez plus restrictif en production.
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion de la requête pre-flight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Inclure les configurations
if (!file_exists('dbConfig.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Le fichier de configuration dbConfig.php est manquant.']);
    exit;
}
require_once 'dbConfig.php';

if (!file_exists('notification.config.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Le fichier de configuration notification.config.php est manquant.']);
    exit;
}
require_once 'notification.config.php';

// --- Fonctions Utilitaires ---

/**
 * Envoie une réponse JSON standardisée et termine le script.
 * @param bool $success
 * @param mixed|null $data
 * @param string|null $error
 * @param int $http_code
 */
function send_json_response($success, $data = null, $error = null, $http_code = 200) {
    http_response_code($http_code);
    echo json_encode(['success' => $success, 'data' => $data, 'error' => $error]);
    exit;
}

/**
 * Récupère le corps de la requête JSON.
 * @return mixed
 */
function get_json_input() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        send_json_response(false, null, 'Invalid JSON payload.', 400);
    }
    return $input;
}

/**
 * Placeholder pour l'authentification.
 * Doit être implémentée pour vérifier le token JWT ou la session.
 * @return array|false Un tableau avec 'user_id' et 'role', ou false si non authentifié.
 */
function authenticate_request() {
    // Exemple de logique à implémenter :
    // 1. Récupérer le header 'Authorization: Bearer <token>'
    // 2. Valider le token JWT avec JWT_SECRET_KEY
    // 3. Si valide, retourner les informations de l'utilisateur.
    // Pour les tests, on peut retourner un utilisateur par défaut.
    // ATTENTION : NE PAS UTILISER EN PRODUCTION
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        return ['user_id' => 1, 'role' => 'admin']; // Utilisateur de test
    }

    // En production, mettez votre vraie logique ici. Si l'authentification échoue:
    // send_json_response(false, null, 'Authentication failed.', 401);

    // Retourner false si aucune authentification n'est gérée pour le moment.
    // Les fonctions devront vérifier ce retour.
    // Pour cet exemple, nous allons simuler un utilisateur authentifié.
     return ['user_id' => 1, 'role' => 'admin'];
}


// On s'assure que la connexion PDO est bien établie.
// Le fichier `dbConfig.php` peut soit définir les constantes, soit créer directement l'objet $pdo.
if (!isset($pdo) || !($pdo instanceof PDO)) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
    } catch (\PDOException $e) {
        send_json_response(false, null, 'Database connection failed: ' . $e->getMessage(), 500);
    }
}


// --- Routeur Principal ---
$action = $_GET['action'] ?? '';
$auth = authenticate_request(); // Vérifie l'authentification pour toutes les routes.

// Certaines actions peuvent ne pas nécessiter d'auth, à ajuster au besoin.
if (!$auth && !in_array($action, ['some_public_action'])) {
     // send_json_response(false, null, 'Authentication required.', 401);
}


switch ($action) {
    // --- Actions sur les Tags ---
    case 'createTag':
        // Seuls les admins peuvent créer des tags
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);
        $input = get_json_input();
        if (empty($input['slug']) || empty($input['label'])) send_json_response(false, null, 'Slug and label are required.', 400);

        $stmt = $pdo->prepare("INSERT INTO notification_tags (slug, label) VALUES (?, ?)");
        try {
            $stmt->execute([$input['slug'], $input['label']]);
            send_json_response(true, ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                send_json_response(false, null, 'Tag slug already exists.', 409);
            }
            send_json_response(false, null, 'Failed to create tag.', 500);
        }
        break;

    case 'listTags':
        $stmt = $pdo->query("SELECT id, slug, label FROM notification_tags ORDER BY label ASC");
        send_json_response(true, $stmt->fetchAll());
        break;

    // --- Actions sur les Notifications ---
    case 'createNotification':
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);
        $input = get_json_input();
        // Validation... (à compléter)

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO notifications (title, body, tag_id, type, priority, channels, meta, status, visible_from, expires_at, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $input['title'],
                $input['body'] ?? null,
                $input['tag_id'] ?? null,
                $input['type'] ?? 'info',
                $input['priority'] ?? 2,
                json_encode($input['channels'] ?? ['inapp']),
                isset($input['meta']) ? json_encode($input['meta']) : null,
                $input['status'] ?? 'draft',
                $input['visible_from'] ?? null,
                $input['expires_at'] ?? null,
                $auth['user_id']
            ]);
            $notificationId = $pdo->lastInsertId();

            // Gérer les cibles (broadcast, roles, etc.)
            if (!empty($input['targets'])) {
                 foreach ($input['targets'] as $target) {
                    $stmt = $pdo->prepare("INSERT INTO notification_targets (notification_id, target_type, target_value) VALUES (?, ?, ?)");
                    $stmt->execute([$notificationId, $target['type'], $target['value']]);
                }
            }

            $pdo->commit();
            send_json_response(true, ['id' => $notificationId]);
        } catch (Exception $e) {
            $pdo->rollBack();
            send_json_response(false, null, 'Failed to create notification: ' . $e->getMessage(), 500);
        }
        break;

    case 'listNotifications':
        $user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT) ?: $auth['user_id'];
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1;
        $per_page = filter_input(INPUT_GET, 'per_page', FILTER_VALIDATE_INT) ?: 20;
        $offset = ($page - 1) * $per_page;

        // Requête pour récupérer les notifications d'un utilisateur spécifique.
        // On ne récupère que les notifications pour lesquelles une entrée existe dans `user_notifications`,
        // car l'action `enqueueSend` est responsable de créer ces entrées pour tous les destinataires.
        $sql = "SELECT
                    n.id, n.title, n.body, n.type, n.priority, n.channels, n.meta, n.created_at,
                    un.is_read, un.read_at
                FROM notifications AS n
                INNER JOIN user_notifications AS un ON n.id = un.notification_id
                WHERE un.user_id = :user_id
                  AND (n.status = 'sent' OR n.status = 'queued')
                  AND (n.visible_from IS NULL OR n.visible_from <= NOW())
                  AND (n.expires_at IS NULL OR n.expires_at > NOW())
                ORDER BY n.priority DESC, n.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $notifications = $stmt->fetchAll();

        // Compter le total pour la pagination
        // Note: une requête de comptage plus simple serait nécessaire pour une pagination précise
        $total_unread_stmt = $pdo->prepare("SELECT COUNT(*) FROM user_notifications WHERE user_id = ? AND is_read = 0");
        $total_unread_stmt->execute([$user_id]);
        $total_unread = $total_unread_stmt->fetchColumn();

        send_json_response(true, ['notifications' => $notifications, 'unread_count' => $total_unread]);
        break;

    case 'markRead':
        $input = get_json_input();
        $notification_id = $input['notification_id'] ?? null;
        if (!$notification_id) send_json_response(false, null, 'notification_id is required.', 400);

        $stmt = $pdo->prepare(
            "INSERT INTO user_notifications (notification_id, user_id, is_read, read_at)
             VALUES (?, ?, 1, NOW())
             ON DUPLICATE KEY UPDATE is_read = 1, read_at = NOW()"
        );
        $stmt->execute([$notification_id, $auth['user_id']]);
        send_json_response(true, ['status' => 'marked as read']);
        break;

    case 'markAllRead':
        $stmt = $pdo->prepare("UPDATE user_notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$auth['user_id']]);
        send_json_response(true, ['status' => 'all marked as read']);
        break;

    case 'subscribePush':
        $input = get_json_input();
        $subscription = $input['subscription'] ?? null;
        if (!$subscription || empty($subscription['endpoint'])) send_json_response(false, null, 'Invalid subscription object.', 400);

        // Supprimer les anciennes subscriptions pour le même endpoint pour éviter les doublons
        $pdo->prepare("DELETE FROM push_subscriptions WHERE JSON_EXTRACT(subscription, '$.endpoint') = ?")
            ->execute([$subscription['endpoint']]);

        $stmt = $pdo->prepare("INSERT INTO push_subscriptions (user_id, subscription, label) VALUES (?, ?, ?)");
        $stmt->execute([
            $auth['user_id'],
            json_encode($subscription),
            $input['label'] ?? null
        ]);
        send_json_response(true, ['status' => 'subscribed']);
        break;

    case 'enqueueSend':
        if ($auth['role'] !== 'admin') send_json_response(false, null, 'Permission denied.', 403);
        $input = get_json_input();
        $notification_id = $input['notification_id'] ?? null;
        if (!$notification_id) send_json_response(false, null, 'notification_id is required.', 400);

        $pdo->beginTransaction();
        try {
            // 1. Vérifier que la notification existe et n'est pas déjà envoyée
            $stmt = $pdo->prepare("SELECT * FROM notifications WHERE id = ? AND status IN ('draft', 'failed')");
            $stmt->execute([$notification_id]);
            $notification = $stmt->fetch();
            if (!$notification) {
                throw new Exception("Notification not found or already processed.");
            }

            // 2. Changer le statut de la notification à 'queued'
            $stmt = $pdo->prepare("UPDATE notifications SET status = 'queued' WHERE id = ?");
            $stmt->execute([$notification_id]);

            // 3. Récupérer les cibles
            $stmt = $pdo->prepare("SELECT target_type, target_value FROM notification_targets WHERE notification_id = ?");
            $stmt->execute([$notification_id]);
            $targets = $stmt->fetchAll();

            // 4. Créer les entrées dans `user_notifications` en fonction des cibles
            if (empty($targets)) {
                // Si aucune cible n'est définie, on ne fait rien.
                // Une notification sans cible n'est visible pour personne.
            } else {
                foreach ($targets as $target) {
                    if ($target['target_type'] === 'user_id') {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO user_notifications (notification_id, user_id) VALUES (?, ?)");
                        $stmt->execute([$notification_id, $target['target_value']]);
                    } elseif ($target['target_type'] === 'broadcast') {
                        // IMPORTANT: Pour que le broadcast fonctionne, vous devez remplacer `users`
                        // ci-dessous par le nom réel de votre table d'utilisateurs.
                        $stmt = $pdo->prepare(
                            "INSERT IGNORE INTO user_notifications (notification_id, user_id)
                             SELECT ?, id FROM users" // <-- MODIFIEZ "users" PAR LE NOM DE VOTRE TABLE UTILISATEURS
                        );
                        $stmt->execute([$notification_id]);
                    }
                    // Vous pouvez ajouter ici d'autres logiques de ciblage (par rôle, groupe, etc.)
                    // en vous basant sur la structure de vos propres tables.
                }
            }

            $pdo->commit();
            send_json_response(true, ['status' => 'Notification enqueued for sending.']);

        } catch (Exception $e) {
            $pdo->rollBack();
            send_json_response(false, null, 'Failed to enqueue: ' . $e->getMessage(), 500);
        }
        break;

    default:
        send_json_response(false, null, 'Invalid action.', 404);
        break;
}
?>
