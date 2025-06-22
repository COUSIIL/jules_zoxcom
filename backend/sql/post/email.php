<?php

header("Content-Type: application/json; charset=UTF-8");

$configPath = __DIR__ . '/../../config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found.',
    ]);
    exit;
}
require_once $configPath;

if ($_GET['action'] === 'createEmail') {

    // Crée la table si elle n'existe pas
    $table_check_query = "SHOW TABLES LIKE 'emails'";
    $table_check_result = $mysqli->query($table_check_query);

    if ($table_check_result->num_rows == 0) {
        $create_table_query = "CREATE TABLE emails (
            id INT AUTO_INCREMENT PRIMARY KEY,
            data_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            email VARCHAR(255) NOT NULL,
            site VARCHAR(255) NOT NULL,
            type VARCHAR(100) NOT NULL,
            isActive BOOLEAN DEFAULT TRUE,
            UNIQUE KEY unique_email_site_type (email, site, type)
        )";
        if (!$mysqli->query($create_table_query)) {
            echo json_encode([
                'success' => false,
                'message' => 'Error creating table: ' . $mysqli->error,
            ]);
            exit;
        }
    }

    // Lire la requête JSON envoyée par le client
    $data = json_decode(file_get_contents('php://input'), true);

    if (
        isset($data['email']) &&
        isset($data['site']) &&
        isset($data['type']) &&
        isset($data['isActive'])
    ) {
        $email = $data['email'];
        $site = $data['site'];
        $type = $data['type'];
        $isActive = $data['isActive'] ? 1 : 0;
        $data_time = date('Y-m-d H:i:s');

        // Vérifier si l'entrée existe pour ce couple email+site+type
        $check_query = $mysqli->prepare("SELECT id FROM emails WHERE email = ? AND site = ? AND type = ?");
        $check_query->bind_param("sss", $email, $site, $type);
        $check_query->execute();
        $check_result = $check_query->get_result();

        if ($check_result->num_rows > 0) {
            // Mise à jour
            $update_query = $mysqli->prepare("UPDATE emails SET data_time = ?, isActive = ? WHERE email = ? AND site = ? AND type = ?");
            $update_query->bind_param("sisss", $data_time, $isActive, $email, $site, $type);
            if ($update_query->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Email updated successfully.',
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error updating email: ' . $mysqli->error,
                ]);
            }
        } else {
            // Insertion
            $insert_query = $mysqli->prepare("INSERT INTO emails (data_time, email, site, type, isActive) VALUES (?, ?, ?, ?, ?)");
            $insert_query->bind_param("ssssi", $data_time, $email, $site, $type, $isActive);
            if ($insert_query->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Email inserted successfully.',
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error inserting email: ' . $mysqli->error,
                ]);
            }
        }

    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields.',
        ]);
    }

    $mysqli->close();
    exit;
}
?>
