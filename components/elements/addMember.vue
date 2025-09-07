<template>
  <div class="boxContainer2">
    <div class="centerFlex">
      <div class="profile-image-upload">
        <label class="upload-label">
          <input type="file" accept="image/*" @change="onImageSelected" hidden />
          <div class="image-preview" v-if="imageUrl">
            <img :src="imageUrl" alt="preview" />
          </div>
          
          <div class="image-placeholder" v-else>
            {{ t('profile picture') }}
            {{ t('choose an image') }}
          </div>
        </label>
      </div>
      <div class="centerColumn">
        <Inputer :placeHolder="t('user name')" v-model="form.username" :required="true"/>
        <Inputer :placeHolder="t('email')" v-model="form.email" :required="true"/>
      </div>
      <!-- Champs existants -->
      
    </div>

    <div class="centerFlex">
      <Inputer :placeHolder="t('name')" v-model="form.firstname" />
      <Inputer :placeHolder="t('fammily name')" v-model="form.lastname" />
    </div>
    
    
    <Inputer :placeHolder="t('password')" v-model="form.password" type="password" :required="true"/>

    <div class="centerFlex">
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

<style scoped>
.profile-image-upload {
  margin-top: 1rem;
  margin: 10px;
}

.upload-label {
  max-width: 150px;
  min-width: 150px;
  max-height: 150px;
  min-height: 150px;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  border: 2px dashed #ccc;
  padding: 1rem;
  justify-content: center;
  align-items: center;
  text-align: center;
  border-radius: 50%;
}

.image-preview img {
  max-width: 150px;
  max-height: 150px;
  border-radius: 50%;
}

.image-placeholder {
  color: #999;
  font-size: 14px;
}

.centerFlex {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  margin-inline: 10px;
}

.centerColumn {
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
  margin: 10px;
}

  .boxContainer2 {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
  }
  .dark .boxContainer2{
    background-color: var(--color-darkly);
  }

</style>
