// utils/push-register.ts

interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string | null;
}

/**
 * Convertit une chaîne VAPID public key (base64) en un Uint8Array.
 * Nécessaire pour la souscription push.
 */
function urlBase64ToUint8Array(base64String: string): Uint8Array {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

/**
 * Logique principale pour l'enregistrement aux notifications push.
 * Cette fonction doit être appelée suite à une action de l'utilisateur (ex: clic sur un bouton).
 */
export async function registerForPushNotifications() {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    console.warn('Push notifications are not supported in this browser.');
    alert('Les notifications Push ne sont pas supportées par ce navigateur.');
    return;
  }

  // Utiliser `useRuntimeConfig` pour accéder aux variables d'environnement côté client.
  // La clé VAPID publique doit être exposée dans `nuxt.config.ts`.
  const runtimeConfig = useRuntimeConfig();
  const vapidPublicKey = runtimeConfig.public.vapidPublicKey;

  if (!vapidPublicKey) {
    console.error('VAPID public key is not configured.');
    alert('La configuration pour les notifications est incomplète.');
    return;
  }

  try {
    // 1. Demander la permission à l'utilisateur
    const permission = await window.Notification.requestPermission();
    if (permission !== 'granted') {
      console.log('User denied notification permission.');
      alert('Vous avez refusé les notifications.');
      return;
    }

    // 2. Enregistrer le Service Worker
    const registration = await navigator.serviceWorker.register('/sw.js');
    console.log('Service Worker registered:', registration);

    // 3. Obtenir l'abonnement push
    let subscription = await registration.pushManager.getSubscription();
    if (subscription === null) {
      console.log('No existing subscription found, creating new one...');
      const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);
      subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey,
      });
    }

    console.log('Push subscription retrieved:', subscription);

    // 4. Envoyer l'abonnement au serveur backend
    const { $api } = useNuxtApp();
    const response = await $api<ApiResponse<any>>('/api.php?action=subscribePush', {
      method: 'POST',
      body: {
        subscription: subscription.toJSON(),
        label: navigator.userAgent, // Optionnel : pour identifier l'appareil
      },
    });

    if (response.success) {
      console.log('Subscription sent to server successfully.');
      alert('Vous êtes maintenant abonné aux notifications !');
    } else {
      throw new Error(response.error || 'Failed to send subscription to server.');
    }

  } catch (error) {
    console.error('Failed to register for push notifications:', error);
    alert(`Une erreur est survenue lors de l'abonnement aux notifications : ${error}`);
  }
}
