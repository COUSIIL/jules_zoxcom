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

      <div class="listTable">
        <div v-if="files.length === 0" style="width: 100%; text-align: center; color: var(--color-garry);">
          {{ t('No documents found.') }}
        </div>
        <div v-for="file in files" :key="file.id" class="childElement2" style="flex-direction: row; align-items: center; justify-content: space-between; width: 100%;">
          <div style="display: flex; align-items: center; overflow: hidden;">
            <Icon :name="getFileIcon(file.file_type)" size="24" style="margin-right: 10px; color: var(--color-darkly);" />
            <div style="display: flex; flex-direction: column; overflow: hidden;">
              <h4 style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ file.original_name }}</h4>
              <h5>{{ formatDate(file.created_at) }} • {{ (file.file_size / 1024).toFixed(1) }} KB</h5>
            </div>
          </div>

          <div style="display: flex; gap: 5px;">
            <a :href="`https://management.hoggari.com/backend/uploads/user_files/${file.stored_name}`" target="_blank" class="numberBtn" :title="t('Download')">
               <Icon name="tabler:download" size="18" />
            </a>
            <button v-if="isEditable" @click="deleteFile(file.id)" class="numberBtn" style="color: var(--color-rady);" :title="t('Delete')">
               <Icon name="tabler:trash" size="18" style="color: var(--color-rady);" />
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

const { t } = useLang()
const route = useRoute()
const user = ref('')
const members = ref([])
const auth = ref(null)
const profileImageFile = ref(null)
const isEditable = ref(false)
const files = ref([])

const defaultImage = 'https://cdn-icons-png.flaticon.com/512/149/149071.png'

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
      files.value = result.data
    }
  } catch (e) {
    console.error(e)
  }
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

const deleteFile = async (fileId) => {
  if (!confirm(t('Are you sure you want to delete this file?'))) return
  const authentic = JSON.parse(localStorage.getItem('auth'))

  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=deleteUserFile', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ file_id: fileId, user_id: auth.value.id, token: authentic?.token || '' })
    })
    const result = await response.json()
    if (result.success) {
      files.value = files.value.filter(f => f.id !== fileId)
    } else {
      alert(result.message)
    }
  } catch (e) {
    alert(t('Delete failed'))
  }
}

const getFileIcon = (type) => {
  if (['jpg', 'jpeg', 'png'].includes(type)) return 'tabler:photo'
  if (type === 'pdf') return 'tabler:file-type-pdf'
  if (['doc', 'docx'].includes(type)) return 'tabler:file-type-doc'
  return 'tabler:file'
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString()
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

/* 🌙 Mode sombre */
.dark .profile-container {
  background: var(--color-darkly);
  color: var(--color-whity);
}

.dark .profile-image {
  border-color: var(--color-darkow);
}
</style>
