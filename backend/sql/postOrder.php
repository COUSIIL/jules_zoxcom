<?php
// Configuration de la connexion à la base de données
include 'backend/config/dbConfig.php';

// Vérifie si la table "orders" existe, sinon crée-la
$table_check_query = "SHOW TABLES LIKE 'orders'";
$table_check_result = $conn->query($table_check_query);

if ($table_check_result->num_rows == 0) {
    // Crée la table "orders" si elle n'existe pas
    $create_table_query = "CREATE TABLE orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        wilaya VARCHAR(255) NOT NULL,
        dp VARCHAR(255) NOT NULL,
        p_name VARCHAR(255) NOT NULL,
        m_id INT NOT NULL,
        commune VARCHAR(255) NOT NULL,
        p_price DECIMAL(10, 2) NOT NULL,
        qty INT NOT NULL,
        dis DECIMAL(10, 2) NOT NULL,
        tax DECIMAL(10, 2) NOT NULL,
        total DECIMAL(10, 2) NOT NULL,
        orders_id VARCHAR(255) NOT NULL,
        plat VARCHAR(255) NOT NULL,
        statut VARCHAR(255) NOT NULL,
        note TEXT,
        d_society VARCHAR(255) NOT NULL,
        d_methode VARCHAR(255) NOT NULL,
        home_office VARCHAR(255) NOT NULL,
        total_products INT NOT NULL,
        p_id INT NOT NULL
    )";

    if ($mysqli->query($create_table_query) === TRUE) {
        echo "Table 'orders' created successfully.<br>";
    } else {
        echo "Error creating table: " . $mysqli->error;
    }
}

// Récupère les données de la commande à partir de la requête POST
$data_time = date('Y-m-d H:i:s'); // Utilisation de l'heure actuelle
$name = $_POST['name'];
$phone = $_POST['phone'];
$wilaya = $_POST['wilaya'];
$dp = $_POST['dp'];
$p_name = $_POST['p_name'];
$m_id = $_POST['m_id'];
$commune = $_POST['commune'];
$p_price = $_POST['p_price'];
$qty = $_POST['qty'];
$dis = $_POST['dis'];
$tax = $_POST['tax'];
$total = $_POST['total'];
$orders_id = $_POST['orders_id'];
$plat = $_POST['plat'];
$statut = $_POST['statut'];
$note = $_POST['note'];
$d_society = $_POST['d_society'];
$d_methode = $_POST['d_methode'];
$home_office = $_POST['home_office'];
$total_products = $_POST['total_products'];
$p_id = $_POST['p_id'];

// Insère la commande dans la table "orders"
$insert_query = "INSERT INTO orders (
        data_time, name, phone, wilaya, dp, p_name, m_id, commune, p_price, qty, 
        dis, tax, total, orders_id, plat, statut, note, d_society, d_methode, home_office, 
        total_products, p_id
    ) 
    VALUES (
        '$data_time', '$name', '$phone', '$wilaya', '$dp', '$p_name', $m_id, '$commune', 
        $p_price, $qty, $dis, $tax, $total, '$orders_id', '$plat', '$statut', '$note', 
        '$d_society', '$d_methode', '$home_office', $total_products, $p_id
    )";

if ($mysqli->query($insert_query) === TRUE) {
    echo "New order added successfully.";
} else {
    echo "Error: " . $insert_query . "<br>" . $mysqli->error;
}

// Ferme la connexion
$mysqli->close();
?>
