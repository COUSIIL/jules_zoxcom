<template>
  <Confirm :isVisible="showConfirm" @confirm="deleteAll" @cancel="showConfirm = false"/>
  <Message :isVisible="isMessage" :message="message" @ok="isMessage = false" />

  <div v-if="show" class="backCalque">

  </div>

  <div v-if="show && ready" class="modalExp" @contextmenu.prevent>
    
    <div v-if="isLargeScreen" style="width: 100%;">
      <RoadLink v-if="liList" :liList="liList" @back-click="backToFolder" @selected-back="backToSelectedFolder" @back-home="backToHome"/>
      <Uploader :folder-id="currentParentId" @uploaded="getImages"/>
      
      <div style="width: 100%; 
          display: flex; 
          justify-content:center; 
          align-items: center;">
        
        <Searcher v-model="search" @search-submitted="toSearch" @filters-changed="onFiltersChanged" />

        <!--Gbtn :text="t('confirm')" @click="confirmExp" color="var(--color-greny)" :svg="confirmImg"/-->

        <Gbtn :text="t('close')" @click="closeExp" color="#ff5555" :svg="icons['closeImg']"/>

      </div>
      <div style="width: 100%; display: flex; align-items: center;">
        <InputBtn
          :placeHolder="t('add folder')"
          :img="icons['folderImg']"
          :required="false"
          v-model="newFolderName"
          :svg="icons['addFolder']"
          color="#7D698E"
          @clicked="addFolderTo(currentParentId, newFolderName)"
        />

        <Gbtn :text="t('delete')" @click="showConfirm = true" color="#ff5555" :svg="icons['deleteImg']"/>
        <Gbtn :text="t('download')" @click="downloadImagesSelected" :svg="icons['download']" color="var(--color-greny)"/>


      </div>
      
      <div v-if="!isAdding" class="folder-tree">
        <div class="folder-item" v-for="folder in folders" :key="folder.id">
          <FolderBox
              v-if="folder.id != 1"
              :folder="folder"
              :img="icons['folderImg2']"
              draggable="true"
              @dragstart="onDragStart($event, 'folder', folder)"
              @drop="onDrop($event, folder)"
              @dragover.prevent
              @clicked="folderRoadList(folder.name, folder.id)"
              @rename="newName => renameFolder(folder.id, newName)"
              @toggle-delete="handleToggleDelete"
              
          />
        </div>
      </div>
      <!-- Trigger invisible pour charger plus -->
      <div ref="loadMoreRef" class="observer-trigger"></div>

      
      <div v-if="!isAdding" class="folder-tree">
        <div class="folder-item" v-for="image in images" :key="image.id">
          <ImageBox
            :src="'https://management.hoggari.com' + image.path"
            :image="image"
            :required="false"
            :img="icons['imageImg']"
            draggable="true"
            @dragstart="onDragStart($event, 'image', image)"
            @drop="onDrop($event, image)"
            @dragover.prevent
            @rename="newName => renameImages(image.name, newName)"
            @toggle-delete="handleToggleDeleteImages"
            @click="confirmExp('https://management.hoggari.com' + image.path)"
            @clicked="selectImage('https://management.hoggari.com' + image.path)"
            
          />
          <Gbtn :text="t('')" @click="downloadImage('https://management.hoggari.com' + image.path)" :svg="icons['download']" color="var(--color-greny)"/>
        </div>
      </div>
      <LoaderBlack v-else width="100px" />
      <!-- Trigger invisible pour charger plus -->
      <div ref="loadMoreRef" class="observer-trigger"></div>

      

        

    </div>
    <div v-else style="width: 100%;">
      <RoadLink v-if="liList" :liList="liList" @back-click="backToFolder" @selected-back="backToSelectedFolder" @back-home="backToHome"/>

      <Uploader :folder-id="currentParentId" @uploaded="getImages"/>

      <div style="display: flex; justify-content: center; align-items: center;">
        <Searcher v-model="search" @search-submitted="toSearch" @filters-changed="onFiltersChanged" />

        <!--Gbtn :text="t('confirm')" @click="confirmExp" color="var(--color-greny)" :svg="confirmImg"/-->

        <Gbtn :text="t('close')" @click="closeExp" color="#ff5555" :svg="icons['closeImg']"/>
      </div>

      


      <div>
        <InputBtn
          :placeHolder="t('add folder')"
          :img="icons['folderImg']"
          :required="false"
          v-model="newFolderName"
          :svg="icons['addFolder']"
          color="#7D698E"
          @clicked="addFolderTo(currentParentId, newFolderName)"
        />

        <div style="width: 100%; display: flex; align-items: center;">
          <Gbtn :text="t('delete')" @click="showConfirm = true" color="#ff5555" :svg="icons['deleteImg']"/>
          <Gbtn :text="t('download')" @click="downloadImagesSelected" :svg="icons['download']" color="var(--color-greny)"/>
        </div>
        

      </div>
      
      <div v-if="!isAdding" class="folder-tree">
        <div class="folder-item" v-for="folder in folders" :key="folder.id">
          <FolderBox
            v-if="folder.id != 1"
            :folder="folder"
            :img="icons['folerImg2']"
            draggable="true"
            @dragstart="onDragStart($event, 'folder', folder)"
            @drop="onDrop($event, folder)"
            @dragover.prevent
            @clicked="folderRoadList(folder.name, folder.id)"
            @rename="newName => renameFolder(folder.id, newName)"
            @toggle-delete="handleToggleDelete"
            
          />
        </div>
      </div>
      
      <!-- Trigger invisible pour charger plus -->
      <div ref="loadMoreRef" class="observer-trigger"></div>


      <div v-if="!isAdding" class="folder-tree">
        <div class="folder-item" v-for="image in images" :key="image.id">
          <ImageBox
            :src="'https://management.hoggari.com' + image.path"
            :image="image"
            :required="false"
            :img="icons['imageImg']"
            draggable="true"
            @dragstart="onDragStart($event, 'image', image)"
            @drop="onDrop($event, image)"
            @dragover.prevent
            @rename="newName => renameImages(image.name, newName)"
            @toggle-delete="handleToggleDeleteImages"
            @click="confirmExp('https://management.hoggari.com' + image.path)"
            @clicked="selectImage('https://management.hoggari.com' + image.path)"
            
          />
          <Gbtn :text="t('')" @click="downloadImage('https://management.hoggari.com' + image.path)" :svg="icons['download']" color="var(--color-greny)"/>
        </div>
      </div>
      <LoaderBlack v-else width="100px" />
      <!-- Trigger invisible pour charger plus -->
      <div ref="loadMoreRef" class="observer-trigger"></div>

      
    </div>
    
    
  </div>

  

</template>

<script setup>

import Gbtn from './bloc/gBtn.vue'
import Searcher from './bloc/searcher.vue'
import InputBtn from './bloc/inputBtn.vue'
import ImageBox from './bloc/imageBox.vue'
import FolderBox from './bloc/folderBox.vue'
import RoadLink from './bloc/roadLink.vue'
import Uploader from './bloc/uploader.vue'
import Confirm from './bloc/confirm.vue'
import Message from './bloc/message.vue'

import icons from '~/public/icons.json'

import LoaderBlack from '~/components/elements/animations/loaderBlack.vue'

const isLargeScreen = useState('isLargeScreen')
const { t } = useLang()

const showConfirm = ref(false)
const liList = ref([])

const props = defineProps({ show: Boolean })
const emit = defineEmits(['select', 'confirm', 'cancel'])

const ready = ref(false)

const flatFolders = computed(() => flattenFolders(folders.value))

const batchSize = 30
const visibleCount = ref(batchSize)

var allFolders = ref([])
const allImages = ref([])

const isMessage = ref(false)
const message = ref('')

const imageToDownload = ref([])




const folders = ref([
  //{ id: 1, name: 'Dossier racine', children: [] }
])

var images = ref([])

const newFolderName = ref('')
const isAdded = ref(false)
const isAdding = ref(false)
const currentParentId = ref(1)

const search = ref('')
const filterOptions = ref({ type: 'all', mode: 'name' })

const draggedItem = ref(null)
const draggedType = ref(null)


const closeExp = () => {
  emit('cancel')
}
const confirmExp = (value) => {
  emit('confirm', value)
}


const onDragStart = (event, type, item) => {
  draggedItem.value = item
  draggedType.value = type
}

const selectImage = (url) => {
  const index = imageToDownload.value.findIndex(item => item === url)
  if (index !== -1) {
    // Si déjà présent, on le retire
    imageToDownload.value.splice(index, 1)
  } else {
    // Sinon on l'ajoute
    imageToDownload.value.push(url)
  }
}


const downloadImagesSelected = () => {
  if(imageToDownload.value.length > 0) {
    
    for(let i = 0; i < imageToDownload.value.length; i++) {
      downloadImage(imageToDownload.value[i])
    }
  } else {
    isMessage.value = true;
    message.value = t('no image selected')
  }
  

}

async function downloadImage(url) {
  try {
    // 1. Récupère l’image en blob
    const response = await fetch(url);
    if (!response.ok) throw new Error(`Erreur ${response.status}`);
    const blob = await response.blob();

    // 2. Crée un objet URL et force le téléchargement
    const blobUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = blobUrl;
    link.download = url.split('/').pop();  // nom du fichier
    document.body.appendChild(link);
    link.click();

    // 3. Clean-up
    document.body.removeChild(link);
    URL.revokeObjectURL(blobUrl);
  } catch (err) {
    console.error('Impossible de télécharger l’image:', err);
  }
}



const onDrop = async (event, targetFolder) => {
  if (!targetFolder || targetFolder.id === draggedItem.value?.id) return

  if (draggedType.value === 'image') {
    await moveImageToFolder(draggedItem.value.id, targetFolder.id)
    await getImages()
  } else if (draggedType.value === 'folder') {
    await moveFolderToFolder(draggedItem.value.id, targetFolder.id)
    await getFolders(targetFolder.id)
  }

  draggedItem.value = null
  draggedType.value = null
}

const toSearch = () => {
  // Logique de filtrage
  applyFilter()
}


const onFiltersChanged = (filters) => {
  filterOptions.value = filters
  applyFilter()
}

const applyFilter = () => {
  let filteredFolders = [...allFolders.value]
  let filteredImages = [...allImages.value]

  const { type, mode } = filterOptions.value
  const q = search.value.trim().toLowerCase()

  if (q) {
    if (type === 'all' || type === 'file') {
      filteredFolders = filteredFolders.filter(folder => {
        if (mode === 'name') return folder.name.toLowerCase().includes(q)
        if (mode === 'id') return folder.id.toString().includes(q)
        return true
      })
    } else {
      filteredFolders = []
    }

    if (type === 'all' || type === 'image') {
      filteredImages = filteredImages.filter(image => {
        if (mode === 'name') return image.name.toLowerCase().includes(q)
        if (mode === 'id') return image.id.toString().includes(q)
        return true
      })
    } else {
      filteredImages = []
    }
  }

  folders.value = filteredFolders
  images.value = filteredImages
}


const addFolderTo = async (parentId, name) => {
  isAdding.value = true;
  const newFolder = {
    id: Date.now(), // ou UUID temporaire
    name,
    markedForDelete: false,
    children: []
  }
  
  await createFolder(name, parentId)
  if(isAdded) {
    const addToParent = (items) => {
      for (const item of items) {
        if (item.id === parentId) {
          item.children.push(newFolder)
          
          return true
        } else if (item.children?.length) {
          const found = addToParent(item.children)
          if (found) return true
        }
      }
      return false
    }

    if (!parentId) {
      folders.value.push(newFolder)
    } else {
      addToParent(folders.value)
      
    }

    newFolderName.value = ''
    }
    
    isAdding.value = false;
}

const flattenFolders = (folders) => {
  const flat = []

  const recurse = (items) => {
    for (const folder of items) {
      if (folder && folder.name) {
        flat.push(folder)
        if (folder.children?.length) {
          recurse(folder.children)
        }
      }
    }
  }

  recurse(folders)
  return flat
}

const loadMoreRef = ref(null)

const deleteAll = () => {
  deleteFolder()
  deleteImages()
  showConfirm.value = false
}


const handleToggleDelete = ({ id, marked }) => {
  const folder = flatFolders.value.find(f => f.id === id)
  if (folder) folder.markedForDelete = marked
}

const handleToggleDeleteImages = ({ id, marked }) => {
  
  const image = images.value.find(f => f.id === id)
  if (image) image.markedForDelete = marked
}

const folderRoadList = (value, id) => {
  id = Number(id); // 🟢 force id à être un nombre

  if (liList.value.length === 0) {
    liList.value.push({ title: value, id })
  } else {
    const init = liList.value.findIndex(item => item.id === id)
    if (init === -1) {
      liList.value.push({ title: value, id })
    }
  }
  currentParentId.value = id
  resetImages(id)
  resetFolders(id)
}


const backToFolder = () => {
  if(liList.value.length == 1) {
    liList.value = []
    currentParentId.value = 1
    resetImages(1)
    resetFolders(currentParentId.value)
  } else if (liList.value.length > 0) {
    
    currentParentId.value = liList.value[liList.value.length - 2].id
    resetImages(currentParentId.value)
    resetFolders(currentParentId.value)
    liList.value.pop()
    
  }
  
}

const backToSelectedFolder = (value) => {

  const index = liList.value.findIndex(item => item.id === value.id)

  if (index !== -1) {
    // Conserve les éléments jusqu'à celui sélectionné (inclus)
    liList.value = liList.value.slice(0, index + 1)
    
    currentParentId.value = value.id
    resetImages(currentParentId.value)
    resetFolders(currentParentId.value)
  }
}

const backToHome = () => {

  liList.value = []
  
  currentParentId.value = 1
  resetImages(currentParentId.value)
  resetFolders(currentParentId.value)
}



const resetFolders = (id) =>
 {
  folders.value = []
  for (const folder of allFolders.value) {
    if (folder.parent_id == id) {
      folders.value.push({
        ...folder,
        markedForDelete: !!folder.markedForDelete,
        children: []
      })
    }
  }
 }

 const resetImages = (id) =>
 {
  images.value = []
  for(const image of allImages.value) {
    if (image.folder_id == id){
      images.value.push({
        ...image,
        markedForDelete: !!image.markedForDelete,
        children: []
      })
    }

  }
 }



const renameFolder = async (id, name) => {
  const folderInfo = {
    id: id,
    new_name: name
  }

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=updateFolderName', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(folderInfo),
  });

  if (!response.ok) {
    isAdded.value = false
    isMessage.value = true
    message.value = t('error fetching data')
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    

    await getFolders(parseInt(id))
    console.log('id: ',id)
    console.log('allFolders.value: ',allFolders.value)
    
    
    
  } else {
    isMessage.value = true
    message.value = t(textResponse.message)
  }
}

const renameImages = async (id, name) => {
  const folderInfo = {
    id: id,
    new_name: name
  }

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=updateImageName', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(folderInfo),
  });

  if (!response.ok) {
    isAdded.value = false
    isMessage.value = true
    message.value = t('error fetching data')
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    
    await getImages()
    
  } else {
    isMessage.value = true
    message.value = t(textResponse.message)
  }
}

const getFolders = async (id) => {
  isAdding.value = true;

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=getFolders', {
    method: 'GET'
  });

  if (!response.ok) {
    isAdding.value = false;
    isMessage.value = true
    message.value = t('error fetching data')
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    allFolders.value = textResponse.data.map(folder => ({
      ...folder,
      markedForDelete: !!folder.marked_for_delete, // conversion en booléen
      children: []
    }))
    currentParentId.value = id
    resetFolders(parseInt(id))
    isAdding.value = false;
  } else {
    
    isAdding.value = false;
    isMessage.value = true
    message.value = t(textResponse.message)
  }

}

const getImages = async () => {
  isAdding.value = true;

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=getImages', {
    method: 'GET'
  });

  if (!response.ok) {
    isAdding.value = false;
    isMessage.value = true
    message.value = t('error fetching data')
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    allImages.value = textResponse.data
    if(currentParentId.value) {
      resetImages(currentParentId.value)
    } else {
      resetImages(0)
    }
    
    isAdding.value = false;
    
  } else {
    isAdding.value = false;
    isMessage.value = true
    message.value = t(textResponse.message)
  }

}

const createFolder = async (name, parrentId) => {

  const folderInfo = 
  {
    "name": name,
    "parent_id": parrentId,
    "size": 0,
    "marked_for_delete": 0
  }

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=createFolder', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(folderInfo),
  });

  if (!response.ok) {
    isAdded.value = false
    isMessage.value = true
    message.value = t('error fetching data')
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {

    getFolders(parrentId)
  } else {
    isMessage.value = true
    message.value = t(textResponse.message)
    isAdded.value = false
  }

}

const deleteImages = async () => {
  
  const toDelete = images.value.filter(img => img.markedForDelete).map(img => img.id)
  if(toDelete.length > 0) {
    try {
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=deleteImages', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ imageIds: toDelete }),
      })

      const result = await response.json()
      if (result.success) {
        getImages() // recharge la liste
        
      } else {
        isMessage.value = true
        message.value = t(textResponse.message)
      }
    } catch (err) {
      isMessage.value = true
      message.value = t('error in delete images')
    }
  }

}


const deleteFolder = async () => {
  const toDelete = []
  for(var i = 0; i < flatFolders.value.length; i++) {
    if (flatFolders.value[i].markedForDelete) {
      toDelete.push(flatFolders.value[i].id)
    }
  }
  if(toDelete.length > 0) {
    try {
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=deleteFolders', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ folder_ids: toDelete }),
      })

      const text = await response.text()

      const result = JSON.parse(text)
      if (result.success) {
        getFolders(1)
      } else {
        isMessage.value = true
        message.value = t(result.message)
      }

    } catch (err) {
      isMessage.value = true
      message.value = t('error in delete folders')
    }
  }


}

const moveImageToFolder = async (imageId, targetFolderId) => {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=moveImage', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ image_id: imageId, folder_id: targetFolderId }),
  })

  const result = await response.json()
  if (!result.success) {
    console.error('Erreur déplacement image : ', result.message)
  }
}

const moveFolderToFolder = async (folderId, targetParentId) => {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=moveFolder', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ folder_id: folderId, parent_id: targetParentId }),
  })

  const result = await response.json()
  if (!result.success) {
    console.error('Erreur déplacement dossier : ', result.message)
  }
}



onMounted(() => {
  getFolders(1)
  getImages()
  const observer = new IntersectionObserver((entries) => {
    if (entries[0].isIntersecting) {
      visibleCount.value += batchSize
    }

    if (visibleCount.value >= flatFolders.value.length) {
      observer.disconnect()
    }

  })

  if (loadMoreRef.value) {
    observer.observe(loadMoreRef.value)
  }

  ready.value = true
})

</script>

<style>

/* Bloquer sélection de texte et gestures par défaut */
* {
  -webkit-touch-callout: none; /* désactive le menu contextuel iOS */
  -webkit-user-select: none;
  user-select: none;
}

.backCalque {
  position: fixed;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(2px);         /* flou principal */
  -webkit-backdrop-filter: blur(2px); /* compatibilité Safari */
  z-index: 2000;
}

.folder-tree {
  width: 100%;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 2fr));
  gap: 12px;
  padding: 12px;
  max-height: 400px;
  overflow-y: auto;

  /* Scrollbar styling */
  scrollbar-width: thin;              /* Firefox */
  scrollbar-color: #888 transparent; /* Firefox */
}

/* Chrome, Edge, Safari */
.folder-tree::-webkit-scrollbar {
  width: 8px;
}

.folder-tree::-webkit-scrollbar-track {
  background: transparent;
}

.folder-tree::-webkit-scrollbar-thumb {
  background-color: rgba(125, 105, 142, 0.6); /* couleur mauve translucide */
  border-radius: 10px;
  border: 2px solid transparent; /* espace autour */
  background-clip: content-box;
  transition: background-color 0.3s ease;
}

.folder-tree::-webkit-scrollbar-thumb:hover {
  background-color: rgba(125, 105, 142, 0.9);
}

.folder-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  user-select: none;
  padding: 8px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.folder-item:hover {
  background-color: #e0e0e0;
}

.folder-item folderbox {
  margin-bottom: 6px;
}



.modalExp {
  position: fixed;
  left: 50%;
  top: 50%;
  transform: translate(-50%, -50%);
  width: 90%;
  max-width: 800px;       /* ou ce que tu veux */
  max-height: 80vh;       /* 80% de la hauteur de la fenêtre */
  overflow-y: auto;       /* activation du scroll vertical */
  padding: 16px;
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.9);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  z-index: 2000;
  
  /* On enlève le centrage vertical pour laisser défiler */
  display: flex;
  flex-direction: column;
  align-items: stretch;   /* <-- important */
  justify-content: flex-start; /* <-- important */
  backdrop-filter: blur(4px);         /* flou principal */
  -webkit-backdrop-filter: blur(4px); /* compatibilité Safari */
}
.dark .modalExp {
  background: rgba(0, 0, 0, 0.8);
  border: 1px solid rgb(29, 29, 29);
  box-shadow: 0 4px 8px #000000e0;
}

.observer-trigger {
  height: 1px;
}

.download-btn {
  background: #3490dc;
  color: white;
  border: none;
  padding: 0.4rem 0.8rem;
  border-radius: 0.25rem;
  font-size: 0.85rem;
  cursor: pointer;
  margin-top: 0.3rem;
}
.download-btn:hover {
  opacity: 0.9;
}


</style>


