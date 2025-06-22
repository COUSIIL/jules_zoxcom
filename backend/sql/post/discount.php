<?php

header("Content-Type: application/json; charset=UTF-8");

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

// Vérifier la connexion MySQL
if (!isset($mysqli) || $mysqli->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $mysqli->connect_error,
        'data' => 'ERROR CODE 2',
    ]);
    die;
}

// Création de la table si elle n'existe pas
$createTableQuery = ["CREATE TABLE IF NOT EXISTS discount (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_time INT NOT NULL DEFAULT (UNIX_TIMESTAMP()),
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    `usage` TINYINT(1) NOT NULL,
    limitation TINYINT(1) NOT NULL,
    `type` TINYINT(1) NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    qty INT NOT NULL,
    usages INT NOT NULL DEFAULT 0,
    valid_until INT DEFAULT NULL, 
    work TINYINT(1) NOT NULL
    )", 
    "CREATE TABLE IF NOT EXISTS discount_use (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    qty INT NOT NULL
    )"];

// Exécution des requêtes de création des tables
foreach ($createTableQuery as $query) {
    if (!$mysqli->query($query)) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create database table: ' . $mysqli->error,
            'data' => 'ERROR CODE 3',
        ]);
        die;

    }
}


// Lire les données JSON
$data = json_decode(file_get_contents('php://input'), true);


if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON received.',
        'data' => 'ERROR CODE 4',
    ]);
    die;
}



// Vérification des données requises
if (!isset($data['name'], $data['usage'], $data['limitation'], $data['type'], $data['value'], $data['qty'], $data['codes'])) {
    echo json_encode([
        'success' => false,
        'message' => "Error: Missing required values.",
        'data' => 'ERROR CODE 5',
    ]);
    die;
}

// Assignation des valeurs
$name = $data['name'];

$usage = (int) $data['usage'];

$limitation = (int) $data['limitation'];

$type = (int) $data['type'];

$value = (float) $data['value'];
$qty = (int) $data['qty'];

$valid_until = isset($data['valid_until']) ? $data['valid_until'] : null;

$valid_until2 = strtotime($valid_until); 

$codes = is_array($data['codes']) ? $data['codes'] : [];

$errors = [];
$created_codes = [];
$usages = 0;



// Vérification de l'existence de codes valides
if (empty($codes)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing codes array.',
        'data' => 'ERROR CODE 6',
    ]);
    die;
}

    $check_query = $mysqli->prepare("SELECT name FROM discount WHERE name = ?");
    $insert_query = $mysqli->prepare(
        "INSERT INTO discount (data_time, code, name, `usage`, limitation, `type`, value, qty, valid_until, work, usages) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $data_time = time();
    $check_query->bind_param("s", $name);
    $check_query->execute();
    $check_query->store_result();
if ($check_query->num_rows > 0) {
        $errors[] = "Codes already exists for name {$name}.";
    
}else {
    // Générer des codes uniques pour chaque quantité
foreach ($codes as $code) {
    $work = 1; // TRUE sous forme de TINYINT(1)
    $check_code = $mysqli->prepare("SELECT code FROM discount WHERE code = ?");
    $check_code->bind_param("s", $code);
    $check_code->execute();
    $check_code->store_result();
    if ($check_code->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Promo code already exist',
            'data' => $code,
        ]);
        $mysqli->close();
        die;
        
    }



            if ($insert_query) {
        $insert_query->bind_param(
            "issiiidiiii",
            $data_time,
            $code,
            $name,
            $usage,
            $limitation,
            $type,
            $value,
            $qty,
            $valid_until2,
            $work,
            $usages
        );

        if ($insert_query->execute()) {
            $created_codes[] = $code;
        } else {
            $errors[] = "Error inserting code {$code}: " . $insert_query->error;
        }

        
    } else {
        $errors[] = "Error preparing statement: " . $mysqli->error;
    }
    
}
}


$insert_query->close();


// Résultat final
if (empty($errors)) {
    echo json_encode([
        'success' => true,
        'message' => 'Promotion codes generated successfully.',
        'data' => $created_codes,
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Some errors occurred while generating codes.',
        'data' => $errors,
    ]);
}

// Fermer la connexion
$mysqli->close();
?>
