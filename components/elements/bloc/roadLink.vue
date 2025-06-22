<template>
  <ul v-if="liList.length > 0" class="road-ul">
    <Gbtn text="" :svg="homeSvg" color="var(--color-zioly3)" @click="emitHome" />
    <Gbtn text="" :svg="listSvg[0]" color="var(--color-zioly2)" @click="emitToggle" />
  
    <li
      v-for="li in liList"
      :key="li.title"
      @click="emitFolder(li)"
      :title="li.title"
      role="button"
      tabindex="0"
      @keydown.enter="emitFolder(li)"
    >
      {{ li.title }}
    </li>
  </ul>
  <div v-else class="linkBar">
    <svg style="margin-inline: 5px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
          <path d="M7.08848 4.76243L6.08847 5.54298C4.57181 6.72681 3.81348 7.31873 3.40674 8.15333C3 8.98792 3 9.95205 3 11.8803V13.9715C3 17.7562 3 19.6485 4.17157 20.8243C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8243C21 19.6485 21 17.7562 21 13.9715V11.8803C21 9.95205 21 8.98792 20.5933 8.15333C20.1865 7.31873 19.4282 6.72681 17.9115 5.54298L16.9115 4.76243C14.5521 2.92081 13.3724 2 12 2C10.6276 2 9.44787 2.92081 7.08848 4.76243Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
          <path d="M15 16.5H17V18.5M15 16.5V18.5H17M15 16.5L17 18.5" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
      </svg>
    {{t('home')}}
  </div>
</template>

<script setup>
import Gbtn from './gBtn.vue'

const { t } = useLang()

//const liList = ref([{title: 'title'}, {title: 'title2'}])
//const liList = ref([])

defineProps({
  liList: {
    type: Array,
    required: true
  }
})

const emit = defineEmits(['back-click', 'selected-back', 'back-home'])

const emitToggle = () => {
  emit('back-click')
}

const emitHome = () => {
  emit('back-home')
}

const emitFolder = (object) => {
  emit('selected-back', object)
}

const listSvg = ref(
[`<svg xmlns="http://www.w3.org/2000/svg"s viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
        <path d="M15 6C15 6 9.00001 10.4189 9 12C8.99999 13.5812 15 18 15 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
</svg>`,
`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
    <path d="M2 12C2 8.22876 2 6.34315 3.17157 5.17157C4.34315 4 6.22876 4 10 4H13C14.9628 4 15.9443 4 16.7889 4.42229C17.6334 4.84458 18.2223 5.62972 19.4 7.2C21.1333 9.51111 22 10.6667 22 12C22 13.3333 21.1333 14.4889 19.4 16.8C18.2223 18.3703 17.6334 19.1554 16.7889 19.5777C15.9443 20 14.9628 20 13 20H10C6.22876 20 4.34315 20 3.17157 18.8284C2 17.6569 2 15.7712 2 12Z" stroke="currentColor" stroke-width="1.5"></path>
    <path d="M11 8V16M15 12L7 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>`,
])

const homeSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
    <path d="M7.08848 4.76243L6.08847 5.54298C4.57181 6.72681 3.81348 7.31873 3.40674 8.15333C3 8.98792 3 9.95205 3 11.8803V13.9715C3 17.7562 3 19.6485 4.17157 20.8243C5.34315 22 7.22876 22 11 22H13C16.7712 22 18.6569 22 19.8284 20.8243C21 19.6485 21 17.7562 21 13.9715V11.8803C21 9.95205 21 8.98792 20.5933 8.15333C20.1865 7.31873 19.4282 6.72681 17.9115 5.54298L16.9115 4.76243C14.5521 2.92081 13.3724 2 12 2C10.6276 2 9.44787 2.92081 7.08848 4.76243Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
    <path d="M15 16.5H17V18.5M15 16.5V18.5H17M15 16.5L17 18.5" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
</svg>`)
</script>

<style scoped>
/* --- Scrollbar personnalisée --- */
.road-ul {
  width: 100%;
  height: 50px;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 0 5px;
  margin: 5px 0;
  list-style: none;
  overflow-x: auto;
  overflow-y: hidden;
  white-space: nowrap;

  scrollbar-width: thin;              /* Firefox */
  scrollbar-color: rgba(125, 105, 142, 0.6) transparent;
}

.road-ul::-webkit-scrollbar {
  height: 8px;
}

.road-ul::-webkit-scrollbar-track {
  background: transparent;
}

.road-ul::-webkit-scrollbar-thumb {
  background-color: rgba(125, 105, 142, 0.6);
  border-radius: 10px;
  border: 2px solid transparent;
  background-clip: content-box;
  transition: background-color 0.3s ease;
}

.road-ul::-webkit-scrollbar-thumb:hover {
  background-color: rgba(125, 105, 142, 0.9);
}

/* --- Liste des éléments (li) --- */
.road-ul li {
  min-width: 50px;
  max-width: 100px;
  height: 40px;
  padding: 0 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--color-whizy);
  border-radius: 6px;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: pointer;
}

.dark .road-ul li {
  background-color: var(--color-darky);
}

/* --- Bar de lien par défaut (si liList est vide) --- */
.linkBar {
  width: calc(100% - 10px);
  height: 40px;
  margin: 5px;
  background-color: var(--color-whizy);
  display: flex;
  align-items: center;
  justify-content: center;
}

.dark .linkBar {
  background-color: var(--color-darky);
}
</style>
