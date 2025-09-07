<template>
    <div class="flex items-center w-full max-w-md p-1.25 m-1.25 overflow-hidden transition-all duration-300 ease-in-out bg-white border-2 rounded-full border-zioly2 dark:bg-darkly dark:border-garry">
        <textarea
            v-model="internalSearch"
            @input="emitSearch"
            class="flex items-center justify-center flex-1 h-10 px-4 text-sm text-center bg-transparent border-none outline-none resize-none text-darkly dark:text-whitly placeholder:text-center"
            :placeholder="t('search')">
        </textarea>
    
        <button class="flex items-center justify-center p-1.25 rounded-full cursor-pointer bg-zioly2" @click="emitSearch">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" class="text-whity">
                <path d="M17.5 17.5L22 22" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M20 11C20 6.02944 15.9706 2 11 2C6.02944 2 2 6.02944 2 11C2 15.9706 6.02944 20 11 20C15.9706 20 20 15.9706 20 11Z" stroke="currentColor" stroke-width="3" stroke-linejoin="round" />
            </svg>
        </button>
    </div>
</template>

<script setup>
import Filter from '../bloc/filterSearchBtn.vue'

const { t } = useLang()

const props = defineProps({ modelValue: String })

const internalSearch = ref(props.modelValue || '')

const emit = defineEmits(['update:modelValue', 'search-submitted', 'filters-changed'])

const filterType = ref([{name: 'all', img: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" color="currentColor" fill="none">
    <path d="M11.9959 18H12.0049" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M17.9998 18H18.0088" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M5.99981 18H6.00879" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M11.9959 12H12.0049" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M11.9998 6H12.0088" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M17.9998 12H18.0088" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M17.9998 6H18.0088" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M5.99981 12H6.00879" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M5.99981 6H6.00879" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>`},
{name: 'file', img: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" color="currentColor" fill="none">
    <path d="M8 7H16.75C18.8567 7 19.91 7 20.6667 7.50559C20.9943 7.72447 21.2755 8.00572 21.4944 8.33329C22 9.08996 22 10.1433 22 12.25C22 15.7612 22 17.5167 21.1573 18.7779C20.7926 19.3238 20.3238 19.7926 19.7779 20.1573C18.5167 21 16.7612 21 13.25 21H12C7.28595 21 4.92893 21 3.46447 19.5355C2 18.0711 2 15.714 2 11V7.94427C2 6.1278 2 5.21956 2.38032 4.53806C2.65142 4.05227 3.05227 3.65142 3.53806 3.38032C4.21956 3 5.1278 3 6.94427 3C8.10802 3 8.6899 3 9.19926 3.19101C10.3622 3.62712 10.8418 4.68358 11.3666 5.73313L12 7" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
</svg>`},
{name: 'image', img: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" color="currentColor" fill="none">
    <circle cx="7.5" cy="7.5" r="1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></circle>
    <path d="M2.5 12C2.5 7.52166 2.5 5.28249 3.89124 3.89124C5.28249 2.5 7.52166 2.5 12 2.5C16.4783 2.5 18.7175 2.5 20.1088 3.89124C21.5 5.28249 21.5 7.52166 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1088C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1088C2.5 18.7175 2.5 16.4783 2.5 12Z" stroke="currentColor" stroke-width="2"></path>
    <path d="M5 21C9.37246 15.775 14.2741 8.88406 21.4975 13.5424" stroke="currentColor" stroke-width="2"></path>
</svg>`}
]) // par défaut
const filterType2 = ref([{name: 'name', img: ''},
{name: 'id', img: ''}
]) // par défaut

const selectedFilter1 = ref(filterType.value[0].name)
const selectedFilter2 = ref(filterType2.value[0].name)

watch([selectedFilter1, selectedFilter2], () => {
  emit('filters-changed', {
    type: selectedFilter1.value,
    mode: selectedFilter2.value
  })
})



watch(() => props.modelValue, (newVal) => {
  internalSearch.value = newVal
})

const emitSearch = () => {
  emit('update:modelValue', internalSearch.value)
  emit('search-submitted', internalSearch.value)
}


</script>