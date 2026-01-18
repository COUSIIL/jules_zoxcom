<template>

  <LoaderBlack v-if="loading" width="80px"/>

  <Message :isVisible="isMessage"
          :message="message"
          @ok="isMessage = false"
        />

  <div :style="{maxWidth: '100%', display: 'flex', flexDirection: 'column', alignItems: 'center'}">
    <div class="boxContainer1">

      <div style="display: flex; justify-content: center; align-items: center; margin-inline: 10px; gap: 5px;">
          <div v-html="resizeSvg(icons['hierarchy'], 20, 20)">

          </div>
          <h1>
          {{ roles.length }} {{ t('Rôles') }}
          </h1>

      </div>

      <CallToAction :text="t('Nouveau Rôle')" :svg="icons['add']" @clicked="openModal(null)"/>

    </div>

    <div v-for="(role, index) in roles" :key="role.id" class="center_column">
      <div class="boxContainer2">
          <div class="center_flex" style="justify-content: flex-start; width: auto; flex-grow: 1;">

            <div class="no_image" style="background-color: var(--color-whizy); display: flex; justify-content: center; align-items: center;">
                 <div v-html="resizeSvg(icons['key'], 40, 40)"></div>
            </div>

            <div class="insider">
              <div class="titleTeam">
                {{ role.name }}
              </div>
              <div class="minTitle">
                {{ role.description }}
              </div>
              <div class="minTitle" style="font-size: 12px; color: #888;">
                {{ role.permissions.length }} permissions
              </div>
            </div>

          </div>

          <div class="center_flex" style="justify-content: flex-end; gap: 20px; width: auto;">
              <!-- Edit -->
              <div v-if="hasPermission('edit_roles')"
                   v-html="resizeSvg(icons['edit'], 24, 24)"
                   @click.stop="openModal(role)"
                   style="cursor: pointer; color: #ffc107;"
                   title="Modifier">
              </div>

              <!-- Delete -->
              <div v-if="hasPermission('delete_roles') && role.name !== 'Admin'"
                   v-html="resizeSvg(icons['deleteImg'], 24, 24)"
                   @click.stop="deleteRole(role.id)"
                   style="cursor: pointer; color: #dc3545;"
                   title="Supprimer">
              </div>
          </div>


      </div>

    </div>

    <RoleModal
      :isVisible="showModal"
      :role="currentRole"
      :availablePermissions="availablePermissions"
      @close="showModal = false"
      @save="handleSave"
    />

  </div>

</template>

<script setup>

  import { ref, onMounted } from 'vue';
  import { useRouter } from 'nuxt/app';

  // Imports consistent with project structure (assuming standard relative paths)
  import CallToAction from '../../components/elements/bloc/callToActionBtn.vue';
  import LoaderBlack from '../../components/elements/animations/loaderBlack.vue';
  import Message from '../../components/elements/bloc/message.vue';
  import RoleModal from '../../components/roles/RoleModal.vue';

  import icons from '~/public/icons.json'

  definePageMeta({
    permission: 'read_roles'
  })

  const router = useRouter();
  const { hasPermission, auth } = useAuth()
  const { t } = useLang()

  var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

  const roles = ref([])
  const availablePermissions = ref([])
  const loading = ref(true)
  const isMessage = ref(false)
  const message = ref('')
  const showModal = ref(false)
  const currentRole = ref(null)

  onMounted(() => {
    fetchRoles()
  })

  const fetchRoles = async () => {
    loading.value = true
    try {
      const res = await fetch('https://management.hoggari.com/backend/api.php?action=getRoles')
      const json = await res.json()
      if (json.success) {
        roles.value = json.data.roles
        availablePermissions.value = json.data.availablePermissions
      }
    } catch (e) {
      console.error(e)
    } finally {
      loading.value = false
    }
  }

  const openModal = (role) => {
    currentRole.value = role
    showModal.value = true
  }

  const handleSave = async (roleData) => {
    try {
      const res = await fetch('https://management.hoggari.com/backend/api.php?action=saveRole', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...roleData, token: auth.value.token })
      })
      const json = await res.json()
      if (json.success) {
        showModal.value = false
        messager(t('Role saved successfully'))
        fetchRoles()
      } else {
        messager(json.message || t('Error'))
      }
    } catch (e) {
      console.error(e)
    }
  }

  const deleteRole = async (id) => {
    if (!confirm(t('Are you sure you want to delete this role?'))) return
    try {
      const res = await fetch('https://management.hoggari.com/backend/api.php?action=deleteRole', {
        method: 'POST',
        body: JSON.stringify({ id, token: auth.value.token })
      })
      const json = await res.json()
      if (json.success) {
        messager(t('Role deleted'))
        fetchRoles()
      } else {
        messager(json.message || t('Error'))
      }
    } catch (e) {
      console.error(e)
    }
  }

  const messager = (value) => {
    isMessage.value = true
    message.value = value
  }

</script>

<style scoped>

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
    align-items: center;
    justify-content: space-between;
    flex-direction: row;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
    padding: 10px;
    cursor: pointer;
  }
  .dark .boxContainer2{
    background-color: var(--color-darkly);
  }


  .no_image {
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

  .center_column {
    width: 90%;
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
  .minTitle {
    font-size: 14px;
    color: #666;
  }
  .dark .minTitle {
    color: #aaa;
  }

</style>