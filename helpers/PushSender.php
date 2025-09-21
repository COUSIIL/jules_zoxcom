<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushSender {
    private $mysqli;
    private $webPush;

    public function __construct(mysqli $mysqli) {
        $this->mysqli = $mysqli;
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

        // Construction dynamique des placeholders
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $types = str_repeat('i', count($userIds));

        $stmt = $this->mysqli->prepare("SELECT id, user_id, subscription FROM push_subscriptions WHERE user_id IN ($placeholders)");
        $stmt->bind_param($types, ...$userIds);
        $stmt->execute();
        $result = $stmt->get_result();
        $subscriptions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($subscriptions)) {
            return ['success' => 0, 'failed' => 0, 'expired' => 0];
        }

        $payload = json_encode([
            'title' => $notification['title'],
            'body' => $notification['body'],
            'icon' => $notification['meta']['icon'] ?? '/icon.png',
            'data' => [
                'url' => $notification['meta']['route'] ?? APP_URL,
                'notification_id' => $notification['id'],
            ],
        ]);

        foreach ($subscriptions as $sub) {
            $this->webPush->queueNotification(
                Subscription::create(json_decode($sub['subscription'], true)),
                $payload,
                ['TTL' => 5000]
            );
        }

        $report = [
            'success' => 0,
            'failed' => 0,
            'expired' => 0,
            'expired_ids' => [],
        ];

        foreach ($this->webPush->flush() as $sentReport) {
            if ($sentReport->isSuccess()) {
                $report['success']++;
            } elseif ($sentReport->isSubscriptionExpired()) {
                $report['expired']++;
                $endpoint = $sentReport->getEndpoint();
                foreach ($subscriptions as $sub) {
                    $subData = json_decode($sub['subscription'], true);
                    if ($subData['endpoint'] === $endpoint) {
                        $report['expired_ids'][] = $sub['id'];
                        break;
                    }
                }
            } else {
                $report['failed']++;
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

        $placeholders = implode(',', array_fill(0, count($subscriptionIds), '?'));
        $types = str_repeat('i', count($subscriptionIds));

        $stmt = $this->mysqli->prepare("DELETE FROM push_subscriptions WHERE id IN ($placeholders)");
        $stmt->bind_param($types, ...$subscriptionIds);
        $stmt->execute();
        $stmt->close();
    }
}
