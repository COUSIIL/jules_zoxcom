<template>
  <div class="wrap">
    <h1>Générateur de Musique (Suno AI)</h1>

    <div class="card">
      <div class="grid">
        <div>
          <label class="lbl">Description de la chanson</label>
          <input v-model="form.prompt" class="inp" placeholder="Ex: A chill lofi beat for studying" />
        </div>
        <div>
          <label class="lbl">Instrumental ?</label>
          <select v-model="form.instrumental" class="inp">
            <option :value="false">Non (Avec paroles)</option>
            <option :value="true">Oui (Instrumental)</option>
          </select>
        </div>
      </div>

      <button class="btn" :disabled="!canGenerate || loading" @click="generateMusic">
        {{ loading ? "Génération..." : "Générer la musique" }}
      </button>

      <p v-if="error" class="err">{{ error }}</p>
      <p v-if="status" class="status">{{ status }}</p>
    </div>

    <div v-if="audioUrl" class="card">
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

const loading = ref(false)
const status = ref("")
const error = ref("")
const audioUrl = ref("")

const form = reactive({
  prompt: "",
  instrumental: false,
})

const canGenerate = computed(() => !!form.prompt)

async function generateMusic() {
  error.value = ""
  status.value = ""
  loading.value = true
  audioUrl.value = ""

  try {
    status.value = "Composition en cours (Suno via KIE)..."

    const fd = new FormData()
    fd.append("prompt", form.prompt)
    fd.append("instrumental", form.instrumental ? "true" : "false")

    // Call backend API
    const res = await $fetch("https://management.hoggari.com/backend/api.php?action=giminiMusicGen", {
      method: "POST",
      body: fd,
    })

    if (!res || !res.success) throw new Error(res?.error || "Erreur de génération")

    audioUrl.value = res.url
    status.value = "Terminé ✅"

  } catch (e) {
    error.value = (e && e.message) || "Erreur inconnue"
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.wrap { max-width: 980px; margin: 30px auto; padding: 0 14px; font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial; }
.card { background: #fff; border: 1px solid #eee; border-radius: 14px; padding: 16px; margin: 14px 0; box-shadow: 0 4px 18px rgba(0,0,0,.04); }
.lbl { display:block; font-size: 12px; opacity:.7; margin: 10px 0 6px; }
.inp { width: 100%; padding: 10px 12px; border:1px solid #ddd; border-radius: 10px; outline: none; }
.grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; margin-top: 10px; }
.btn { margin-top: 12px; padding: 10px 14px; border-radius: 12px; border:0; background:#111; color:#fff; cursor:pointer; }
.btn:disabled { opacity:.5; cursor:not-allowed; }
.preview audio { width: 100%; margin-top: 10px; }
.err { color: #b00020; margin-top: 10px; }
.status { margin-top: 10px; opacity: .8; }
.link { display:inline-block; margin-top: 8px; }
@media (max-width: 800px) {
  .grid { grid-template-columns: 1fr; }
}
</style>
