<template>
    <LoaderBlack v-if="isSaving" width="100px" />
    <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
    <Confirm :isVisible="toConfirm" @confirm="deleteIp" @cancel="toConfirm = false" />

    <div v-if="isMounted" class="flex flex-col items-center justify-center w-full">
        <div class="flex flex-col items-center justify-between w-full max-w-3xl min-w-[300px] my-1.25 transition-all duration-300 ease-in-out rounded-md shadow-md" v-for="(ip, index) in data" :key="index">
            <div class="flex items-center justify-between w-full my-1.25">
                <div class="p-1.25 items-start">
                    <div class="flex items-center justify-between text-base font-bold">
                        {{ ip.ip_address }}
                    </div>
                    <div class="m-1.25 text-sm text-start text-zioly1 dark:text-garry">
                        {{ ip.created_at }}
                    </div>
                    <div class="m-1.25 text-sm text-start text-zioly1 dark:text-garry">
                        {{ t('reason') }} : {{ ip.reason }}
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <button class="flex items-center justify-between p-0.5 m-1.25 text-base bg-whizy dark:bg-darkow rounded-lg gap-0.5 cursor-pointer" type="button" @click="toConfirm = true, ipIndex = index">
                        <div v-html="resizedImgDelete" ></div>
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