<?php

header("Content-Type: application/json; charset=UTF-8");

// Inclure la configuration de la base de données
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
$table_check_query = "SHOW TABLES LIKE 'reminder'";
$table_check_result = $mysqli->query($table_check_query);

if ($table_check_result === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Error checking table: ' . $mysqli->error
    ]);
    exit;
}

if ($table_check_result->num_rows == 0) {
    $create_table_query = "CREATE TABLE `reminder` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reminder_date TIMESTAMP NOT NULL,
        work INT NOT NULL,
        note TEXT NOT NULL,
        data_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

// ✅ Sécuriser et convertir les données
$note_raw = $data['note'] ?? '';
if (is_array($note_raw) || is_object($note_raw)) {
    // Si c’est un tableau ou objet, on le convertit proprement en JSON
    $note = json_encode($note_raw, JSON_UNESCAPED_UNICODE);
} else {
    // Sinon, on s’assure que c’est une chaîne
    $note = (string)$note_raw;
}

$reminder_date = $data['reminder_date'] ?? '';
$work = isset($data['work']) ? (int)$data['work'] : 0;
$data_time = date('Y-m-d H:i:s');

// Insertion
$insert_query = $mysqli->prepare("INSERT INTO `reminder` (reminder_date, work, note, data_time) VALUES (?, ?, ?, ?)");
if ($insert_query) {
    $insert_query->bind_param("siss", $reminder_date, $work, $note, $data_time);
    if ($insert_query->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Reminder created successfully.',
            'data' => [
                'id' => $mysqli->insert_id,
                'reminder_date' => $reminder_date,
                'work' => $work,
                'note' => $note,
                'data_time' => $data_time
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating reminder: ' . $insert_query->error
        ]);
    }
    $insert_query->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to prepare statement: ' . $mysqli->error
    ]);
}

$mysqli->close();
?>
