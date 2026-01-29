<template>
  <div class="wrap">
    <h1>Générateur d'Image</h1>

    <div class="card">
      <div class="grid">
        <div>
          <label class="lbl">Prompt</label>
          <input v-model="form.prompt" class="inp" placeholder="Ex: A futuristic city in the clouds" />
        </div>
        <div>
          <label class="lbl">Format</label>
          <select v-model="form.aspect" class="inp">
            <option value="1:1">1:1 (Carré)</option>
            <option value="16:9">16:9 (Paysage)</option>
            <option value="9:16">9:16 (Portrait)</option>
          </select>
        </div>
      </div>

      <button class="btn" :disabled="!canGenerate || loading" @click="generateImage">
        {{ loading ? "Génération..." : "Générer l'image" }}
      </button>

      <p v-if="error" class="err">{{ error }}</p>
      <p v-if="status" class="status">{{ status }}</p>
    </div>

    <div v-if="imageUrl" class="card">
      <h2>Image générée</h2>
      <div class="preview">
        <img :src="imageUrl" alt="Generated Image" />
        <br/>
        <a :href="imageUrl" download class="link">Télécharger l'image</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';

const loading = ref(false)
const status = ref("")
const error = ref("")
const imageUrl = ref("")

const form = reactive({
  prompt: "",
  aspect: "1:1",
})

const canGenerate = computed(() => !!form.prompt)

async function generateImage() {
  error.value = ""
  status.value = ""
  loading.value = true
  imageUrl.value = ""

  try {
    status.value = "Génération de l'image (KIE)..."

    const fd = new FormData()
    fd.append("prompt", form.prompt)
    fd.append("aspect_ratio", form.aspect)

    // Call backend API
    const res = await $fetch("https://management.hoggari.com/backend/api.php?action=giminiImageGen", {
      method: "POST",
      body: fd,
    })

    if (!res || !res.success) throw new Error(res?.error || "Erreur de génération")

    imageUrl.value = res.url
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
.preview img { max-width: 100%; border-radius: 12px; border: 1px solid #eee; margin-top: 10px; }
.err { color: #b00020; margin-top: 10px; }
.status { margin-top: 10px; opacity: .8; }
.link { display:inline-block; margin-top: 8px; }
@media (max-width: 800px) {
  .grid { grid-template-columns: 1fr; }
}
</style>
