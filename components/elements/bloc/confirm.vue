<template>
  <nav v-if="isVisible && isMounted" class="overlay">
    <div class="modal1">
      <DotLottieVue
        style="height: 50px; width: 50px"
        src="/animations/important.lottie"
        autoplay
        loop
      />
      <p>{{ message }}</p>
      <div class="buttons">
        <button class="confirm" @click="$emit('confirm')">Yes</button>
        <button class="cancel" @click="$emit('cancel')">No</button>
      </div>
    </div>
  </nav>
</template>

<script>
import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

export default {
  name: 'Confirm',
  components: {
    DotLottieVue,
  },
  data() {
    return {
      isMounted: false,
    }
  },
  props: {
    isVisible: {
      type: Boolean,
      default: false,
    },
    message: {
      type: String,
      default: "You confirm this action ?",
    },
  },
  mounted() {
    this.isMounted = true
  },
}
</script>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 5000;
  animation: fadeIn 0.25s ease-in-out;
}

.modal1 {
  background: var(--color-whity);
  color: var(--color-darky);
  padding: 24px 28px;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  animation: scaleIn 0.3s ease;
  min-width: 260px;
  max-width: 300px;
  transition: all 0.3s ease;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

.dark .modal1 {
  background: var(--color-darkly);
  color: var(--color-whity);
}

.modal1 p {
  font-size: 16px;
  margin: 20px 0;
}

.buttons {
  display: flex;
  justify-content: center;
  gap: 12px;
}

.buttons button {
  padding: 10px 18px;
  font-size: 14px;
  font-weight: 500;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: transform 0.2s ease, background-color 0.3s ease;
}

.confirm {
  background-color: #4caf50;
  color: white;
}

.confirm:hover {
  background-color: #43a047;
}

.cancel {
  background-color: #f44336;
  color: white;
}

.cancel:hover {
  background-color: #e53935;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes scaleIn {
  from {
    transform: scale(0.95);
    opacity: 0;
  }
  to {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
