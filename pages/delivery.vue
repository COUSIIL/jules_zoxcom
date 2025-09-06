<template>

    <div style="width: 100%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <div class="boxDelivery" v-if="isCreating === false">
            <div class="DeliveryContent">
                <div v-html="resizedImg">

                </div>
                
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


<style>
.boxDelivery {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .boxDelivery{
    background-color: var(--color-darkly);
}

.DeliveryContent {
    display: flex;
    align-items: center;
    justify-content: start;
    margin-inline: 10px;
    gap: 5px;
}

</style>