import { ref, computed, onMounted, onUnmounted } from 'vue';

// --- Interfaces TypeScript pour la robustesse du code ---

export interface NotificationMeta {
  route?: string; // ex: '/orders/123'
  resourceId?: number | string;
  [key: string]: any;
}

export interface Notification {
  id: number;
  title: string;
  body: string;
  tag_id: number | null;
  type: 'info' | 'success' | 'warning' | 'error' | 'promo' | 'system';
  priority: number;
  channels: string[];
  meta: NotificationMeta | null;
  created_at: string;
  is_read: 0 | 1;
  read_at: string | null;
}

interface ApiResponse<T> {
  success: boolean;
  data: T;
  error?: string | null;
}

interface ListNotificationsData {
    notifications: Notification[];
    unread_count: number;
}

// --- Le Composable ---

export const useNotifications = () => {
  // --- État (State) ---
  const notifications = ref<Notification[]>([]);
  const unreadCount = ref<number>(0);
  const isLoading = ref<boolean>(false);
  const error = ref<string | null>(null);

  const { $api } = useNuxtApp(); // Plugin pour $fetch avec la base URL configurée (voir README)

  let eventSource: EventSource | null = null;

  // --- Actions ---

  /**
   * Récupère les notifications depuis l'API.
   * Cette fonction met à jour l'état local.
   */
  const fetchNotifications = async () => {
    if (isLoading.value) return;
    isLoading.value = true;
    error.value = null;

    try {
      // Note: l'user_id est géré côté backend via la session ou le token JWT
      const response = await $api<ApiResponse<ListNotificationsData>>('/api.php?action=listNotifications', {
        method: 'GET',
      });

      if (response.success && response.data) {
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.unread_count;
      } else {
        throw new Error(response.error || 'Failed to fetch notifications.');
      }
    } catch (e: any) {
      error.value = e.message;
      console.error("Error fetching notifications:", e);
    } finally {
      isLoading.value = false;
    }
  };

  /**
   * Marque une notification comme lue (avec mise à jour optimiste).
   * @param notificationId - L'ID de la notification à marquer comme lue.
   */
  const markAsRead = async (notificationId: number) => {
    const notification = notifications.value.find(n => n.id === notificationId);
    if (!notification || notification.is_read) return;

    // Mise à jour optimiste
    const originalStatus = notification.is_read;
    notification.is_read = 1;
    unreadCount.value = Math.max(0, unreadCount.value - 1);

    try {
      const response = await $api<ApiResponse<any>>('/api.php?action=markRead', {
        method: 'POST',
        body: { notification_id: notificationId },
      });

      if (!response.success) {
        // Annuler la mise à jour si l'API échoue
        notification.is_read = originalStatus;
        unreadCount.value++;
        throw new Error(response.error || 'Failed to mark notification as read.');
      }
    } catch (e: any) {
      error.value = e.message;
      console.error(e);
    }
  };

  /**
   * Marque toutes les notifications comme lues.
   */
  const markAllAsRead = async () => {
    const originalNotifications = JSON.parse(JSON.stringify(notifications.value));
    const originalUnreadCount = unreadCount.value;

    // Mise à jour optimiste
    notifications.value.forEach(n => n.is_read = 1);
    unreadCount.value = 0;

    try {
      const response = await $api<ApiResponse<any>>('/api.php?action=markAllRead', {
        method: 'POST',
        // Le user_id est géré par le backend
      });

      if (!response.success) {
        // Annuler si l'API échoue
        notifications.value = originalNotifications;
        unreadCount.value = originalUnreadCount;
        throw new Error(response.error || 'Failed to mark all as read.');
      }
    } catch (e: any) {
      error.value = e.message;
      console.error(e);
    }
  };


  // --- Real-time (SSE) ---

  const connectToSse = () => {
    if (eventSource) {
      eventSource.close();
    }

    // Note: Le chemin est relatif à la racine du site.
    // Assurez-vous que le serveur web sert bien ce fichier PHP.
    eventSource = new EventSource('/backend/sse_notifications.php');

    eventSource.onopen = () => {
      console.log('SSE connection established.');
      error.value = null;
    };

    eventSource.addEventListener('notification', (event) => {
      try {
        const newNotification: Notification = JSON.parse(event.data);

        // Ajoute la nouvelle notification en haut de la liste
        notifications.value.unshift(newNotification);

        // Incrémente le compteur de non-lus
        if (!newNotification.is_read) {
            unreadCount.value++;
        }

      } catch (e) {
        console.error('Failed to parse SSE notification event:', e);
      }
    });

    eventSource.onerror = (err) => {
      console.error('SSE Error:', err);
      error.value = "Real-time connection to the server failed. Reconnecting...";
      // EventSource réessaie de se connecter automatiquement.
      // On pourrait vouloir fermer après N tentatives.
    };
  };

  const disconnectFromSse = () => {
    if (eventSource) {
      console.log('Closing SSE connection.');
      eventSource.close();
      eventSource = null;
    }
  };


  // --- Hooks de cycle de vie ---
  onMounted(() => {
    fetchNotifications(); // Premier fetch pour l'historique
    connectToSse();       // Connexion temps réel pour les nouvelles
  });

  onUnmounted(() => {
    disconnectFromSse(); // Nettoyage pour éviter les fuites de mémoire
  });


  // --- Exportations ---
  return {
    notifications: computed(() => notifications.value),
    unreadCount: computed(() => unreadCount.value),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),

    fetchNotifications,
    markAsRead,
    markAllAsRead,
  };
};
