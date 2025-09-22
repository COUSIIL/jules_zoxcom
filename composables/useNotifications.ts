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

  let polling: any | null = null;

  // --- Actions ---

  /**
   * Récupère les notifications depuis l'API.
   * Cette fonction met à jour l'état local.
   */
  let lastNotificationId: number | null = null;

  const fetchNotifications = async (sinceId: number | null = null) => {
    if (isLoading.value && !sinceId) return; // Allow polling requests to go through
    isLoading.value = true;
    error.value = null;

    try {
      const url = sinceId
        ? `/backend/notificationApi.php?action=listNotifications&user_id=2&since_id=${sinceId}`
        : `/backend/notificationApi.php?action=listNotifications&user_id=2`;

      const response = await $api<ApiResponse<ListNotificationsData>>(url, { method: 'GET' });

      if (response.success && response.data) {
        if (sinceId) {
          // Polling request: prepend new notifications
          if (response.data.notifications.length > 0) {
            notifications.value = [...response.data.notifications, ...notifications.value];
            lastNotificationId = response.data.notifications[response.data.notifications.length - 1].id;
          }
        } else {
          // Initial load: replace notifications
          notifications.value = response.data.notifications;
          if (notifications.value.length > 0) {
            lastNotificationId = notifications.value[0].id;
          }
        }
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

  const fetchNewNotifications = () => {
    fetchNotifications(lastNotificationId);
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
      const response = await $api<ApiResponse<any>>('/backend/notificationApi.php?action=markRead', {
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
      const response = await $api<ApiResponse<any>>('/backend/notificationApi.php?action=markAllRead', {
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


  // --- Real-time (Polling) ---

  const startPolling = () => {
    if (polling) {
      clearInterval(polling);
    }
    // Fetch new notifications every 5 seconds
    polling = setInterval(fetchNewNotifications, 5000);
  };

  const stopPolling = () => {
    if (polling) {
      clearInterval(polling);
      polling = null;
    }
  };


  // --- Hooks de cycle de vie ---
  onMounted(() => {
    fetchNotifications(); // Premier fetch pour l'historique
    startPolling();       // Démarrer le polling pour les mises à jour
  });

  onUnmounted(() => {
    stopPolling(); // Nettoyage pour éviter les fuites de mémoire
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
