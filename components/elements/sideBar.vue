<template>
  <div class="fixed top-0 left-0 z-50 w-full h-full bg-black bg-opacity-25" v-if="prop.isVisible" @click="viewMenu">
  </div>

  <div
    :class="[
      'fixed top-[50px] right-0 h-full z-50 transition-transform duration-500 ease-[cubic-bezier(0.25,1,0.5,1)]',
      'bg-whitly dark:bg-darkly shadow-lg dark:shadow-darky overflow-y-auto',
      isLargeScreen ? 'w-[250px] translate-x-full' : 'w-[200px] translate-x-full',
      { 'translate-x-0': isHovered || prop.isVisible }
    ]"
    @mouseover="handleHover(true)"
    @mouseleave="handleHover(false)">

    <div class="text-sm font-bold p-4">
      v1 {{ t('valid until: ') }} 23/03/2026
    </div>

    <FlagBtn class="px-4"/>

    <ul class="text-zioly4 dark:text-whizy">
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['home']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('home') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/shops" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['store']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('store') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/team" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['team']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('team') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/orders" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['order']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('orders') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/products" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['package']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('products') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/categories" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['category']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('categories') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/discount" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['discount']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('discounts') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/customers" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['customer']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('customers') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/delivery" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['delivery']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('delivery') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/modules" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['puzzle']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('modules') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/ban" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class.w-5.h-5 v-html="icons['unautorized']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('black list') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/bank" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['bank']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('banks') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/transaction" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['transfer']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('transaction') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <NuxtLink to="/setings" class="flex items-center justify-between w-full h-full p-2" active-class="bg-whizy dark:bg-darky rounded-lg">
          <div class="w-5 h-5" v-html="icons['settings']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('settings') }}</h3>
        </NuxtLink>
      </li>
      <li @click="close" class="m-1.5 rounded-lg" :class="isLargeScreen ? 'h-10' : 'h-9'">
        <button class="flex items-center justify-between w-full h-full p-2 cursor-pointer" @click="handleLogout">
          <div class="w-5 h-5" v-html="icons['disconnect']"></div>
          <h3 class="flex items-center justify-center w-full text-xs mx-1.5">{{ t('disconnect') }}</h3>
        </button>
      </li>
    </ul>

    <div class="h-[50px]"></div>
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
/* Custom scrollbar styles can be added here if needed, but are omitted for now */
/* The icon colors are handled by the parent text color, so we can remove the specific svg color rules */
</style>