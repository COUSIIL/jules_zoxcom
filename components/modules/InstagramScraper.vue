<template>
  <div>
    <input v-model="keyword" placeholder="Enter keyword" />
    <button @click="fetchData">Fetch Instagram Data</button>
    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>
    <div v-if="data">
      <pre>{{ data }}</pre>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const keyword = ref('')
const data = ref(null)
const loading = ref(false)
const error = ref(null)

const fetchData = async () => {
  loading.value = true
  error.value = null
  data.value = null
  try {
    const response = await fetch(`/backend/yizzra/yizzraApi.php?keyword=${keyword.value}`)
    if (!response.ok) {
      throw new Error('Network response was not ok')
    }
    const result = await response.json()
    data.value = result
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}
</script>
