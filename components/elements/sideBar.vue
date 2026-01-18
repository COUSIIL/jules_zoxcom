<template>
  <div v-if="prop.isVisible" class="sidebar-overlay" @click="close"></div>

  <div
    ref="sidebarRef"
    :class="['newSideBar', isLargeScreen ? 'newSideBar--large' : 'newSideBar--small', { 'is-open': prop.isVisible }]"
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

      <div v-if="hasPermission('manage_roles')" class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/roles" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['key']"></div>
          <h3 class="newSideBar__text">Rôles</h3>
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
        <NuxtLink class="newSideBar__link" to="/modules" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['puzzle']"></div>
          <h3 class="newSideBar__text">{{ t('modules') }}</h3>
        </NuxtLink>
      </div>

      <div class="newSideBar__item" @click="close">
        <NuxtLink class="newSideBar__link" to="/image_video" exact-active-class="is-active">
          <div class="newSideBar__icon" v-html="icons['imageVideo']"></div>
          <h3 class="newSideBar__text">{{ t('image to video') }}</h3>
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
//const isHovered = ref(false)


var prop = defineProps({
  isVisible: Boolean,
})


const justClicked = ref(false)

const { t } = useLang()
const { hasPermission } = useAuth()

// Déclaration des emits
const emit = defineEmits(['viewMenu', 'handleLogout', 'close'])

// Méthode qui émet l'événement
const viewMenu = () => {
  emit('viewMenu')
}

const handleLogout = () => {
  emit('handleLogout')
}

/*function handleHover(state) {
  if (justClicked.value === false) isHovered.value = state
}*/

const close = () => {
  justClicked.value = true;
  //isHovered.value = false;
  emit('close');
  setTimeout(() => { justClicked.value = false }, 500);
}

</script>

<style scoped>
  /* ==========================================================================
   newSideBar
   ========================================================================== */
.sidebar-overlay{
  position: fixed;
  inset: 0;
  z-index: 4999;         /* sous la sidebar */
  background: transparent; /* ou rgba(0,0,0,.25) */
}

.newSideBar{
  position: fixed;
  top: 50px;
  right: 0;
  height: 100%;
  transform: translateX(100%);
  transition: transform 0.5s cubic-bezier(0.25, 1, 0.5, 1);
  z-index: 5000;
  background: var(--color-whitly);
  overflow-y: auto;
}

.newSideBar.is-open{
  transform: translateX(0);
}

.newSideBar--large {
  width: 250px;
  transform: translateX(100%);
}

.newSideBar--small {
  width: 200px;
}

.sidebar-handle{
  position: fixed;
  top: 50px;
  right: 0;
  width: 12px;
  height: calc(100% - 50px);
  z-index: 4999; /* juste sous sidebar */
}
.newSideBar:not(.is-open){ pointer-events:auto; }
.newSideBar.is-open{ 
  transform: translateX(0);
  pointer-events:auto; }

.dark .newSideBar {
  background: var(--color-darkly);
  box-shadow: 0px 8px 6px var(--color-darky);
}

.newSideBar::-webkit-scrollbar {
  width: 6px;
}
.newSideBar::-webkit-scrollbar-thumb {
  background-color: rgba(0,0,0,0.3);
  border-radius: 3px;
}
.newSideBar::-webkit-scrollbar-track {
  background: transparent;
}

.newSideBar__link-list {
  list-style: none;
  margin: 0;
}

.newSideBar__item {
  border-radius: 8px;
  margin: 10px;
}

.newSideBar__link {
  height: 35px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: var(--color-zioly4);
  text-decoration: none;
  width: 100%;
  padding: 5px;
}

.newSideBar__link.is-active {
  background-color: var(--color-whizy);
  border-radius: 8px;
}

.dark .newSideBar__link {
  color: var(--color-whizy);
}

.dark .newSideBar__link.is-active {
  background-color: var(--color-darkow);
  border-radius: 8px;
}

.newSideBar__icon {
  padding-inline: 0;
}

.newSideBar__icon svg {
  width: 24px;
  height: 24px;
}

.newSideBar__text {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 12px;
  margin-inline: 5px;
}

.newSideBar__footer-spacer {
  height: 50px;
}


.newSideBar__version-info {
  font-size: 14px;
  font-weight: bold;
  padding: 15px;
  text-align: center;

}
</style>