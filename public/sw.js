// public/sw.js

// Ce nom de cache est souvent utilisé pour la mise en cache des ressources de l'application (PWA),
// mais pour ce cas d'usage, nous nous concentrons uniquement sur les notifications push.
const CACHE_NAME = 'notification-system-cache-v1';

self.addEventListener('install', (event) => {
  // Le service worker est installé.
  // On peut pré-cacher des assets ici si nécessaire.
  console.log('Service Worker: Installed');
  // force l'activation immédiate du nouveau service worker
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  // Le service worker est activé.
  // C'est un bon endroit pour nettoyer les anciens caches.
  console.log('Service Worker: Activated');
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  // Prend le contrôle de la page immédiatement
  return self.clients.claim();
});


/**
 * Écouteur pour les événements push entrants.
 * C'est ici que la magie opère.
 */
self.addEventListener('push', (event) => {
  console.log('Service Worker: Push Received.');

  let data = {};
  if (event.data) {
    try {
      data = event.data.json();
    } catch (e) {
      console.error('Error parsing push data:', e);
      data = {
        title: 'Notification',
        body: event.data.text(),
      };
    }
  }

  const title = data.title || 'Nouvelle Notification';
  const options = {
    body: data.body || 'Vous avez reçu une nouvelle notification.',
    icon: data.icon || '/icon-192x192.png', // Assurez-vous que cette icône existe dans /public
    badge: data.badge || '/badge-72x72.png', // Icône pour la barre de statut Android
    data: {
      url: data.data?.url || '/', // URL à ouvrir au clic
      notification_id: data.data?.notification_id
    },
  };

  event.waitUntil(self.registration.showNotification(title, options));
});


/**
 * Écouteur pour les clics sur les notifications.
 */
self.addEventListener('notificationclick', (event) => {
  console.log('Service Worker: Notification clicked.');

  const notification = event.notification;
  const urlToOpen = notification.data.url || '/';

  // Ferme la notification
  notification.close();

  // Ouvre la page correspondante
  event.waitUntil(
    clients.matchAll({ type: 'window' }).then((clientList) => {
      // Vérifie si le site est déjà ouvert
      for (const client of clientList) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      // Sinon, ouvre une nouvelle fenêtre
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );

  // Ici, on pourrait aussi envoyer une requête à l'API pour marquer la notif comme lue
  // fetch(`/api.php?action=markRead`, { method: 'POST', body: JSON.stringify({ notification_id: notification.data.notification_id }) });
});
