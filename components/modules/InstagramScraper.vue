<template>
  <div class="instagram-fetcher">
    <input
      v-model="keyword"
      type="text"
      placeholder="🔎 Entrez un mot-clé (ex: fashion)"
      @keyup.enter="fetchData"
    />

    <button @click="fetchData" :disabled="loading">
      {{ loading ? 'Chargement...' : 'Rechercher' }}
    </button>

    <div v-if="error" class="error">❌ {{ error }}</div>

    <div v-if="data" class="result">
      <h3>📦 Résultat :</h3>
      <pre>{{ JSON.stringify(data, null, 2) }}</pre>
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
  // ✅ Validation avant l’envoi
  if (!keyword.value.trim()) {
    error.value = 'Veuillez entrer un mot-clé.'
    return
  }

  loading.value = true
  error.value = null
  data.value = null

  try {
    // ✅ Appel correct avec le paramètre "action"
    const url = `https://management.hoggari.com/backend/yizzra/yizzraApi.php?action=scrapInstagram&keyword=${encodeURIComponent(keyword.value.trim())}`

    const response = await fetch(url)

    if (!response.ok) {
      throw new Error(`Erreur réseau (${response.status})`)
    }

    const result = await response.json()

    if (result.status === 'error') {
      throw new Error(result.message || 'Erreur API inconnue')
    }

    data.value = result
  } catch (e) {
    error.value = e.message || 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.instagram-fetcher {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  max-width: 600px;
  margin: 2rem auto;
  font-family: sans-serif;
}

input {
  padding: 0.5rem 0.8rem;
  border: 1px solid #ccc;
  border-radius: 6px;
}

button {
  padding: 0.5rem 0.8rem;
  border: none;
  border-radius: 6px;
  background-color: #007bff;
  color: white;
  cursor: pointer;
  transition: background 0.2s;
}

button:hover {
  background-color: #0056b3;
}

.error {
  color: red;
  font-weight: bold;
}

.result {
  background: #f7f7f7;
  padding: 1rem;
  border-radius: 6px;
  white-space: pre-wrap;
}
</style>
