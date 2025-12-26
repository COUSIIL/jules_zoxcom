<?php
header("Content-Type: application/json; charset=UTF-8");

// Inclure le fichier de configuration
$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}
require_once $configPath;

if (!$mysqli) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . mysqli_connect_error()]);
    exit;
}

// Vérifier si la table existe
$table_check_query = "SHOW TABLES LIKE 'reminder'";
$table_check_result = $mysqli->query($table_check_query);
if ($table_check_result === false) {
    echo json_encode(['success' => false, 'message' => 'Error checking table: ' . $mysqli->error]);
    exit;
}
if ($table_check_result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Table reminder does not exist.']);
    exit;
}

// Récupérer l'ID depuis GET ou JSON
$id = $_GET['id'] ?? null;

// Si JSON envoyé en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['id'])) {
        $id = $data['id'];
    }
}

// Préparer la requête selon la présence de l'ID
if ($id) {
    $stmt = $mysqli->prepare("SELECT * FROM `reminder` WHERE id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
        exit;
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No reminder found with this ID.']);
    }

    $stmt->close();
} else {
    // Sinon récupérer tous les rappels
    $query = "SELECT * FROM `reminder` ORDER BY reminder_date ASC";
    $result = $mysqli->query($query);

    if ($result === false) {
        echo json_encode(['success' => false, 'message' => 'Error fetching reminders: ' . $mysqli->error]);
        exit;
    }

    $reminders = [];
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }

    echo json_encode(['success' => true, 'data' => $reminders]);
}

$mysqli->close();
?>
