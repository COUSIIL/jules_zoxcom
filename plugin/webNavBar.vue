<template>

  <Nav :user="user" :isMounted="isMounted" :isDark="isDark" :isDeasy="isDeasy" :isAuthenticated="isAuthenticated" :logoDark="logoDark" :logoWhite="logoWhite" :varqWhite="varqWhite" :varqDark="varqDark" :isVisible="isVisible" @darkMode="toggleDarkMode" @sideBar="viewMenu" @viewMenu="toggleDarkMode" @handleLogout="handleLogout"/>


  <SideBar @viewMenu="viewMenu" @handleLogout="handleLogout" :isVisible="isVisible" @close="isVisible = false"/>


  
      

</template>



<script setup>
import { ref, onMounted } from "vue";
import Nav from '../components/elements/nav1.vue';
import SideBar from '../components/elements/sideBar.vue'



const isMounted = ref(false);
const isVisible = ref(false);
const isDark = ref();
const user = ref('Connexion');
const isAuthenticated = ref(false);
const isDeasy = ref(true);
const logoDark = ref("/zoxcom.svg");
const logoWhite = ref("/zoxcomWhite.svg");
const varqWhite = ref("/dini.svg");
const varqDark = ref("/diniWhite.svg");

const handleLogout = () => {
  localStorage.setItem('auth', null);
  window.location.href = '/connexion' // Rediriger vers la page de connexion après déconnexion
}

onMounted(() => {
  if(JSON.parse(localStorage.getItem('auth'))) {
    const authData = JSON.parse(localStorage.getItem('auth'));
    user.value = authData.user;
    isAuthenticated.value = true;
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


<style>


.textModel{
max-width: 50px;
font-size: 2vh;
margin: 5px;
}

.price{
max-width: 100%;
font-size: 3vh;
font-weight: bold;
margin: 5px;
color: var(--color-rangy);
text-align: center;
}

.textProduct {
max-width: 100px;
max-height: 50px;
font-size: 2.5vh;
font-weight: bold;
margin: 5px;
overflow: hidden; /* Cache le texte qui dépasse */
display: -webkit-box;
-webkit-box-orient: vertical;
-webkit-line-clamp: 2; /* Nombre max de lignes avant coupure */
text-overflow: ellipsis; /* Ajoute "..." si coupé */
line-height: 1.2em; /* Ajuste l'espacement des lignes */
}


.btn2{
display: flex;
justify-content: space-around;
align-items: center;
width: 90%;
min-width: 100px;
max-width: 400px;
margin: 10px;
padding: 5px;
cursor: pointer;
background-color: var(--color-whity);
border-radius: 6px;
border: 1px solid var(--color-rangy);
}
.dark .btn2{
background-color: var(--color-darky);
}

.btn3{
display: flex;
justify-content: space-around;
align-items: center;
margin: 10px;
width: 90%;
min-width: 100px;
max-width: 400px;
padding: 5px;
cursor: pointer;
border: 1px solid var(--color-rangy);
background-color: var(--color-whity);
border-radius: 6px;
box-shadow: 0px 2px 5px var(--color-hoggari);
color: var(--color-darkly);
}
.dark .btn3{
background-color: var(--color-darky);
color: var(--color-whitly);
}

.btn1{
display: flex;
justify-content: space-around;
align-items: center;
width: 90%;
cursor: pointer;
border-radius: 6px;
min-width: 100px;
margin: 10px;
padding: 5px;
border: 1px solid var(--color-rangy);
box-shadow: 0px 2px 5px var(--color-rangy);
background-color: var(--color-whity);
}
.dark .btn1{
background-color: var(--color-darky);
}

.btn1 svg{
min-width: 20px;
margin-inline: 5px;
color: var(--color-darkly);
}
.dark .btn1 svg{
color: var(--color-whitly);
}

.input{
display: flex;
align-items: center;
width: 90%;
min-width: 200px;
max-width: 450px;
background: linear-gradient(to right, var(--color-whity), #e9f4f2);
border: 2px solid white;
border-radius: 5px;
padding: 5px;
overflow: hidden;
margin: 5px;
}
.dark .input{
background: linear-gradient(to right, var(--color-darky), #311a2f);
border: 2px solid black;
}

.input button{
background-color: var(--color-rangy);
cursor: pointer;
border: none;
cursor: pointer;
padding: 10px;
border-radius: 50%;
display: flex;
align-items: center;
justify-content: center;
}

.input button svg{
color: var(--color-whity);
}

.discountInput{
display: flex;
align-items: center;
width: 90%;
min-width: 200px;
max-width: 450px;
background-color: white;
border: 2px dashed var(--color-blue-400);
border-radius: 5px;
padding: 5px;
margin: 5px;
color: var(--color-blue-400);
font-size: 3vh;
font-weight: bold;
text-align: center;
}
.dark .discountInput{
background-color: var(--color-darkly);
border: 2px dashed var(--color-blue-400);
}
.discountInput:focus {
border: 2px dashed var(--color-blue-400);
}



.hiddenInput {
display: none; /* Cache l'input file */
z-index: 100;
}



.list{
height: auto; /* Permet d'ajuster la hauteur selon le contenu */
display: flex;
justify-content: center;
align-items: center;
width: 100%;
max-width: 800px;
border-radius: 6px;
transition: all 0.3s ease;
padding-block: 10px;
margin-block: 5px;
overflow: hidden;

}


.listTable {
display: flex;
flex-wrap: wrap; /* Permet aux éléments de passer à la ligne */
max-width: 800px;
gap: 10px; /* Espacement entre les éléments */
padding: 10px;
}




.list2 {
display: flex;
flex-wrap: nowrap; /* Empêche le retour à la ligne */
overflow-x: auto; /* Active le scroll horizontal */
scroll-snap-type: x mandatory; /* Active un défilement fluide */
max-width: 800px;
gap: 10px; /* Espacement entre les produits */
padding: 10px;
max-width: 100%;
white-space: nowrap; /* Empêche le retour à la ligne */
}


.list2::-webkit-scrollbar {
height: 6px;
background: var(--color-whity);
}
.dark .list2::-webkit-scrollbar {
height: 6px;
background: var(--color-darky);
}

.list2::-webkit-scrollbar-thumb {
background: var(--color-rangy);
border-radius: 4px;
}

.list2::-webkit-scrollbar-thumb:hover {
background: var(--color-hoggari);
}

.list2 > div {
scroll-snap-align: start; /* Chaque produit s'aligne sur le scroll */
flex: 0 0 auto; /* Les éléments restent en ligne */
padding: 0px;

}






.des{
display: flex;
align-items: center;
background-color: white;
border: 2px solid var(--color-gorry);
border-radius: 5px;
padding: 5px;
overflow: hidden;
transition: all 0.3s ease;
margin: 5px;
}
.dark .des{
background-color: var(--color-darkly);
border: 2px solid var(--color-garry);
}


.desBar {
flex: 1;
border: none;
outline: none;
width: 80%;
min-width: 200px;
background: transparent;
font-size: 16px;
padding: 10px 15px;
resize: none;
height: 40px;
overflow: hidden;
color: var(--color-darkly);
}

/* Effet focus */
.desBar:focus {
border-color: #007bff;
}

/* Mode sombre */
.dark .desBar {
background: transparent;
color: var(--color-whitly);
border-color: var(--color-darky);
}

.dark .desBar:focus {
border-color: #00bcd4;
}

/* Ajustement automatique de la hauteur */
.desBar.auto-expand {
min-height: 50px;
height: auto;
overflow-y: hidden;
}

.order{
width: 100%;
height: 60px;
display: flex;
max-width: 800px;
justify-content: center;
align-items: center;
margin-top: 5px;
border-radius: 6px 6px 0px 0px;
padding-inline: 10px;
background-color: var(--color-whitly);
box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .order{
background-color: var(--color-darkly);
}

.order2{
width: 100%;
height: 60px;
max-width: 800px;
display: flex;
justify-content: center;
align-items: center;
flex-direction: column;
margin-top: 5px;
border-radius: 6px 6px 0px 0px;
padding-inline: 10px;
background-color: var(--color-whitly);
box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .order2{
background-color: var(--color-darkly);
}

.order3{
width: 100%;
display: flex;
justify-content: space-between;
align-items: center;

}

.more{
display: flex;
align-items: center;
margin-block: 5px;
}

.moreD{
width: 100%;
max-width: 800px;
align-items: center;
margin-block: 5px;
border-radius: 6px;
padding: 10px;
background-color: var(--color-whitly);
box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .moreD{
background-color: var(--color-darkly);
}





</style>