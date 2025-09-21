# Système de Notification Complet (PHP + Nuxt 3)

Ce guide explique comment installer et intégrer ce système de notification complet dans votre projet existant basé sur PHP/MySQL et Nuxt 3.

## Table des matières
1. [Fonctionnalités](#fonctionnalités)
2. [Architecture](#architecture)
3. [Installation du Backend (PHP)](#installation-du-backend-php)
4. [Intégration du Frontend (Nuxt 3)](#intégration-du-frontend-nuxt-3)
5. [Tester l'API](#tester-lapi)
6. [Recommandations de sécurité](#recommandations-de-sécurité)
7. [Notes pour l'hébergement mutualisé](#notes-pour-lhébergement-mutualisé)

---

### Fonctionnalités
- Notifications **In-App** (centre de notifications).
- Notifications **Web Push** pour engager les utilisateurs même hors du site.
- Gestion des notifications lues/non lues.
- Ciblage par utilisateur, rôle, ou broadcast.
- File d'attente et envoi asynchrone via une tâche CRON.
- Priorisation des notifications.

### Architecture
- **Backend**: Un script `api.php` servant de point d'entrée unique, un worker `sendNotification.php` pour les tâches CRON, et une configuration centralisée.
- **Frontend**: Des composants Vue (`NotificationBell`, `NotificationDropdown`), un composable (`useNotifications`) pour la logique et l'état, et un Service Worker (`sw.js`) pour le Push.
- **Base de données**: Schéma MySQL optimisé avec `InnoDB`.

---

## Installation du Backend (PHP)

### Étape 1: Base de données
Importez le fichier `schema.sql` dans votre base de données MySQL. Cela créera toutes les tables nécessaires (`notifications`, `user_notifications`, etc.).

```bash
mysql -u votre_user -p votre_db < schema.sql
```

### Étape 2: Fichiers de configuration
Ce système utilise deux fichiers de configuration :
- `dbConfig.php`: Gère la connexion à la base de données. Le système est flexible : ce fichier peut soit définir les constantes de connexion (`DB_HOST`, `DB_NAME`, etc.), soit instancier directement un objet PDO dans une variable `$pdo`. Les deux approches sont supportées.
- `notification.config.php`: Pour les paramètres spécifiques aux notifications.

**Marche à suivre :**
1. Copiez `notification.config-example.php` et renommez la copie en `notification.config.php`.
2. Ouvrez `notification.config.php` et remplissez les constantes requises (`JWT_SECRET_KEY`, clés `VAPID`, etc.).

### Étape 3: Dépendances PHP
Ce projet utilise la bibliothèque `minishlink/web-push` pour envoyer les notifications Push. Installez-la avec Composer.

```bash
# Si vous n'avez pas de fichier composer.json, créez-en un.
# Puis exécutez :
composer require minishlink/web-push
```
Cela créera un répertoire `vendor` avec la bibliothèque et son autoloader.

### Étape 4: Génération des clés VAPID
Les clés VAPID sont essentielles pour sécuriser les notifications Push.
1. Générez vos clés en utilisant l'outil fourni par la bibliothèque :
   ```bash
   ./vendor/bin/web-push generate-vapid-keys
   ```
2. Copiez la clé publique (`Public Key`) et la clé privée (`Private Key`) dans votre fichier `notification.config.php` aux constantes `VAPID_PUBLIC_KEY` et `VAPID_PRIVATE_KEY`.
3. Renseignez également `VAPID_SUBJECT` avec une URL `mailto:` ou le lien de votre site.

### Étape 5: Configuration de la tâche CRON
Le script `sendNotification.php` doit être exécuté périodiquement pour envoyer les notifications en attente.

1. Connectez-vous au panneau de contrôle de votre hébergeur (ex: cPanel).
2. Trouvez la section "Cron Jobs" ou "Tâches Cron".
3. Créez une nouvelle tâche. La fréquence dépend de votre volume (toutes les 5 minutes est un bon début).

**Exemple de commande CRON :**
```bash
*/5 * * * * /usr/local/bin/php /home/votre_user/public_html/votre_projet/api/sendNotification.php > /dev/null 2>&1
```
- **Attention**: Le chemin vers l'exécutable PHP (`/usr/local/bin/php`) peut varier. Demandez à votre hébergeur si vous n'êtes pas sûr.
- Le chemin vers votre script doit être le chemin complet sur le serveur.
- `> /dev/null 2>&1` empêche l'envoi d'e-mails de rapport à chaque exécution. Retirez-le temporairement pour débugger si besoin.

---

## Intégration du Frontend (Nuxt 3)

### Étape 1: Copier les fichiers
Copiez les répertoires et fichiers générés dans votre projet Nuxt :
- `composables/useNotifications.ts` -> `votre-projet-nuxt/composables/`
- `components/NotificationBell.vue` -> `votre-projet-nuxt/components/`
- `components/NotificationDropdown.vue` -> `votre-projet-nuxt/components/`
- `utils/push-register.ts` -> `votre-projet-nuxt/utils/`
- `public/sw.js` -> `votre-projet-nuxt/public/`

### Étape 2: Configurer `nuxt.config.ts`
Vous devez exposer votre clé publique VAPID au code côté client.

Modifiez votre `nuxt.config.ts` :
```typescript
export default defineNuxtConfig({
  // ... autres configurations
  runtimeConfig: {
    public: {
      // Clé VAPID publique de votre config.php
      vapidPublicKey: 'VOTRE_CLE_PUBLIQUE_VAPID_ICI'
    }
  }
});
```

### Étape 3: Créer un plugin pour l'API
Pour simplifier les appels à l'API PHP, créez un plugin Nuxt.
Créez `plugins/api.ts`:
```typescript
// plugins/api.ts
export default defineNuxtPlugin(() => {
  const $api = $fetch.create({
    // Adaptez l'URL de base à l'emplacement de votre api.php
    baseURL: 'https://votre-domaine.com/api',
    // Vous pouvez ajouter ici la logique pour inclure le token d'authentification
    // onRequest({ options }) {
    //   const token = useCookie('auth_token').value;
    //   if (token) {
    //     options.headers = { ...options.headers, Authorization: `Bearer ${token}` };
    //   }
    // }
  });

  return {
    provide: {
      api: $api
    }
  }
});
```

### Étape 4: Utiliser les composants
Ajoutez la cloche de notification à votre layout principal, par exemple dans `layouts/default.vue`:
```vue
<template>
  <div>
    <header>
      <!-- Votre navigation -->
      <nav>
        <NuxtLink to="/">Home</NuxtLink>
        <div style="margin-left: auto;">
          <NotificationBell />
        </div>
      </nav>
    </header>
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import NotificationBell from '~/components/NotificationBell.vue';
</script>
```

### Étape 5: Activer les notifications Push
Créez un bouton dans une page de paramètres pour que l'utilisateur puisse s'abonner.

```vue
<template>
  <div>
    <h2>Paramètres</h2>
    <button @click="subscribeToPush">Activer les notifications</button>
  </div>
</template>

<script setup>
import { registerForPushNotifications } from '~/utils/push-register';

const subscribeToPush = async () => {
  await registerForPushNotifications();
};
</script>
```

---

## Tester l'API
Un fichier `tests.md` sera généré avec des exemples de commandes `curl` pour chaque endpoint de l'API.

---

## Recommandations de sécurité
1.  **Implémentez `authenticate_request()`**: La fonction `authenticate_request()` dans `api.php` est un placeholder. Vous **devez** y intégrer votre propre logique de vérification de session ou de token JWT pour sécuriser vos endpoints.
2.  **Droits d'accès**: Assurez-vous que les actions sensibles (`createNotification`, `enqueueSend`) sont bien protégées et accessibles uniquement aux administrateurs.
3.  **Validation des données**: Validez et nettoyez toutes les données reçues, en particulier les `meta` JSON, pour éviter les injections.
4.  **Rate Limiting**: Envisagez de mettre en place une limitation du nombre de requêtes sur les endpoints les plus coûteux.

---

## Notes pour l'hébergement mutualisé
- **Limites d'exécution**: Le script `sendNotification.php` est conçu pour traiter les notifications par lots (`SEND_BATCH_SIZE`) afin d'éviter les timeouts. Ajustez cette valeur en fonction des limites de votre hébergeur.
- **Fréquence des CRON**: Ne configurez pas une fréquence trop élevée (ex: toutes les secondes) qui pourrait être considérée comme un abus par votre hébergeur.
- **Extensions PHP**: Assurez-vous que les extensions PHP `pdo_mysql`, `json`, et `curl` sont bien activées pour votre compte.
- **WebSockets**: Ce système évite les WebSockets, qui sont généralement mal supportés ou interdits sur les serveurs mutualisés. Le polling est un fallback robuste.

### Étape 6: Configuration du Broadcast (Optionnel)
La fonctionnalité de broadcast (envoyer à tous les utilisateurs) nécessite une petite configuration manuelle pour des raisons de sécurité et de flexibilité.

1.  Ouvrez le fichier `api.php`.
2.  Allez à l'action `enqueueSend` (vers la fin du fichier).
3.  Trouvez la ligne de code avec le commentaire `MODIFIEZ "users"`.
4.  Remplacez `users` par le nom réel de votre table contenant tous les utilisateurs.

Pour envoyer un broadcast, vous devez créer une notification avec une cible spécifique dans le payload JSON :
```json
"targets": [{ "type": "broadcast", "value": "all" }]
```
