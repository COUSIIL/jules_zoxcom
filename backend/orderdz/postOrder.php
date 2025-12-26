<?php
header("Content-Type: application/json; charset=UTF-8");


// Inclure le fichier de configuration de la base de données
$orderDz = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/orderDzConfig.php';

if (file_exists($orderDz)) {
    $dataOrderDz = include $orderDz;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'File not found.',
    ]);
    die;
}

// Vérifie si des données ont été retournées
if (empty($dataOrderDz)) {
    echo json_encode([
        'success' => false,
        'message' => 'No orderDz api key found.',
    ]);
    die;
}

$api_key = $dataOrderDz[0]['key'];
$url = "https://orderdz.com/api/v1/feeef/order"; // PUT endpoint

// ===================================
// LECTURE DES DONNÉES ENTRANTES
// ===================================

$input = json_decode(file_get_contents('php://input'), true);

    /*$data = [
        'reference' => $value['reference'] ?? '',
        'nom_client' => $value['nom_client'] ?? '', // ✅ Décodage UTF-8
        'telephone' => $value['telephone'] ?? '',
        'telephone_2' => $value['telephone_2'] ?? '',
        'adresse' => $value['adresse'] ?? 'adresse',
        'code_postal' => $value['code_postal'] ?? '',
        'commune' => $value['commune'] ?? '',
        'code_wilaya' => $value['code_wilaya'] ?? '',
        'montant' => $value['montant'] ?? '',
        'remarque' => $value['remarque'] ?? '',
        'produit' => $value['produit'] ?? '', // ✅ Correction ici
        'stock' => $value['stock'] ?? '',
        'quantite' => $value['quantite'] ?? '',
        'produit_a_recupere' => $value['produit_a_recupere'] ?? '',
        'boutique' => $value['boutique'] ?? '',
        'type' => $value['type'] ?? '',
        'stop_desk' => $value['stop_desk'] ?? '',
        'weight' => $value['weight'] ?? ''
    ];*/


    // ❌ JSON invalide ?
    //if (!$input) {
    //    echo json_encode(["success" => false, "message" => "Invalid JSON"]);
    //    die;
    //}

    // ================================
    // ⚠️ POUR L'INSTANT : on force la commande de test demandée
    // ================================

    $data = [
        "order_id"       => $input['reference'],
        "first_name"     => $input['nom_client'],
        "last_name"      => $input['nom_client'],
        "phone"          => $input['telephone'],
        "phone2"         => $input['telephone2'],
        "province_id"    => $input['code_wilaya'],
        "province"       => $input['wilaya'],
        "city"           => $input['commune'],
        "notes"          => $input['remarque'],
        "email"          => $input['email'],
        "address"        => $input['address'],
        "address2"       => $input['address2'],
        "zipcode"        => $input['code_postal'],
        "ip_address"     => $input['ip_address'],
        "user_agent"     => $input['user_agent'],
        "stop_desk"      => intval($input['stop_desk']),
        "shipping_price" => floatval($input['shipping_price']),
        "total"          => floatval($input['montant']),
        "items"          => $input['items']
    ];


    // JSON propre
    $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);


    // ===================================
    // ENVOI CURL
    // ===================================

    $headers = [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json",
        "Content-Length: " . strlen($jsonData)
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ===================================
    // RÉPONSE
    // ===================================

    if ($error) {
        echo json_encode([
            "success" => false,
            "message" => "Erreur cURL",
            "error" => $error
        ]);
        die;
    }

    echo json_encode([
        "success" => true,
        "message" => json_decode($response, true),
        "data" => $jsonData
    ]);

?>
