<template>
  <div>
    <Header />
    <NuxtPage />
    <NotificationBar ref="notificationBar" message="" />
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
import Header from '~/plugin/webNavBar.vue';
import NotificationBar from '~/components/NotificationBar.vue';
import { useNotifications } from '~/composables/useNotifications';

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
  if (newNotifications.length === 0) return;

  // On récupère toutes les notifs avec un id > lastShownId
  const newOnes = newNotifications.filter(n => n.id > lastShownId);

  if (newOnes.length > 0) {
    // Mise à jour du dernier id affiché (le plus grand)
    lastShownId = Math.max(...newOnes.map(n => n.id));

    // Les afficher une par une avec un décalage
    newOnes.reverse().forEach((notif, index) => {
      setTimeout(() => {
        notificationBar.value?.show(notif.title, notif.type);
      }, index * 6000); // délai entre chaque notif
    });
  }
}, { deep: true });

function checkScreenSize() {
  if (process.client) {
    isLargeScreen.value = window.matchMedia('(min-width: 1024px)').matches;
  }
}


onMounted(() => {
  try {
    const auth = JSON.parse(localStorage.getItem('auth'));
    if (auth) {
      isAuth.value = true;
    } else {
      isAuth.value = false;
      router.push('/connexion');
    }
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

<style scoped>
div {
  text-align: center;
}
</style>
