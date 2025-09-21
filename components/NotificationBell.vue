<template>
  <div class="notification-bell-container" ref="bellContainer">
    <button @click="toggleDropdown" class="bell-button">
      <!-- Icône de la cloche (SVG ou font-icon) -->
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
        <path d="M13.73 21a2 2 0 0 1-3.46 0" />
      </svg>

      <!-- Badge de notifications non lues -->
      <span v-if="unreadCount > 0" class="unread-badge">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown qui s'affiche/se masque -->
    <NotificationDropdown v-if="isDropdownOpen" @close="closeDropdown" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useNotifications } from '~/composables/useNotifications';
import NotificationDropdown from './NotificationDropdown.vue';

const { unreadCount } = useNotifications();

const isDropdownOpen = ref(false);
const bellContainer = ref<HTMLElement | null>(null);

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = () => {
  isDropdownOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
  if (bellContainer.value && !bellContainer.value.contains(event.target as Node)) {
    closeDropdown();
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.notification-bell-container {
  position: relative;
  display: inline-block;
}

.bell-button {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  width: 40px;
  height: 40px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
  border-radius: 50%;
  transition: background-color 0.2s;
  margin: 5px;
}

.bell-button:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.bell-button svg {
  color: #333;
}

.unread-badge {
  position: absolute;
  top: 0px;
  right: 0px;
  background-color: #e53e3e; /* Rouge */
  color: white;
  border-radius: 50%;
  padding: 2px 5px;
  font-size: 10px;
  font-weight: bold;
  line-height: 1;
  text-align: center;
  min-width: 18px;
  box-sizing: border-box;
  border: 2px solid white;
}
</style>
