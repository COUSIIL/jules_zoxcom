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

if (!$mysqli) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

// Vérifier si la table existe, sinon la créer
$table_check_query = "SHOW TABLES LIKE 'ups_module'";
$table_check_result = $mysqli->query($table_check_query);

if ($table_check_result === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Error checking table: ' . $mysqli->error
    ]);
    exit;
}

if ($table_check_result->num_rows == 0) {
    $create_table_query = "CREATE TABLE `ups_module` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        work INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        `key` TEXT
    )";

    if ($mysqli->query($create_table_query) === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating table: ' . $mysqli->error
        ]);
        exit;
    }
}

// Lire la requête JSON envoyée par le client
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['name'], $data['key'], $data['work'])) {
    $name = $mysqli->real_escape_string($data['name']);
    $key = $mysqli->real_escape_string($data['key']);
    $work = (int) $data['work'];
    $data_time = date('Y-m-d H:i:s');

    // Vérifier si l'entrée existe déjà dans la base de données
    $check_query = $mysqli->prepare("SELECT id FROM `ups_module` WHERE name = ?");
    if ($check_query) {
        $check_query->bind_param("s", $name);
        $check_query->execute();
        $check_result = $check_query->get_result();

        if ($check_result->num_rows > 0) {
            // L'entrée existe déjà, effectuer une mise à jour
            $update_query = $mysqli->prepare("UPDATE `ups_module` SET data_time = ?, work = ?, `key` = ? WHERE name = ?");
            if ($update_query) {
                $update_query->bind_param("siss", $data_time, $work, $key, $name);
                if ($update_query->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'UPS updated successfully.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error updating UPS: ' . $update_query->error
                    ]);
                }
                $update_query->close();
            }
        } else {
            // L'entrée n'existe pas, effectuer une insertion
            $insert_query = $mysqli->prepare("INSERT INTO `ups_module` (data_time, work, name, `key`) VALUES (?, ?, ?, ?)");
            if ($insert_query) {
                $insert_query->bind_param("siss", $data_time, $work, $name, $key);
                if ($insert_query->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'UPS added successfully.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error inserting UPS: ' . $insert_query->error
                    ]);
                }
                $insert_query->close();
            }
        }
        $check_query->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error preparing check query: ' . $mysqli->error
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input. All fields are required.'
    ]);
}

// Fermer la connexion
$mysqli->close();
