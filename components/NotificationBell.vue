<template>
  <div class="notification-bell-container" ref="bellContainer">
    <button @click="toggleDropdown" class="bell-button">
      <!-- Icône de la cloche (SVG ou font-icon) -->
      <div v-html="resizeSvg(icons['bell'], 24, 24)">

      </div>

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
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useNotifications } from '~/composables/useNotifications.ts';
import NotificationDropdown from './NotificationDropdown.vue';
import icons from '~/public/icons.json'

const resizeSvg = (svg: '', width: 24, height: 24) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

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
