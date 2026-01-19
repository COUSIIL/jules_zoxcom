<template>
  <div
    class="uploadBox" style="position: relative;"
    @dragover.prevent
    @drop.prevent="handleDrop"
  >
    <div>

      <!-- Boutons en haut à droite -->
      <div style="position: absolute; top: 5px; right: 5px; display: flex; gap: 10px; z-index: 10; font-size: 14px; align-items: center;">
        
        <div style="display: flex; align-items: center; gap: 5px;">
          <span>WebP</span>
          <Toggle :toggle="convertToWebP" @toggle="convertToWebP = !convertToWebP" />
        </div>

        <!-- Import Images -->
        <div class="relative inline-block text-center">
          <input
            id="fileInput"
            type="file"
            accept="image/*"
            multiple
            class="hidden"
            @change="onImageSelect"
            ref="fileInputRef"
          />
          <label
            for="fileInput"
            class="cursor-pointer px-2 py-2 text-zioly4 dark:text-zioly1 rounded-xl hover:text-zioly2 transition duration-200 ease-in-out inline-flex items-center gap-2"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="currentColor" fill="none">
              <path d="M12 15L12 5M12 15C11.2998 15 9.99153 13.0057 9.5 12.5M12 15C12.7002 15 14.0085 13.0057 14.5 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
              <path d="M5 19H19.0001" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            {{ t('import images') }}
          </label>
        </div>

        <!-- Import Folders -->
        <div class="relative inline-block text-center">
          <input
            id="folderInput"
            type="file"
            accept="image/*"
            multiple
            webkitdirectory
            mozdirectory
            class="hidden"
            @change="onImageSelect"
          />
          <label
            for="folderInput"
            class="cursor-pointer px-2 py-2 text-zioly4 dark:text-zioly1 rounded-xl hover:text-zioly2 transition duration-200 ease-in-out inline-flex items-center gap-2"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="currentColor" fill="none">
              <path d="M8 7H16.75C18.8567 7 19.91 7 20.6667 7.50559C20.9943 7.72447 21.2755 8.00572 21.4944 8.33329C22 9.08996 22 10.1433 22 12.25C22 15.7612 22 17.5167 21.1573 18.7779C20.7926 19.3238 20.3238 19.7926 19.7779 20.1573C18.5167 21 16.7612 21 13.25 21H12C7.28595 21 4.92893 21 3.46447 19.5355C2 18.0711 2 15.714 2 11V7.94427C2 6.1278 2 5.21956 2.38032 4.53806C2.65142 4.05227 3.05227 3.65142 3.53806 3.38032C4.21956 3 5.1278 3 6.94427 3C8.10802 3 8.6899 3 9.19926 3.19101C10.3622 3.62712 10.8418 4.68358 11.3666 5.73313L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
            </svg>
            {{ t('import folders') }}
          </label>
        </div>
      </div>

      <!-- Texte central -->
      <div style="margin-block: 75px; font-weight: bold; font-size: 14px; color: var(--color-zioly1); display: flex; justify-content: center; align-items: center; gap: 10px;">
        {{ t('or just drag and drop here') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
          <path d="M12 15L12 5M12 15C11.2998 15 9.99153 13.0057 9.5 12.5M12 15C12.7002 15 14.0085 13.0057 14.5 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M5 19H19.0001" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      </div>
    </div>


    

    <div v-if="previews.length" class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4 w-full">
      <div
        v-for="(preview, index) in previews"
        :key="index"
        class="relative group" style="width: 150px; height: 150px;"
      >
        <img
          :src="preview.url"
          :alt="preview.file.name"
          class="rounded shadow-md w-full aspect-square object-cover"
        />
        <button
          @click="removeImage(index)"
          class="absolute top-1 right-1 text-white bg-red-500 rounded-full p-1 opacity-0 group-hover:opacity-100 transition"
        >
          ✕
        </button>
        <div v-if="preview.progress !== null" class="w-full mt-1 bg-gray-200 h-2 rounded overflow-hidden">
          <div
            class="bg-green-500 h-full transition-all duration-200 ease-in-out"
            :style="{ width: preview.progress + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <div v-if="previews.length" class="mt-4 flex gap-4">
      <button @click="uploadImages" class="px-4 py-2 bg-green-600 text-white rounded shadow hover:bg-green-700">
        Uploader {{ previews.length }} image(s)
      </button>
      <button @click="clearAll" class="px-4 py-2 bg-gray-400 text-white rounded shadow hover:bg-gray-500">
        Annuler tout
      </button>
    </div>

    <div v-if="uploadMessage" class="text-sm text-gray-600 mt-2">{{ uploadMessage }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Toggle from '~/components/toggle.vue'

const { t } = useLang()

const fileInputRef = ref(null)
const previews = ref([])
const uploadMessage = ref('')
const convertToWebP = ref(true)
const emit = defineEmits(['uploaded'])

const props = defineProps({
  folderId: {
    type: Number,
    default: 0,
  },
})

// Ajoute des fichiers dans la liste
const handleFiles = (files) => {
  for (const file of files) {
    if (file.type.startsWith('image/')) {
      const url = URL.createObjectURL(file)
      previews.value.push({
        file,
        url,
        progress: null, // pour afficher la progression
      })
    }
  }
}

// Sélection via input
const onImageSelect = (e) => {
  handleFiles(e.target.files)
}

// Glisser-déposer
const handleDrop = (e) => {
  const items = [...e.dataTransfer.items]
  const files = []
  const promises = items.map(item => {
    const entry = item.webkitGetAsEntry?.()
    return entry ? readEntryRecursive(entry, files) : Promise.resolve()
  })
  Promise.all(promises).then(() => handleFiles(files))
}

// Lecture récursive des dossiers
const readEntryRecursive = (entry, fileList) => {
  return new Promise(resolve => {
    if (entry.isFile) {
      entry.file(file => {
        if (file.type.startsWith('image/')) fileList.push(file)
        resolve()
      })
    } else if (entry.isDirectory) {
      const reader = entry.createReader()
      reader.readEntries(entries => {
        const subPromises = entries.map(subEntry => readEntryRecursive(subEntry, fileList))
        Promise.all(subPromises).then(resolve)
      })
    } else {
      resolve()
    }
  })
}

const removeImage = (index) => {
  URL.revokeObjectURL(previews.value[index].url)
  previews.value.splice(index, 1)
}

const clearAll = () => {
  previews.value.forEach(p => URL.revokeObjectURL(p.url))
  previews.value = []
  uploadMessage.value = ''
}

const processFile = async (file) => {
  if (!convertToWebP.value || !file.type.startsWith('image/') || file.type === 'image/webp') {
    return file
  }

  return new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => {
      const canvas = document.createElement('canvas')
      canvas.width = img.width
      canvas.height = img.height
      const ctx = canvas.getContext('2d')
      ctx.drawImage(img, 0, 0)
      canvas.toBlob((blob) => {
        if (blob) {
          // Create a new File object with .webp extension
          const newName = file.name.replace(/\.[^/.]+$/, "") + ".webp"
          const newFile = new File([blob], newName, { type: 'image/webp' })
          resolve(newFile)
        } else {
          reject(new Error('WebP conversion failed'))
        }
      }, 'image/webp', 0.8)
      URL.revokeObjectURL(img.src)
    }
    img.onerror = () => {
      URL.revokeObjectURL(img.src)
      reject(new Error('Image load failed'))
    }
    img.src = URL.createObjectURL(file)
  })
}

// Envoie chaque image avec barre de progression
const uploadImages = async () => {
  const uploadPromises = previews.value.map(async (preview, index) => {
    return new Promise(async (resolve, reject) => {
      try {
        const fileToUpload = await processFile(preview.file)

        const xhr = new XMLHttpRequest()
        const formData = new FormData()

        formData.append('image', fileToUpload)
        const nameWithoutExt = fileToUpload.name.replace(/\.[^/.]+$/, "")
        formData.append('imageName', nameWithoutExt)
        formData.append('table', 'images')
        formData.append('folderId', props.folderId)

        xhr.upload.addEventListener('progress', (e) => {
          if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100)
            previews.value[index].progress = percent
          }
        })

        xhr.onload = () => {
          if (xhr.status === 200) {
            try {
              const response = JSON.parse(xhr.responseText)
              if (response.success) {
                resolve()
              } else {
                reject(response.message || `Erreur serveur pour ${preview.file.name}`)
              }
            } catch (e) {
              reject(`Réponse invalide pour ${preview.file.name}`)
            }
          } else {
            reject(`Erreur HTTP ${xhr.status} pour ${preview.file.name}`)
          }
        }

        xhr.onerror = () => reject(`Échec du téléchargement pour ${preview.file.name}`)

        xhr.open('POST', 'https://management.hoggari.com/backend/api.php?action=saveImages')
        xhr.send(formData)
      } catch (error) {
        reject(error.message || `Erreur de traitement pour ${preview.file.name}`)
      }
    })
  })

  try {
    await Promise.all(uploadPromises)
    uploadMessage.value = t('all images uploaded successfully')
    
    setTimeout(clearAll, 1000)
    emit('uploaded')
  } catch (err) {
    uploadMessage.value = err
  }
}
</script>

<style>
.uploadBox {
  width: calc(100% - 20px);
  min-height: 150px;
  padding: 1rem;
  border: 2px dashed #ccc;
  border-radius: 12px;
  background: transparent;
  text-align: center;
  transition: background 0.2s ease;
  margin: 10px;
}
.uploadBox:hover {
  background: #f0f0f0;
}

.uploadBox2 {
  width: calc(100% - 20px);
  background: transparent;
}

.dark .uploadBox {
  background: transparent;
  transition: background 0.2s ease;
}

.dark .uploadBox:hover {
  background: var(--color-darky);
}
</style>
