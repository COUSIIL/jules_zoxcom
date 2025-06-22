<template>
  <div>
    <div v-if="isAuthenticated">
          <!-- En-tête global (facultatif, si nécessaire sur toutes les pages) -->
      <Header />

      <!-- Le routeur Nuxt insère les pages ici -->

      <NuxtPage />
    </div>
    <div v-else>
      
      <Header />
      
      <NuxtPage />
    </div>
    

  </div>
</template>

<script setup>

import { useRouter } from 'vue-router'
import { onMounted, onBeforeUnmount, computed } from 'vue'
import Header from '~/plugin/webNavBar.vue'
//import LoaderBlack from '~/components/elements/animations/loaderBlack.vue'
//import LoaderWhite from '~/components/elements/animations/loaderWhite.vue'

//const isLargeScreen = useState('isLargeScreen')


// Variable globale partagée
const isLargeScreen = useState('isLargeScreen', () => false)
const isAuth = useState('isAuth', () => false)

const loading = ref(true);

const router = useRouter()

function checkScreenSize() {
  isLargeScreen.value = window.matchMedia('(min-width: 1024px)').matches
}

const isAuthenticated = computed(() => {
  if(JSON.parse(localStorage.getItem('auth'))) {
    loading.value = false;
    isAuth.value = true;
    return true
  } else {
    loading.value = false;
    isAuth.value = false;
    router.push('/connexion') // remplace "/login" par ta route de destination
    
    return false
  }
  // Exemple : retourne false pour déclencher la redirection
  
})


onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)

  
})


onBeforeUnmount(() => {
  window.removeEventListener('resize', checkScreenSize)
})
</script>

<style scoped>


div {
  text-align: center;
}
</style>
