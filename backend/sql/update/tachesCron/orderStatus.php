<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure le fichier de configuration de la base de données
$configPath = __DIR__ . '/../../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

// --- Charger API Anderson ---
$anderson = __DIR__ . '/../../../../backend/config/andersonConfig.php';
if (!file_exists($anderson)) {
    echo json_encode(['success' => false, 'message' => 'File not found.']);
    exit;
}
$dataAnderson = include $anderson;
if (empty($dataAnderson) || empty($dataAnderson[0]['key'])) {
    echo json_encode(['success' => false, 'message' => 'No Anderson API key found']);
    exit;
}
$api_key = $dataAnderson[0]['key'];

// --- Récupérer les commandes en shipping avec tracking_code ---
$sql = "SELECT id, tracking_code, status FROM orders WHERE status = 'shipping' AND tracking_code IS NOT NULL AND tracking_code != ''";
$result = $mysqli->query($sql);
if (!$result) {
    echo json_encode(['success' => false, 'message' => $mysqli->error]);
    exit;
}

$updated = 0;
while ($row = $result->fetch_assoc()) {
    $tracking = $row['tracking_code'];
    $orderId = $row['id'];

    // Appel API Anderson
    $url = "https://anderson-ecommerce.ecotrack.dz/api/v1/get/tracking/info?tracking=" . urlencode($tracking);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key",
        "Accept: application/json"
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$response) continue;

    $data = json_decode($response, true);
    if (!$data || empty($data['activity'])) continue;

    // Déterminer le dernier statut
    $lastStatus = end($data['activity'])['status'] ?? '';

    // Mapper le statut Anderson vers ton DB
    switch ($lastStatus) {
        case 'livred':
        case 'payed':
        case 'encaissed':
            $newStatus = 'accomplished';
            break;
        case 'return_asked':
        case 'return_in_transit':
        case 'Return_received':
            $newStatus = 'returned';
            break;
        default:
            $newStatus = 'shipping';
            break;
    }

    // Mettre à jour la commande si changement de statut
    if ($newStatus !== $row['status']) {
        // Récupérer infos client
        $stmt2 = $mysqli->prepare("SELECT customer_name, phone, delivery_zone, total_price FROM orders WHERE id = ?");
        $stmt2->bind_param("i", $orderId);
        $stmt2->execute();
        $stmt2->bind_result($customerName, $phone, $deliveryZone, $totalPrice);
        $stmt2->fetch();
        $stmt2->close();

        // Mettre à jour la commande
        $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $newStatus, $orderId);
        $stmt->execute();
        $stmt->close();

        // Envoi email
        $sendData = http_build_query([
            'orderID'      => $orderId,
            'name'         => $customerName,
            'phone'        => $phone,
            'trackingCode' => $tracking,
            'status'       => $newStatus,
            'deliveryZone' => $deliveryZone,
            'totalPrice'   => $totalPrice
        ]);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://management.hoggari.com/backend/email/sendTrackingUpdate.php",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $sendData,
            CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"]
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $updated++;
    }

}

echo json_encode(['success' => true, 'message' => $updated]);
$mysqli->close();
