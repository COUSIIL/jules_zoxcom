<template>
  <div class="spacer"></div>
  <div class="container">
      <h2>{{ t('transaction configuration') }}</h2>
      <p>{{ t('transaction distribution settings coming soon') }}</p>
      <!-- Placeholder for transaction modal logic -->
  </div>
  <div class="spacer"></div>
</template>

<script setup>
const { t } = useLang()

const props = defineProps({
  modelValue: { type: Object, required: true }
})
</script>

<style scoped>
.spacer{ height: 80px; }
.container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 90%;
    background-color: var(--color-whitly);
    border-radius: 10px;
    padding: 20px;
}
.dark .container {
    background-color: var(--color-darkly);
}
</style>