<template>
    <button class="flex items-center justify-between w-full h-6 my-2.5 mx-1.25 rounded-lg cursor-pointer bg-zioly2" @click="clicker">
        <img class="w-7 h-7 mx-2.5 rounded-xl" :src="flagSrc" alt="flag" />
        <p class="mx-2.5 text-xs font-bold text-whizy">{{ t('lg') }}</p>
    </button>

    <div 
        v-if="btn" 
        class="absolute z-50 w-52 mt-2 bg-white border rounded-lg shadow-lg dark:bg-darkly"
        >
            <button
                v-for="(status) in allStatus" 
                :key="status.name"
                @click="setLg(status.value, status.flag)"
                class="flex items-center w-48 px-4 py-1 m-1.25 text-left hover:bg-gorry"
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