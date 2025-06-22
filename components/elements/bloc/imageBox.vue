<template>
  <div style="width: 100px; position: relative;">
    <div class="id">
      id:{{ image.id }}
      <div
          class="toggle-delete"
          :class="image.markedForDelete ? 'selected1' : 'notSelected1'"
          @click.stop="emitToggleDelete"
          title="Marquer pour suppression"
          @click="emitToggle"
      ></div>
      
    </div>

    <div class="image-box relative">


        <img
          ref="imageRef"
          :src="thumbSrc"
          loading="lazy"
          decoding="async"
          alt="preview"
          class="preview-image"
          @load="onImageLoad"
          v-show="imageLoaded"
        />

        
    </div>
    

    <div class="floating-label">
      
      <input
        type="text"
        :title="inputName"
        v-model="inputName"
        class="input-name"
        :required="required"
        @blur="emitRename(inputName)"
      />

      <div v-if="required" class="required-icon">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" fill="none">
          <path d="M10.7962 2.91338C11.4188 2.29077 12.2756 1.96039 13.1551 2.0038L18.7587 2.28039C20.3601 2.35944 21.6406 3.63993 21.7196 5.24131L21.9962 10.8449C22.0396 11.7244 21.7092 12.5811 21.0866 13.2037L13.5082 20.7822C11.8844 22.4059 9.25177 22.4059 7.62799 20.7822L3.21783 16.372C1.59406 14.7482 1.59406 12.1156 3.21783 10.4918L10.7962 2.91338Z" stroke="currentColor" stroke-width="1.5"/>
          <path d="M17.5002 6.5L17.4912 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M12.982 12.2064L10.0004 14M10.0004 14L7.01869 15.7936M10.0004 14L10.0187 17.5M10.0004 14L9.98202 10.5M10.0004 14L13 15.7063M10.0004 14L7 12.2935" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>

import { ref, onMounted, onBeforeUnmount } from 'vue'

const canvas = document.createElement('canvas')
const ctx = canvas.getContext('2d')

const props = defineProps({
  image: { type: Object, required: true },
  src: { type: String, required: true },
  required: Boolean
})
const emit = defineEmits(['clicked', 'rename', 'toggle-delete'])

const inputName = ref(props.image.name)
const imageLoaded = ref(false)
const thumbSrc = ref('')
const imageRef = ref(null)
let observer = null

// Génère une miniature redimensionnée sans flou (plus net) pour consommation réduite
function generateThumbnail(url, size = 80) {
  return new Promise((resolve) => {
    const imgEl = new Image()
    imgEl.crossOrigin = 'anonymous'
    imgEl.onload = () => {
      let { width, height } = imgEl
      if (width > height) {
        height = (size / width) * height
        width = size
      } else {
        width = (size / height) * width
        height = size
      }
      canvas.width = width
      canvas.height = height
      ctx.filter = 'none'
      ctx.drawImage(imgEl, 0, 0, width, height)
      resolve(canvas.toDataURL('image/webp', 0.9))
    }
    imgEl.onerror = () => resolve(url) // fallback
    imgEl.src = url
  })
}


function onImageLoad() {
  imageLoaded.value = true
}
function emitToggle() { emit('clicked') }
function emitRename(value) { emit('rename', value) }
function emitToggleDelete() { emit('toggle-delete', { id: props.image.id, marked: !props.image.markedForDelete }) }

onMounted(async () => {
  // 1) affiche la miniature
  thumbSrc.value = await generateThumbnail(props.src, 200)
  

  // 2) observer pour futur traitements éventuels
  observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        // on peut précharger full image si besoin
        observer.unobserve(entry.target)
      }
    })
  }, { rootMargin: '100px' })

  if (imageRef.value) observer.observe(imageRef.value)

  imageLoaded.value = true
})

onBeforeUnmount(() => {
  if (observer && imageRef.value) observer.unobserve(imageRef.value)
})
/*
const editImage = async () => {
  try {
    const response = await fetch(`https://image-api.photoroom.com/v2/edit?imageUrl=${encodeURIComponent(props.src)}&background.color=transparent`, {
      headers: {
        'x-api-key': 'sandbox_2660229c29f71cb04dfc77de4b3f3fba0e9438de' // Remplace avec ta vraie clé API Photoroom
      }
    });

    if (!response.ok) {
      throw new Error('Erreur API Photoroom');
    }

    const blob = await response.blob();
    const editedImageUrl = URL.createObjectURL(blob);
    window.open(editedImageUrl, '_blank'); // ou tu peux remplacer l’image d’origine ici
  } catch (err) {
    console.error('Erreur édition image :', err);
  }
}
*/
</script>

<style scoped>

.edit {
  position: absolute;
  bottom: 5px;
  right: 5px;
  z-index: 2;
  display: flex;
  justify-content: center;
  align-items: center;
}

.toggle-delete {
  display: flex;
  justify-content: center;
  align-items: center;
}

.notSelected1 {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  cursor: pointer;
  border: 2px solid var(--color-darkly);
  border-radius: 50%;
  background-color: transparent;
}
.dark .notSelected1 {
  border: 2px solid var(--color-whitly);
}

.selected1 {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  cursor: pointer;
  background-color: var(--color-rady);
  border-radius: 50%;
}

.image-box {
  position: relative;
  width: 100px;
  height: 100px;
  border-radius: 16px;
  overflow: hidden;
  background-color: var(--color-whizy);
  color: var(--color-darky);
  display: flex;
  justify-content: center;
  align-items: center;
}
.dark .image-box {
  background-color: var(--color-darky);
  color: var(--color-whizy);
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.floating-label {
  height: 30px;
  width: 100%;
  margin: 5px auto 0;
  background-color: var(--color-zioly4);
  color: var(--color-whitly);
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center; /* centre contenu horizontalement */
  gap: 4px;
  backdrop-filter: blur(4px);
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.dark .floating-label {
  background-color: var(--color-whizy);
  color: var(--color-darky);
}

.input-name {
  border: none;
  background: transparent;
  color: inherit;
  font-size: 12px;
  outline: none;
  width: 100%;          /* prend toute la largeur */
  text-align: center;   /* centre le texte */
  white-space: nowrap;  /* texte sur une ligne */
  overflow: hidden;     /* cache débordement */
  text-overflow: ellipsis; /* "..." si trop long */
}

.input-name::placeholder {
  color: var(--color-whitly);
}

.id {
  width: 100%;
  text-align: left;
  white-space: nowrap;
  overflow: hidden;    
  text-overflow: ellipsis;
  font-size: 12px;
  margin-inline: 5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.required-icon {
  display: flex;
  align-items: center;
}

.preview-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.download-overlay {
  position: absolute;
  top: 4px;
  right: 4px;
  background: rgba(0,0,0,0.4);
  border: none;
  color: white;
  padding: 4px;
  border-radius: 50%;
  cursor: pointer;
  font-size: 0.9rem;
}
.download-overlay:hover {
  background: rgba(0,0,0,0.6);
}
</style>
