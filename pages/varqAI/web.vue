<template>
  <div class="wrap">
    <h1>Image to HTML Converter</h1>
    <p class="subtitle">Convertissez une maquette image en landing page HTML/CSS/JS avec Kie.</p>

    <div class="card">
      <div class="uploader-section">
        <label class="lbl">Image de la maquette</label>
        <div class="selector-row">
          <Gbtn
            :text="selectedImageUrl ? 'Changer l\'image' : 'Sélectionner une image'"
            @click="showExplorer = true"
            color="#7D698E"
            :svg="icons['imageImg']"
          />
          <div v-if="selectedImageUrl" class="selected-thumb">
            <img :src="selectedImageUrl" alt="Selected" />
          </div>
        </div>
      </div>

      <div class="actions">
        <Gbtn
          text="Convertir en HTML"
          :disabled="!selectedImageUrl || isLoading"
          @click="startConversion"
          color="#007aff"
          svg=""
        />
      </div>

      <div v-if="isLoading" class="loading-indicator">
        <div class="spinner"></div>
        <p>L'IA génère le code... ({{ generatedLength }} caractères)</p>
      </div>

      <p v-if="error" class="err">{{ error }}</p>
    </div>

    <div v-if="cleanHtml" class="card result-section">
      <div class="tabs">
        <button :class="{ active: viewMode === 'preview' }" @click="viewMode = 'preview'">Aperçu</button>
        <button :class="{ active: viewMode === 'code' }" @click="viewMode = 'code'">Code</button>
      </div>

      <div v-if="viewMode === 'preview'" class="html-preview">
        <iframe :srcdoc="cleanHtml" frameborder="0"></iframe>
      </div>

      <div v-else class="code-view">
        <div class="code-header">
          <button @click="copyToClipboard">Copier le code</button>
        </div>
        <pre><code>{{ cleanHtml }}</code></pre>
      </div>
    </div>

    <Explorer
      :show="showExplorer"
      @confirm="handleImageSelected"
      @cancel="showExplorer = false"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import Gbtn from '~/components/elements/bloc/gBtn.vue'
import Explorer from '~/components/elements/explorer.vue'
import icons from '~/public/icons.json'

const showExplorer = ref(false)
const selectedImageUrl = ref(null)
const isLoading = ref(false)
const generatedHtml = ref('')
const error = ref('')
const viewMode = ref('preview')

const generatedLength = computed(() => generatedHtml.value.length)

const cleanHtml = computed(() => {
    return generatedHtml.value.replace(/```html|```/g, '').trim();
});

const handleImageSelected = (url) => {
  selectedImageUrl.value = url
  showExplorer.value = false
  generatedHtml.value = ''
  error.value = ''
}

const startConversion = async () => {
  if (!selectedImageUrl.value) return

  isLoading.value = true
  generatedHtml.value = ''
  error.value = ''

  try {
    // Extract relative path from URL
    let filePath = ''
    try {
      const urlObj = new URL(selectedImageUrl.value)
      filePath = urlObj.pathname
    } catch (e) {
      // Fallback if not a valid URL (e.g. relative path)
      filePath = selectedImageUrl.value
    }

    const response = await fetch(`https://management.hoggari.com/backend/api.php?action=convertImageToHtml&filePath=${encodeURIComponent(filePath)}`, {
      method: 'GET',
    })

    if (!response.ok) {
      throw new Error(`Erreur HTTP: ${response.status}`)
    }

    const reader = response.body.getReader()
    const decoder = new TextDecoder("utf-8")

    while (true) {
      const { done, value } = await reader.read()
      if (done) break

      const chunk = decoder.decode(value, { stream: true })
      const lines = chunk.split("\n")

      for (const line of lines) {
        if (line.startsWith("data: ")) {
          const jsonStr = line.substring(6).trim()
          if (jsonStr === "[DONE]") break

          try {
            const data = JSON.parse(jsonStr)
            if (data.text) {
              generatedHtml.value += data.text
            }
            if (data.error) {
              throw new Error(data.error)
            }
          } catch (e) {
            // ignore partial json
          }
        }
      }
    }

  } catch (err) {
    error.value = err.message
  } finally {
    isLoading.value = false
  }
}

const copyToClipboard = () => {
  navigator.clipboard.writeText(cleanHtml.value).then(() => {
    alert('Code copié !');
  });
};

</script>

<style scoped>
.wrap { max-width: 1200px; margin: 30px auto; padding: 0 14px; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; }
.card { background: #fff; border: 1px solid #eee; border-radius: 14px; padding: 20px; margin: 14px 0; box-shadow: 0 4px 18px rgba(0,0,0,.04); }
.subtitle { color: #666; margin-bottom: 20px; }

.uploader-section { margin-bottom: 20px; }
.lbl { display:block; font-size: 12px; opacity:.7; margin: 10px 0 6px; }
.selector-row { display: flex; align-items: center; gap: 10px; }
.selected-thumb { width: 80px; height: 80px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
.selected-thumb img { width: 100%; height: 100%; object-fit: cover; }

.actions { display: flex; justify-content: center; }

.loading-indicator { text-align: center; margin-top: 20px; }
.spinner { width: 30px; height: 30px; border: 3px solid #eee; border-top-color: #007aff; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 10px; }
@keyframes spin { to { transform: rotate(360deg); } }

.err { color: #b00020; margin-top: 10px; }

.result-section { padding: 0; overflow: hidden; display: flex; flex-direction: column; height: 800px; }
.tabs { display: flex; border-bottom: 1px solid #eee; background: #f9f9f9; }
.tabs button { flex: 1; padding: 12px; border: none; background: none; cursor: pointer; font-weight: 500; }
.tabs button.active { background: #fff; border-bottom: 2px solid #007aff; color: #007aff; }

.html-preview, .code-view { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
.html-preview iframe { width: 100%; height: 100%; border: none; }

.code-header { padding: 10px; background: #f5f5f5; border-bottom: 1px solid #eee; text-align: right; }
.code-header button { padding: 6px 12px; background: #fff; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; }
.code-view pre { margin: 0; padding: 20px; overflow: auto; background: #1e1e1e; color: #d4d4d4; flex: 1; font-family: monospace; }
</style>
