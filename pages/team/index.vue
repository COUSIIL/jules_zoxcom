<template>

  <LoaderBlack v-if="isSaving" width="80px"/>

  <Message :isVisible="isMessage"
          :message="message"
          @ok="isMessage = false"
        />

  <div class="flex flex-col items-center max-w-full">
    <div class="flex items-center justify-between w-full max-w-3xl min-w-[300px] p-2.5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
  
      <div class="flex items-center justify-center gap-1.25 mx-2.5">
          <div v-html="resizeSvg(icons['team'], 20, 20)">

          </div>
          <h1>
          {{ membersLength }} {{ t('members') }}
          </h1>
          
      </div>

      <CallToAction :text="t('add member')" :svg="icons['addMember']" @clicked="activate"/>

    </div>

    <AddMember v-if="isActive" @saving="saving" @success="success" @cancel="cancel" @message="messager" @x="isActive = false"/>

    <div v-for="(user, index) in members" :key="index" class="flex flex-col items-center justify-center w-11/12">
      <div class="flex flex-col items-end justify-between w-full max-w-3xl min-w-[300px] p-1.25 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md cursor-pointer bg-whitly dark:bg-darkly" @click="router.push(`/team/${user['username']}`)">
          <div class="flex items-center justify-center w-full min-w-[250px] max-w-52 gap-2.5 mx-1.25">
            <img v-if="user['profile_image']" :src="'https://management.hoggari.com/uploads/profile/' + user['profile_image']" :alt="user['profile_image']" class="flex object-cover w-24 h-24 rounded-full min-w-24 min-h-24">
            <div v-else class="w-24 h-24 rounded-full min-w-24 min-h-24 bg-whizy dark:bg-darkow">

            </div>
            <div class="flex flex-col items-center justify-center w-1/2 min-w-36 max-w-72">
              <div class="flex items-center justify-start min-w-[150px] max-w-[150px] overflow-hidden text-xl font-bold text-ellipsis whitespace-nowrap">
                {{ user['username'] }}
              </div>
              <div class="flex items-center justify-start min-w-[150px] max-w-[150px] overflow-hidden text-sm text-ellipsis whitespace-nowrap">
                {{ user['name'] }} {{ user['family_name'] }}
              </div>
              <div class="flex items-center justify-start min-w-[150px] max-w-[150px] overflow-hidden text-sm text-ellipsis whitespace-nowrap">
                {{ user['created_at'] }}
              </div>
            </div>
    
          </div>

          <div v-if="user['email']" class="flex items-center justify-center w-full min-w-[250px] max-w-52 gap-2.5 mx-1.25">
            <div v-html="resizeSvg(icons['mail'], 20, 20)">

            </div>
            <p>
              {{ user['email'] }}
            </p>
          </div>

          <div v-if="user['phone']" class="flex items-center justify-center w-full min-w-[250px] max-w-52 gap-2.5 mx-1.25">
            <div v-html="resizeSvg(icons['phone'], 20, 20)">

            </div>
            <p>
              {{ user['phone'] }}
            </p>
          </div>

        

        
      </div>

      
    </div>
    
  </div>

</template>

<script setup>

  import { ref, onMounted } from 'vue';
  import { useRouter } from 'nuxt/app';

  import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
  import AddMember from '../components/elements/addMember.vue';
  import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
  import Message from '../components/elements/bloc/message.vue';

  import icons from '~/public/icons.json'

  const router = useRouter();

  var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

  const { t } = useLang()

  const membersLength = ref(0)
  const members = ref()
  const successData = ref()
  const isSaving = ref(false)
  const isMessage = ref(false)
  const message = ref('')
  const isActive = ref(false)

  onMounted(() => {
    getUsers()
  })

  const activate = () => {
    isActive.value = true
  }

  const success = (value) => {
    successData.value = value
    getUsers()
  }
  const cancel = () => {
    isActive.value = false
  }
  const saving = (value) => {
    isSaving.value = value
  }
  const messager = (value) => {
    isMessage.value = true
    message.value = value
  }

  const getUsers = async () => {

    const response = await fetch('https://management.hoggari.com/backend/api.php?action=getUsers', {
      method: 'GET',
    })

    if(!response.ok) {
      console.error(t('error'))
      return
    }

    const result = await response.json()
    membersLength.value = result.data.length
    members.value = result.data
    

  }

</script>
