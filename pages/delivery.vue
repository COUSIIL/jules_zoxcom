<template>
    <div class="flex flex-col items-center justify-center w-full">
        <div class="flex items-center justify-between w-full max-w-3xl min-w-[300px] p-2.5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly" v-if="isCreating === false">
            <div class="flex items-center justify-start mx-2.5 gap-1.25">
                <div v-html="resizedImg"></div>
                {{ deliveryList.length }}
                {{ t('delivery method') }}
            </div>
            <CallToAction :text="t('add delivery')" :svg="icons['add']" @clicked="isCreating = !isCreating"/>
        </div>
        <AddDelivery v-else @cancel="isCreating = false" @save="isSaving = !isSaving"/>
    </div>
    <DeliveryList @deliveryList="setDelivery" :update="isSaving"/>
</template>

<script setup>

import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
import AddDelivery from '../components/elements/addDelivery.vue';
import DeliveryList from '../components/elements/deliveryList.vue';

import icons from '~/public/icons.json'

const { t } = useLang()

var isCreating = ref(false)
var isSaving = ref(false)

var deliveryList = ref([])

const newWidth = 20
const newHeight = 20

const resizedImg = computed(() => {
  if (!icons['delivery']) return ''
  return icons['delivery']
    .replace(/width="[^"]+"/, `width="${newWidth}"`)
    .replace(/height="[^"]+"/, `height="${newHeight}"`)
})

const setDelivery = (val) => {
    if(val) deliveryList.value = val; else deliveryList.value = []
    
}


</script>