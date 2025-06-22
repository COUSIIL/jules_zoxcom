<?php
header('Content-Type: application/json');

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


$data = json_decode(file_get_contents('php://input'), true);
$response = [];
$work = 1;
$message = '1';


// Vérifier si les données nécessaires sont présentes
if (!empty($data['code']) && isset($data['time']) && !empty($data['phone'])) {
    $code = $data['code'];
    $time = $data['time'] ?? 0;
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
                'message' => '8', //no code promo found
            ]);
            $mysqli->close();
            die;
        }       
        
        $data_time = $row['valid_until'];


        




        $customCode = $phone . $code;

        $stmtPromo1 = $mysqli->prepare("SELECT phone_code FROM used_discount WHERE phone_code = ?");
        $stmtPromo1->bind_param("s", $customCode);
        $stmtPromo1->execute();
        $resultPromo1 = $stmtPromo1->get_result();
        $codePromo1 = $resultPromo1->fetch_assoc();
        $stmtPromo1->close();

        if(!empty($row['work'])) {

            if($data_time !== 0 && $data_time < $time) {
                
                $message = '2'; //time ended
                $work = 0;
                $response = [
                    'success' => false,
                    'message' => $message,
                ];
                
            } else {
                if ($row['usage'] === 1 && $row['limitation'] === 0) {
                    if ($codePromo1) {
                        $message = '3'; //Code used with this phone number
                        $response = [
                            'success' => false,
                            'message' => $message,
                        ];
                    } else {
                        
                        $row2 = ['value' => $row['value'], 'type' => $row['type']];
                        $response = [
                            'success' => true,
                            'message' => $message,
                            'data' => $row2
                        ];
                    }
                    
                } else if ($row['usage'] === 0 && $row['limitation'] === 0) {
                    if ($codePromo1) {
                        $message = '3'; //Code used with this phone number
                        $response = [
                            'success' => false,
                            'message' => $message,
                        ];
                    } else {
                        if($row['usages'] < $row['qty']) {
                            $row2 = ['value' => $row['value'], 'type' => $row['type']];
                            $response = [
                                'success' => true,
                                'message' => $message,
                                'data' => $row2
                            ];
                        } else {
                            $message = '4'; //Code limited by usage
                            $work = 0;
                            $response = [
                                'success' => false,
                                'message' => $message,
                            ];
    
                        }
                    }



                    
    
    
                } else {
                    $row2 = ['value' => $row['value'], 'type' => $row['type']];
                        $response = [
                            'success' => true,
                            'message' => $message,
                            'data' => $row2
                        ];
                }
            }


        } else {
            $message = '5'; //Code not working
            $response = [
                'success' => false,
                'message' => $message,
            ];
        }

    } else {
        $message = '6'; //Code not exist
        $response = [
            'success' => false,
            'message' => $message,
        ];
    }
} else {
    $message = '7'; //All value required
    $response = [
        'success' => false,
        'message' => $message,
    ];
}

if($work === 0) {
    $update_query = $mysqli->prepare("UPDATE discount SET work = ? WHERE code = ?");
    $update_query->bind_param("is", $work, $code);
    $update_query->execute();
    $update_query->close();
    $mysqli->close();
}
http_response_code(200);
echo json_encode($response);
$mysqli->close();
die;

?>