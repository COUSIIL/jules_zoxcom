<?php
header('Content-Type: application/json');

// Inclure le fichier de configuration de la base de données
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}

require_once $configPath;

// --- Update Database Structure ---
$alters = [
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS email VARCHAR(255) UNIQUE AFTER phone",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS password VARCHAR(255) AFTER email",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS token VARCHAR(255) AFTER password",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS wilaya VARCHAR(100) AFTER token",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS commune VARCHAR(100) AFTER wilaya",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS address TEXT AFTER commune",
    "ALTER TABLE customers ADD COLUMN IF NOT EXISTS power INT DEFAULT 0 AFTER address"
];

foreach ($alters as $sql) {
    // Suppress errors for duplicate column if version doesn't support IF NOT EXISTS correctly or other minor issues
    try {
        $mysqli->query($sql);
    } catch (Exception $e) {
        // Continue if column exists
    }
}

// --- Search Logic ---
$search = isset($_GET['search']) ? $mysqli->real_escape_string($_GET['search']) : '';
$whereClause = "";

if (!empty($search)) {
    $whereClause = "WHERE name LIKE '%$search%' OR phone LIKE '%$search%' OR email LIKE '%$search%' OR id = '$search'";
}

// Requêtes pour obtenir les données des tables
$tables = [
    'customers' => "SELECT * FROM customers $whereClause ORDER BY power DESC, id DESC LIMIT 200", // Default sort by power, limit for performance
    'details' => "SELECT * FROM customers_details",
];

$data = [];
foreach ($tables as $key => $query) {
    $result = $mysqli->query($query);
    if (!$result) {
        echo json_encode([
            'success' => false,
            'message' => "Failed to fetch data from $key",
            'error' => $mysqli->error
        ]);
        $mysqli->close();
        exit;
    }
    $data[$key] = $result->fetch_all(MYSQLI_ASSOC);
}

// Organisation des détails par ID de client
$groupedModels = [];
foreach ($data['details'] as $model) {
    if (!isset($model['customers_id'])) continue;
    $groupedModels[$model['customers_id']][] = [
        'id' => $model['id'],
        'country' => $model['country'],
        'method' => $model['method'],
        'delivery_zone' => $model['delivery_zone'],
        'sZone' => $model['sZone'],
        'mZone' => $model['mZone'],
        'created_at' => $model['created_at'],
    ];
}

// Construction de la réponse : tous les clients avec leurs détails
$customers = [];
foreach ($data['customers'] as $customerData) {
    $customerId = $customerData['id'];
    $customers[] = [
        'id' => $customerId,
        'name' => $customerData['name'],
        'phone' => $customerData['phone'],
        'email' => $customerData['email'] ?? '',
        'wilaya' => $customerData['wilaya'] ?? '',
        'commune' => $customerData['commune'] ?? '',
        'address' => $customerData['address'] ?? '',
        'power' => $customerData['power'],
        'items' => $groupedModels[$customerId] ?? [],
    ];
}

// Envoi de la réponse JSON
echo json_encode([
    'success' => true,
    'message' => 'Customer list with details',
    'data' => $customers,
]);

// Fermeture de la connexion
$mysqli->close();
?>
