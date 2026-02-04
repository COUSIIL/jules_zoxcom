<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$host = $_ENV['DB_HOST'] ?? 'localhost';
$port = $_ENV['DB_PORT'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$database = $_ENV['DB_NAME'];


// Connexion à la base de données MySQL
$mysqli = mysqli_connect($host, $user, $password, $database, $port);

// Vérification de la connexion
if (!$mysqli) {
    echo json_encode([
        'success' => false,
        'message' => "Connection failed: " . mysqli_connect_error(),
    ]);
    die;
}

$mysqli->set_charset('utf8mb4');

?>