<template>
  <LoaderBlack v-if="isSaving" width="100px" />
  <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>

  <div class="flex flex-col items-center justify-center w-11/12 p-2.5 m-2.5 text-center rounded-lg shadow-lg bg-whitly dark:bg-darkly">
    <div class="flex items-center justify-start w-full gap-2.5 p-1.25">
      <div v-html="Icons['delivery']"></div>
      {{ t('delivery method') }}
    </div>
    
    <Selector :placeHolder="t('method')" :img="Icons['action']" v-if="!isSaving" :options="deliveryList" @update:modelLabel="setMethod" :modelValue="newDelivery" :modelImage="deliveryImage" />

    <Gbtn v-if="!isSaving" text="save" :svg="resizeSvg(Icons['check'], 20, 20)" color="var(--color-zioly2)" @click="setDelivery"/>
    <Loader v-else width="100px"/>
  </div>
</template>

<script setup>

import Inputer from './bloc/input.vue'
import Selector from './bloc/select.vue'
import Gbtn from './bloc/gBtn.vue'
import Loader from './animations/loaderBlack.vue'
import Icons from '~/public/icons.json'

import LoaderBlack from '../elements/animations/loaderBlack.vue';
import Message from '../elements/bloc/message.vue';


const { t } = useLang()

const newMethod = ref('')
const isSaving = ref(false)
const isMessage = ref(false)
const message = ref('')

const deliveryList = ref([])
const newDelivery = ref()
const deliveryImage = ref()

const props = defineProps({
  name: String
})

var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
}



const setMethod = (val) => {
  newMethod.value = val
}

const setDelivery = async () => {
  isSaving.value = true

  if(!newMethod.value || !props.name){
    message.value = t('no method or name selected')
    isMessage.value = true
    isSaving.value = false
    return
  }

  const storeInfo = 
  {
    "method": newMethod.value,
    "name": props.name,
  }

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=selectStoreDelivery', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(storeInfo),
  });

  if (!response.ok) {

    message.value = t('error in response')
    isMessage.value = true
    isSaving.value = false
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    message.value = textResponse.message
    isMessage.value = true
    isSaving.value = false
    
  } else {
    message.value = textResponse.message
    isMessage.value = true
    isSaving.value = false
  }

}

const getStoreDelivery = async () => {

  isSaving.value = true

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=getStoreDelivery', {
    method: 'GET',
  });

  if (!response.ok) {
    isSaving.value = false
    isMessage.value = true
    message.value = t('error in response')
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    
    for (var dev of textResponse.data) {
      newDelivery.value = dev.method
      for (var newDev of deliveryList.value) {
        if (newDev.label === newDelivery.value) {
          deliveryImage.value = newDev.img
          break
        }
      }
      break
    }
    isSaving.value = false
  } else {
    isMessage.value = true
    message.value = t(textResponse.message)
    isSaving.value = false
  }

  isSaving.value = false

}


const getDelivery = async () => {

  isSaving.value = true

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=getDeliveryMethod', {
    method: 'GET',
  });

  if (!response.ok) {
    isSaving.value = false
    isMessage.value = true
    message.value = t('error in response')
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    for(var i of textResponse.data) {
        try {
            const contentObj = JSON.parse(i.delivery_content)
            deliveryList.value.push(contentObj)
        } catch (e) {
            isSaving.value = false
            isMessage.value = true
            message.value = t('error in parsing data')
        }

    }
    isSaving.value = false
    
  } else {
    isMessage.value = true
    message.value = t(textResponse.message)
    isSaving.value = false
  }

  isSaving.value = false

}


onMounted(() => {
  getDelivery()
  getStoreDelivery()
})


</script>