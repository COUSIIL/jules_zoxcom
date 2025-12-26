<template>

  <Header />
  <NuxtPage />
  <NotificationBar ref="notificationBar" message="" />

</template>

<script setup>
import { useRouter } from 'vue-router';
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import Header from './components/webNavBar.vue';
import NotificationBar from './components/NotificationBar.vue';
import { useNotifications } from './composables/useNotifications';

const isLargeScreen = useState('isLargeScreen', () => false);
const isAuth = useState('isAuth', () => false);
const loading = ref(true);

const router = useRouter();
const notificationBar = ref(null);

// Notifications
const { notifications } = useNotifications();

// 👉 On garde le dernier ID affiché
let lastShownId = 0;

watch(notifications, (newNotifications) => {
  // ✅ Sécurité : s'assurer qu'on a bien un tableau
  if (!Array.isArray(newNotifications) || newNotifications.length === 0) return;

  // ✅ Vérifie s'il reste des notifications non lues
  const unread = newNotifications.filter(n => !n.is_read || n.is_read == 0);
  if (unread.length === 0) return; // ⛔️ Stop si tout est lu

  // On récupère les nouvelles notifications (id > lastShownId)
  const newOnes = unread.filter(n => n && n.id && n.id > lastShownId);

  if (newOnes.length > 0) {
    // Mise à jour du dernier id affiché (le plus grand)
    lastShownId = Math.max(...newOnes.map(n => n.id));

    // ✅ Les afficher une par une avec un décalage
    newOnes.reverse().forEach((notif) => {
      notificationBar.value?.show(notif.title, notif.type);
    });
  }
}, { deep: true });



function checkScreenSize() {
  if (process.client) {
    isLargeScreen.value = window.matchMedia('(min-width: 1024px)').matches;
  }
}


onMounted(() => {
  if (!process.client) return;

  try {
    const auth = JSON.parse(localStorage.getItem('auth'));
    isAuth.value = !!auth;
    if (!auth) router.push('/connexion');
  } catch (e) {
    console.error("Failed to parse auth status from localStorage", e);
    isAuth.value = false;
    router.push('/connexion');
  } finally {
    loading.value = false;
  }

  checkScreenSize();
  window.addEventListener('resize', checkScreenSize);
});


onBeforeUnmount(() => {
  if (process.client) {
    window.removeEventListener('resize', checkScreenSize);
  }
});
</script>


