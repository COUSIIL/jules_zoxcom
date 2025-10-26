<template>
  <div class="notif-page">
    <h1>🔔 Paramètres des notifications</h1>

    <div class="card">
      <h2>État du système</h2>
      <ul>
        <li>
          <span>Support navigateur :</span>
          <strong :class="support.push ? 'ok' : 'fail'">
            {{ support.push ? '✅ Compatible' : '❌ Non compatible' }}
          </strong>
        </li>
        <li>
          <span>Service Worker :</span>
          <strong :class="support.sw ? 'ok' : 'fail'">
            {{ support.sw ? '✅ Actif' : '❌ Inactif' }}
          </strong>
        </li>
        <li>
          <span>Permission notification :</span>
          <strong
            :class="{
              ok: support.permission === 'granted',
              fail: support.permission === 'denied'
            }"
          >
            {{
              support.permission === 'granted'
                ? '✅ Accordée'
                : support.permission === 'denied'
                ? '❌ Refusée'
                : '🕓 En attente'
            }}
          </strong>
        </li>
        <li>
          <span>Subscription enregistrée :</span>
          <strong :class="subscription ? 'ok' : 'fail'">
            {{ subscription ? '✅ Oui' : '❌ Non' }}
          </strong>
        </li>
      </ul>
    </div>

    <div class="actions">
      <button @click="enableNotifications" :disabled="support.permission === 'granted'">
        🔑 Activer les notifications
      </button>

      <button @click="refreshSubscription" :disabled="!subscription">
        🔁 Mettre à jour la subscription
      </button>

      <button @click="testNotification" :disabled="!subscription">
        🚀 Tester une notification
      </button>
    </div>

    <div v-if="message" class="result">{{ message }}</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotifications } from '@/composables/useNotifications.js'

const { registerPushNotifications, sendTestNotification, checkSupport } = useNotifications()

const subscription = ref(null)
const support = ref({
  push: false,
  sw: false,
  permission: 'default'
})
const message = ref('')

// 🧠 Charger l’état initial
onMounted(async () => {
  support.value = checkSupport()
  if (!support.value.sw || !support.value.push) return

  try {
    const reg = await navigator.serviceWorker.ready
    const sub = await reg.pushManager.getSubscription()
    if (sub) subscription.value = sub.toJSON()
  } catch (err) {
    console.warn('SW non prêt :', err)
  }
})

// 🔑 Activer les notifications via le composable
async function enableNotifications() {
  message.value = '⏳ Activation en cours...'
  const result = await registerPushNotifications()
  if (result.success) {
    subscription.value = result.subscription
    support.value.permission = 'granted'
    message.value = '✅ Notifications activées avec succès !'
  } else {
    message.value = '❌ ' + (result.message || 'Erreur lors de l’activation.')
  }
}

// 🔁 Mettre à jour / réenregistrer la subscription
async function refreshSubscription() {
  try {
    message.value = '♻️ Vérification de la subscription...';

    const reg = await navigator.serviceWorker.ready;
    let sub = await reg.pushManager.getSubscription();

    // Si subscription existante, on l’utilise
    if (!sub) {
      // Créer une nouvelle subscription si aucune n’existe
      sub = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicVapidKey),
      });
      console.log('🆕 Nouvelle subscription créée:', sub);
    } else {
      console.log('✅ Subscription existante:', sub);
    }

    // Envoi au backend
    const auth = JSON.parse(localStorage.getItem('auth') || '{}');
    const user_id = auth.id;
    if (!user_id) throw new Error('Aucun utilisateur connecté.');

    const res = await fetch(
      `https://management.hoggari.com/backend/notificationApi.php?action=subscribePush&user_id=${user_id}`,
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ subscription: sub.toJSON() }),
      }
    ).then(r => r.json());

    if (res.success) {
      subscription.value = sub.toJSON();
      message.value = '✅ Subscription mise à jour avec succès !';
    } else {
      message.value = '❌ ' + (res.message || 'Erreur serveur');
    }
  } catch (err) {
    console.error(err);
    message.value = '💥 Erreur : ' + err.message;
  }
}


// 🚀 Envoyer une notification test
async function testNotification() {
  message.value = '📡 Envoi de la notification...'
  const result = await sendTestNotification()
  if (result.success) {
    message.value = `✅ Notification test envoyée (${result.sent}/${result.total})`
  } else {
    message.value = `❌ ${result.message}`
  }
}
</script>

<style scoped>
.notif-page {
  max-width: 600px;
  margin: 40px auto;
  padding: 20px;
  font-family: system-ui;
}
.card {
  background: #fafafa;
  border-radius: 10px;
  padding: 15px 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.card ul {
  list-style: none;
  padding: 0;
}
.card li {
  display: flex;
  justify-content: space-between;
  padding: 8px 0;
  border-bottom: 1px solid #eee;
}
.ok { color: green; }
.fail { color: red; }
.actions {
  margin-top: 20px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}
button {
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  background: #007aff;
  color: white;
  transition: 0.2s;
}
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.result {
  margin-top: 15px;
  font-weight: 600;
}
</style>
