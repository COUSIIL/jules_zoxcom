<template>
  <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;" v-if="isMounted">
    <WebStore />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import WebStore from '~/components/store/webStore.vue'  // ✅ Utilise `~` pour Nuxt 3

const isMounted = ref(false)

onMounted(() => {
  isMounted.value = true
})
</script>

<style>
/* ton style ici */
</style>
