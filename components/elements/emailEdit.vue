<template>
  <div class="emailMenu">
    {{ t('email info') }}

    <Inputer :placeHolder="t('orders email')" :img="emailSvg" type="email" v-model="newEmail" :required="true" @update:modelValue="test"/>

    <Gbtn v-if="!isEmail" text="save" :svg="saveSvg" color="var(--color-zioly2)" @click="createEmail"/>
    <Loader v-else style="width: 50px;"/>

    <div v-if="log" style="font-weight: bold; color: var(--color-zioly2); font-size: 14px;">
      {{ log }}
    </div>



  </div>

    

</template>

<script setup>

import Inputer from './bloc/input.vue'
import Gbtn from './bloc/gBtn.vue'
import Loader from './animations/loaderBlack.vue'


const { t } = useLang()

const newEmail = ref('')
const isEmail = ref(false)
const log = ref('')

const emailSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" color="currentColor" fill="none">
    <path d="M2 6L8.91302 9.91697C11.4616 11.361 12.5384 11.361 15.087 9.91697L22 6" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
    <path d="M2.01577 13.4756C2.08114 16.5412 2.11383 18.0739 3.24496 19.2094C4.37608 20.3448 5.95033 20.3843 9.09883 20.4634C11.0393 20.5122 12.9607 20.5122 14.9012 20.4634C18.0497 20.3843 19.6239 20.3448 20.7551 19.2094C21.8862 18.0739 21.9189 16.5412 21.9842 13.4756C22.0053 12.4899 22.0053 11.5101 21.9842 10.5244C21.9189 7.45886 21.8862 5.92609 20.7551 4.79066C19.6239 3.65523 18.0497 3.61568 14.9012 3.53657C12.9607 3.48781 11.0393 3.48781 9.09882 3.53656C5.95033 3.61566 4.37608 3.65521 3.24495 4.79065C2.11382 5.92608 2.08114 7.45885 2.01576 10.5244C1.99474 11.5101 1.99475 12.4899 2.01577 13.4756Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
</svg>`)

const saveSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
    <path d="M4 17.9808V9.70753C4 6.07416 4 4.25748 5.17157 3.12874C6.34315 2 8.22876 2 12 2C15.7712 2 17.6569 2 18.8284 3.12874C20 4.25748 20 6.07416 20 9.70753V17.9808C20 20.2867 20 21.4396 19.2272 21.8523C17.7305 22.6514 14.9232 19.9852 13.59 19.1824C12.8168 18.7168 12.4302 18.484 12 18.484C11.5698 18.484 11.1832 18.7168 10.41 19.1824C9.0768 19.9852 6.26947 22.6514 4.77285 21.8523C4 21.4396 4 20.2867 4 17.9808Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M4 7H20" stroke="currentColor" stroke-width="1.5" />
</svg>`)


const test = (value) => {
  newEmail.value = value
}



const createEmail = async () => {

  isEmail.value = true

  const emailInfo = 
  {
    "isActive": true,
    "type": 'order',
    "email": newEmail.value,
    "site": 'https://dinarz.hoggari.com',
  }

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=createEmail', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(emailInfo),
  });

  if (!response.ok) {
    isEmail.value = false
    log.value = t('error in response')
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    log.value = textResponse.message
    isEmail.value = false
    
  } else {
    log.value = textResponse.message
    isEmail.value = false
  }

}

const getEmail = async () => {

  isEmail.value = true

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=getEmail', {
    method: 'GET',
  });

  if (!response.ok) {
    isEmail.value = false
    return;
  }

  const textResponse = await response.json();

  if (textResponse.success) {
    for(var email in textResponse.data) {
      if(textResponse.data[email].type === 'order') {
        newEmail.value = textResponse.data[email].email
        break
      }
    }
    
    isEmail.value = false
    
  } else {
    console.error('res: ', textResponse.message)
    isEmail.value = false
  }

}


onMounted(() => {
  getEmail()
})


</script>

<style>

  .emailMenu {
    width: calc(100% - 20px);
    margin: 10px;
    border-radius: 8px;
    padding-block: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    background-color: var(--color-whitly);
    box-shadow: 0 4px 8px #3b3b3b20;
    text-align: center;
  }
  .dark .emailMenu {
    background-color: var(--color-darkly);
  }

</style>