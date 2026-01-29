<template>
  <div class="wrap">
    <h1>Générateur vidéo promo (≤ 40s)</h1>

    <div class="card">

      <div class="grid">
        <div>
          <label class="lbl">Url de l'image</label>
          <input v-model="form.imgUrl" class="inp" placeholder="Ex: https://....." />
        </div>
        <div>
          <label class="lbl">Nom du produit</label>
          <input v-model="form.productName" class="inp" placeholder="Ex: Parfum Oud Royal" />
        </div>
        <div>
          <label class="lbl">Prix (DA)</label>
          <input v-model="form.price" class="inp" placeholder="Ex: 4500" />
        </div>
        <div>
          <label class="lbl">WhatsApp</label>
          <input v-model="form.whatsapp" class="inp" placeholder="Ex: +213xxxxxxxxx" />
        </div>
        <div>
          <label class="lbl">Livraison</label>
          <input v-model="form.delivery" class="inp" placeholder="Ex: 58 wilayas" />
        </div>
        <div>
          <label class="lbl">Format vidéo</label>
          <select v-model="form.aspect" class="inp">
            <option value="9:16">9:16 (Reels/TikTok)</option>
            <option value="16:9">16:9 (YouTube)</option>
          </select>
        </div>
        <div>
          <label class="lbl">Langue</label>
          <select v-model="form.language" class="inp">
            <option value="dz_darija">Darija DZ + sous-titres FR</option>
            <option value="fr">FR</option>
            <option value="ar">AR</option>
          </select>
        </div>
      </div>

      <button class="btn" :disabled="!canGenerate || loading" @click="generateAll">
        {{ loading ? "Génération..." : "Générer (script + clips)" }}
      </button>

      <p v-if="error" class="err">{{ error }}</p>
      <p v-if="status" class="status">{{ status }}</p>
    </div>

    <div v-if="plan" class="card">
      <h2>Script (≤ 40s)</h2>
      <pre class="pre">{{ plan.script }}</pre>

      <h3>Storyboard</h3>
      <pre class="pre">{{ plan.storyboard }}</pre>

      <h3>Prompts vidéos</h3>
      <ol class="ol">
        <li v-for="(p, i) in plan.video_prompts" :key="i">
          <pre class="pre">{{ p }}</pre>
        </li>
      </ol>
    </div>

    <div v-if="clips.length" class="card">
      <h2>Clips générés (mp4)</h2>

      <div class="clips">
        <div v-for="(c, i) in clips" :key="i" class="clip">
          <p><b>Clip {{ i + 1 }}</b></p>
          <video :src="c.url" controls playsinline></video>
          <a :href="c.url" download class="link">Télécharger clip {{ i + 1 }}</a>
        </div>
      </div>

      <p class="hint">
        Si tu as ffmpeg sur serveur, tu peux concaténer en 1 vidéo. Sinon tu gardes 5 clips.
      </p>
    </div>
  </div>
</template>

<script setup>

const loading = ref(false)
const status = ref("")
const error = ref("")

const plan = ref(null)
const clips = ref([])

const form = reactive({
  productName: "",
  imgUrl: "",
  price: "",
  whatsapp: "",
  delivery: "58 wilayas",
  aspect: "9:16",
  language: "dz_darija",
})

const canGenerate = computed(() => {
  return !!form.imgUrl && !!form.productName && !!form.price && !!form.whatsapp
})

function setErr(msg) {
  error.value = msg
  loading.value = false
  status.value = ""
}

/**
 * Appelle ton PHP Gemini texte pour obtenir:
 * - script
 * - storyboard
 * - video_prompts (5 prompts)
 */
async function generatePlan() {
  status.value = "1/3 Génération du script & prompts..."

  const fd = new FormData()
  fd.append("action", "giminiGeneratePlan")
  fd.append("product_name", form.productName)
  fd.append("price_da", form.price)
  fd.append("whatsapp", form.whatsapp)
  fd.append("delivery", form.delivery)
  fd.append("aspect", form.aspect)
  fd.append("language", form.language)
  fd.append("max_seconds", "40")//40
  fd.append("clips", "1")//5
  fd.append("seconds_per_clip", "8")
  fd.append("image_url", form.imgUrl)

  const res = await $fetch("https://management.hoggari.com/backend/api.php?action=giminiGeneratePlan", {
    method: "POST",
    body: fd,
  })

  console.log('res: ', res)

  return res
}


/**
 * Lance Veo pour un prompt -> retourne operation.name
 */
async function startVideo(prompt) {
  const fd = new FormData()
  fd.append("prompt", prompt)
  fd.append("image_url", form.imgUrl)     // ✅ image URL
  fd.append("model", "veo3_fast")         // ou le modèle exact côté KIE
  fd.append("duration", "5")              // "5" ou "10"
  fd.append("sound", "false")             // "true" / "false"

  const res = await $fetch("https://management.hoggari.com/backend/api.php?action=giminiImageToVideo", {
    method: "POST",
    body: fd,
  })

  if (!res || !res.taskId) throw new Error("Réponse createTask invalide (taskId manquant)")
  return res.taskId
}



/**
 * Poll operation jusqu'à done=true, puis récupère video.uri
 */
async function pollUntilDone(taskId) {
  const started = Date.now()
  const timeoutMs = 10 * 60 * 1000
  const sleep = (ms) => new Promise((r) => setTimeout(r, ms))

  while (true) {
    if (Date.now() - started > timeoutMs) throw new Error("Timeout polling task KIE")

    const op = await $fetch("https://management.hoggari.com/backend/api.php?action=giminiPoll", {
      params: { taskId },
    })

    if (!op || op.success !== true) {
      throw new Error((op && op.error) || "Erreur polling KIE")
    }

    if (op.done) {
      if (op.state === "fail") throw new Error(op.failMsg || "Génération échouée")
      if (!op.video_url) throw new Error("Succès mais video_url introuvable")
      return op.video_url
    }

    await sleep(2500)
  }
}


async function generateAll() {
  error.value = ""
  status.value = ""
  loading.value = true
  plan.value = null
  clips.value = []

  try {
    const p = await generatePlan()
    plan.value = p

    const prompts = (p && p.video_prompts ? p.video_prompts : []).slice(0, 5)
    if (!prompts.length) throw new Error("Aucun prompt vidéo retourné.")

    status.value = "2/3 Génération des clips (KIE)..."

    const out = []
    for (let i = 0; i < prompts.length; i++) {
      status.value = `2/3 Génération clip ${i + 1}/${prompts.length}...`

      const taskId = await startVideo(prompts[i])     // ✅ createTask -> taskId
      const videoUrl = await pollUntilDone(taskId)    // ✅ recordInfo -> resultUrls[0]
      out.push({ url: videoUrl })                     // ✅ une seule fois
    }

    clips.value = out
    status.value = "3/3 Terminé ✅"
  } catch (e) {
    setErr((e && e.message) || "Erreur inconnue")
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
.preview img { max-width: 260px; border-radius: 12px; border: 1px solid #eee; margin-top: 10px; }
.pre { white-space: pre-wrap; background:#fafafa; border:1px solid #eee; padding: 12px; border-radius: 12px; overflow:auto; }
.ol { padding-left: 18px; }
.err { color: #b00020; margin-top: 10px; }
.status { margin-top: 10px; opacity: .8; }
.clips { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px; }
.clip video { width: 100%; border-radius: 12px; border: 1px solid #eee; }
.link { display:inline-block; margin-top: 8px; }
.hint { opacity: .7; margin-top: 10px; font-size: 13px; }
@media (max-width: 800px) {
  .grid, .clips { grid-template-columns: 1fr; }
}
</style>
