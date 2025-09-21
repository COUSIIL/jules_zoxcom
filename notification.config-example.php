<?php
// Fichier de configuration pour le système de notifications
// Copiez ce fichier en `notification.config.php` et remplissez les valeurs.
// Le fichier `notification.config.php` doit être ignoré par git.

// -- Configuration de l'Authentification (exemple avec une clé secrète pour JWT) --
// Assurez-vous que cette clé est longue, aléatoire et gardée secrète.
define('JWT_SECRET_KEY', 'votre_cle_secrete_jwt_tres_longue_et_aleatoire');
// Durée de validité du token en secondes (ex: 1 heure)
define('JWT_EXPIRATION', 3600);


// -- Configuration VAPID pour les Web Push Notifications --
// Vous pouvez générer ces clés via des bibliothèques ou des outils en ligne.
// Par exemple, avec la librairie `web-push-php`, utilisez : `vendor/bin/web-push generate-vapid-keys`
define('VAPID_PUBLIC_KEY', 'VOTRE_CLE_PUBLIQUE_VAPID');
define('VAPID_PRIVATE_KEY', 'VOTRE_CLE_PRIVEE_VAPID');
define('VAPID_SUBJECT', 'mailto:votre-email@votre-domaine.com'); // Adresse email de contact

// -- Paramètres de l'application --
// URL de base de votre application, utilisée pour construire des liens dans les notifications.
define('APP_URL', 'https://votre-site.com');

// -- Configuration du Worker d'envoi (sendNotification.php) --
// Nombre maximum de notifications à traiter par batch pour éviter les timeouts.
define('SEND_BATCH_SIZE', 100);
// Nombre maximum de tentatives d'envoi pour une notification avant de la marquer comme "failed".
define('MAX_SEND_ATTEMPTS', 3);

// -- Gestion des erreurs --
// Mettre à `true` en développement pour voir les erreurs, `false` en production.
define('DEBUG_MODE', false);

// Initialisation du mode d'affichage des erreurs en fonction du mode debug.
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Fuseau horaire
date_default_timezone_set('Europe/Paris');

?>
