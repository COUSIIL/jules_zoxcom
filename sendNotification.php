<?php
// Ce script doit être exécuté uniquement en ligne de commande (CLI).
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.");
}

echo "--- Notification Worker started at " . date('Y-m-d H:i:s') . " ---\n";

// --- Initialisation ---
if (!file_exists('dbConfig.php')) {
    die("Error: dbConfig.php not found.\n");
}
require_once 'dbConfig.php';

if (!file_exists('notification.config.php')) {
    die("Error: notification.config.php not found.\n");
}
require_once 'notification.config.php';
require_once 'helpers/PushSender.php';
// require_once 'helpers/EmailSender.php'; // À créer si nécessaire

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
        die("Database connection failed: " . $e->getMessage() . "\n");
    }
}

// --- Initialisation des services d'envoi ---
$pushSender = new PushSender($pdo);
// $emailSender = new EmailSender($pdo);

// --- Traitement de la file d'attente ---
try {
    // Démarrer une transaction pour verrouiller les notifications à traiter
    $pdo->beginTransaction();

    // 1. Sélectionner un lot de notifications en attente avec verrouillage
    $stmt = $pdo->prepare(
        "SELECT * FROM notifications
         WHERE status = 'queued'
         ORDER BY priority DESC, created_at ASC
         LIMIT " . SEND_BATCH_SIZE . "
         FOR UPDATE"
    );
    $stmt->execute();
    $notifications = $stmt->fetchAll();

    if (empty($notifications)) {
        echo "No queued notifications to process.\n";
        $pdo->commit(); // Important de commit même si vide pour libérer les verrous potentiels
        exit;
    }

    echo "Processing " . count($notifications) . " notification(s)...\n";

    // 2. Mettre à jour immédiatement leur statut à 'sending' pour éviter qu'un autre worker les prenne
    $notificationIds = array_column($notifications, 'id');
    $placeholders = rtrim(str_repeat('?,', count($notificationIds)), ',');
    $updateStmt = $pdo->prepare("UPDATE notifications SET status = 'sending' WHERE id IN ($placeholders)");
    $updateStmt->execute($notificationIds);

    // Valider la transaction initiale pour libérer les verrous rapidement
    $pdo->commit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    die("Error during notification selection: " . $e->getMessage() . "\n");
}


// --- Boucle de traitement et d'envoi ---
foreach ($notifications as $notification) {
    echo "  - Processing notification #{$notification['id']}: '{$notification['title']}'\n";
    $notification['channels'] = json_decode($notification['channels'], true);
    $notification['meta'] = json_decode($notification['meta'], true) ?? [];

    $finalStatus = 'sent'; // Statut par défaut après traitement

    try {
        // Récupérer tous les utilisateurs ciblés par cette notification
        $userStmt = $pdo->prepare("SELECT user_id FROM user_notifications WHERE notification_id = ?");
        $userStmt->execute([$notification['id']]);
        $userIds = $userStmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($userIds)) {
            echo "    - No target users found. Marking as sent.\n";
        } else {
            // Traitement par canal
            foreach ($notification['channels'] as $channel) {
                echo "    - Channel: $channel\n";
                switch ($channel) {
                    case 'inapp':
                        // L'entrée existe déjà dans user_notifications, on met juste à jour delivered_at
                        $deliveredStmt = $pdo->prepare("UPDATE user_notifications SET delivered_at = NOW() WHERE notification_id = ? AND delivered_at IS NULL");
                        $deliveredStmt->execute([$notification['id']]);
                        echo "      - Marked as delivered for " . $deliveredStmt->rowCount() . " users.\n";
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
                        // Exemple de logique pour l'email
                        // if (class_exists('EmailSender')) {
                        //     $emailSender->sendToUsers($notification, $userIds);
                        //     echo "      - Email sending process initiated.\n";
                        // }
                        echo "      - Email channel not implemented yet. Skipping.\n";
                        break;
                }
            }
        }

    } catch (Exception $e) {
        $finalStatus = 'failed';
        echo "    - ERROR processing notification #{$notification['id']}: " . $e->getMessage() . "\n";
    }

    // Mettre à jour le statut final de la notification
    $statusUpdateStmt = $pdo->prepare("UPDATE notifications SET status = ? WHERE id = ?");
    $statusUpdateStmt->execute([$finalStatus, $notification['id']]);
    echo "    - Final status: $finalStatus\n";
}

echo "--- Notification Worker finished at " . date('Y-m-d H:i:s') . " ---\n";
?>
