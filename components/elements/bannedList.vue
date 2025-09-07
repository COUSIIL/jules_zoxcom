<template>

    <LoaderBlack v-if="isSaving" width="100px" />
    <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
    <Confirm :isVisible="toConfirm" @confirm="deleteIp" @cancel="toConfirm = false" />

    <div v-if="isMounted" style="width: 100%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <div class="boxDelivery" v-for="(ip, index) in data" :key="index">
            <div class="boxDeliveryTitle">
                <div class="bigBox">
                    <div class="directBox">
                        {{ ip.ip_address }}
                    </div>
                    <div class="miniBox">
                        {{ ip.created_at }}
                    </div>
                    <div class="miniBox">
                        {{ t('reason') }} : {{ ip.reason }}
                    </div>
                    
                </div>
                
                

                <div class="rowBox">

                    <button class="directBtn" type="button" @click="toConfirm = true, ipIndex = index">
                        <div v-html="resizedImgDelete" >

                        </div>
                    </button>

                    
                </div>
            </div>
        </div>
    </div>


</template>

<script setup>

import LoaderBlack from '../elements/animations/loaderBlack.vue';
import Radio from './bloc/radio.vue';
import Inputer from './bloc/input.vue';
import icons from '~/public/icons.json'
import Selector from './bloc/select.vue';
import Message from '../elements/bloc/message.vue';
import Confirm from '../elements/bloc/confirm.vue';

import { computed, ref, watch } from 'vue'

const { t } = useLang()

const props = defineProps({
    data: {type: Array, default: []},

})

const isMessage = ref(false)
const message = ref('')
const toConfirm = ref(false)
const isSaving = ref(false)
const isMounted = ref(false)
const ipIndex = ref(-1)

/*var resizeSvg = (svg, width, height) => {
    return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
}*/

const resizedImgDelete = computed(() => {
  if (!icons['deleteImg']) return ''
  return icons['deleteImg']
    .replace(/width="[^"]+"/, `width="${20}"`)
    .replace(/height="[^"]+"/, `height="${20}"`)
    .replace(/color="[^"]+"/, `color="${'var(--color-rady)'}"`)
})

const emit = defineEmits(['updated'])

const deleteIp = async () => {
  toConfirm.value = false
  isSaving.value = true

  // 1. Validations
  if (ipIndex.value < 0 || ipIndex.value >= props.data.length) {
    message.value = t("Please select a valid IP")
    isMessage.value = true
    isSaving.value = false
    return
  }

  // ✅ Correction : accès direct sans .value
  const payload = {
    ip: props.data[ipIndex.value].ip_address
  }


  // 4. Envoi
  try {
    const response = await fetch(
    'https://management.hoggari.com/backend/api.php?action=deleteIp',
      {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload)
      }
    )

    if (!response.ok) {
      message.value   = t("error in response")
      isMessage.value = true
      isSaving.value  = false
      return   // <<-- Interrompt ici pour ne pas continuer
    }

    // 5. Traitement de la réponse
    const result = await response.json()
    message.value   = result.message
    isMessage.value = true
    isSaving.value  = false

    if (result.success) {
      message.value   = result.message
      isMessage.value = true
      isSaving.value  = false
      emit('updated')
    }
  } catch (e) {
    message.value   = e
    isMessage.value = true
    isSaving.value  = false
  }

}

onMounted(() => {
    isMounted.value = true
})

</script>

<style>

.bigBox {
    padding: 5px;
    align-items: start;
}

.directBtn {
    
    font-size: 16px;
    margin: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px;
    border-radius: 8px;
    background-color: var(--color-whizy);
    gap: 2px;
    cursor: pointer;

}
.dark .directBtn {
    background-color: var(--color-darkow);
}

.directBox {
    font-size: 16px;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
    align-items: center;

}

.rowBox {
    display: flex;
    justify-content: space-between;
    align-items: center;
}


.miniBox {
    font-size: 14px;
    margin: 5px;
    color: var(--color-zioly1);
    text-align: start;
}
.dark .miniBox {
    color: var(--color-garry);
}

.contentDelivery {
    width: 100%; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    flex-direction: column;
    padding-block: 5px;
    margin-block: 5px;
}


.boxDelivery {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-direction: column;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    border-radius: 6px;
    transition: all 0.3s ease;
    margin-block: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}

.boxDeliveryTitle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin-block: 5px;
}


</style>