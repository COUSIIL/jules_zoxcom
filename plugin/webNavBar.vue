<template>

  <Nav :user="user" :isMounted="isMounted" :isDark="isDark" :isDeasy="isDeasy" :isAuthenticated="isAuthenticated" :logoDark="logoDark" :logoWhite="logoWhite" :varqWhite="varqWhite" :varqDark="varqDark" :isVisible="isVisible" @darkMode="toggleDarkMode" @sideBar="viewMenu" @viewMenu="toggleDarkMode" @handleLogout="handleLogout"/>


  <SideBar v-if="isAuthenticated" @viewMenu="viewMenu" @handleLogout="handleLogout" :isVisible="isVisible" @close="isVisible = false"/>


  
      

</template>



<script setup>
import { ref, onMounted } from "vue";
import Nav from '../components/elements/nav1.vue';
import SideBar from '../components/elements/sideBar.vue'
import { useLang } from '~/composables/useLang';

const { t } = useLang();

const isMounted = ref(false);
const isVisible = ref(false);
const isDark = ref();
const user = ref(t('connexion'));
const isAuthenticated = ref(false);
const isDeasy = ref(true);
const logoDark = ref("/zoxcom.svg");
const logoWhite = ref("/zoxcomWhite.svg");
const varqWhite = ref("/dini.svg");
const varqDark = ref("/diniWhite.svg");

const handleLogout = () => {
  localStorage.setItem('auth', null);
  localStorage.setItem('user', null);
  window.location.href = '/connexion' // Rediriger vers la page de connexion après déconnexion
}


onMounted(() => {
  if(localStorage.getItem('auth')) {
    const authData = JSON.parse(localStorage.getItem('auth'));
    if(authData) {
      user.value = authData.username;
      isAuthenticated.value = true;
    }
    
    
  }
    // Vérifie si le thème est enregistré dans le localStorage
    if (localStorage.getItem('darkMode')) {
      isDark.value = JSON.parse(localStorage.getItem('darkMode'));
    } else {
      // Si rien dans le localStorage, détecte le thème de l'appareil
      isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    // Applique la classe en fonction du thème détecté
    document.documentElement.classList.toggle('dark', isDark.value);

    isMounted.value = true;
});

const viewMenu = () => {
  isVisible.value = !isVisible.value;
  localStorage.removeItem('productID');
};

const toggleDarkMode = () => {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle("dark", isDark.value);
  localStorage.setItem('darkMode', isDark.value);
};



</script>

