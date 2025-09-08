<template>
  <div class="sidebar-overlay" v-if="prop.isVisible" @click="viewMenu"></div>

  <div
    :class="['sidebar', isLargeScreen ? 'sidebar--large' : 'sidebar--small', { 'is-open': isHovered || prop.isVisible }]"
    @mouseover="handleHover(true)"
    @mouseleave="handleHover(false)"
  >
    <div class="sidebar__version-info">
      v1 {{ t('valid until: ') }} 	23/03/2026
    </div>

    <FlagBtn/>

    <ul class="sidebar__link-list">
      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['home']"></div>
          <h3 class="sidebar__text">{{ t('home') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/shops" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['store']"></div>
          <h3 class="sidebar__text">{{ t('store') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/team" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['team']"></div>
          <h3 class="sidebar__text">{{ t('team') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/orders" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['order']"></div>
          <h3 class="sidebar__text">{{ t('orders') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/products" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['package']"></div>
          <h3 class="sidebar__text">{{ t('products') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/categories" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['category']"></div>
          <h3 class="sidebar__text">{{ t('categories') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/discount" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['discount']"></div>
          <h3 class="sidebar__text">{{ t('discounts') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/customers" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['customer']"></div>
          <h3 class="sidebar__text">{{ t('customers') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/delivery" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['delivery']"></div>
          <h3 class="sidebar__text">{{ t('delivery') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/modules" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['puzzle']"></div>
          <h3 class="sidebar__text">{{ t('modules') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/ban" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['unautorized']"></div>
          <h3 class="sidebar__text">{{ t('black list') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/bank" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['bank']"></div>
          <h3 class="sidebar__text">{{ t('banks') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/transaction" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['transfer']"></div>
          <h3 class="sidebar__text">{{ t('transaction') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <NuxtLink class="sidebar__link" to="/setings" exact-active-class="is-active">
          <div class="sidebar__icon" v-html="icons['settings']"></div>
          <h3 class="sidebar__text">{{ t('settings') }}</h3>
        </NuxtLink>
      </li>

      <li class="sidebar__item" @click="close">
        <button class="sidebar__link" style="cursor: pointer;" @click="handleLogout">
          <div class="sidebar__icon" v-html="icons['disconnect']"></div>
          <h3 class="sidebar__text">{{ t('disconnect') }}</h3>
        </button>
      </li>
    </ul>

    <div class="sidebar__footer-spacer"></div>
  </div>
</template>

<script setup>
import FlagBtn from './bloc/flagBtn.vue'
import icons from '~/public/icons.json'
import { ref } from 'vue'

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
  if (justClicked.value === false) isHovered.value = state
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