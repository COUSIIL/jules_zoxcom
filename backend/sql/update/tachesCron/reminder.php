<?php
/**
 * reminderCron.php
 * ----------------
 * Vérifie chaque jour les rappels du jour et envoie un email via sendReminder.php
 */

header("Content-Type: application/json; charset=UTF-8");

// --- CONFIGURATION BASE DE DONNÉES ---
$configPath = __DIR__ . '/../../../config/dbConfig.php';

if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'message' => 'Configuration file not found.']);
    exit;
}

require_once $configPath;

// ✅ Vérification connexion MySQL
if (!$mysqli || $mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $mysqli->connect_error]);
    exit;
}

// --- DATE DU JOUR ---
$today = date('Y-m-d');

// --- RÉCUPÉRER LES RAPPELS DU JOUR ---
$sql = "SELECT id, work, note, reminder_date FROM reminder WHERE DATE(reminder_date) = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit;
}

$total = 0;
$sent = 0;
$errors = [];

while ($row = $result->fetch_assoc()) {
    $total++;
    $reminderID   = $row['id'];
    $work         = $row['work'];
    $note         = $row['note'];
    $reminderDate = $row['reminder_date'];

    // --- ENVOI EMAIL VIA sendReminder.php ---
    $sendData = http_build_query([
        'reminderID'   => $reminderID,
        'work'         => $work,
        'note'         => $note,
        'reminder_date'=> $reminderDate,
        'toEmail'      => 'hoggari.mail@gmail.com'
    ]);

    $ch = curl_init("https://management.hoggari.com/backend/email/sendReminder.php");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $sendData,
        CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
        CURLOPT_TIMEOUT => 15
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && strpos($response, '"success":true') !== false) {
        $sent++;
    } else {
        $errors[] = "Échec pour le rappel ID $reminderID";
    }
}

$stmt->close();
$mysqli->close();

?>
