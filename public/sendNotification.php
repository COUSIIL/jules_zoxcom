<?php
// Ce script doit être exécuté uniquement en ligne de commande (CLI).
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

echo "--- Notification Worker started at " . date('Y-m-d H:i:s') . " ---\n";

$configPath = __DIR__ . '/backend/config/dbConfig.php';
$notConfigPath = __DIR__ . 'notification.config.php';

// --- Initialisation ---
if (!file_exists($configPath)) {
    die("Error: dbConfig.php not found.\n");
}
require_once $configPath;

if (!file_exists($notConfigPath)) {
    die("Error: notification.config.php not found.\n");
}
require_once $notConfigPath;
require_once '/helpers/PushSender.php';
// require_once 'helpers/EmailSender.php'; // À créer si nécessaire

// Vérification connexion MySQLi
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    die("Database connection not established.\n");
}

// --- Initialisation des services d'envoi ---
$pushSender = new PushSender($mysqli);
// $emailSender = new EmailSender($mysqli);

// --- Traitement de la file d'attente ---
try {
    // Démarrer une transaction pour verrouiller les notifications à traiter
    $mysqli->begin_transaction();

    // 1. Sélectionner un lot de notifications en attente avec verrouillage
    $sql = "SELECT * FROM notifications
            WHERE status = 'queued'
            ORDER BY priority DESC, created_at ASC
            LIMIT " . intval(SEND_BATCH_SIZE) . "
            FOR UPDATE";

    $result = $mysqli->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $mysqli->error);
    }

    $notifications = $result->fetch_all(MYSQLI_ASSOC);

    if (empty($notifications)) {
        echo "No queued notifications to process.\n";
        $mysqli->commit(); // Libérer les verrous
        exit;
    }

    echo "Processing " . count($notifications) . " notification(s)...\n";

    // 2. Mettre à jour immédiatement leur statut à 'sending'
    $notificationIds = array_column($notifications, 'id');
    $placeholders = implode(',', array_fill(0, count($notificationIds), '?'));

    $types = str_repeat('i', count($notificationIds));
    $stmt = $mysqli->prepare("UPDATE notifications SET status = 'sending' WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$notificationIds);
    $stmt->execute();

    // Valider la transaction initiale
    $mysqli->commit();

} catch (Exception $e) {
    if ($mysqli->errno) {
        $mysqli->rollback();
    }
    die("Error during notification selection: " . $e->getMessage() . "\n");
}

// --- Boucle de traitement et d'envoi ---
foreach ($notifications as $notification) {
    echo "  - Processing notification #{$notification['id']}: '{$notification['title']}'\n";
    $notification['channels'] = json_decode($notification['channels'], true);
    $notification['meta'] = json_decode($notification['meta'], true) ?? [];

    $finalStatus = 'sent'; // Statut par défaut

    try {
        // Récupérer tous les utilisateurs ciblés
        $stmt = $mysqli->prepare("SELECT user_id FROM user_notifications WHERE notification_id = ?");
        $stmt->bind_param("i", $notification['id']);
        $stmt->execute();
        $res = $stmt->get_result();
        $userIds = $res->fetch_all(MYSQLI_ASSOC);
        $userIds = array_column($userIds, 'user_id');

        if (empty($userIds)) {
            echo "    - No target users found. Marking as sent.\n";
        } else {
            foreach ($notification['channels'] as $channel) {
                echo "    - Channel: $channel\n";
                switch ($channel) {
                    case 'inapp':
                        $stmt = $mysqli->prepare(
                            "UPDATE user_notifications 
                             SET delivered_at = NOW() 
                             WHERE notification_id = ? AND delivered_at IS NULL"
                        );
                        $stmt->bind_param("i", $notification['id']);
                        $stmt->execute();
                        echo "      - Marked as delivered for " . $stmt->affected_rows . " users.\n";
                        break;

                    case 'push':
                        if (class_exists('PushSender')) {
                            $report = $pushSender->sendToUsers($notification, $userIds);
                            echo "      - Push report: {$report['success']} success, {$report['failed']} failed, {$report['expired']} expired.\n";
                        } else {
                            echo "      - PushSender class not found. Skipping.\n";
                        }
                        break;

                    case 'email':
                        echo "      - Email channel not implemented yet. Skipping.\n";
                        break;
                }
            }
        }

    } catch (Exception $e) {
        $finalStatus = 'failed';
        echo "    - ERROR processing notification #{$notification['id']}: " . $e->getMessage() . "\n";
    }

    // Mettre à jour le statut final
    $stmt = $mysqli->prepare("UPDATE notifications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $finalStatus, $notification['id']);
    $stmt->execute();
    echo "    - Final status: $finalStatus\n";
}

echo "--- Notification Worker finished at " . date('Y-m-d H:i:s') . " ---\n";
