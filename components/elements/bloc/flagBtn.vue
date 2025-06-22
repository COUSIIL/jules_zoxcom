<template>
    <button class="flagBtn" @click="clicker">
        
        <img class="flag" :src="flagSrc" alt="flag" />
        <p>{{ t('lg') }}</p>
    </button>

    <div 
        v-if="btn" 
        class="absolute mt-2 w-52 bg-whitly dark:bg-darkly border rounded-lg shadow-lg"
        
        >
            <button
                v-for="(status) in allStatus" 
                :key="status.name"
                @click="setLg(status.value, status.flag)"
                class="flex items-center w-48 px-4 py-1 text-left hover:bg-gorry"
                :style="{margin: '5px'}"
                >
                <img v-if="status.flag" :src="status.flag" class="w-6 h-6 mr-2" />
                <span>{{ status.name }}</span>
            </button>
    </div>

</template>

<script setup>

import { ref, onMounted } from "vue";

const { t, setLocale } = useLang()

const btn = ref(false)
const flagSrc = ref('')
const allStatus = ref([
    {name: t('fr'),
    flag: '/svg/fr.svg',
    value: 'fr'},
    {name: t('en'),
    flag: '/svg/en.svg',
    value: 'en'},
    {name: t('ar'),
    flag: '/svg/dz.svg',
    value: 'ar'},
])

onMounted(() => {
    if(t('lg') === 'العربية') {
        flagSrc.value = '/svg/dz.svg'
    } else if(t('lg') === 'Français') {
        flagSrc.value = '/svg/fr.svg'
    } else {
        flagSrc.value = '/svg/en.svg'
    }
})


const clicker = () => {
    btn.value = !btn.value;
}

const setLg = (value, flag) => {
    setLocale(value)
    btn.value = false
    flagSrc.value = flag

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