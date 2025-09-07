<template>
  <div
    class="relative w-full min-h-[150px] p-4 m-2.5 text-center transition-colors duration-200 ease-in-out border-2 border-dashed rounded-xl border-gray-300 hover:bg-gray-100 dark:hover:bg-darky"
    @dragover.prevent
    @drop.prevent="handleDrop"
  >
    <div>
      <!-- Boutons en haut à droite -->
      <div class="absolute top-1.25 right-1.25 z-10 flex gap-2.5 text-sm">
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
            class="inline-flex items-center gap-2 px-2 py-2 transition duration-200 ease-in-out rounded-xl cursor-pointer text-zioly4 dark:text-zioly1 hover:text-zioly2"
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
            class="inline-flex items-center gap-2 px-2 py-2 transition duration-200 ease-in-out rounded-xl cursor-pointer text-zioly4 dark:text-zioly1 hover:text-zioly2"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="currentColor" fill="none">
              <path d="M8 7H16.75C18.8567 7 19.91 7 20.6667 7.50559C20.9943 7.72447 21.2755 8.00572 21.4944 8.33329C22 9.08996 22 10.1433 22 12.25C22 15.7612 22 17.5167 21.1573 18.7779C20.7926 19.3238 20.3238 19.7926 19.7779 20.1573C18.5167 21 16.7612 21 13.25 21H12C7.28595 21 4.92893 21 3.46447 19.5355C2 18.0711 2 15.714 2 11V7.94427C2 6.1278 2 5.21956 2.38032 4.53806C2.65142 4.05227 3.05227 3.65142 3.53806 3.38032C4.21956 3 5.1278 3 6.94427 3C8.10802 3 8.6899 3 9.19926 3.19101C10.3622 3.62712 10.8418 4.68358 11.3666 5.73313L12 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
            </svg>
            {{ t('import folders') }}
          </label>
        </div>
      </div>

      <!-- Texte central -->
      <div class="flex items-center justify-center gap-2.5 my-20 text-sm font-bold text-zioly1">
        {{ t('or just drag and drop here') }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
          <path d="M12 15L12 5M12 15C11.2998 15 9.99153 13.0057 9.5 12.5M12 15C12.7002 15 14.0085 13.0057 14.5 12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M5 19H19.0001" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
      </div>
    </div>

    <div v-if="previews.length" class="grid w-full grid-cols-2 gap-4 mt-4 md:grid-cols-3">
      <div
        v-for="(preview, index) in previews"
        :key="index"
        class="relative w-36 h-36 group"
      >
        <img
          :src="preview.url"
          :alt="preview.file.name"
          class="object-cover w-full rounded shadow-md aspect-square"
        />
        <button
          @click="removeImage(index)"
          class="absolute p-1 text-white transition bg-red-500 rounded-full opacity-0 top-1 right-1 group-hover:opacity-100"
        >
          ✕
        </button>
        <div v-if="preview.progress !== null" class="w-full h-2 mt-1 overflow-hidden bg-gray-200 rounded">
          <div
            class="h-full transition-all duration-200 ease-in-out bg-green-500"
            :style="{ width: preview.progress + '%' }"
          ></div>
        </div>
      </div>
    </div>

    <div v-if="previews.length" class="flex gap-4 mt-4">
      <button @click="uploadImages" class="px-4 py-2 text-white bg-green-600 rounded shadow hover:bg-green-700">
        Uploader {{ previews.length }} image(s)
      </button>
      <button @click="clearAll" class="px-4 py-2 text-white bg-gray-400 rounded shadow hover:bg-gray-500">
        Annuler tout
      </button>
    </div>

    <div v-if="uploadMessage" class="mt-2 text-sm text-gray-600">{{ uploadMessage }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const { t } = useLang()

const fileInputRef = ref(null)
const previews = ref([])
const uploadMessage = ref('')
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

// Envoie chaque image avec barre de progression
const uploadImages = async () => {
  const uploadPromises = previews.value.map(async (preview, index) => {
    return new Promise((resolve, reject) => {
      const xhr = new XMLHttpRequest()
      const formData = new FormData()

      formData.append('image', preview.file)
      formData.append('imageName', preview.file.name.split('.')[0])
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
          resolve()
        } else {
          reject(`Erreur pour ${preview.file.name}`)
        }
      }

      xhr.onerror = () => reject(`Échec du téléchargement pour ${preview.file.name}`)

      xhr.open('POST', 'https://management.hoggari.com/backend/api.php?action=saveImages')
      xhr.send(formData)
    })
  })

  try {
    await Promise.all(uploadPromises)
    uploadMessage.value = 'Toutes les images ont été uploadées avec succès.'
    
    setTimeout(clearAll, 1000)
    emit('uploaded')
  } catch (err) {
    uploadMessage.value = err
  }
}
</script>
