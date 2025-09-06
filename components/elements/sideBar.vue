<template>
  <div class="simpleBack" v-if="prop.isVisible" @click="viewMenu">
    
  </div>

  <div :class="[isLargeScreen ? 'menuSideBar' : 'menuSideBar2', { open: isHovered || prop.isVisible }]" 
  @mouseover="handleHover(true)"
  @mouseleave="handleHover(false)">

    <div style="font-size: 14px; font-weight: bold;">
      v1 {{ t('valid until: ') }} 	23/03/2026
    </div>

    <FlagBtn/>


    

    <ul>
      
      
      <li @click="close">
        
        <NuxtLink class="title" to="/" exact-active-class="selected">
          
          <div class="iconi" v-html="icons['home']">

          </div>
          <h3>
            {{ t('home') }}
          </h3>
          
        </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/shops" exact-active-class="selected">
          <div class="iconi" v-html="icons['store']">

          </div>
          <h3>
            {{ t('store') }}
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/team" exact-active-class="selected">
          
          <div class="iconi" v-html="icons['team']">

          </div>
          <h3>
            {{ t('team') }}
          
          </h3>
        </NuxtLink>
      </li>
      <li @click="close">
        
        <NuxtLink class="title" to="/orders" exact-active-class="selected">
          
          <div class="iconi" v-html="icons['order']">

          </div>
          <h3>
            {{ t('orders') }}
          
          </h3>
          </NuxtLink>
      </li>
      <li @click="close">
        
        <NuxtLink class="title" to="/products" exact-active-class="selected">
          
          <div class="iconi" v-html="icons['package']">

          </div>
          <h3>
            {{ t('products') }}
          
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/categories" exact-active-class="selected">
          
          <div class="iconi" v-html="icons['category']">

          </div>
          <h3>
            {{ t('categories') }}
          
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/discount" exact-active-class="selected">
          
          <div class="iconi" v-html="icons['discount']">

          </div>
          <h3>
            {{ t('discounts') }}
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/customers" exact-active-class="selected">
          
          <div class="iconi" v-html="icons['customer']">

          </div>
          <h3>
            {{ t('customers') }}
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/delivery" exact-active-class="selected">
          
          <div class="iconi" v-html="icons['delivery']">

          </div>
          <h3>
            {{ t('delivery') }}
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/modules" exact-active-class="selected">
          <div class="iconi" v-html="icons['puzzle']">

          </div>
          <h3>
            {{ t('modules') }}
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/ban" exact-active-class="selected">
          <div class="iconi" v-html="icons['unautorized']">

          </div>
          <h3>
            {{ t('black list') }}
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/bank" exact-active-class="selected">
          <div class="iconi" v-html="icons['bank']">

          </div>
          <h3>
            {{ t('banks') }}
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/transaction" exact-active-class="selected">
          <div class="iconi" v-html="icons['transfer']">

          </div>
          <h3>
            {{ t('transaction') }}
          </h3>
          </NuxtLink>
      </li>

      <li @click="close">
        
        <NuxtLink class="title" to="/setings" exact-active-class="selected">
          <div class="iconi" v-html="icons['settings']">

          </div>
          <h3>
            {{ t('settings') }}
          </h3>
          </NuxtLink>
      </li>


      <li @click="close">
        
        <button class="title" style="cursor: pointer;" @click="handleLogout">
          
          <div class="iconi" v-html="icons['disconnect']">

          </div>
          <h3>
            {{ t('disconnect') }}
          </h3>
          </button>
      </li>
      
      
    </ul>

    <div style="height: 50px;">

    </div>

  </div>
</template>

<script setup>

import FlagBtn from './bloc/flagBtn.vue'
import icons from '~/public/icons.json'

const isLargeScreen = useState('isLargeScreen')

var prop = defineProps({
  isVisible: Boolean,

})

const justClicked = ref(false)

const { t } = useLang()

// Déclaration des emits
const emit = defineEmits(['viewMenu', 'handleLogout', 'close'])

// Méthode qui émet l'événement
const viewMenu = () => {

    emit('viewMenu')
}


const handleLogout = () => {
    emit('handleLogout')
}

const isHovered = ref(false)

function handleHover(state) {
  if(justClicked.value === false) isHovered.value = state
  
  // Tu peux exécuter une logique ici (changer style, charger données, etc.)
}

const close = () => {
  justClicked.value = true
  isHovered.value = false
  emit('close')
  setTimeout(() => {
    justClicked.value = false
  }, 500)
}
</script>

<style>

.iconi svg {
  width: 20px;
  height: 20px;
  padding-inline: 0;
}

.title {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.title h3 {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 12px;
  margin-inline: 5px;
}

.simpleBack {
position: fixed;
width: 100%;
height: 100%;
z-index: 100; /* Place le menu au premier plan */
}

.menuSideBar {
  width: 250px;
  height: 100%;
  position: fixed;
  top: 50px;
  right: 0;
  transform: translateX(80%);
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1); /* 🎯 Effet fluide */
  z-index: 5000;
  background: var(--color-whitly);
  box-shadow: 0px 8px 6px var(--color-tioly);
  overflow-y: auto;  /* active le scroll vertical */
  scrollbar-width: thin; /* style scrollbar (Firefox) */
}

/* Optionnel : style scrollbar pour Chrome/Safari/Edge */
.menuSideBar::-webkit-scrollbar {
  width: 6px;
}
.menuSideBar::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.3);
  border-radius: 3px;
}
.menuSideBar::-webkit-scrollbar-track {
  background: transparent;
}

.menuSideBar.hovered,
.menuSideBar2.hovered {
  background-color: #f0f0f0;
}

.menuSideBar.open {
  transform: translateX(0);
}


.menuSideBar svg {
  color: var(--color-zioly4);
  width: 30px;
}

.menuSideBar svg path {
  stroke-width: 1.5;
}

.menuSideBar li{
  height: 40px;
  margin: 5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 8px;
  color: var(--color-zioly4);

}

.menuSideBar li .selected{
  height: 40px;
  margin: 5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 8px;
  background-color: var(--color-whizy);
}

.dark .menuSideBar li .selected{
  background-color: var(--color-darky);
}


.dark .menuSideBar {
  background: var(--color-darkly);
  box-shadow: 0px 8px 6px var(--color-darky);

}

.dark .menuSideBar li {
    color: var(--color-whizy);
}

.dark .menuSideBar svg {
  color: var(--color-whitly);
}

.menuSideBar2 {
  width: 200px;
  height: 100%;
  position: fixed;
  top: 50px;
  right: 0;
  transform: translateX(100%);
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1); /* 🎯 Effet fluide */
  z-index: 5000;
  background: var(--color-whitly);
  box-shadow: 0px 8px 6px var(--color-tioly);
  overflow-y: auto;  /* active le scroll vertical */
  scrollbar-width: thin; /* style scrollbar (Firefox) */
}

/* Optionnel : style scrollbar pour Chrome/Safari/Edge */
.menuSideBar2::-webkit-scrollbar {
  width: 6px;
}
.menuSideBar2::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.3);
  border-radius: 3px;
}
.menuSideBar2::-webkit-scrollbar-track {
  background: transparent;
}

.menuSideBar2.open {
  transform: translateX(0);
}


.menuSideBar2 svg {
  color: var(--color-zioly4);
  width: 30px;
}

.menuSideBar2 svg path {
  stroke-width: 1.5;
}

.menuSideBar2 li{
    height: 35px;
    margin: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px;
    color: var(--color-zioly4);
}

.dark .menuSideBar2 {
  background: var(--color-darkly);
  box-shadow: 0px 8px 6px var(--color-darky);

}
.dark .menuSideBar2 li {
    color: var(--color-whizy);
}
.dark .menuSideBar2 svg {
  color: var(--color-whitly);
}

.menuSideBar2 li .selected{
  height: 35px;
  margin: 5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 8px;
  background-color: var(--color-whizy);
}

.dark .menuSideBar2 li .selected{
  height: 35px;
  margin: 5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 8px;
  background-color: var(--color-darky);
}



</style>