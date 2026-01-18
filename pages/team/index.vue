<template>

  <LoaderBlack v-if="isSaving || loading" width="80px"/>

  <Message :isVisible="isMessage"
          :message="message"
          @ok="isMessage = false"
        />

  <div :style="{maxWidth: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center'}">

    <!-- Header Box -->
    <div class="boxContainerRole">
  
      <div style="display: flex; justify-content: center; align-items: center; margin-inline: 10px; gap: 5px;">
          <div v-html="resizeSvg(icons['team'], 20, 20)"></div>
          <h1>
          {{ membersLength }} {{ t('members') }}
          </h1>
      </div>

      <CallToAction :text="t('add member')" :svg="icons['addMember']" @clicked="activate"/>

    </div>

    <AddMember v-if="isActive" @saving="saving" @success="success" @cancel="cancel" @message="messager" @x="isActive = false"/>

    <!-- User List -->
    <div v-for="(user, index) in members" :key="index" class="center_column">

      <!-- Action Bar (Delete/Edit permissions) -->
       <div v-if="hasPermission('delete_users') || hasPermission('manage_roles') || hasPermission('all_permissions')" style="width: 100%; display: flex; justify-content: right; align-items: center;">
          <div class="action_bar">
            <div v-html="resizeSvg(iconsFilled['trashX'], 24, 24)" @click="deleteUser(user)" style="cursor: pointer; color: #ff5555;"></div>
          </div>
      </div>

      <!-- User Card -->
      <div class="boxContainerRole2" @click="router.push(`/team/${user['username']}`)">

          <div class="center_flex" style="justify-content: flex-start; width: auto; flex-grow: 1;">

            <img v-if="user['profile_image']" :src="'https://management.hoggari.com/uploads/profile/' + user['profile_image']" :alt="user['profile_image']" class="profile_img">
            <div v-else class="no_image"></div>

            <div class="insider">
              <div class="titleTeam">
                {{ user['username'] }}
              </div>
              <div class="minTitleRole">
                {{ user['name'] }} {{ user['family_name'] }}
              </div>
              <div class="minTitleRole" style="font-size: 12px; color: #888;">
                {{ user['role_name'] || t('No Role') }}
              </div>
            </div>

          </div>

          <div class="infos_row">
            <div v-if="user['email']" class="center_flex_small">
              <div v-html="resizeSvg(icons['mail'], 16, 16)"></div>
              <p>{{ user['email'] }}</p>
            </div>
            <div v-if="user['phone']" class="center_flex_small">
              <div v-html="resizeSvg(icons['phone'], 16, 16)"></div>
              <p>{{ user['phone'] }}</p>
            </div>
             <div class="center_flex_small">
                <p style="font-size: 12px; color: #aaa;">{{ user['created_at'] }}</p>
             </div>
          </div>

          <!-- Role Selector Trigger -->
          <div v-if="hasPermission('assign_roles') || hasPermission('manage_roles') || hasPermission('all_permissions') && roles.length > 0" class="role_section" @click.stop>
             <!-- Using a button/label to trigger the selector modal -->
             <div class="role_trigger" @click="openRoleSelector(user)">
                <div v-html="resizeSvg(icons['key'], 18, 18)"></div>
                <span>{{ user['role_name'] || t('Assign Role') }}</span>
                <div v-html="resizeSvg(icons['edit'], 14, 14)" style="margin-left: auto;"></div>
             </div>
          </div>
        
      </div>
    </div>
    
  </div>

  <!-- Role Selector Modal -->
  <div style="height: 0; overflow: visible;">
    <Selector
      :options="roleOptions"
      :showIt="showRoleSelector"
      :modelValue="selectedRoleId"
      :placeHolder="'Select Role'"
      :disabled="true"
      @close="showRoleSelector = false"
      @update:modelValue="handleRoleSelect"
    />
  </div>

</template>

<script setup>

  import { ref, onMounted, computed } from 'vue';
  import { useRouter } from 'nuxt/app';

  import CallToAction from '../../components/elements/bloc/callToActionBtn.vue';
  import AddMember from '../../components/elements/addMember.vue';
  import LoaderBlack from '../../components/elements/animations/loaderBlack.vue';
  import Message from '../../components/elements/bloc/message.vue';
  import Selector from '../../components/elements/bloc/select.vue';

  import icons from '~/public/icons.json'
  import iconsFilled from '~/public/iconsFilled.json'

  const router = useRouter();
  const { hasPermission, auth } = useAuth()
  const { t } = useLang()

  var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

  const membersLength = ref(0)
  const members = ref()
  const successData = ref()
  const isSaving = ref(false)
  const loading = ref(false)
  const isMessage = ref(false)
  const message = ref('')
  const isActive = ref(false)
  const roles = ref([])

  // Selector state
  const showRoleSelector = ref(false)
  const currentUserToEdit = ref(null)
  const selectedRoleId = ref(null)

  const roleOptions = computed(() => {
    const opts = roles.value.map(r => ({
      label: r.name,
      value: r.id,
      img: ''
    }))
    // Add "No Role" option
    opts.unshift({ label: t('No Role'), value: null, img: '' })
    return opts
  })

  onMounted(() => {
    getUsers()
    if (hasPermission('manage_users') || hasPermission('assign_roles')) {
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

  const openRoleSelector = (user) => {
    currentUserToEdit.value = user
    selectedRoleId.value = user.role_id
    showRoleSelector.value = true
  }

  const handleRoleSelect = (newRoleId) => {
    if (currentUserToEdit.value) {
        // Update local state temporarily/optimistically
        currentUserToEdit.value.role_id = newRoleId
        // Find role name
        const role = roles.value.find(r => r.id === newRoleId)
        currentUserToEdit.value.role_name = role ? role.name : null

        changeRole(currentUserToEdit.value)
    }
  }

  const changeRole = async (user) => {
    isSaving.value = true
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
        messager(t('Role updated'))
      } else {
        messager(json.message || t('Error'))
      }
    } catch (e) {
      console.error(e)
    } finally {
        isSaving.value = false
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
    loading.value = true
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=getUsers', {
      method: 'GET',
    })

    if(!response.ok) {
      console.error(t('error'))
      loading.value = false
      return
    }

    const result = await response.json()
    membersLength.value = result.data.length
    members.value = result.data
    loading.value = false

  }

</script>

<style scoped>

  /* Styles borrowed from Roles page to ensure consistency */
  .boxContainerRole {
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
  .dark .boxContainerRole{
    background-color: var(--color-darkly);
  }

  .boxContainerRole2 {
    display: flex;
    align-items: left;
    justify-content: center;
    flex-direction: column;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    margin-bottom: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
    padding: 10px;
    cursor: pointer;
  }
  .dark .boxContainerRole2{
    background-color: var(--color-darkly);
  }

  .center_column {
    width: 90%;
    max-width: 800px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  .center_flex {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .center_flex_small {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 13px;
    color: #666;
  }
  .dark .center_flex_small {
    color: #bbb;
  }

  .action_bar {
    display: flex;
    align-items: center;
    justify-content: right;
    background-color: var(--color-whitly);
    border-radius: 6px;
    padding: 5px;
    margin-block: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
    gap: 10px;
  }
  .dark .action_bar {
    background-color: var(--color-darkly);
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 1);
  }


  .insider {
    display: flex;
    flex-direction: column;
    margin-left: 10px;
  }
  .titleTeam {
    font-size: 18px;
    font-weight: bold;
    color: var(--color-darkly);
  }
  .dark .titleTeam {
    color: var(--color-whitly);
  }
  .minTitleRole {
    width: 100%;
    font-size: 14px;
    color: #666;
  }
  .dark .minTitleRole {
    color: #aaa;
  }

  .no_image {
    display: flex; justify-content: center; align-items: center;
    width: 60px;
    height: 60px;
    min-width: 60px;
    min-height: 60px;
    border-radius: 50%;
    background-color: var(--color-whizy);
  }
  .dark .no_image {
    background-color: var(--color-darkow);
  }

  .profile_img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
  }

  .infos_row {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 10px;
      padding-top: 10px;
      border-top: 1px solid rgba(0,0,0,0.05);
  }
  .dark .infos_row {
      border-top: 1px solid rgba(255,255,255,0.05);
  }

  .role_section {
      margin-top: 10px;
      width: 100%;
  }

  .role_trigger {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 12px;
      background-color: var(--color-whizy);
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.2s;
  }
  .dark .role_trigger {
      background-color: var(--color-darkow);
  }
  .role_trigger:hover {
      background-color: #eee;
  }
  .dark .role_trigger:hover {
      background-color: #333;
  }

</style>