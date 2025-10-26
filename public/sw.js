// --- Service Worker pour notifications Push ---

self.addEventListener('push', (event) => {
  if (!event.data) return;

  let data = {};
  try {
    data = event.data.json();
  } catch (e) {
    console.error('❌ Erreur parsing JSON push data :', e);
    return;
  }

  const title = data.title || "Nouvelle notification";
  const options = {
    body: data.message || data.text || "Vous avez une nouvelle notification.",
    icon: data.icon || '/favicon.ico',
    badge: data.badge || '/favicon.ico',
    data: {
      url: data.link || '/',
      id: data.id || null,
    },
    tag: data.tag || `notif-${Date.now()}`, // évite duplication
    renotify: true,
  };

  // Affiche la notification
  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});


// --- Gestion du clic sur la notification ---
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // Si une fenêtre du site est déjà ouverte → la focus
      for (const client of clientList) {
        if (client.url.includes(self.location.origin)) {
          client.focus();
          client.postMessage({
            type: 'NOTIFICATION_CLICKED',
            data: event.notification.data,
          });
          return;
        }
      }
      // Sinon ouvre une nouvelle fenêtre
      return clients.openWindow(targetUrl);
    })
  );
});


// --- Optionnel : Gestion de la fermeture ---
self.addEventListener('notificationclose', (event) => {
  console.log('🔕 Notification fermée :', event.notification?.data);
});
