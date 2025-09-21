<template>
  <div v-if="notification" class="notification-bar" :class="`notification-bar--${notification.type}`">
    <div class="notification-bar__content">
      <span class="notification-bar__title">{{ notification.title }}</span>
      <span class="notification-bar__body">{{ notification.body }}</span>
    </div>
    <button class="notification-bar__close" @click="dismiss">&times;</button>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  notification: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['dismiss']);

const dismiss = () => {
  emit('dismiss');
};
</script>

<style scoped>
.notification-bar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  padding: 1rem;
  background-color: #333;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1000;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.notification-bar--info {
  background-color: #2979ff;
}

.notification-bar--success {
  background-color: #00c853;
}

.notification-bar--warning {
  background-color: #ff9100;
}

.notification-bar--error {
  background-color: #d50000;
}

.notification-bar--system {
    background-color: #607d8b;
}


.notification-bar__content {
  display: flex;
  flex-direction: column;
}

.notification-bar__title {
  font-weight: bold;
}

.notification-bar__body {
  font-size: 0.9rem;
}

.notification-bar__close {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
}
</style>
