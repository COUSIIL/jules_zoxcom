<template>
  <div v-if="auth" class="container">
    <div class="profile-container">
      <!-- Photo de profil -->
      <div class="profile-image-container">
        <img v-if="auth.profile_image"
          class="profile-image"
          :src="`https://management.hoggari.com/uploads/profile/${auth.profile_image}`"
          alt="Profile Image"
        />
        <img v-else class="profile-image" :src="defaultImage" alt="defaultImage">
        <label v-if="isEditable" class="btn1" style="margin-top: 10px; justify-content: center;">
          <Icon name="tabler:camera" size="20" />
          {{ t('Change photo') }}
          <input type="file" @change="updateProfileImage" hidden />
        </label>
      </div>

      <!-- Informations utilisateur -->
      <div class="profile-info">
        <div class="floating-input2">
          <input type="text" v-model="auth.username" disabled placeholder=" " />
          <label>{{ t('Username') }}</label>
        </div>

        <div class="floating-input2">
          <input type="text" v-model="auth.name" :disabled="!isEditable" placeholder=" " />
          <label>{{ t('First name') }}</label>
        </div>

        <div class="floating-input2">
          <input type="text" v-model="auth.family_name" :disabled="!isEditable" placeholder=" " />
          <label>{{ t('Last name') }}</label>
        </div>

        <div class="floating-input2">
          <input type="email" v-model="auth.email" :disabled="!isEditable" placeholder=" " />
          <label>{{ t('Email') }}</label>
        </div>

        <div class="floating-input2">
          <input type="text" v-model="auth.ip_adresse" disabled placeholder=" " />
          <label>{{ t('IP Address') }}</label>
        </div>
      </div>
    </div>

    <!-- Description Professionnelle -->
    <div class="moreDirect">
      <div class="order3">
        <h4>{{ t('Professional Description') }}</h4>
        <button v-if="isEditable" class="gBtn" @click="saveProfile">
           <div class="holder">
             {{ t('Save') }}
           </div>
           <div class="svg bg-rangy">
             <Icon name="tabler:device-floppy" size="18" style="color: white;" />
           </div>
        </button>
      </div>
      <div class="des">
        <textarea v-if="isEditable" v-model="auth.description" class="desBar auto-expand" :placeholder="t('Describe your professional path...')"></textarea>
        <p v-else class="desBar" style="white-space: pre-wrap;">{{ auth.description || t('No description available.') }}</p>
      </div>
    </div>

    <!-- Explorateur de Fichiers -->
    <div class="moreDirect">
       <div class="order3">
        <h4>{{ t('Documents') }}</h4>
        <div style="display: flex; gap: 10px;">
          <button v-if="hasSelection && isEditable" class="gBtn" @click="deleteSelected" style="background-color: var(--color-rady);">
               <div class="holder">
                 {{ t('Delete Selected') }}
               </div>
               <div class="svg">
                 <Icon name="tabler:trash" size="18" style="color: white;" />
               </div>
          </button>

          <label v-if="isEditable" class="gBtn" style="width: auto;">
               <div class="holder">
                 {{ t('Upload') }}
               </div>
               <div class="svg bg-green">
                 <Icon name="tabler:upload" size="18" style="color: white;" />
               </div>
               <input type="file" @change="uploadFile" hidden />
          </label>
        </div>
      </div>

      <div class="file-grid">
        <div v-if="files.length === 0" style="width: 100%; text-align: center; color: var(--color-garry); grid-column: 1 / -1;">
          {{ t('No documents found.') }}
        </div>

        <div v-for="file in files" :key="file.id" class="file-item">
          <ImageBox
             v-if="['jpg', 'jpeg', 'png', 'webp'].includes(file.file_type)"
             :image="file"
             :src="getFileUrl(file)"
             @clicked="handleFileClick(file)"
             @toggle-delete="handleToggleDelete"
          />
          <FileBox
             v-else
             :file="file"
             @clicked="handleFileClick(file)"
             @toggle-delete="handleToggleDelete"
          />
        </div>
      </div>
    </div>

    <!-- Preview Modal -->
    <div v-if="showPreview" class="modal-overlay" @click.self="closePreview">
      <div class="modal-content">
        <button class="close-btn" @click="closePreview">
          <Icon name="tabler:x" size="24" />
        </button>

        <div class="preview-body">
          <img v-if="isPreviewImage" :src="getFileUrl(previewFile)" class="preview-img" />
          <iframe v-else-if="previewFile.file_type === 'pdf'" :src="getFileUrl(previewFile)" class="preview-iframe"></iframe>
          <div v-else class="no-preview">
            <Icon name="tabler:file-unknown" size="64" />
            <p>{{ t('No preview available for this file type.') }}</p>
          </div>
        </div>

        <div class="preview-footer">
          <a :href="getFileUrl(previewFile)" download class="gBtn" style="width: auto; text-decoration: none;">
             <div class="holder">
               {{ t('Download') }}
             </div>
             <div class="svg bg-blue">
               <Icon name="tabler:download" size="18" style="color: white;" />
             </div>
          </a>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'
import ImageBox from '../../components/elements/bloc/imageBox.vue'
import FileBox from '../../components/elements/bloc/fileBox.vue'

const { t } = useLang()
const route = useRoute()
const user = ref('')
const members = ref([])
const auth = ref(null)
const profileImageFile = ref(null)
const isEditable = ref(false)
const files = ref([])
const showPreview = ref(false)
const previewFile = ref(null)

const defaultImage = 'https://cdn-icons-png.flaticon.com/512/149/149071.png'

const isPreviewImage = computed(() => {
  return previewFile.value && ['jpg', 'jpeg', 'png', 'webp'].includes(previewFile.value.file_type)
})

const hasSelection = computed(() => {
  return files.value.some(f => f.markedForDelete)
})

onMounted(() => {
  const storedId = route.params.id
  user.value = storedId?.toString() || null
  getUsers()
})

const getUsers = async () => {
  const authentic = JSON.parse(localStorage.getItem('auth'))
  
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=getUsers')
  if (!response.ok) {
    console.error(t('Error retrieving users'))
    return
  }
  const result = await response.json()
  members.value = result.data
  auth.value = members.value.find(u => u.username === user.value) || null

  if (auth.value && authentic && auth.value.username === authentic.username) {
    isEditable.value = true
  } else {
    isEditable.value = false
  }
  
  if (auth.value) {
    getUserFiles()
  }
}

const getUserFiles = async () => {
  if (!auth.value?.id) return
  try {
    const response = await fetch(`https://management.hoggari.com/backend/api.php?action=getUserFiles&user_id=${auth.value.id}`)
    const result = await response.json()
    if (result.success) {
      files.value = result.data.map(f => ({
        ...f,
        name: f.original_name,
        markedForDelete: false
      }))
    }
  } catch (e) {
    console.error(e)
  }
}

const getFileUrl = (file) => {
  if (!file) return ''
  return `https://management.hoggari.com/backend/uploads/user_files/${file.stored_name}`
}

const handleFileClick = (file) => {
  previewFile.value = file
  showPreview.value = true
}

const closePreview = () => {
  showPreview.value = false
  previewFile.value = null
}

const handleToggleDelete = ({ id, marked }) => {
  if (!isEditable.value) return
  const f = files.value.find(x => x.id === id)
  if (f) f.markedForDelete = marked
}

const deleteSelected = async () => {
  const selected = files.value.filter(f => f.markedForDelete)
  if (selected.length === 0) return

  if (!confirm(t('Are you sure you want to delete selected files?'))) return

  const authentic = JSON.parse(localStorage.getItem('auth'))

  for (const file of selected) {
    try {
      await fetch('https://management.hoggari.com/backend/api.php?action=deleteUserFile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ file_id: file.id, user_id: auth.value.id, token: authentic?.token || '' })
      })
    } catch (e) {
      console.error('Delete failed for', file.id)
    }
  }
  // Refresh list
  getUserFiles()
}


const updateProfileImage = async (event) => {
  const file = event.target.files[0]
  if (!file || !auth.value?.id) return

  profileImageFile.value = file

  // Prévisualisation immédiate
  const reader = new FileReader()
  reader.onload = () => {
    auth.value.profile_image = reader.result
  }
  reader.readAsDataURL(file)

  // Envoi au backend
  const formData = new FormData()
  formData.append('id', auth.value.id)
  formData.append('profile_image', file)

  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=updateProfileImage', {
      method: 'POST',
      body: formData
    })

    const result = await response.json()

    if (result.success) {
      auth.value.profile_image = result['profile_image']
    } else {
      console.error(t('Error updating image'))
    }
  } catch (error) {
    console.error(t('Network error:'), error)
  }
}

const saveProfile = async () => {
  if (!auth.value || !auth.value.id) {
    alert(t('User not found'))
    return
  }

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=updateUserInfo', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id: auth.value.id,
      username: auth.value.username || '',
      email: auth.value.email || '',
      firstname: auth.value.name || '',
      lastname: auth.value.family_name || '',
      role: auth.value.role || '',
      description: auth.value.description || ''
    }),
  })

  const result = await response.json()
  if (result.success) {
    alert(t('Profile updated successfully!'))
  } else {
    alert(t('Error during update.'))
  }
}

const uploadFile = async (event) => {
  const file = event.target.files[0]
  if (!file || !auth.value?.id) return
  const authentic = JSON.parse(localStorage.getItem('auth'))

  const formData = new FormData()
  formData.append('user_id', auth.value.id)
  formData.append('file', file)
  formData.append('token', authentic?.token || '')

  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=uploadUserFile', {
      method: 'POST',
      body: formData
    })
    const result = await response.json()
    if (result.success) {
      getUserFiles() // Refresh list
    } else {
      alert(result.message || t('Error uploading file'))
    }
  } catch (e) {
    alert(t('Upload failed'))
  }
}

</script>

<style scoped>
.profile-container {
  padding: 20px;
  background: var(--color-whitly);
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-block: 20px;
}

.profile-image-container {
  flex: 1 1 200px;
  display: flex;
  justify-content: center;
  flex-direction: column;
  align-items: center;
}

.profile-image {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--color-whizy);
}

.profile-info {
  flex: 2 1 400px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

/* File Grid */
.file-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
  gap: 15px;
  margin-top: 15px;
  width: 100%;
}

.file-item {
  display: flex;
  justify-content: center;
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  z-index: 3000;
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background: var(--color-whitly);
  padding: 20px;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.dark .modal-content {
  background: var(--color-darkly);
  color: var(--color-whity);
}

.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--color-darkly);
}
.dark .close-btn {
  color: var(--color-whity);
}

.preview-body {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  margin-bottom: 20px;
  min-height: 200px;
}

.preview-img {
  max-width: 100%;
  max-height: 60vh;
  object-fit: contain;
  border-radius: 8px;
}

.preview-iframe {
  width: 100%;
  height: 60vh;
  border: none;
  border-radius: 8px;
}

.no-preview {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  color: var(--color-garry);
}

.preview-footer {
  display: flex;
  justify-content: center;
}

/* 🌙 Mode sombre */
.dark .profile-container {
  background: var(--color-darkly);
  color: var(--color-whity);
}

.dark .profile-image {
  border-color: var(--color-darkow);
}
</style>
