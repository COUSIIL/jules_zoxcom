<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushSender {
    private $pdo;
    private $webPush;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
        $auth = [
            'VAPID' => [
                'subject' => VAPID_SUBJECT,
                'publicKey' => VAPID_PUBLIC_KEY,
                'privateKey' => VAPID_PRIVATE_KEY,
            ],
        ];
        $this->webPush = new WebPush($auth);
    }

    /**
     * Envoie une notification push à une liste d'utilisateurs.
     *
     * @param array $notification
     * @param array $userIds
     * @return array Un rapport des envois
     */
    public function sendToUsers(array $notification, array $userIds): array {
        if (empty($userIds)) {
            return ['success' => 0, 'failed' => 0, 'expired' => 0];
        }

        $placeholders = rtrim(str_repeat('?,', count($userIds)), ',');
        $stmt = $this->pdo->prepare("SELECT id, user_id, subscription FROM push_subscriptions WHERE user_id IN ($placeholders)");
        $stmt->execute($userIds);
        $subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($subscriptions)) {
            return ['success' => 0, 'failed' => 0, 'expired' => 0];
        }

        $payload = json_encode([
            'title' => $notification['title'],
            'body' => $notification['body'],
            'icon' => $notification['meta']['icon'] ?? '/icon.png', // URL vers une icône
            'data' => [
                'url' => $notification['meta']['route'] ?? APP_URL,
                'notification_id' => $notification['id'],
            ],
        ]);

        foreach ($subscriptions as $sub) {
            $this->webPush->queueNotification(
                Subscription::create(json_decode($sub['subscription'], true)),
                $payload,
                ['TTL' => 5000] // Time To Live
            );
        }

        $report = [
            'success' => 0,
            'failed' => 0,
            'expired' => 0,
            'expired_ids' => [],
        ];

        /** @var \Minishlink\WebPush\MessageSentReport $sentReport */
        foreach ($this->webPush->flush() as $sentReport) {
            if ($sentReport->isSuccess()) {
                $report['success']++;
            } elseif ($sentReport->isSubscriptionExpired()) {
                // Le endpoint n'est plus valide
                $report['expired']++;
                $endpoint = $sentReport->getEndpoint();
                // On cherche l'ID de la souscription à supprimer
                foreach ($subscriptions as $sub) {
                    $subData = json_decode($sub['subscription'], true);
                    if ($subData['endpoint'] === $endpoint) {
                        $report['expired_ids'][] = $sub['id'];
                        break;
                    }
                }
            } else {
                // Autre erreur
                $report['failed']++;
                // Pour le debug: echo "Push failed for endpoint {$sentReport->getEndpoint()}: {$sentReport->getReason()}\n";
            }
        }

        // Nettoyer les abonnements expirés
        if (!empty($report['expired_ids'])) {
            $this->deleteSubscriptions($report['expired_ids']);
        }

        return $report;
    }

    /**
     * Supprime les abonnements par leurs IDs.
     *
     * @param array $subscriptionIds
     */
    private function deleteSubscriptions(array $subscriptionIds) {
        if (empty($subscriptionIds)) {
            return;
        }
        $placeholders = rtrim(str_repeat('?,', count($subscriptionIds)), ',');
        $stmt = $this->pdo->prepare("DELETE FROM push_subscriptions WHERE id IN ($placeholders)");
        $stmt->execute($subscriptionIds);
    }
}
