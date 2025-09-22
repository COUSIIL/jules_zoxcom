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

// Global shared state
const isLargeScreen = useState('isLargeScreen', () => false);
const isAuth = useState('isAuth', () => false);
const loading = ref(true);

const router = useRouter();
const notificationBar = ref(null);

// Notifications
const { notifications } = useNotifications();

watch(notifications, (newNotifications, oldNotifications) => {
  const newCount = newNotifications.length - oldNotifications.length;
  if (newCount > 0) {
    const addedNotifications = newNotifications.slice(0, newCount).reverse();
    addedNotifications.forEach((notification, index) => {
      setTimeout(() => {
        if (notificationBar.value) {
          notificationBar.value.show(notification.title, notification.type);
        }
      }, index * 6000); // Stagger notifications to appear one after another
    });
  }
}, { deep: true });


function checkScreenSize() {
  // window is only available on the client
  if (process.client) {
    isLargeScreen.value = window.matchMedia('(min-width: 1024px)').matches;
  }
}

onMounted(() => {
  // This code runs only on the client, so window and localStorage are available
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
