<template>
  <div v-if="auth" class="flex flex-wrap max-w-3xl gap-5 p-5 mx-auto my-7 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.08)] bg-whitly dark:bg-darkly dark:text-whity transition-colors duration-300">
    <!-- Photo de profil -->
    <div class="flex flex-col items-center justify-center flex-1 basis-52">
      <img v-if="auth.profile_image"
        class="object-cover border-3 w-36 h-36 rounded-full border-whizy dark:border-darkow"
        :src="`https://management.hoggari.com/uploads/profile/${auth.profile_image}`"
        alt="Profile Image"
      />
      <img v-else class="object-cover border-3 w-36 h-36 rounded-full border-whizy dark:border-darkow" :src="defaultImage" alt="defaultImage">
      <label v-if="isEditable" class="inline-block px-4 py-2 mt-2.5 text-sm rounded-lg cursor-pointer bg-whizy text-darky dark:bg-darkow dark:text-whity hover:bg-darkow hover:text-whity dark:hover:bg-whizy dark:hover:text-darky transition-colors duration-300">
        {{ t('Change photo') }}
        <input type="file" @change="updateProfileImage" hidden />
      </label>
    </div>

    <!-- Informations utilisateur -->
    <div class="flex-2 basis-100">
      <div class="mb-4">
        <label class="block mb-1.25 font-bold">{{ t('Username') }}</label>
        <input type="text" v-model="auth.username" disabled class="w-full p-2 border rounded-lg bg-whitly border-whizy text-darky dark:bg-darkow dark:border-darkow dark:text-whity transition-colors duration-300" />
      </div>

      <div class="mb-4">
        <label class="block mb-1.25 font-bold">{{ t('First name') }}</label>
        <input v-if="isEditable" type="text" v-model="auth.name" :placeholder="t('Enter your first name')" class="w-full p-2 border rounded-lg bg-whitly border-whizy text-darky dark:bg-darkow dark:border-darkow dark:text-whity transition-colors duration-300" />
        <input v-else type="text" v-model="auth.name" :placeholder="t('Enter your first name')" disabled class="w-full p-2 border rounded-lg bg-whitly border-whizy text-darky dark:bg-darkow dark:border-darkow dark:text-whity transition-colors duration-300"/>
      </div>

      <div class="mb-4">
        <label class="block mb-1.25 font-bold">{{ t('Last name') }}</label>
        <input v-if="isEditable" type="text" v-model="auth.family_name" :placeholder="t('Enter your last name')" class="w-full p-2 border rounded-lg bg-whitly border-whizy text-darky dark:bg-darkow dark:border-darkow dark:text-whity transition-colors duration-300" />
        <input v-else type="text" v-model="auth.name" :placeholder="t('Enter your last name')" disabled class="w-full p-2 border rounded-lg bg-whitly border-whizy text-darky dark:bg-darkow dark:border-darkow dark:text-whity transition-colors duration-300"/>
      </div>

      <div class="mb-4">
        <label class="block mb-1.25 font-bold">{{ t('Email') }}</label>
        <input v-if="isEditable" type="email" v-model="auth.email" :placeholder="t('Enter your email')" class="w-full p-2 border rounded-lg bg-whitly border-whizy text-darky dark:bg-darkow dark:border-darkow dark:text-whity transition-colors duration-300" />
        <input v-else type="email" v-model="auth.email" :placeholder="t('Enter your email')" disabled class="w-full p-2 border rounded-lg bg-whitly border-whizy text-darky dark:bg-darkow dark:border-darkow dark:text-whity transition-colors duration-300"/>
      </div>

      <div class="mb-4">
        <label class="block mb-1.25 font-bold">{{ t('IP Address') }}</label>
        <input type="text" v-model="auth.ip_adresse" disabled class="w-full p-2 border rounded-lg bg-whitly border-whizy text-darky dark:bg-darkow dark:border-darkow dark:text-whity transition-colors duration-300" />
      </div>

      <button v-if="isEditable" class="px-5 py-2.5 bg-whizy text-darky border-none rounded-lg cursor-pointer hover:bg-darkow hover:text-whity dark:bg-darkow dark:text-whity dark:hover:bg-whizy dark:hover:text-darky transition-colors duration-300" @click="saveProfile">{{ t('💾 Save') }}</button>
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

</script>

