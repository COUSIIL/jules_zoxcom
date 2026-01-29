<template>
  <div class="wrap">
    <h1>Générateur de Musique (Suno AI)</h1>

    <div class="card">
      <div class="grid">
        <Input
          v-model="form.prompt"
          placeHolder="Description de la chanson"
          img="edit"
          :required="true"
        />
        <Select
          v-model="form.instrumental"
          :options="instrumentalOptions"
          placeHolder="Type"
          img="settings"
          :showIt="showTypeSelect"
          @close="showTypeSelect = false"
          @click="showTypeSelect = !showTypeSelect"
        />
      </div>

      <div class="file-upload-section">
        <label class="lbl">Exemple audio (Optionnel)</label>
        <div class="upload-box">
          <input type="file" ref="fileInput" @change="handleFileChange" accept="audio/*" style="display: none" />
          <Gbtn
            :text="selectedFile ? selectedFile.name : 'Choisir un fichier audio'"
            @click="$refs.fileInput.click()"
            color="#7D698E"
            svg=""
          />
          <span v-if="selectedFile" class="remove-file" @click="clearFile">×</span>
        </div>
      </div>

      <div class="actions">
        <Gbtn
          text="Générer la musique"
          :disabled="(!canGenerate && !selectedFile) || loading"
          @click="generateMusic"
          color="#111"
          svg=""
        />
      </div>

      <p v-if="error" class="err">{{ error }}</p>
      <p v-if="status" class="status">{{ status }}</p>
    </div>

    <div v-if="audioUrl" class="card result-card">
      <h2>Musique générée</h2>
      <div class="preview">
        <audio :src="audioUrl" controls></audio>
        <br/>
        <a :href="audioUrl" download class="link">Télécharger le MP3</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import Input from '~/components/elements/bloc/input.vue'
import Select from '~/components/elements/bloc/select.vue'
import Gbtn from '~/components/elements/bloc/gBtn.vue'

const loading = ref(false)
const status = ref("")
const error = ref("")
const audioUrl = ref("")
const fileInput = ref(null)
const selectedFile = ref(null)
const showTypeSelect = ref(false)

const form = reactive({
  prompt: "",
  instrumental: false,
})

const instrumentalOptions = [
  { label: "Avec paroles", value: false },
  { label: "Instrumental", value: true }
]

const canGenerate = computed(() => !!form.prompt)

function handleFileChange(event) {
  const file = event.target.files[0]
  if (file) {
    selectedFile.value = file
  }
}

function clearFile() {
  selectedFile.value = null
  if (fileInput.value) fileInput.value.value = ''
}

async function generateMusic() {
  error.value = ""
  status.value = ""
  loading.value = true
  audioUrl.value = ""

  try {
    status.value = "Initialisation..."

    const fd = new FormData()
    fd.append("prompt", form.prompt)
    fd.append("instrumental", form.instrumental ? "true" : "false")
    if (selectedFile.value) {
      fd.append("audio_sample", selectedFile.value)
    }

    // 1. Start Generation
    const res = await $fetch("https://management.hoggari.com/backend/api.php?action=giminiMusicGen", {
      method: "POST",
      body: fd,
    })

    if (!res || !res.success) throw new Error(res?.error || "Erreur de génération")

    const taskId = res.taskId
    if (!taskId) throw new Error("Pas de taskId reçu")

    status.value = "Composition en cours..."

    // 2. Poll
    let finalUrl = ""
    while (true) {
      await new Promise(r => setTimeout(r, 3000)) // 3s delay

      const pollRes = await $fetch(`https://management.hoggari.com/backend/api.php?action=giminiPoll&taskId=${taskId}`)

      if (!pollRes) continue

      if (pollRes.state === "fail") {
        throw new Error(pollRes.failMsg || "Erreur durant la génération")
      }

      if (pollRes.state === "success" && pollRes.resultUrls && pollRes.resultUrls.length > 0) {
        finalUrl = pollRes.resultUrls[0]
        break
      }

      status.value = `Composition en cours... (${pollRes.state || 'waiting'})`
    }

    // 3. Save to local
    status.value = "Sauvegarde du fichier..."
    const saveRes = await $fetch("https://management.hoggari.com/backend/api.php?action=saveExternalUrl", {
      method: "POST",
      body: JSON.stringify({ url: finalUrl })
    })

    if (!saveRes || !saveRes.success) throw new Error("Erreur de sauvegarde locale")

    // Use local path relative to domain
    audioUrl.value = saveRes.data.path
    status.value = "Terminé ✅"

  } catch (e) {
    error.value = (e && e.message) || "Erreur inconnue"
    status.value = "Erreur"
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.wrap { max-width: 980px; margin: 30px auto; padding: 0 14px; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; }
.card { background: #fff; border: 1px solid #eee; border-radius: 14px; padding: 16px; margin: 14px 0; box-shadow: 0 4px 18px rgba(0,0,0,.04); }
.lbl { display:block; font-size: 12px; opacity:.7; margin: 10px 0 6px; }
.grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; margin-top: 10px; }
.actions { margin-top: 20px; display: flex; justify-content: center; }
.preview audio { width: 100%; margin-top: 10px; }
.err { color: #b00020; margin-top: 10px; }
.status { margin-top: 10px; opacity: .8; }
.link { display:inline-block; margin-top: 8px; }

.file-upload-section { margin-top: 15px; }
.upload-box { display: flex; align-items: center; gap: 10px; }
.remove-file { cursor: pointer; color: #b00020; font-size: 20px; font-weight: bold; }

@media (max-width: 800px) {
  .grid { grid-template-columns: 1fr; }
}
</style>
