<template>
    <button class="filterSearchBtn" @click="clicker">
        <div v-if="currentFilter1.img" class="filterValue" style="color: var(--color-whizy)" v-html="currentFilter1.img" />
        <p>{{ t(currentFilter1.name) }}</p>
    </button>

    <div 
        v-if="btn" 
        class="absolute mt-2 w-52 bg-whitly dark:bg-darkly border rounded-lg shadow-lg z-50"
        
        >
            <button
                v-for="(status) in filter" 
                :key="status.name"
                @click="setLg(status.name, status.img)"
                class="flex items-center w-48 px-4 py-1 text-left hover:bg-gorry"
                :style="{margin: '5px'}"
                >
                <div v-if="status.img" v-html="status.img" style="display: flex; justify-content: center; align-items: center; margin: 2px;" />
                <span style="display: flex; justify-content: center; align-items: center; margin: 2px;">{{ t(status.name) }}</span>
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

<style>
.filterValue{
        display: flex;
    justify-content: space-between;
    align-items: center;
 border-radius: 24px;
 width: 24px;
 height: 24px;
 margin-inline: 5px;
}

.filterSearchBtn {
    height: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 2px;
    background-color: var(--color-zioly2);
    border-radius: 12px;
    cursor: pointer;

}
.filterSearchBtn p {
    margin-inline: 5px;
    font-weight: bold;
    font-size: 12px;
    color: var(--color-whizy);
}
</style>