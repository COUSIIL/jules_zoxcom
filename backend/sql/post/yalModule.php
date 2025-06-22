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

// Vérifier si la table yal_module existe, sinon la créer
$table_check_query = "SHOW TABLES LIKE 'yal_module'";
$table_check_result = $mysqli->query($table_check_query);

if ($table_check_result === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Error checking table: ' . $mysqli->error
    ]);
    exit;
}

if ($table_check_result->num_rows == 0) {
    $create_table_query = "CREATE TABLE `yal_module` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        work INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        `api_id` TEXT,
        `api_token` TEXT
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

if (isset($data['name'], $data['api_id'], $data['api_token'])) {
    $name = $mysqli->real_escape_string($data['name']);
    $api_id = $mysqli->real_escape_string($data['api_id']);
    $api_token = $mysqli->real_escape_string($data['api_token']);
    $work = (int) $data['work'];
    $data_time = date('Y-m-d H:i:s');

    // Vérifier si l'entrée existe déjà
    $check_query = $mysqli->prepare("SELECT id FROM `yal_module` WHERE name = ?");
    if ($check_query) {
        $check_query->bind_param("s", $name);
        $check_query->execute();
        $check_result = $check_query->get_result();

        if ($check_result->num_rows > 0) {
            // Mise à jour si l'entrée existe
            $update_query = $mysqli->prepare("UPDATE `yal_module` SET data_time = ?, work = ?, `api_id` = ?, `api_token` = ? WHERE name = ?");
            if ($update_query) {
                $update_query->bind_param("sisss", $data_time, $work, $api_id, $api_token, $name);
                if ($update_query->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Yalidine settings updated successfully.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error updating Yalidine settings: ' . $update_query->error
                    ]);
                }
                $update_query->close();
            }
        } else {
            // Insertion si l'entrée n'existe pas
            $insert_query = $mysqli->prepare("INSERT INTO `yal_module` (data_time, work, name, `api_id`, `api_token`) VALUES (?, ?, ?, ?, ?)");
            if ($insert_query) {
                $insert_query->bind_param("sisss", $data_time, $work, $name, $api_id, $api_token);
                if ($insert_query->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Yalidine settings saved successfully.'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error inserting Yalidine settings: ' . $insert_query->error
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
        'message' => 'Invalid input. All fields (name, api_id, api_token, work) are required.'
    ]);
}

// Fermer la connexion
$mysqli->close();
