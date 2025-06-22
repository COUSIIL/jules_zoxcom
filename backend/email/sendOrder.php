<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');

$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8'; // 🔥 Important pour bien afficher les accents


$name = $_POST['name'] ?? 'Nom inconnu';
$phone = $_POST['phone'] ?? 'Inconnu';
$deliveryZone = $_POST['deliveryZone'] ?? 'Inconnue';
$totalPrice = $_POST['totalPrice'] ?? 0;
$id = $_POST['orderID'] ?? 0;



try {
    $mail->isSMTP();
    $mail->Host = 'management.hoggari.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'no-reply@management.hoggari.com';
    $mail->Password = 'Jhmkhkm1999'; // 🔒 Remplace ici par le vrai mot de passe sécurisé
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // meilleure constante que 'ssl'
    $mail->Port = 465;

    $mail->setFrom('no-reply@management.hoggari.com', 'Hoggari.com');
    $mail->addAddress('hoggari.mail@gmail.com'); // À changer si besoin

    $mail->isHTML(true);
    $mail->Subject = "Nouvelle commande";

    // Simule des données — à remplacer par les vraies variables (par ex. $_POST['name'])


    // Construction du mail
    $body = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>';
    $body .= "<h2>Commande enregistrée avec succès !</h2>";
    $body .= "<p><strong>Nom :</strong> " . htmlspecialchars($name) . "</p>";
    $body .= "<p><strong>Numéro de téléphone :</strong> " . htmlspecialchars($phone) . "</p>";
    $body .= "<p><strong>Wilaya :</strong> " . htmlspecialchars($deliveryZone) . "</p>";
    $body .= "<p><strong>Montant total :</strong> " . htmlspecialchars($totalPrice) . " DA</p>";
    $body .= '</body></html>';

    $mail->Body = $body;

    $mail->send();

    echo json_encode([
        "success" => true, 
        "message" => "Order submited and saved",
        "data" => $id
    ]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Erreur lors de l'envoi : {$mail->ErrorInfo}"]);
}
