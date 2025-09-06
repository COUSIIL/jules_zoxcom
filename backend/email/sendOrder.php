<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');

$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';

// Récupération des données POST
$name         = $_POST['name']         ?? 'Nom inconnu';
$phone        = $_POST['phone']        ?? 'Inconnu';
$deliveryZone = $_POST['deliveryZone'] ?? 'Inconnue';
$totalPrice   = $_POST['totalPrice']   ?? 0;
$id           = $_POST['orderID']      ?? 0;

// Date courante
$date = date("Y-m-d H:i:s");

try {
    $mail->isSMTP();
    $mail->Host       = 'management.hoggari.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@management.hoggari.com';
    $mail->Password   = 'Jhmkhkm1999';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('no-reply@management.hoggari.com', 'Hoggari.com');
    $mail->addAddress('commandes.dnz@gmail.com');
    $mail->isHTML(true);
    $mail->Subject = "Nouvelle commande : $phone";

    // === Construction du corps HTML comme ton template WP ===
    $body  = '<html><body>';
    $body .= '<table style="
        width:100%; max-width:600px; margin:0 auto;
        background:#fff; padding:20px; border-radius:10px;
        border:1px solid #ccc; font-family:Arial,sans-serif;
        text-align:left;
    ">';
    $body .= '<tr style="background:#ff5800; color:#fff;">
                <td colspan="2" style="text-align:center; padding:10px 0;">
                  <h2>Nouvelle Commande</h2>
                </td>
              </tr>';
    $body .= "<tr><td colspan=\"2\" style=\"text-align:center; padding:5px 0;\">$date</td></tr>";
    $body .= '<tr style="background:#ffd5a9;">
                <td style="padding:10px;"><strong>Nom :</strong></td>
                <td style="padding:10px; color:#555;">'.htmlspecialchars($name).'</td>
              </tr>';
    $body .= '<tr>
                <td style="padding:10px;"><strong>Téléphone :</strong></td>
                <td style="padding:10px; color:#555;">'.htmlspecialchars($phone).'</td>
              </tr>';
    $body .= '<tr style="background:#ffd5a9;">
                <td style="padding:10px;"><strong>Wilaya :</strong></td>
                <td style="padding:10px; color:#555;">'.htmlspecialchars($deliveryZone).'</td>
              </tr>';
    $body .= '<tr>
                <td style="padding:10px;"><strong>Total :</strong></td>
                <td style="padding:10px; color:#555;">'.number_format($totalPrice, 2).' DA</td>
              </tr>';
    $body .= '</table>';
    $body .= '</body></html>';
    // ==========================================================

    $mail->Body = $body;
    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "Order submitted and email sent",
        "data"    => $id
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'envoi : {$mail->ErrorInfo}"
    ]);
}
