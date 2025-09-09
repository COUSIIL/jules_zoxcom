<template>
    <button class="flagBtn" @click="clicker">
        
        <img class="flag" :src="flagSrc" alt="flag" />
        <p>{{ t('lg') }}</p>
    </button>

    <div 
        v-if="btn" 
        class="flagDrop"
        
        >
            <button
                v-for="(status) in allStatus" 
                :key="status.name"
                @click="setLg(status.value, status.flag)"
                class="falgElements"
                >
                <img v-if="status.flag" :src="status.flag" class="flagImg" />
                <span>{{ status.name }}</span>
            </button>
    </div>

</template>

<script setup>
import { ref, onMounted } from "vue";

const { t, setLocale } = useLang()

const btn = ref(false)
const flagSrc = ref('')

// toutes les langues disponibles
const allStatus = ref([
  { name: 'Français', flag: '/svg/fr.svg', value: 'fr' },
  { name: 'English', flag: '/svg/en.svg', value: 'en' },
  { name: 'العربية', flag: '/svg/dz.svg', value: 'ar' },
])

// au montage, on restaure la langue
onMounted(() => {
  const saved = localStorage.getItem('lg') || 'fr'
  setLocale(saved)

  // retrouver le flag correspondant
  const found = allStatus.value.find(l => l.value === saved)
  flagSrc.value = found ? found.flag : '/svg/fr.svg'
})

// bouton toggle
const clicker = () => {
  btn.value = !btn.value
}

// changement de langue
const setLg = (value, flag) => {
  setLocale(value)
  btn.value = false
  flagSrc.value = flag

  localStorage.setItem('lg', value) // ✅ on sauvegarde bien le code langue
}
</script>


<style>
.flag{
 border-radius: 12px;
 width: 28px;
 height: 28px;
 margin-inline: 10px;
}

.flagBtn {
    width: calc(100% - 10px);
    height: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-inline: 5px;
    margin-block: 10px;
    border-radius: 8px;
    background-color: var(--color-zioly2);
    cursor: pointer;
}
.flagBtn p {
    margin-inline: 10px;
    font-weight: bold;
    font-size: 12px;
    color: var(--color-whizy);
}
</style>