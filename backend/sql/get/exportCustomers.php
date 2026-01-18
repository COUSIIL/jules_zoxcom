<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customers.csv');

$configPath = __DIR__ . '/../../../backend/config/dbConfig.php';
require_once $configPath;

$output = fopen('php://output', 'w');

// Headers
fputcsv($output, ['ID', 'Name', 'Phone', 'Email', 'Wilaya', 'Commune', 'Address', 'Power']);

// Query
$sql = "SELECT id, name, phone, email, wilaya, commune, address, power FROM customers ORDER BY id DESC";
$result = $mysqli->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['phone'],
            $row['email'],
            $row['wilaya'],
            $row['commune'],
            $row['address'],
            $row['power']
        ]);
    }
}

fclose($output);
$mysqli->close();
?>
