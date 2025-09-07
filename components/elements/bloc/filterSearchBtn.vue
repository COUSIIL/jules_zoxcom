<template>
    <button class="flex items-center justify-between h-6 m-0.5 rounded-xl cursor-pointer bg-zioly2" @click="clicker">
        <div v-if="currentFilter1.img" class="flex items-center justify-between w-6 h-6 mx-1.25 rounded-3xl text-whizy" v-html="currentFilter1.img" />
        <p class="mx-1.25 text-xs font-bold text-whizy">{{ t(currentFilter1.name) }}</p>
    </button>

    <div 
        v-if="btn" 
        class="absolute z-50 w-52 mt-2 bg-white border rounded-lg shadow-lg dark:bg-darkly"
        >
            <button
                v-for="(status) in filter" 
                :key="status.name"
                @click="setLg(status.name, status.img)"
                class="flex items-center w-48 px-4 py-1 m-1.25 text-left hover:bg-gorry"
                >
                <div v-if="status.img" v-html="status.img" class="flex items-center justify-center m-0.5" />
                <span class="flex items-center justify-center m-0.5">{{ t(status.name) }}</span>
            </button>
    </div>
</template>

<script setup>

const { t } = useLang()

const props = defineProps({
  filter: Array,
  currentFilter: Object,

})

var btn = ref(false)

const currentFilter1 = ref({ ...props.currentFilter })


const clicker = () => {
    btn.value = !btn.value;
}


const setLg = (value, img) => {
    btn.value = false
    currentFilter1.value.img = img
    currentFilter1.value.name = value

}

</script>