<template>
  <div class="flex flex-col items-center justify-between w-full max-w-3xl min-w-[300px] p-2.5 m-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
    <div class="flex items-center justify-center w-full mx-2.5">
      <div class="mt-4 m-2.5">
        <label class="flex flex-col items-center justify-center max-w-[150px] min-w-[150px] max-h-[150px] min-h-[150px] p-4 text-center border-2 border-dashed rounded-full cursor-pointer border-gray-300">
          <input type="file" accept="image/*" @change="onImageSelected" hidden />
          <div v-if="imageUrl">
            <img :src="imageUrl" alt="preview" class="max-w-[150px] max-h-[150px] rounded-full" />
          </div>
          
          <div class="text-sm text-gray-500" v-else>
            {{ t('profile picture') }}
            {{ t('choose an image') }}
          </div>
        </label>
      </div>
      <div class="flex flex-col items-center justify-between w-full m-2.5">
        <Inputer :placeHolder="t('user name')" v-model="form.username" :required="true"/>
        <Inputer :placeHolder="t('email')" v-model="form.email" :required="true"/>
      </div>
    </div>

    <div class="flex items-center justify-center w-full mx-2.5">
      <Inputer :placeHolder="t('name')" v-model="form.firstname" />
      <Inputer :placeHolder="t('fammily name')" v-model="form.lastname" />
    </div>
    
    <Inputer :placeHolder="t('password')" v-model="form.password" type="password" :required="true"/>

    <div class="flex items-center justify-center w-full mx-2.5">
      <CBtn :text="t('cancel')" :svg="icons['x']" @clicked="emit('x')"/>
      <CallToAction :text="t('add')" :svg="icons['check']" @clicked="submitForm"/>
    </div>
  </div>
</template>

<script setup>
import Inputer from '../components/elements/bloc/input.vue'
import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
import CBtn from '../components/elements/bloc/cancelBtn.vue'
import { ref } from 'vue'
import icons from '~/public/icons.json'

const { t } = useLang()

// Données du formulaire
const form = ref({
  username: '',
  email: '',
  firstname: '',
  lastname: '',
  password: '',
  profileImage: null,
})

const imageUrl = ref(null)

const emit = defineEmits(['saving', 'cancel', 'success', 'message', 'x'])

function onImageSelected(event) {
  const file = event.target.files[0]
  if (file && file.type.startsWith('image/')) {
    form.value.profileImage = file
    imageUrl.value = URL.createObjectURL(file)
  }
}

const submitForm = async () => {
  emit('saving', true)
  if(!form.value.username) {
    emit('message', t('enter a valid username'))
    return
  }
  if(!form.value.email) {
    emit('message', t('enter a valid email'))
    return
  }
  if(!form.value.password) {
    emit('message', t('enter a valid password'))
    return
  }
  const formData = new FormData()
  formData.append('username', form.value.username)
  formData.append('email', form.value.email)
  formData.append('firstname', form.value.firstname)
  formData.append('lastname', form.value.lastname)
  formData.append('password', form.value.password)
  if (form.value.profileImage) {
    formData.append('profile_image', form.value.profileImage)
  }

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=addUser', {
    method: 'POST',
    body: formData,
  })

  if(!response.ok) {
    emit('saving', false)
    emit('message', result.message)
    emit('cancel', true)
    
    return
  }

  const result = await response.json()

  if(result.success) {
    emit('success', result)
    emit('message', result.message)
    emit('saving', false)
  } else {
    
    emit('message', result.message)
    emit('cancel', true)
    emit('saving', false)
  }
  

}

</script>
