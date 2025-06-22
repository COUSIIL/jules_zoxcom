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

$createTables = ["CREATE TABLE IF NOT EXISTS discount_use (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone INT NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    qty INT NOT NULL,
)
"];

// Lire la requête JSON envoyée par le client
$data = json_decode(file_get_contents('php://input'), true);

// Vérifier si les données nécessaires sont présentes
if (isset($data['name']) && isset($data['image'])) {
    $code = $data['code'];
    $time = $data['time'];
    $phone = $data['phone'];

    // Vérifier si l'entrée existe déjà dans la base de données
    $check_query = $mysqli->prepare("SELECT * FROM discount WHERE code = ?");
    $check_query->bind_param("s", $code);
    $check_query->execute();
    $check_result = $check_query->get_result();

    if ($check_result->num_rows > 0) {
        $row = $check_result->fetch_assoc(); // Récupérer la ligne sous forme de tableau associatif
        if (!$row) {
            echo json_encode([
                'success' => false,
                'message' => 'No data yet',
            ]);
            $mysqli->close();
            exit();
        }       
        
 
        $data_time = $row['valid_until'];
        if(!empty($row['work'])) {
            if($row['usage'] === 1 && $row['limitation'] === 1) {
                if($data_time) {
                    $valid_time = $data_time - $time;
                    if($valid_time < 0) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'time ended',
                        ]);
                        $mysqli->close();
                        exit();
                    } else {
                        // L'entrée existe déjà, effectuer une mise à jour
                        $work = 0;
                        $update_query = $mysqli->prepare("UPDATE discount SET work = ? WHERE code = ?");
                        $update_query->bind_param("is", $work, $code);

                        if ($update_query->execute()) {
                            $row2 = ['value' => $row['value'], 'type' => $row['type']];
                            echo json_encode([
                                'success' => true,
                                'message' => 'Discount updated successfully.',
                                'data' => $row2
                            ]);
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => "Error updating discount: " . $mysqli->error,
                            ]);
                        }
                        $mysqli->close();
                        exit();
                    }
                } else {
                    echo json_encode([
                            'success' => false,
                            'message' => 'no time found',
                        ]);
                        $mysqli->close();
                        exit();
                }
            } else if ($row['usage'] === 1 && $row['limitation'] === 0) {
                

                $work = 0;
                $update_query = $mysqli->prepare("UPDATE discount SET work = ? WHERE code = ?");
                $update_query->bind_param("is", $work, $code);

                if ($update_query->execute()) {
                    $row2 = ['value' => $row['value'], 'type' => $row['type']];
                        echo json_encode([
                            'success' => true,
                            'message' => 'Discount updated successfully.',
                            'data' => $row2
                        ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => "Error updating discount: " . $mysqli->error,
                    ]);
                }
                $mysqli->close();
                exit();
            } else if ($row['usage'] === 0 && $row['limitation'] === 0) {
                
                if($data['usages'] < $data['qty']) {
                    $usages = $data['qty'] + 1;
                    $update_query = $mysqli->prepare("UPDATE discount SET qty = ? WHERE code = ?");
                    $update_query->bind_param("is", $usages, $code);

                    if ($update_query->execute()) {
                        $row2 = ['value' => $row['value'], 'type' => $row['type']];
                        echo json_encode([
                            'success' => true,
                            'message' => 'Discount updated successfully.',
                            'data' => $row2
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => "Error updating discount: " . $mysqli->error,
                        ]);
                    }
                    $mysqli->close();
                    exit();
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'limited usage',
                    ]);
                    $mysqli->close();
                    exit();
                }
            } else if ($row['usage'] === 0 && $row['limitation'] === 1) {
                $check_query1 = $mysqli->prepare("SELECT * FROM discount_use");
                $check_query1->execute();
                $check_result1 = $check_query1->get_result();

                while ($row3 = $check_result1->fetch_assoc()) {
                    if ($row3['phone'] === $phone){
                        if($row3['code'] === $row['code']) {
                            echo json_encode([
                                'success' => false,
                                'message' => "Code used with this phone",
                            ]);
                            $mysqli->close();
                            exit();
                        }
                    }
                    
                }
                if($data_time) {
                    $valid_time = $data_time - $time;
                    if($valid_time < 0) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'time ended',
                        ]);
                        $mysqli->close();
                        exit();
                    } else {
                        $row2 = ['value' => $row['value'], 'type' => $row['type']];

                        echo json_encode([
                            'success' => true,
                            'message' => 'valid',
                            'data' => $row2
                        ]);
                        $mysqli->close();
                        exit();
                    }
                } else {
                    echo json_encode([
                            'success' => false,
                            'message' => 'no time found',
                        ]);
                        $mysqli->close();
                        exit();
                }
            }

        } else {
            echo json_encode([
                'success' => false,
                'message' => 'not working, pleas change code.',
            ]);
            $mysqli->close();
            exit();
        }
        
    } else {
        // L'entrée n'existe pas, effectuer une insertion
        $insert_query = $mysqli->prepare("INSERT INTO category (data_time, name, image) VALUES (?, ?, ?)");
        $insert_query->bind_param("sss", $data_time, $name, $image);

        if ($insert_query->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'New category added successfully.',
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "Error inserting new category: " . $mysqli->error,
            ]);
        }
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => "Error: All values are required.",
    ]);
}



// Ferme la connexion
$mysqli->close();
?>
