<template>
    <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
    <Confirm :isVisible="toConfirm" @confirm="save()" @cancel="toConfirm = false" />

    <div class="flex flex-col items-center justify-center w-full">
        <div class="flex items-center justify-between w-full max-w-3xl min-w-[300px] p-2.5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly" v-if="isCreating === false">
            <div class="flex items-center justify-start mx-2.5 gap-1.25">
                <div v-html="resizedImg"></div>
                {{ t('black list') }}
            </div>
            <Inputer class="max-w-xs" type="text" :lock="false" :placeHolder="t('ip')" :holder="t('')" v-model="ip"/>
            <Inputer class="max-w-xs" type="text" :lock="false" :placeHolder="t('reason')" :holder="t('ex: abuse')" v-model="reason"/>
            <CallToAction :text="t('add new ip to black list')" :svg="icons['add']" @clicked="toConfirm = !toConfirm"/>
        </div>

        <BanList v-if="isSaving === false && data" :data="data" @updated="getIp" />
    </div>
</template>

<script setup>

import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
import Editor from '../components/editor.vue';
import Radio from '../components/elements/bloc/radio.vue';
import Inputer from '../components/elements/bloc/input.vue';
import InputBtn from '../components/elements/bloc/inputBtn.vue';
import Lister from '../components/elements/bloc/list.vue';
import Selector from '../components/elements/bloc/select.vue';
import Gbtn from '../components/elements/bloc/gBtn.vue';
import Message from '../components/elements/bloc/message.vue';
import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
import CancelBtn from '../components/elements/bloc/cancelBtn.vue';
import Explorer from '../components/elements/explorer.vue';
import EditCat from '../components/elements/editCategory.vue';
import AddDelivery from '../components/elements/addDelivery.vue';
import BanList from '../components/elements/bannedList.vue';

import icons from '~/public/icons.json'

import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

const { t } = useLang()

const isCreating = ref(false)
const isSaving = ref(false)
const toConfirm = ref(false)
const isMessage = ref(false)
const message = ref('')
const ip = ref('')
const reason = ref('')
const data = ref([])



const newWidth = 20
const newHeight = 20

const resizedImg = computed(() => {
  if (!icons['delivery']) return ''
  return icons['delivery']
    .replace(/width="[^"]+"/, `width="${newWidth}"`)
    .replace(/height="[^"]+"/, `height="${newHeight}"`)
})

onMounted(() => {
    getIp()
})

const save = async () => {
  toConfirm.value = false
  isSaving.value = true

  // 1. Validations
  if (!ip.value) {
    message.value = t("please choose ip")
    isMessage.value = true
    isSaving.value = false
    return
  }
  if (!reason.value) {
    message.value = t("please enter reason")
    isMessage.value = true
    isSaving.value = false
    return
  }



  const payload = {
    ip:           ip.value,
    reason:    reason.value,

  }


  // 4. Envoi
  try {
    const response = await fetch(
    'https://management.hoggari.com/backend/api.php?action=banIp',
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
    }
  } catch (e) {
    message.value   = e
    isMessage.value = true
    isSaving.value  = false
  }

}


const getIp = async() => {
    isSaving.value = true
    try {
        var response
        response = await fetch(`https://management.hoggari.com/backend/api.php?action=ipList`, {
        method: 'GET',
        });
        

        if (!response.ok) {
        isMessage.value = true
        message.value = t('error in request get wilaya')
        isSaving.value = false
        return false
        }

        const result = await response.json();
        if(result.success) {
            data.value = result.data
        } else {
            isMessage.value = true
            message.value = t(result.message)
        }
        
        isSaving.value = false
        
    }catch (e) {
        isMessage.value = true
        message.value = t(e)
        isSaving.value = false
    }
}


</script>