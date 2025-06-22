<?php
function response($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'message' => $message]);
    die;
}
try {
    
    header("Content-Type: application/json");
    
    
    
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
    

    // Lecture des données JSON
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['id'])) {
        response(false, "Invalid input data.", 400);
    }

    $product_id = $data['id'];
    

    // Supprimer les images associées (product_descriptions)
    deleteImage($product_id, $mysqli);
    
        // Récupérer les URLs des images
    $stmt = $mysqli->prepare("SELECT image_url FROM product_models WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $row['image_url'];

        // Vérifier si le fichier existe avant de le supprimer
        if (file_exists($imagePath)) {
            unlink($imagePath); // Supprimer le fichier physique
        }
    }

    $stmt->close();
    
    // Supprimer les entrées dans la base de données
    $deleteStmt = $mysqli->prepare("DELETE FROM product_models WHERE product_id = ?");
    $deleteStmt->bind_param("i", $product_id);
    $deleteStmt->execute();
    $deleteStmt->close();

            // Récupérer les URLs des images
    $stmt = $mysqli->prepare("SELECT image_url FROM product_descriptions WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $row['image_url'];

        // Vérifier si le fichier existe avant de le supprimer
        if (file_exists($imagePath)) {
            unlink($imagePath); // Supprimer le fichier physique
        }
    }

    $stmt->close();
    
    // Supprimer les entrées dans la base de données
    $deleteStmt = $mysqli->prepare("DELETE FROM product_descriptions WHERE product_id = ?");
    $deleteStmt->bind_param("i", $product_id);
    $deleteStmt->execute();
    $deleteStmt->close();
    
                // Récupérer les URLs des images
    $stmt = $mysqli->prepare("SELECT image_url FROM product_catalogs WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $row['image_url'];

        // Vérifier si le fichier existe avant de le supprimer
        if (file_exists($imagePath)) {
            unlink($imagePath); // Supprimer le fichier physique
        }
    }

    $stmt->close();
    
    // Supprimer les entrées dans la base de données
    $deleteStmt = $mysqli->prepare("DELETE FROM product_catalogs WHERE product_id = ?");
    $deleteStmt->bind_param("i", $product_id);
    $deleteStmt->execute();
    $deleteStmt->close();


    // Supprimer le produit (les entrées liées dans product_models seront supprimées automatiquement grâce au `ON DELETE CASCADE`)
    $deleteProduct = $mysqli->prepare("DELETE FROM products WHERE id = ?");
    $deleteProduct->bind_param("i", $product_id);
    
    if ($deleteProduct->execute()) {
        response(true, "Product deleted successfully.");
    } else {
        response(false, "Error deleting product: " . $deleteProduct->error);
    }
    

    $deleteProduct->close();
    

} catch (Exception $e) {
    response(false, "Error: " . $e->getMessage());
}



function deleteImage($product_id, $mysqli) {
    // Récupérer les URLs des images
    $stmt = $mysqli->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Récupérer le chemin de l'image
    if ($row = $result->fetch_assoc()) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $row['image'];

        // Vérifier si le fichier existe avant de le supprimer
        if (file_exists($imagePath)) {
            unlink($imagePath); // Supprimer le fichier physique
        }
    }

    $stmt->close();

    // Supprimer le produit dans la base de données
    $deleteStmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->bind_param("i", $product_id);
    $deleteStmt->execute();
    $deleteStmt->close();
}



?>
