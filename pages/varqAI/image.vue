<template>
  <div class="wrap">
    <h1>Générateur d'Image</h1>

    <div class="card">
      <div class="grid">
        <Input
          v-model="form.prompt"
          placeHolder="Prompt (Description)"
          img="edit"
          :required="true"
        />
        <Select
          v-model="form.aspect"
          :options="aspectOptions"
          placeHolder="Format"
          img="screen"
          :showIt="showAspectSelect"
          @close="showAspectSelect = false"
          @click="showAspectSelect = !showAspectSelect"
        />
      </div>

      <div class="image-selector">
        <label class="lbl">Image de référence (Optionnel)</label>
        <div class="selector-row">
          <Gbtn
            :text="form.imgUrl ? 'Changer l\'image' : 'Sélectionner une image'"
            @click="showExplorer = true"
            color="#7D698E"
            :svg="icons['imageImg']"
          />
          <div v-if="form.imgUrl" class="selected-thumb">
            <img :src="form.imgUrl" alt="Reference" />
            <span class="remove-btn" @click="form.imgUrl = ''">×</span>
          </div>
        </div>
      </div>

      <div class="actions">
        <Gbtn
          text="Générer l'image"
          :disabled="!canGenerate || loading"
          @click="generateImage"
          color="#111"
          svg=""
        />
      </div>

      <p v-if="error" class="err">{{ error }}</p>
      <p v-if="status" class="status">{{ status }}</p>
    </div>

    <div v-if="imageUrl" class="card result-card">
      <h2>Image générée</h2>
      <div class="preview">
        <img :src="imageUrl" alt="Generated Image" />
        <br/>
        <a :href="imageUrl" download class="link">Télécharger l'image</a>
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
import { ref, reactive, computed } from 'vue';
import Input from '~/components/elements/bloc/input.vue'
import Select from '~/components/elements/bloc/select.vue'
import Gbtn from '~/components/elements/bloc/gBtn.vue'
import Explorer from '~/components/elements/explorer.vue'
import icons from '~/public/icons.json'

const loading = ref(false)
const status = ref("")
const error = ref("")
const imageUrl = ref("")
const showExplorer = ref(false)
const showAspectSelect = ref(false)

const form = reactive({
  prompt: "",
  aspect: "1:1",
  imgUrl: ""
})

const aspectOptions = [
  { label: "1:1 (Carré)", value: "1:1" },
  { label: "16:9 (Paysage)", value: "16:9" },
  { label: "9:16 (Portrait)", value: "9:16" }
]

const canGenerate = computed(() => !!form.prompt)

function handleImageSelected(url) {
  form.imgUrl = url
  showExplorer.value = false
}

async function generateImage() {
  error.value = ""
  status.value = ""
  loading.value = true
  imageUrl.value = ""

  try {
    status.value = "Initialisation..."

    const fd = new FormData()
    fd.append("prompt", form.prompt)
    fd.append("aspect_ratio", form.aspect)
    if (form.imgUrl) {
      fd.append("image_url", form.imgUrl)
    }

    // 1. Start Generation
    const res = await $fetch("https://management.hoggari.com/backend/api.php?action=giminiImageGen", {
      method: "POST",
      body: fd,
    })

    if (!res || !res.success) throw new Error(res?.error || "Erreur de génération")

    const taskId = res.taskId
    if (!taskId) throw new Error("Pas de taskId reçu")

    status.value = "Génération en cours..."

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

      status.value = `Génération en cours... (${pollRes.state || 'waiting'})`
    }

    // 3. Save to local
    status.value = "Sauvegarde de l'image..."
    const saveRes = await $fetch("https://management.hoggari.com/backend/api.php?action=saveExternalUrl", {
      method: "POST",
      body: JSON.stringify({ url: finalUrl })
    })

    if (!saveRes || !saveRes.success) throw new Error("Erreur de sauvegarde locale")

    // Use local path relative to domain
    imageUrl.value = saveRes.data.path
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
.preview img { max-width: 100%; border-radius: 12px; border: 1px solid #eee; margin-top: 10px; }
.err { color: #b00020; margin-top: 10px; }
.status { margin-top: 10px; opacity: .8; }
.link { display:inline-block; margin-top: 8px; }

.image-selector { margin-top: 15px; }
.selector-row { display: flex; align-items: center; gap: 10px; }
.selected-thumb { position: relative; width: 60px; height: 60px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd; }
.selected-thumb img { width: 100%; height: 100%; object-fit: cover; }
.remove-btn { position: absolute; top: 0; right: 0; background: rgba(0,0,0,0.5); color: #fff; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; }

@media (max-width: 800px) {
  .grid { grid-template-columns: 1fr; }
}
</style>
