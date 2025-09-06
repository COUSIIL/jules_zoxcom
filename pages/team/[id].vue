<template>
  <div v-if="auth" class="profile-container">
    <!-- Photo de profil -->
    <div class="profile-image-container">
      <img v-if="auth.profile_image"
        class="profile-image"
        :src="`https://management.hoggari.com/uploads/profile/${auth.profile_image}`"
        alt="Profile Image"
      />
      <img v-else class="profile-image" :src="defaultImage" alt="defaultImage">
      <label v-if="isEditable" class="upload-btn">
        Changer la photo
        <input type="file" @change="updateProfileImage" hidden />
      </label>
    </div>

    <!-- Informations utilisateur -->
    <div class="profile-info">
      <div class="field">
        <label>Nom d'utilisateur</label>
        <input type="text" v-model="auth.username" disabled />
      </div>

      <div class="field">
        <label>Prénom</label>
        <input v-if="isEditable" type="text" v-model="auth.name" placeholder="Entrez votre prénom" />
        <input v-else type="text" v-model="auth.name" placeholder="Entrez votre prénom" disabled/>
      </div>

      <div class="field">
        <label>Nom</label>
        <input v-if="isEditable" type="text" v-model="auth.family_name" placeholder="Entrez votre nom" />
        <input v-else type="text" v-model="auth.name" placeholder="Entrez votre prénom" disabled/>
      </div>

      <div class="field">
        <label>Email</label>
        <input v-if="isEditable" type="email" v-model="auth.email" placeholder="Entrez votre email" />
        <input v-else type="email" v-model="auth.email" placeholder="Entrez votre email" disabled/>
      </div>

      <div class="field">
        <label>Adresse IP</label>
        <input type="text" v-model="auth.ip_adresse" disabled />
      </div>

      <button v-if="isEditable" class="save-btn" @click="saveProfile">💾 Enregistrer</button>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
const user = ref('')
const members = ref([])
const auth = ref(null)
const profileImageFile = ref(null)
const isEditable = ref(false)

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
    console.error('Erreur de récupération des utilisateurs')
    return
  }
  const result = await response.json()
  members.value = result.data
  auth.value = members.value.find(u => u.username === user.value) || null

  if(auth.value['username'] === authentic['user']) {
    isEditable.value = true
  } else {
    isEditable.value = false
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
      console.error('Erreur lors de la mise à jour de l’image')
    }
  } catch (error) {
    console.error('Erreur réseau :', error)
  }
}


const saveProfile = async () => {
  if (!auth.value || !auth.value.id) {
    alert('Utilisateur introuvable')
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
    alert('Profil mis à jour avec succès !')
  } else {
    alert('Erreur lors de la mise à jour.')
  }
}

</script>

<style scoped>
.profile-container {
  max-width: 800px;
  margin: 30px auto;
  padding: 20px;
  background: var(--color-whitly);
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  transition: background 0.3s, color 0.3s;
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

.upload-btn {
  display: inline-block;
  margin-top: 10px;
  padding: 8px 15px;
  background: var(--color-whizy);
  color: var(--color-darky);
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.3s;
}
.upload-btn:hover {
  background: var(--color-darkow);
  color: var(--color-whity);
}

.profile-info {
  flex: 2 1 400px;
}

.field {
  margin-bottom: 15px;
}

.field label {
  display: block;
  font-weight: bold;
  margin-bottom: 5px;
}

.field input {
  width: 100%;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid var(--color-whizy);
  background: var(--color-whity);
  color: var(--color-darky);
  transition: background 0.3s, color 0.3s;
}

.save-btn {
  padding: 10px 20px;
  background: var(--color-whizy);
  color: var(--color-darky);
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s, color 0.3s;
}
.save-btn:hover {
  background: var(--color-darkow);
  color: var(--color-whity);
}

/* 🌙 Mode sombre */
.dark .profile-container {
  background: var(--color-darkly);
  color: var(--color-whity);
}

.dark .profile-image {
  border-color: var(--color-darkow);
}

.dark .upload-btn {
  background: var(--color-darkow);
  color: var(--color-whity);
}
.dark .upload-btn:hover {
  background: var(--color-whizy);
  color: var(--color-darky);
}

.dark .field input {
  background: var(--color-darkow);
  border-color: var(--color-darkow);
  color: var(--color-whity);
}

.dark .save-btn {
  background: var(--color-darkow);
  color: var(--color-whity);
}
.dark .save-btn:hover {
  background: var(--color-whizy);
  color: var(--color-darky);
}
</style>
