<template>
  <div class="notifications-page">
    <div class="header">
      <h1>🔔 Centre de Notifications</h1>
      <button
        v-if="unreadCount > 0"
        @click="markAllAsRead"
        class="action-btn"
      >
        Tout marquer comme lu
      </button>
    </div>

    <!-- Section pour afficher les notifications reçues -->
    <div class="notifications-container">
      <div v-if="isLoading" class="loading-state">
         <div class="spinner"></div> Chargement...
      </div>
      <div v-else-if="error" class="error-state">
        {{ error }}
      </div>

      <div v-else-if="notifications.length > 0" class="notifications-list">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="notif-card"
          :class="{ 'is-unread': !notification.is_read }"
        >
          <div class="notif-icon">
             <span v-if="notification.type === 'success'">✅</span>
             <span v-else-if="notification.type === 'warning'">⚠️</span>
             <span v-else-if="notification.type === 'error'">❌</span>
             <span v-else>ℹ️</span>
          </div>
          <div class="notif-content">
            <div class="notif-header">
              <h3 class="notif-title">{{ notification.title }}</h3>
              <span class="notif-time">{{ formatDate(notification.created_at) }}</span>
            </div>
            <p class="notif-body">{{ notification.body }}</p>
          </div>
          <div class="notif-actions" v-if="!notification.is_read">
            <button @click="markAsRead(notification.id)" class="read-btn" title="Marquer comme lu">
              ✓
            </button>
          </div>
        </div>
      </div>

      <div v-else class="empty-state">
        <div class="empty-icon">📭</div>
        <p>Aucune notification pour le moment.</p>
      </div>
    </div>

    <!-- Section Admin (Collapsible) -->
    <div class="admin-section" v-if="isAdmin">
        <details>
            <summary>Envoyer une notification (Admin)</summary>
            <div class="send-form">
                <div class="form-group">
                    <label>Destinataire</label>
                    <select v-model="selectedUser">
                        <option value="">Choisir...</option>
                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.username }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Titre</label>
                    <input v-model="notificationTitle" type="text" placeholder="Titre">
                </div>
                <div class="form-group">
                    <label>Message</label>
                    <textarea v-model="notificationBody" placeholder="Message"></textarea>
                </div>
                <button @click="sendNotification" :disabled="isSending" class="send-btn">Envoyer</button>
                <div v-if="sendSuccess" class="success-msg">Envoyé!</div>
            </div>
        </details>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNotifications } from '~/composables/useNotifications.js';

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

// Admin / Send Logic
const users = ref([]);
const selectedUser = ref('');
const notificationTitle = ref('');
const notificationBody = ref('');
const isSending = ref(false);
const sendSuccess = ref(false);

// Simple admin check
const isAdmin = ref(false);

onMounted(() => {
    // Check if admin
    if (typeof localStorage !== 'undefined') {
        const auth = JSON.parse(localStorage.getItem('auth') || '{}');
        if (auth.role === 'admin' || auth.role_id === 1) {
            isAdmin.value = true;
            fetchUsers();
        }
    }
});

const fetchUsers = async () => {
    try {
        const res = await fetch('https://management.hoggari.com/backend/sql/get/users.php').then(r => r.json());
        if (res.success) users.value = res.data;
    } catch(e) { console.error(e) }
}

const sendNotification = async () => {
    if (!selectedUser.value || !notificationTitle.value) return;
    isSending.value = true;
    try {
        const res = await fetch('https://management.hoggari.com/backend/notificationApi.php?action=createNotification', {
            method: 'POST',
            body: JSON.stringify({
                title: notificationTitle.value,
                body: notificationBody.value,
                targets: [{ type: 'user_id', value: selectedUser.value }]
            })
        }).then(r => r.json());

        if (res.success) {
            sendSuccess.value = true;
            notificationTitle.value = '';
            notificationBody.value = '';
            setTimeout(() => sendSuccess.value = false, 3000);
        }
    } catch(e) {
        console.error(e);
    } finally {
        isSending.value = false;
    }
}

function formatDate(dateStr) {
    if(!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
}
</script>

<style scoped>
.notifications-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  min-height: 80vh;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.header h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-text);
}

.action-btn {
  background: var(--color-primary, #007aff);
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 20px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: opacity 0.2s;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
.action-btn:hover { opacity: 0.9; }

/* List */
.notifications-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.notif-card {
    background: var(--color-whitly, #fff);
    border-radius: 12px;
    padding: 15px;
    display: flex;
    gap: 15px;
    align-items: flex-start;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border-left: 4px solid transparent;
    transition: transform 0.2s, box-shadow 0.2s;
}
.notif-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.notif-card.is-unread {
    border-left-color: var(--color-primary, #007aff);
    background: var(--color-background-soft, #f8f9fa);
}

.notif-icon {
    font-size: 1.2rem;
    padding-top: 2px;
}

.notif-content {
    flex: 1;
}

.notif-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    flex-wrap: wrap;
}

.notif-title {
    font-weight: 600;
    font-size: 1rem;
    margin: 0;
    color: var(--color-text);
}

.notif-time {
    font-size: 0.8rem;
    color: #888;
}

.notif-body {
    color: #555;
    font-size: 0.95rem;
    line-height: 1.4;
    margin: 0;
}

.read-btn {
    background: none;
    border: 1px solid #ddd;
    color: #2ecc71;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
}
.read-btn:hover {
    background: #2ecc71;
    color: white;
    border-color: #2ecc71;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #888;
}
.empty-icon {
    font-size: 3rem;
    margin-bottom: 10px;
}

.loading-state {
    text-align: center;
    padding: 20px;
    color: #666;
}
.error-state {
    color: #e74c3c;
    text-align: center;
    padding: 20px;
}

/* Admin Section */
.admin-section {
    margin-top: 40px;
    border-top: 1px solid #eee;
    padding-top: 20px;
}
.send-form {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 15px;
    background: #f5f5f5;
    padding: 20px;
    border-radius: 12px;
}
.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.form-group label {
    font-weight: 600;
    font-size: 0.9rem;
}
.form-group input, .form-group textarea, .form-group select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 1rem;
}
.send-btn {
    align-self: flex-start;
    padding: 10px 20px;
    background: var(--color-primary, #007aff);
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.send-btn:disabled { opacity: 0.5; }

/* Dark Mode Support (Implicit via vars) */
.dark .notif-card {
    background: var(--color-darkow, #222);
}
.dark .notif-title { color: #eee; }
.dark .notif-body { color: #aaa; }
.dark .notif-card.is-unread {
    background: var(--color-darkly, #333);
}
.dark .send-form {
    background: var(--color-darkly, #333);
}
.dark .form-group input, .dark .form-group textarea, .dark .form-group select {
    background: var(--color-darkow, #222);
    color: white;
    border-color: #444;
}
</style>
