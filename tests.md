# Exemples de Test cURL pour l'API de Notification

Ce fichier fournit des exemples de commandes `curl` pour tester chaque endpoint de `api.php`.

**Note importante**:
- Remplacez `https://votre-domaine.com/api` par l'URL réelle de votre API.
- Les exemples ci-dessous supposent que la fonction `authenticate_request()` dans `api.php` retourne un utilisateur valide (par exemple, l'utilisateur de test en mode `DEBUG_MODE`). En production, vous devrez ajouter un header d'authentification à vos requêtes, par exemple : `-H "Authorization: Bearer VOTRE_TOKEN_JWT"`.

---

### 1. Gestion des Tags

#### Créer un nouveau tag (admin)
```bash
# Crée un tag "promotions"
curl -X POST "https://votre-domaine.com/api/api.php?action=createTag" \
-H "Content-Type: application/json" \
-d '{
  "slug": "promo-ete-2024",
  "label": "Promo Été 2024"
}'

# Réponse attendue:
# {"success":true,"data":{"id":"5"},"error":null}
```

#### Lister tous les tags
```bash
curl -X GET "https://votre-domaine.com/api/api.php?action=listTags"

# Réponse attendue:
# {"success":true,"data":[{"id":1,"slug":"general","label":"Général"}, ...],"error":null}
```

---

### 2. Création et Envoi de Notifications (par un admin)

#### Étape A: Créer une notification en brouillon (`draft`)
```bash
# Crée une notification de type "promo" ciblant un utilisateur spécifique (user_id: 12)
curl -X POST "https://votre-domaine.com/api/api.php?action=createNotification" \
-H "Content-Type: application/json" \
-d '{
  "title": "Offre Spéciale d''Été !",
  "body": "Profitez de -20% sur tout le site avec le code ETE2024.",
  "tag_id": 1,
  "type": "promo",
  "priority": 3,
  "channels": ["inapp", "push", "email"],
  "status": "draft",
  "meta": {
    "route": "/promotions/ete-2024",
    "icon": "/icons/promo.png"
  },
  "targets": [
    { "type": "user_id", "value": "12" },
    { "type": "user_id", "value": "15" }
  ]
}'

# Réponse attendue (notez l'ID de la notification, ex: 101):
# {"success":true,"data":{"id":101},"error":null}
```

#### Étape B: Mettre la notification en file d'attente (`queued`)
```bash
# Utilise l'ID de la notification créée à l'étape précédente (ex: 101)
curl -X POST "https://votre-domaine.com/api/api.php?action=enqueueSend" \
-H "Content-Type: application/json" \
-d '{
  "notification_id": 101
}'

# Réponse attendue:
# {"success":true,"data":{"status":"Notification enqueued for sending."},"error":null}
```
**Après cette étape, la prochaine exécution de votre tâche CRON (`sendNotification.php`) devrait traiter et envoyer cette notification.**

---

### 3. Consultation par l'Utilisateur

#### Lister les notifications d'un utilisateur
```bash
# Récupère les notifications pour l'utilisateur authentifié (ou user_id=12 si spécifié)
# Le backend doit associer le token d'authentification à un user_id
curl -X GET "https://votre-domaine.com/api/api.php?action=listNotifications&user_id=12"

# Réponse attendue:
# {
#   "success": true,
#   "data": {
#     "notifications": [
#       {
#         "id": 101,
#         "title": "Offre Spéciale d''Été !",
#         "body": "Profitez de -20% sur tout le site avec le code ETE2024.",
#         ...
#         "is_read": 0,
#         "read_at": null
#       }
#     ],
#     "unread_count": "1"
#   },
#   "error": null
# }
```

#### Marquer une notification comme lue
```bash
# L'utilisateur (géré par l'auth backend) marque la notification 101 comme lue
curl -X POST "https://votre-domaine.com/api/api.php?action=markRead" \
-H "Content-Type: application/json" \
-d '{
  "notification_id": 101
}'

# Réponse attendue:
# {"success":true,"data":{"status":"marked as read"},"error":null}
```

#### Marquer toutes les notifications comme lues
```bash
curl -X POST "https://votre-domaine.com/api/api.php?action=markAllRead" \
-H "Content-Type: application/json" \
-d '{}'

# Réponse attendue:
# {"success":true,"data":{"status":"all marked as read"},"error":null}
```

---

### 4. Abonnement au Push

#### Enregistrer un abonnement Push
```bash
# Simule l'envoi d'un objet PushSubscription par le frontend
curl -X POST "https://votre-domaine.com/api/api.php?action=subscribePush" \
-H "Content-Type: application/json" \
-d '{
  "subscription": {
    "endpoint": "https://fcm.googleapis.com/fcm/send/...",
    "expirationTime": null,
    "keys": {
      "p256dh": "...",
      "auth": "..."
    }
  },
  "label": "Chrome sur Desktop"
}'

# Réponse attendue:
# {"success":true,"data":{"status":"subscribed"},"error":null}
```
