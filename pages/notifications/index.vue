<template>
  <div class="notifications-page">
    <h1>Centre de Notifications</h1>

    <!-- Section pour envoyer une notification -->
    <div class="send-notification-section card">
      <h2>Envoyer une Notification</h2>
      <form @submit.prevent="sendNotification">
        <div class="form-group">
          <label for="user-select">Destinataire</label>
          <select id="user-select" v-model="selectedUser" required>
            <option disabled value="">Choisissez un utilisateur</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.username }} ({{ user.email }})
            </option>
          </select>
        </div>
        <div class="form-group">
          <label for="notification-title">Titre</label>
          <input id="notification-title" v-model="notificationTitle" type="text" placeholder="Titre de la notification" required />
        </div>
        <div class="form-group">
          <label for="notification-body">Message</label>
          <textarea id="notification-body" v-model="notificationBody" placeholder="Contenu du message..."></textarea>
        </div>
        <div class="form-group">
          <label for="notification-type">Type</label>
          <select id="notification-type" v-model="notificationType">
            <option value="info">Information</option>
            <option value="success">Succès</option>
            <option value="warning">Avertissement</option>
            <option value="error">Erreur</option>
          </select>
        </div>
        <button type="submit" :disabled="isSending">
          {{ isSending ? 'Envoi en cours...' : 'Envoyer' }}
        </button>
        <p v-if="sendError" class="error-message">{{ sendError }}</p>
        <p v-if="sendSuccess" class="success-message">Notification envoyée avec succès !</p>
      </form>
    </div>

    <!-- Section pour afficher les notifications reçues -->
    <div class="received-notifications-section card">
      <h2>Notifications Reçues</h2>
      <div class="actions">
        <button @click="markAllAsRead" :disabled="unreadCount === 0">Marquer tout comme lu</button>
      </div>
      <div v-if="isLoading" class="loading">Chargement des notifications...</div>
      <div v-else-if="error" class="error-message">{{ error }}</div>
      <ul v-else-if="notifications.length > 0" class="notifications-list">
        <li v-for="notification in notifications" :key="notification.id" class="notification-item" :class="{ 'is-read': notification.is_read }">
          <div class="notification-content">
            <strong>{{ notification.title }}</strong>
            <p>{{ notification.body }}</p>
            <small>{{ new Date(notification.created_at).toLocaleString() }}</small>
          </div>
          <button v-if="!notification.is_read" @click="markAsRead(notification.id)" class="mark-read-btn">
            Marquer comme lu
          </button>
        </li>
      </ul>
      <div v-else class="no-notifications">
        Aucune notification pour le moment.
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useNotifications, type Notification } from '~/composables/useNotifications';

// --- State for sending notifications ---
const users = ref([]);
const selectedUser = ref('');
const notificationTitle = ref('');
const notificationBody = ref('');
const notificationType = ref('info');
const isSending = ref(false);
const sendError = ref<string | null>(null);
const sendSuccess = ref(false);

// --- State for receiving notifications ---
const {
  notifications,
  unreadCount,
  isLoading,
  error,
  markAsRead,
  markAllAsRead,
  fetchNotifications,
} = useNotifications();

const { $api } = useNuxtApp();

// --- Fetch users for the dropdown ---
const fetchUsers = async () => {
  try {
    const response = await $api('/backend/sql/get/users.php');
    if (response.success) {
      users.value = response.data;
    } else {
      throw new Error(response.message || 'Failed to fetch users.');
    }
  } catch (e: any) {
    console.error("Error fetching users:", e);
    sendError.value = "Impossible de charger la liste des utilisateurs.";
  }
};

// --- Send notification logic ---
const sendNotification = async () => {
  if (!selectedUser.value || !notificationTitle.value) {
    sendError.value = "Le destinataire et le titre sont requis.";
    return;
  }

  isSending.value = true;
  sendError.value = null;
  sendSuccess.value = false;

  const notificationPayload = {
    title: notificationTitle.value,
    body: notificationBody.value,
    type: notificationType.value,
    channels: ['inapp', 'push'], // Example: send to in-app and push
    priority: 2,
    targets: [
      {
        type: 'user_id',
        value: selectedUser.value,
      },
    ],
  };

  try {
    const response = await $api('/backend/notificationApi.php?action=createNotification', {
      method: 'POST',
      body: notificationPayload,
    });

    if (response.success) {
      sendSuccess.value = true;
      // Reset form
      notificationTitle.value = '';
      notificationBody.value = '';
      selectedUser.value = '';
      setTimeout(() => sendSuccess.value = false, 3000);
    } else {
      throw new Error(response.message || 'Failed to send notification.');
    }
  } catch (e: any) {
    sendError.value = e.message;
    console.error("Error sending notification:", e);
  } finally {
    isSending.value = false;
  }
};

// --- Lifecycle hooks ---
onMounted(() => {
  fetchUsers();
  // Notifications are already being fetched by the composable's onMounted hook
});

</script>

<style scoped>
.notifications-page {
  max-width: 900px;
  margin: 2rem auto;
  padding: 1rem;
  font-family: sans-serif;
}

.card {
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  padding: 1.5rem;
  margin-bottom: 2rem;
}

h1, h2 {
  color: #333;
}

/* Send Notification Form */
.send-notification-section form {
  display: grid;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

label {
  margin-bottom: 0.5rem;
  font-weight: bold;
  color: #555;
}

input[type="text"],
textarea,
select {
  padding: 0.75rem;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: 1rem;
}

textarea {
  min-height: 100px;
  resize: vertical;
}

button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  background-color: #007bff;
  color: white;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

button:hover {
  background-color: #0056b3;
}

button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

/* Received Notifications List */
.received-notifications-section .actions {
  margin-bottom: 1rem;
  text-align: right;
}

.notifications-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.notification-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #eee;
  transition: background-color 0.2s;
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item.is-read {
  opacity: 0.6;
  background-color: #f9f9f9;
}

.notification-content {
  flex-grow: 1;
}

.notification-content p {
  margin: 0.25rem 0;
  color: #666;
}

.notification-content small {
  color: #999;
}

.mark-read-btn {
  background-color: #28a745;
  font-size: 0.8rem;
  padding: 0.4rem 0.8rem;
}

.mark-read-btn:hover {
  background-color: #218838;
}

.no-notifications, .loading {
  text-align: center;
  padding: 2rem;
  color: #777;
}

.error-message {
  color: #dc3545;
  margin-top: 0.5rem;
}

.success-message {
  color: #28a745;
  margin-top: 0.5rem;
}
</style>