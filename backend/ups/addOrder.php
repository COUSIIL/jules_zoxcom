

<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure le fichier de configuration de la base de données
$ups = $_SERVER['DOCUMENT_ROOT'] . '/backend/config/upsConfig.php';

if (file_exists($ups)) {
    $dataUps = include $ups;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'File not found.',
    ]);
    die;
}

// Vérifie si des données ont été retournées
if (empty($dataUps)) {
    echo json_encode([
        'success' => false,
        'message' => 'No ups api key found.',
    ]);
    die;
}

$api_key = $dataUps[0]['key'];
$url = "https://app.conexlog-dz.com/api/v1/create/order";

$value = json_decode(file_get_contents('php://input'), true);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$value) {
        echo json_encode(["success" => false, "message" => "Invalid JSON"]);
        die;
    }

    $data = [
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
    ];


    $headers = [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    curl_close($ch);

    echo $response;
}
?>

