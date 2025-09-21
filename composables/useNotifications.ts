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

  let pollingInterval: NodeJS.Timeout | null = null;

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


  // --- Polling ---

  /**
   * Démarre le polling pour vérifier les nouvelles notifications.
   * @param interval - L'intervalle en millisecondes (par défaut 30s).
   */
  const startPolling = (interval: number = 30000) => {
    if (pollingInterval) return; // Déjà démarré
    console.log('Starting notification polling...');
    pollingInterval = setInterval(fetchNotifications, interval);
  };

  /**
   * Arrête le polling.
   */
  const stopPolling = () => {
    if (pollingInterval) {
      console.log('Stopping notification polling.');
      clearInterval(pollingInterval);
      pollingInterval = null;
    }
  };

  // --- Hooks de cycle de vie ---
  onMounted(() => {
    fetchNotifications(); // Premier fetch
    startPolling();
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
