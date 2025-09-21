<template>
  <div v-if="visible" class="notification-bar" :class="`notification--${notificationType}`">
    <div class="notification-bar__content">
      <span class="notification-bar__message">{{ message }}</span>
    </div>
    <button @click="hide" class="notification-bar__close">
      &times;
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

interface Props {
  message: string;
  type?: 'info' | 'success' | 'warning' | 'error';
  duration?: number;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'info',
  duration: 5000, // 5 seconds
});

const visible = ref(false);
const notificationType = ref(props.type);

let timeoutId: ReturnType<typeof setTimeout> | null = null;

const show = (newMessage: string, newType: Props['type'] = 'info') => {
  if (timeoutId) {
    clearTimeout(timeoutId);
  }
  message.value = newMessage;
  notificationType.value = newType;
  visible.value = true;
  timeoutId = setTimeout(hide, props.duration);
};

const hide = () => {
  visible.value = false;
  if (timeoutId) {
    clearTimeout(timeoutId);
    timeoutId = null;
  }
};

// Expose the show method to be called from the parent
defineExpose({
  show,
});

// Internal state for the message, as props shouldn't be mutated.
const message = ref(props.message);

watch(() => props.message, (newMessage) => {
    if(newMessage) {
        show(newMessage, props.type);
    }
});

</script>

<style scoped>
.notification-bar {
  position: fixed;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  max-width: 600px;
  padding: 1rem 1.5rem;
  margin: 1rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 1000;
  color: #fff;
  font-family: sans-serif;
  animation: slide-down 0.5s ease-out forwards;
  opacity: 0;
}

@keyframes slide-down {
  from {
    top: -100px;
    opacity: 0;
  }
  to {
    top: 0;
    opacity: 1;
  }
}

.notification-bar__content {
  flex-grow: 1;
}

.notification-bar__message {
  font-size: 1rem;
}

.notification-bar__close {
  background: none;
  border: none;
  color: inherit;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  padding: 0 0 0 1rem;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.notification-bar__close:hover {
  opacity: 1;
}

/* Color Variants */
.notification--info {
  background-color: #2f86eb;
}
.notification--success {
  background-color: #34a853;
}
.notification--warning {
  background-color: #fbbc05;
  color: #333;
}
.notification--error {
  background-color: #ea4335;
}
</style>
