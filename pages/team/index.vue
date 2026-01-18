<template>

  <LoaderBlack v-if="isSaving" width="80px"/>

  <Message :isVisible="isMessage"
          :message="message"
          @ok="isMessage = false"
        />

  <div :style="{maxWidth: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center'}">
    <div class="boxContainer1">
  
      <div style="display: flex; justify-content: center; align-items: center; margin-inline: 10px; gap: 5px;">
          <div v-html="resizeSvg(icons['team'], 20, 20)">

          </div>
          <h1>
          {{ membersLength }} {{ t('members') }}
          </h1>
          
      </div>

      <CallToAction :text="t('add member')" :svg="icons['addMember']" @clicked="activate"/>

    </div>

    <AddMember v-if="isActive" @saving="saving" @success="success" @cancel="cancel" @message="messager" @x="isActive = false"/>

    <div v-for="(user, index) in members" :key="index" class="center_column">
      <div class="boxContainer2" @click="router.push(`/team/${user['username']}`)">
          <div class="center_flex">
            <img v-if="user['profile_image']" :src="'https://management.hoggari.com/uploads/profile/' + user['profile_image']" :alt="user['profile_image']">
            <div v-else class="no_image">

            </div>
            <div class="insider">
              <div class="titleTeam">
                {{ user['username'] }}
              </div>
              <div class="minTitle">
                {{ user['name'] }} {{ user['family_name'] }}
              </div>
              <div class="minTitle">
                {{ user['created_at'] }}
              </div>
            </div>
    
          </div>

          <div v-if="user['email']" class="center_flex">
            <div v-html="resizeSvg(icons['mail'], 20, 20)">

            </div>
            <p>
              {{ user['email'] }}
            </p>
          </div>

          <div v-if="user['phone']" class="center_flex">
            <div v-html="resizeSvg(icons['phone'], 20, 20)">

            </div>
            <p>
              {{ user['phone'] }}
            </p>
          </div>

          <div v-if="hasPermission('assign_roles') && roles.length > 0" class="center_flex" @click.stop style="flex-direction: column; align-items: flex-start;">
             <label style="font-size: 12px; font-weight: bold;">Rôle:</label>
             <select v-model="user.role_id" @change="changeRole(user)" style="padding: 5px; border-radius: 4px; border: 1px solid #ccc; width: 100%;">
                <option :value="null">Aucun</option>
                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
             </select>
          </div>

          <div v-if="hasPermission('delete_users')" class="center_flex" @click.stop>
            <div v-html="resizeSvg(icons['deleteImg'], 24, 24)" @click="deleteUser(user)" style="cursor: pointer; color: #dc3545;"></div>
          </div>
        
      </div>

      
    </div>
    
  </div>

</template>

<script setup>

  import { ref, onMounted } from 'vue';
  import { useRouter } from 'nuxt/app';

  import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
  import AddMember from '../../components/elements/addMember.vue';
  import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
  import Message from '../components/elements/bloc/message.vue';

  import icons from '~/public/icons.json'

  const router = useRouter();
  const { hasPermission, auth } = useAuth()

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
  const roles = ref([])

  onMounted(() => {
    getUsers()
    if (hasPermission('manage_users')) {
      fetchRoles()
    }
  })

  const fetchRoles = async () => {
    try {
      const res = await fetch('https://management.hoggari.com/backend/api.php?action=getRoles')
      const json = await res.json()
      if (json.success) {
        roles.value = json.data.roles
      }
    } catch (e) {
      console.error(e)
    }
  }

  const changeRole = async (user) => {
    try {
      const res = await fetch('https://management.hoggari.com/backend/api.php?action=assignRole', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          user_id: user.id,
          role_id: user.role_id,
          token: auth.value.token
        })
      })
      const json = await res.json()
      if (json.success) {
        messager('Rôle mis à jour')
      } else {
        messager(json.message || 'Erreur')
      }
    } catch (e) {
      console.error(e)
    }
  }

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

  const deleteUser = async (user) => {
    if (!confirm(t('Are you sure you want to delete this user?'))) return

    try {
      const res = await fetch('https://management.hoggari.com/backend/api.php?action=deleteUser', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          id: user.id,
          token: auth.value.token
        })
      })
      const json = await res.json()
      if (json.success) {
        messager(t('User deleted'))
        getUsers()
      } else {
        messager(json.message || t('Error'))
      }
    } catch (e) {
      console.error(e)
    }
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

<style>

  .boxContainer1 {
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
  .dark .boxContainer1{
    background-color: var(--color-darkly);
  }

  .boxContainer2 {
    display: flex;
    align-items: right;
    justify-content: space-between;
    flex-direction: column;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
    padding: 5px;
    cursor: pointer;
  }
  .dark .boxContainer2{
    background-color: var(--color-darkly);
  }
  

  .no_image {
    width: 100px;
    height: 100px;
    min-width: 100px;
    min-height: 100px;
    border-radius: 50px;
    background-color: var(--color-whizy);
  }
  .dark .no_image {
    background-color: var(--color-darkow);
  }

  .center_column {
    width: 90%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  .center_flex {
    width: 100%;
    display: flex;
    min-width: 250px;
    max-width: 200px;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-inline: 5px;
  }

  .insider {
    width: 50%;
    display: flex;
    min-width: 150px;
    max-width: 300px;
    justify-content: center;
    align-items: center;
    flex-direction: column;
  }
  .titleTeam {
    display: flex;
    justify-content: left;
    align-items: center;
    min-width: 150px;
    max-width: 150px;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 20px;
    font-weight: bold;
  }
  .minTitle {
    display: flex;
    justify-content: left;
    align-items: center;
    min-width: 150px;
    max-width: 150px;
    overflow: hidden;
    white-space: nowrap;       /* Empêche le retour à la ligne */
    text-overflow: ellipsis;   /* Ajoute ... si ça dépasse */
    font-size: 14px;
  }

  .center_flex img {
    width: 100px;
    height: 100px;
    min-width: 100px;
    min-height: 100px;
    object-fit: cover; /* Remplit la zone en gardant le ratio */
    display: flex;
    border-radius: 50%; /* Cercle parfait */
    

  }


</style>