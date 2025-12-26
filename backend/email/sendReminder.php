<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json; charset=UTF-8');

$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';

// Données POST
$reminderID   = $_POST['reminderID']   ?? 0;
$work         = $_POST['work']         ?? '(Aucun travail défini)';
$note         = $_POST['note']         ?? '(Pas de note)';
$reminderDate = $_POST['reminder_date'] ?? date('Y-m-d');
$toEmail      = $_POST['toEmail']      ?? 'hoggari.mail@gmail.com'; // destinataire par défaut

// Date d’envoi
$today = date('d/m/Y H:i:s');

try {
    $mail->isSMTP();
    $mail->Host       = 'management.hoggari.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'no-reply@management.hoggari.com';
    $mail->Password   = 'Jhmkhkm1999';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->setFrom('no-reply@management.hoggari.com', 'Hoggari Reminder');
    $mail->addAddress($toEmail);

    $mail->isHTML(true);
    $mail->Subject = "🔔 Rappel du jour (#$reminderID) - $reminderDate";

    // Corps du message
    $body  = '<html><body>';
    $body .= '<table style="
        width:100%; max-width:600px; margin:0 auto;
        background:#fff; padding:20px; border-radius:10px;
        border:1px solid #ccc; font-family:Arial,sans-serif;
    ">';
    $body .= '<tr style="background:#007bff; color:#fff;">
                <td colspan="2" style="text-align:center; padding:10px 0;">
                    <h2>🕒 Rappel programmé</h2>
                </td>
              </tr>';
    $body .= "<tr><td colspan=\"2\" style=\"text-align:center; padding:5px 0;\">$today</td></tr>";
    $body .= '<tr style="background:#e2f0ff;">
                <td style="padding:10px;"><strong>Date du rappel :</strong></td>
                <td style="padding:10px;">' . htmlspecialchars($reminderDate) . '</td>
              </tr>';
    $body .= '<tr>
                <td style="padding:10px;"><strong>Travail :</strong></td>
                <td style="padding:10px;">' . nl2br(htmlspecialchars($work)) . '</td>
              </tr>';
    $body .= '<tr style="background:#e2f0ff;">
                <td style="padding:10px;"><strong>Note :</strong></td>
                <td style="padding:10px;">' . nl2br(htmlspecialchars($note)) . '</td>
              </tr>';
    $body .= '</table>';
    $body .= '<p style="text-align:center; font-size:12px; color:#888;">📩 Envoyé automatiquement par le système Hoggari</p>';
    $body .= '</body></html>';

    $mail->Body = $body;
    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "Email de rappel envoyé",
        "reminderID" => $reminderID
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur lors de l'envoi : {$mail->ErrorInfo}"
    ]);
}
?>
