import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useAuth } from './useAuth';

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
  const currentNotification = ref<Notification | null>(null);

  const { $api } = useNuxtApp();
  const { getauth } = useAuth();
  const authUser = getauth();

  let eventSource: EventSource | null = null;

  // --- Actions ---

  const dismissNotification = () => {
    currentNotification.value = null;
  };

  const fetchNotifications = async (isTriggeredByPing = false) => {
    if (isLoading.value || !authUser) return;
    isLoading.value = true;
    error.value = null;

    try {
      const response = await $api<ApiResponse<ListNotificationsData>>(
        `/backend/notificationApi.php?action=listNotifications`,
        { method: 'GET' }
      );

      if (response.success && response.data) {
        const oldNotifications = notifications.value;
        notifications.value = response.data.notifications;
        unreadCount.value = response.data.unread_count;

        if (isTriggeredByPing && notifications.value.length > 0) {
          const oldIdSet = new Set(oldNotifications.map(n => n.id));
          const newNotification = notifications.value.find(n => !oldIdSet.has(n.id));
          if (newNotification) {
            currentNotification.value = newNotification;
          }
        }
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

  const markAsRead = async (notificationId: number) => {
    const notification = notifications.value.find(n => n.id === notificationId);
    if (!notification || notification.is_read) return;

    const originalStatus = notification.is_read;
    notification.is_read = 1;
    unreadCount.value = Math.max(0, unreadCount.value - 1);

    try {
      await $api<ApiResponse<any>>('/backend/notificationApi.php?action=markRead', {
        method: 'POST',
        body: { notification_id: notificationId },
      });
    } catch (e: any) {
      notification.is_read = originalStatus;
      unreadCount.value++;
      error.value = e.message;
      console.error(e);
    }
  };

  const markAllAsRead = async () => {
    const originalNotifications = JSON.parse(JSON.stringify(notifications.value));
    const originalUnreadCount = unreadCount.value;

    notifications.value.forEach(n => n.is_read = 1);
    unreadCount.value = 0;

    try {
      await $api<ApiResponse<any>>('/backend/notificationApi.php?action=markAllAsRead', {
        method: 'POST',
      });
    } catch (e: any) {
      notifications.value = originalNotifications;
      unreadCount.value = originalUnreadCount;
      error.value = e.message;
      console.error(e);
    }
  };

  const connectToSse = () => {
    if (eventSource || !authUser) {
      return;
    }

    eventSource = new EventSource('/backend/notificationApi.php?action=sse', { withCredentials: true });

    eventSource.onopen = () => {
      console.log('SSE connection established.');
      error.value = null;
    };

    eventSource.addEventListener('new-notification', (event) => {
      fetchNotifications(true);
    });

    eventSource.onerror = (err) => {
      console.error('SSE Error:', err);
      error.value = "Real-time connection failed.";
    };
  };

  const disconnectFromSse = () => {
    if (eventSource) {
      console.log('Closing SSE connection.');
      eventSource.close();
      eventSource = null;
    }
  };

  onMounted(() => {
    if (authUser) {
        fetchNotifications();
        connectToSse();
    }
  });

  onUnmounted(() => {
    disconnectFromSse();
  });

  return {
    notifications: computed(() => notifications.value),
    unreadCount: computed(() => unreadCount.value),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    currentNotification: computed(() => currentNotification.value),
    fetchNotifications,
    markAsRead,
    markAllAsRead,
    dismissNotification,
  };
};
