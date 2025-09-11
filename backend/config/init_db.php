<?php
require_once __DIR__ . '/dbConfig.php';

// --- Création des tables si elles n'existent pas ---
$createTables = [

    // Table des conversations
    "CREATE TABLE IF NOT EXISTS conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL DEFAULT 'New Conversation',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Table des messages
    "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        role ENUM('user','assistant') NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE
    )"

];

foreach ($createTables as $query) {
    if (!$mysqli->query($query)) {
        echo json_encode([
            "success" => false,
            "message" => "Erreur SQL lors de la création des tables : " . $mysqli->error
        ]);
        exit;
    }
}
