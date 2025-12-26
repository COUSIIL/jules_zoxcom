<?php
header("Content-Type: application/json; charset=UTF-8");

// --- CONFIGURATION BASE DE DONNÉES ---
$configPath = __DIR__ . '/../../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}




// --- CONFIGURATION ANDERSON ---
$anderson = __DIR__ . '/../../../../backend/config/andersonConfig.php';
if (!file_exists($anderson)) {
    echo json_encode(['success' => false, 'message' => 'Anderson config file not found.']);
    $mysqli->close();
    exit;
}

$dataAnderson = include $anderson;
if (empty($dataAnderson) || empty($dataAnderson[0]['key'])) {
    echo json_encode(['success' => false, 'message' => 'No Anderson API key found.']);
    $mysqli->close();
    exit;
}

$api_key = trim($dataAnderson[0]['key']);

require $configPath; // charge et ouvre la connexion MySQL

// ✅ Vérification connexion MySQL
if (!isset($mysqli) || !$mysqli instanceof mysqli) {
    echo json_encode(['success' => false, 'message' => 'MySQL object not initialized.']);
    exit;
}
if ($mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'MySQL connection error: ' . $mysqli->connect_error]);
    exit;
}
if (!$mysqli->ping()) {
    echo json_encode(['success' => false, 'message' => 'MySQL connection lost.']);
    exit;
}


// --- RÉCUPÉRATION COMMANDES EN COURS ---
$sql = "SELECT id, tracking_code, status 
        FROM orders 
        WHERE status = 'shipping' 
        AND tracking_code IS NOT NULL 
        AND tracking_code != ''";
        
        

$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . $mysqli->error]);
    $mysqli->close();
    exit;
}

$updated = 0;
$totalChecked = 0;

while ($row = $result->fetch_assoc()) {
    $totalChecked++;
    $tracking = $row['tracking_code'];
    $orderId = (int) $row['id'];

    // --- APPEL API ANDERSON ---
    $url = "https://anderson-ecommerce.ecotrack.dz/api/v1/get/tracking/info?tracking=" . urlencode($tracking);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $api_key",
            "Accept: application/json"
        ],
        CURLOPT_TIMEOUT => 20
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    
    
    if ($httpCode !== 200 || !$response) {
        continue; // ignorer cette commande si la requête échoue
    }

    $data = json_decode($response, true);
    if (!is_array($data) || empty($data['activity'])) {
        continue;
    }

    $lastStatus = strtolower(end($data['activity'])['status'] ?? '');

    // --- MAPPING DES STATUTS ---
    switch ($lastStatus) {
        case 'livred':
        case 'payed':
        case 'encaissed':
        case 'livre_non_encaisse':
        case 'encaisse_non_paye': 
        case 'paiements_prets': 
        case 'paye_et_archive': 
            $newStatus = 'completed';
            break;
        case 'retour_chez_livreur':
        case 'retour_transit_entrepot':
        case 'retour_en_traitement':
        case 'retour_recu':
        case 'retour_archive':
        case 'returned' :
        case 'annule':
            $newStatus = 'returned';
            break;
        default:
            $newStatus = 'shipping';
            break;
    }

    if ($newStatus !== $row['status']) {
        // --- RÉCUPÉRATION DES INFOS CLIENT ---
        $stmt2 = $mysqli->prepare("SELECT name, phone, delivery_zone, total_price FROM orders WHERE id = ?");
        if (!$stmt2) continue;

        $stmt2->bind_param("i", $orderId);
        $stmt2->execute();
        $stmt2->bind_result($customerName, $phone, $deliveryZone, $totalPrice);
        $stmt2->fetch();
        $stmt2->close();

        // --- MISE À JOUR STATUT COMMANDE ---
        $stmt = $mysqli->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("si", $newStatus, $orderId);
            $stmt->execute();
            $stmt->close();
        }

        // --- ENVOI EMAIL DE NOTIFICATION ---
        $sendData = http_build_query([
            'orderID'      => $orderId,
            'name'         => $customerName,
            'phone'        => $phone,
            'trackingCode' => $tracking,
            'status'       => $newStatus,
            'deliveryZone' => $deliveryZone,
            'totalPrice'   => $totalPrice
        ]);

        $curl = curl_init("https://management.hoggari.com/backend/email/sendTrack.php");
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $sendData,
            CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
            CURLOPT_TIMEOUT => 15
        ]);
        curl_exec($curl);
        curl_close($curl);

        $updated++;
    }
}



$result->free();
$mysqli->close();

?>
