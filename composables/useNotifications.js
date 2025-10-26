// composables/useNotifications.js
import { ref, computed, onMounted, onUnmounted } from 'vue'

export const useNotifications = () => {
  const notifications = ref([])
  const unreadCount = ref(0)
  const isLoading = ref(false)
  const error = ref(null)
  const userId = ref(null)
  const authData = ref(null)

  const { $api } = useNuxtApp()

  const publicVapidKey = "BCcIPD0QlNkfi3Zaw93Sd0D7Y5WvZlLAlaDfsjppa3yeYkLo_f_t0p1dEPy-mgUYN3Yb_Fz8CegClBa8ymz_xeQ"

  let polling = null
  let lastNotificationId = null

  /* ------------------------------------
   🧠 Vérification support navigateur
  ------------------------------------ */
  const checkSupport = () => ({
    push: 'PushManager' in window,
    sw: 'serviceWorker' in navigator,
    permission: Notification.permission
  })

  /* ------------------------------------
   🔑 Demander permission notification
  ------------------------------------ */
  const requestPermission = async () => {
    if (!('Notification' in window)) return 'unsupported'
    if (Notification.permission === 'default') {
      await Notification.requestPermission()
    }
    return Notification.permission
  }

    const markAsRead = async (notificationId) => {
    const notif = notifications.value.find(n => n.id === notificationId)
    if (!notif || notif.is_read) return

    const original = notif.is_read
    notif.is_read = 1
    unreadCount.value = Math.max(0, unreadCount.value - 1)

    try {
      const response = await $api(
        `/backend/notificationApi.php?action=markRead&notification_id=${notificationId}&user_id=${userId.value}`,
        { method: 'GET' }
      )
      if (!response.success) throw new Error(response.error)
    } catch (e) {
      notif.is_read = original
      unreadCount.value++
      console.error(e)
    }
  }

  const markAllAsRead = async () => {
    const backup = JSON.parse(JSON.stringify(notifications.value))
    const backupCount = unreadCount.value

    notifications.value.forEach(n => (n.is_read = 1))
    unreadCount.value = 0

    try {
      const response = await $api(`/backend/notificationApi.php?action=markAllRead&user_id=${userId.value}`, {
        method: 'GET',
      })
      if (!response.success) throw new Error(response.error)
    } catch (e) {
      notifications.value = backup
      unreadCount.value = backupCount
      console.error(e)
    }
  }

  /* ------------------------------------
   🔔 Notification locale
  ------------------------------------ */
  const showBrowserNotification = (notif) => {
    if (!('Notification' in navigator) || Notification.permission !== 'granted') return

    const title = notif.title || 'Nouvelle notification'
    const options = {
      body: notif.message || notif.text || 'Vous avez une nouvelle notification.',
      icon: notif.icon || '/favicon.ico',
      tag: `notif-${notif.id || Date.now()}`,
      renotify: true,
    }

    const notification = new Notification(title, options)
    notification.onclick = () => {
      window.focus()
      if (notif.link) window.open(notif.link, '_blank')
    }
  }

  /* ------------------------------------
   🌐 Récupération depuis backend
  ------------------------------------ */
  const fetchNotifications = async (sinceId = null) => {
    if (isLoading.value && !sinceId) return
    if (!userId.value) return

    isLoading.value = true
    error.value = null

    try {
      const url = sinceId
        ? `https://management.hoggari.com/backend/notificationApi.php?action=listNotifications&user_id=${userId.value}&since_id=${sinceId}`
        : `https://management.hoggari.com/backend/notificationApi.php?action=listNotifications&user_id=${userId.value}`

      const res = await fetch(url)

      if (!res.ok) {
        const text = await res.text() // récupérer le message brut du serveur
        throw new Error(`HTTP ${res.status}: ${text}`)
      }

      const data = await res.json() // ok, c'est du JSON valide
      // ... traiter data
    } catch (err) {
      console.error('Error fetching notifications:', err)
    }

  }

  /* ------------------------------------
   📬 Enregistrement Push
  ------------------------------------ */
  const registerPushNotifications = async (uid = null) => {
    try {
      if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return { success: false, message: 'Notifications push non supportées.' }
      }

      const registration = await navigator.serviceWorker.register('/sw.js')
      await navigator.serviceWorker.ready

      const permission = await Notification.requestPermission()
      if (permission !== 'granted') {
        return { success: false, message: 'Permission refusée.' }
      }

      let subscription = await registration.pushManager.getSubscription()
      if (!subscription) {
        subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: urlBase64ToUint8Array(publicVapidKey),
        })
      }

      const auth = JSON.parse(localStorage.getItem('auth') || '{}')
      const user_id = uid || auth.id
      if (!user_id) return { success: false, message: 'Aucun utilisateur connecté.' }

      const res = await fetch(`https://management.hoggari.com/backend/notificationApi.php?action=subscribePush&user_id=${user_id}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ subscription }),
      }).then(r => r.json())

      if (res.success) {
        return { success: true, subscription: subscription.toJSON() }
      } else {
        return { success: false, message: res.message || 'Erreur serveur' }
      }
    } catch (err) {
      console.error('💥 Erreur d’enregistrement push :', err)
      return { success: false, message: err.message }
    }
  }

  /* ------------------------------------
   🧪 Envoi d’une notification test
  ------------------------------------ */
  const sendTestNotification = async (uid = null) => {
    const auth = JSON.parse(localStorage.getItem('auth') || '{}')
    const user_id = uid || auth.id
    if (!user_id) return { success: false, message: 'Aucun utilisateur connecté.' }

    try {
      const res = await fetch(`https://management.hoggari.com/backend/notificationApi.php?action=testPush&user_id=${user_id}`).then(r => r.json())
      return res
    } catch (err) {
      console.error('Erreur test notification:', err)
      return { success: false, message: err.message }
    }
  }

  /* ------------------------------------
   🧮 Utils
  ------------------------------------ */
  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
    const rawData = window.atob(base64)
    return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)))
  }

  /* ------------------------------------
   🔁 Polling
  ------------------------------------ */
  const startPolling = () => {
    stopPolling()
    polling = setInterval(() => fetchNotifications(lastNotificationId), 5000)
  }

  const stopPolling = () => {
    if (polling) clearInterval(polling)
  }

  /* ------------------------------------
   🌱 Lifecycle
  ------------------------------------ */
  onMounted(async () => {
    const auth = localStorage.getItem('auth')
    if (auth) {
      authData.value = JSON.parse(auth)
      if (authData.value) {
        userId.value = authData.value.id
        await fetchNotifications()
        startPolling()
      }
    }
  })

  onUnmounted(stopPolling)

  return {
    // 🔔 notifications classiques
    notifications: computed(() => notifications.value),
    unreadCount: computed(() => unreadCount.value),
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),
    fetchNotifications,

    // 🔑 gestion push
    checkSupport,
    registerPushNotifications,
    sendTestNotification,
    markAllAsRead,
    markAsRead
  }
}
