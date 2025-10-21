import { ref, computed, onMounted, onUnmounted } from 'vue';

// --- Le Composable ---

export const useNotifications = () => {
  // --- État (State) ---
  const notifications = ref([]);
  const unreadCount = ref(0);
  const isLoading = ref(false);
  const error = ref(null);
  const userId = ref(1);
  var authData = ref();

  const { $api } = useNuxtApp(); // Plugin pour $fetch avec la base URL configurée (voir README)

  let polling = null;

  // --- Actions ---

  /**
   * Récupère les notifications depuis l'API.
   * Cette fonction met à jour l'état local.
   */
  let lastNotificationId = null;

  const requestPermission = async () => {
    if ("Notification" in navigator) {
      if (Notification.permission === "default") {
        await Notification.requestPermission();
      }
    }
  };

  const fetchNotifications = async (sinceId = null) => {
    if (isLoading.value && !sinceId) return; // Allow polling requests to go through
    isLoading.value = true;
    error.value = null;

    try {
      const url = sinceId
        ? `/backend/notificationApi.php?action=listNotifications&user_id=${userId.value}&since_id=${sinceId}`
        : `/backend/notificationApi.php?action=listNotifications&user_id=${userId.value}`;

      const response = await $api(url, { method: 'GET' });

      if (response.success && response.data) {
        // On ne garde que les notifications non lues
        const unreadNotifications = response.data.notifications.filter(n => n.is_read === 0);

        if (sinceId) {
          // Polling request: prepend new notifications
          if (unreadNotifications.length > 0) {
            notifications.value = [...unreadNotifications, ...notifications.value];
            lastNotificationId = unreadNotifications[0].id;
          }
        } else {
          // Initial load: replace notifications
          notifications.value = unreadNotifications;
          if (notifications.value.length > 0) {
            lastNotificationId = notifications.value[0].id;
          }
        }

        unreadCount.value = response.data.unread_count;
      } else {
        throw new Error(response.error || 'Failed to fetch notifications.');
      }
    } catch (e) {
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
  const markAsRead = async (notificationId) => {
    const notification = notifications.value.find(n => n.id === notificationId);
    if (!notification || notification.is_read) return;

    // Mise à jour optimiste
    const originalStatus = notification.is_read;
    notification.is_read = 1;
    unreadCount.value = Math.max(0, unreadCount.value - 1);

    try {
      const response = await $api(`/backend/notificationApi.php?action=markRead&notification_id=${notificationId}&user_id=${userId.value}`, {
        method: 'GET',
      });

      console.log('res: ', response);

      if (!response.success) {
        // Annuler la mise à jour si l'API échoue
        notification.is_read = originalStatus;
        unreadCount.value++;
        throw new Error(response.error || 'Failed to mark notification as read.');
      }
    } catch (e) {
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
      const response = await $api(`/backend/notificationApi.php?action=markAllRead&user_id=${userId.value}`, {
        method: 'GET',
        // Le user_id est géré par le backend
      });

      if (!response.success) {
        // Annuler si l'API échoue
        notifications.value = originalNotifications;
        unreadCount.value = originalUnreadCount;
        throw new Error(response.error || 'Failed to mark all as read.');
      }

    } catch (e) {
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
    requestPermission();
    
    if(localStorage.getItem('auth')) {
      authData.value = localStorage.getItem('auth');
      if(authData.value) {
        userId.value = JSON.parse(authData.value).id
        fetchNotifications(); // Premier fetch pour l'historique
        startPolling();       // Démarrer le polling pour les mises à jour
      }
      
      
    }

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
