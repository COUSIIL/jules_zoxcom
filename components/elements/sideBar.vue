<template>
  <div
    ref="sidebarRef"
    :class="['newSideBar', isLargeScreen ? 'newSideBar--large' : 'newSideBar--small', { 'is-open': isHovered || prop.isVisible }]"
    @mouseover="handleHover(true)"
    @mouseleave="handleHover(false)"
  >
    <div class="newSideBar__version-info">
      v1 {{ t('valid until: ') }} 	23/03/2026
    </div>

    <FlagBtn @click="close"/>

    <div class="newSideBar__link-list">
      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['home']"></div>
          <h3 class="newSideBar__text">{{ t('home') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/shops" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['store']"></div>
          <h3 class="newSideBar__text">{{ t('store') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/team" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['team']"></div>
          <h3 class="newSideBar__text">{{ t('team') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/orders" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['order']"></div>
          <h3 class="newSideBar__text">{{ t('orders') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/products" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['package']"></div>
          <h3 class="newSideBar__text">{{ t('products') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/categories" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['category']"></div>
          <h3 class="newSideBar__text">{{ t('categories') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/discount" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['discount']"></div>
          <h3 class="newSideBar__text">{{ t('discounts') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/customers" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['customer']"></div>
          <h3 class="newSideBar__text">{{ t('customers') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/delivery" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['delivery']"></div>
          <h3 class="newSideBar__text">{{ t('delivery') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/moddives" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['puzzle']"></div>
          <h3 class="newSideBar__text">{{ t('moddives') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/reminder" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['calendar']"></div>
          <h3 class="newSideBar__text">{{t('reminder')}}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/ban" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['unautorized']"></div>
          <h3 class="newSideBar__text">{{ t('black list') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/bank" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['bank']"></div>
          <h3 class="newSideBar__text">{{ t('banks') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/transaction" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['transfer']"></div>
          <h3 class="newSideBar__text">{{ t('transaction') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/tm" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['partition']"></div>
          <h3 class="newSideBar__text">{{ t('transaction model') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/promotion" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['speaker']"></div>
          <h3 class="newSideBar__text">{{ t('promotion') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/setings" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['settings']"></div>
          <h3 class="newSideBar__text">{{ t('settings') }}</h3>
        </NuxtLink>
      </div>


      <div class="newSideBar__item" @click="close">
        <button class="newSideBar__link" style="cursor: pointer;" @click="handleLogout">
          <div class="newSideBar__icon" v-html="icons['disconnect']"></div>
          <h3 class="newSideBar__text">{{ t('disconnect') }}</h3>
        </button>
      </div>
    </div>

    <div class="newSideBar__footer-spacer"></div>
  </div>
</template>

<script setup>
import FlagBtn from './bloc/flagBtn.vue'
import icons from '~/public/icons.json'
import { ref, watch, onUnmounted } from 'vue'
//import './css/sideBar.css'

const isLargeScreen = useState('isLargeScreen')

const sidebarRef = ref(null)

var prop = defineProps({
  isVisible: Boolean,
})

const handleClickOutside = (event) => {
  if (sidebarRef.value && !sidebarRef.value.contains(event.target)) {
    close()
  }
}

watch(() => prop.isVisible, (newValue) => {
  if (newValue) {
    setTimeout(() => {
      window.addEventListener('click', handleClickOutside)
    }, 0)
  } else {
    window.removeEventListener('click', handleClickOutside)
  }
})

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside)
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