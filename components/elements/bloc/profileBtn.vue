<template>
  <div v-if="isLargeScreen"  class="flex items-center justify-between h-8">
    <NuxtLink class="flex items-center justify-between h-7 w-52 max-w-52 min-w-24 min-h-7 p-0.5 mx-1.25 text-lg font-bold rounded-full bg-zioly2 text-whizy dark:shadow-darky" to="/connexion">
      <div v-if="isAuth" class="flex items-center justify-center w-6 h-6 min-w-6 min-h-6 overflow-hidden bg-white rounded-full">
        <img :src="`https://management.hoggari.com/uploads/profile/${profileImage}`" :alt="user" class="object-cover w-full h-full">
      </div>
      <div class="flex items-center w-24 h-7 max-w-30 min-w-12 min-h-7 mx-0.5">
        {{ user }}
      </div>
    </NuxtLink>
  </div>

  <div v-else class="flex items-center justify-between h-8">
    <NuxtLink class="flex items-center justify-center h-7 w-auto max-w-36 min-w-7 min-h-7 mx-1.25 text-lg font-bold border rounded-full bg-zioly2 text-whizy shadow-zioly2 border-zioly1 dark:shadow-darky" to="/connexion">
      <div v-if="isAuth" class="flex items-center justify-center w-6 h-6 min-w-6 min-h-6 overflow-hidden bg-white rounded-full">
        <img :src="`https://management.hoggari.com/uploads/profile/${profileImage}`" :alt="user" class="object-cover w-full h-full">
      </div>
      <div v-else>
        <div class="flex items-center w-24 h-7 max-w-30 min-w-12 min-h-7 mx-0.5">
          {{ user }}
        </div>
      </div>
    </NuxtLink>
  </div>
</template>

<script setup>
  const isAuth = useState('isAuth')
  const isLargeScreen = useState('isLargeScreen')

  const profileImage = ref('')

  onMounted(() => {
    testLogin()
  })

  defineProps({
    user: String,
  })

  const testLogin = async () => {
    const stored = localStorage.getItem('auth')

    if (stored) {
      //const data = JSON.parse(stored) // maintenant c’est un objet JS
      const data2 = JSON.parse(stored) // maintenant c’est un objet JS
       // tu peux y accéder directement
      profileImage.value = data2.profile_image
    }
  }
</script>