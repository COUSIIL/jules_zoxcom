<template>
  <div class="relative w-24">
    <div class="flex items-center justify-between w-full mx-1.25 overflow-hidden text-xs text-left whitespace-nowrap text-ellipsis">
      id:{{ image.id }}
      <div
          class="flex items-center justify-center"
          :class="image.markedForDelete ? 'w-3.5 h-3.5 cursor-pointer bg-rady rounded-full' : 'flex items-center justify-center w-3.5 h-3.5 cursor-pointer border-2 rounded-full bg-transparent border-darkly dark:border-whitly'"
          @click.stop="emitToggleDelete"
          title="Marquer pour suppression"
          @click="emitToggle"
      ></div>
    </div>

    <div class="relative flex items-center justify-center w-24 h-24 overflow-hidden rounded-2xl bg-whizy dark:bg-darky text-darky dark:text-whizy">
        <img
          ref="imageRef"
          :src="thumbSrc"
          loading="lazy"
          decoding="async"
          alt="preview"
          class="object-cover w-full h-full"
          @load="onImageLoad"
          v-show="imageLoaded"
        />
    </div>
    
    <div class="flex items-center justify-center w-full h-8 gap-1 px-1.5 py-0.5 mx-auto mt-1.25 text-xs rounded-lg shadow-md bg-zioly4 text-whitly backdrop-blur-sm dark:bg-whizy dark:text-darky">
      <input
        type="text"
        :title="inputName"
        v-model="inputName"
        class="w-full text-xs text-center bg-transparent border-none outline-none text-inherit overflow-hidden text-ellipsis whitespace-nowrap placeholder-whitly"
        :required="required"
        @blur="emitRename(inputName)"
      />
      <div v-if="required" class="flex items-center">
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
