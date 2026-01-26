<template>
  <div class="notif-page">

    <div class="card">
      <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
        <span style="display: flex; align-items: center; justify-content: center; gap: 10px;">
          <div v-html="resizeSvg(icons['bell'], 24, 24)">

          </div>
          <h1>Notifications</h1>
        </span>
        
        <Radio :selected="userSettings.notifications" @changed="saveSettings" />
      </div>
    </div>
    <div v-if="message" class="result">{{ message }}

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useNotifications } from '@/composables/useNotifications.js'
import Radio from './bloc/radio.vue';

import icons from '../../public/icons.json'



const { registerPushNotifications, sendTestNotification, checkSupport } = useNotifications()

const subscription = ref(null)
const support = ref({
  push: false,
  sw: false,
  permission: 'default'
})
const message = ref('')
const userSettings = ref({ notifications: true })


var resizeSvg = (svg, width, height) => {
  return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
}

// 🧠 Charger l’état initial
onMounted(async () => {
  fetchSettings()
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

async function fetchSettings() {
    if (typeof localStorage === 'undefined') return
    const auth = JSON.parse(localStorage.getItem('auth') || '{}')
    if (!auth.token) return

    try {
        const res = await fetch('https://management.hoggari.com/backend/api.php?action=checkConnexion', {
            method: 'POST',
            body: JSON.stringify({ token: auth.token })
        }).then(r => r.json())

        if (res.success && res.data.settings) {
            // Merge with default to ensure keys exist
            userSettings.value = { ...userSettings.value, ...res.data.settings }
        }
    } catch (e) {
        console.error("Error fetching settings:", e)
    }
}

async function saveSettings() {
     const auth = JSON.parse(localStorage.getItem('auth') || '{}')
     if (!auth.id) return

     try {
         const res = await fetch('https://management.hoggari.com/backend/api.php?action=updateUserSettings', {
             method: 'POST',
             body: JSON.stringify({
                 id: auth.id,
                 settings: userSettings.value
             })
         }).then(r => r.json())

         if (res.success) {
             message.value = '✅ Préférences enregistrées'
             setTimeout(() => message.value = '', 3000)
         } else {
             message.value = '❌ Erreur enregistrement'
         }
     } catch (e) {
         console.error(e)
         message.value = '❌ Erreur de connexion'
     }
}

// 🔑 Activer les notifications via le composable
async function enableNotifications() {
  if(subscription.value) {
    subscription.value = false
  } else {
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
.pref-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
}
.radio-group {
    display: flex;
    gap: 15px;
}
.radio-label {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    font-weight: 500;
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
