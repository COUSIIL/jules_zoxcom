<template>
  <div class="dropdown-panel">
    <header class="dropdown-header">
      <h3>Notifications</h3>
      <button v-if="unreadCount > 0" @click="handleMarkAllRead" class="mark-all-read-btn">
        Tout marquer comme lu
      </button>
    </header>

    <div v-if="!fixedNotifications" class="loading-state">
      Chargement...
    </div>

    <div v-else-if="fixedNotifications.length === 0" class="empty-state">
      <p>Vous n'avez aucune notification.</p>
    </div>

    <ul v-else class="notification-list">
      <li
        v-for="notification in fixedNotifications"
        :key="notification.id"
        :class="{ 'is-unread': !notification.is_read }"
        @click="handleNotificationClick(notification)"
        class="notification-item"
      >
        <div v-if="notification.type === 'system'" class="icon_box">
          <div v-html="resizeSvg(icons['purshase'], 30, 30)" class="icon_content"></div>
        </div>

        <div class="notification-content">
          <strong class="notification-title">{{ notification.title }}</strong>
          <p class="notification-body">{{ notification.body }}</p>
          <time class="notification-time">{{ formatTimeAgo(notification.created_at) }}</time>
        </div>

        <div v-if="!notification.is_read" class="unread-dot"></div>
      </li>
    </ul>

    <footer class="dropdown-footer">
      <NuxtLink to="/notifications" @click="emit('close')">
        Voir toutes les notifications
      </NuxtLink>
    </footer>
  </div>
</template>

<script setup>
import { useNotifications } from '../composables/useNotifications'
import { useRouter } from 'vue-router'
import { watch, ref, onMounted } from 'vue'

const emit = defineEmits(['close'])

const {
  notifications,
  unreadCount,
  isLoading,
  markAsRead,
  markAllAsRead
} = useNotifications()

const router = useRouter()

const icons = ref({})
onMounted(async () => {
  try {
    const res = await fetch('/icons.json')
    icons.value = await res.json()
  } catch (err) {
    console.error('Erreur de chargement des icônes :', err)
  }
})

const resizeSvg = (svg, width = 24, height = 24) => {
  if (!svg) return ''
  return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
}

const fixedNotifications = ref([])

const stop = watch(notifications, (newVal) => {
  if (newVal.length > 0) {
    fixedNotifications.value = [...newVal]
    stop()
  }
})

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    await markAsRead(notification.id)
  }
  if (notification.meta?.route) {
    router.push(notification.meta.route)
  }
  emit('close')
}

const handleMarkAllRead = async () => {
  await markAllAsRead()
}

const formatTimeAgo = (dateString) => {
  const date = new Date(dateString)
  const seconds = Math.floor((new Date().getTime() - date.getTime()) / 1000)
  let interval = seconds / 31536000
  if (interval > 1) return Math.floor(interval) + ' ans'
  interval = seconds / 2592000
  if (interval > 1) return Math.floor(interval) + ' mois'
  interval = seconds / 86400
  if (interval > 1) return 'il y a ' + Math.floor(interval) + ' j'
  interval = seconds / 3600
  if (interval > 1) return 'il y a ' + Math.floor(interval) + ' h'
  interval = seconds / 60
  if (interval > 1) return 'il y a ' + Math.floor(interval) + ' min'
  return "à l'instant"
}
</script>


<style>
.dropdown-panel {
  position: fixed;
  top: 50px;
  left: 50%;
  transform: translateX(-50%);
  width: 90%;
  max-width: 600px;
  max-height: 450px;
  margin-top: 1rem;
  background-color: var(--color-whitly);
  border: 1px solid var(--color-whity);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  z-index: 900;
}

.dark .dropdown-panel {
  background-color: var(--color-darkow);
  border: 1px solid var(--color-darkly);
}

.icon_box {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #c6dcff52;
  margin-right: 10px;
}

.icon_content {
  width: 25px;
  height: 25px;
  margin: 7.5px;
  color: var(--color-blumy);
}

.dropdown-header,
.dropdown-footer {
  padding: 12px 16px;
  border-bottom: 1px solid var(--color-whiby);
}

.dark .dropdown-header,
.dropdown-footer {
  border-bottom: 1px solid var(--color-darkiw);
}

.dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dropdown-header h3 {
  margin: 0;
  font-size: 16px;
}

.mark-all-read-btn {
  background: none;
  border: none;
  color: var(--color-blumy);
  font-size: 12px;
  cursor: pointer;
}

.notification-list {
  list-style: none;
  margin: 0;
  padding: 0;
  overflow-y: auto;
  flex-grow: 1;
}

.notification-item {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  cursor: pointer;
  transition: background-color 0.2s;
  border-bottom: 1px solid #f1f5f9;
}

.notification-item:hover {
  background-color: #f8fafc;
}

.notification-item.is-unread {
  font-weight: bold;
  background-color: var(--color-whitly);
}

.dark .notification-item.is-unread {
  background-color: var(--color-darkow);
}

.notification-content {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.notification-title {
  font-size: 14px;
  color: #1e293b;
}

.notification-body {
  font-size: 13px;
  color: #475569;
  margin: 4px 0 0;
}

.notification-time {
  font-size: 11px;
  color: #64748b;
  margin-top: 4px;
}

.unread-dot {
  width: 8px;
  height: 8px;
  background-color: #3b82f6;
  border-radius: 50%;
  margin-left: 16px;
  flex-shrink: 0;
}

.loading-state,
.empty-state {
  padding: 40px 16px;
  text-align: center;
  color: #64748b;
}

.dropdown-footer {
  text-align: center;
  border-top: 1px solid #e2e8f0;
  border-bottom: none;
}

.dropdown-footer a {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}
</style>
