<?php
header("Content-Type: application/json; charset=UTF-8");

// 📌 Inclure la config DB
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}

require_once $configPath; // fichier qui contient la connexion $mysqli

$response = [
    "success" => false,
    "message" => "",
    "data" => []
];

function ensure_schema($mysqli) {
    $sql1 = "
    CREATE TABLE IF NOT EXISTS banks (
        id              INT AUTO_INCREMENT PRIMARY KEY,
        name            VARCHAR(255) NOT NULL,
        type            ENUM('banks','cash') NOT NULL,
        currency        CHAR(3) NOT NULL DEFAULT 'DZD',
        opening_balance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        current_balance DECIMAL(18,2) NOT NULL DEFAULT 0.00,
        status          ENUM('active','archived') NOT NULL DEFAULT 'active',
        created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uk_banks_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    $sql2 = "
    CREATE TABLE IF NOT EXISTS account_transactions (
        id              BIGINT AUTO_INCREMENT PRIMARY KEY,
        account_id      INT NOT NULL,
        kind            ENUM('in','out','transfer_in','transfer_out','opening','adjustment') NOT NULL,
        amount          DECIMAL(18,2) NOT NULL,
        description     VARCHAR(255) DEFAULT NULL,
        reference       VARCHAR(100) DEFAULT NULL,
        tx_date         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        created_by      VARCHAR(100) DEFAULT NULL,
        counterparty_account_id INT DEFAULT NULL,
        CONSTRAINT fk_tx_account FOREIGN KEY (account_id) REFERENCES banks(id) ON DELETE CASCADE,
        INDEX idx_tx_account_date (account_id, tx_date),
        INDEX idx_tx_date (tx_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";

    if (!$mysqli->query($sql1)) {
        response(false, 'Erreur création table banks: ' . $mysqli->error, null, 500);
    }
    if (!$mysqli->query($sql2)) {
        response(false, 'Erreur création table account_transactions: ' . $mysqli->error, null, 500);
    }
}

try {
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }

    $sql = "SELECT * FROM banks";
    $result = $mysqli->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $mysqli->error);
    }

    $banks = [];
    while ($row = $result->fetch_assoc()) {
        $banks[] = $row;
    }

    $response["success"] = true;
    $response["data"] = $banks;
    $response["message"] = count($banks) . " banks found.";

    echo json_encode($response);
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
    echo json_encode($response);
}

$mysqli->close();
