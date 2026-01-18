<template>
  <div class="roles-page">
    <div class="header">
      <h1>Gestion des Rôles</h1>
      <button @click="openModal(null)" class="btn-add">Nouveau Rôle</button>
    </div>

    <div v-if="loading" class="loading">Chargement...</div>

    <div v-else class="roles-list">
      <div v-for="role in roles" :key="role.id" class="role-card">
        <div class="role-header">
          <h3>{{ role.name }}</h3>
          <div class="actions">
            <button @click="openModal(role)" class="btn-edit">Modifier</button>
            <button @click="deleteRole(role.id)" class="btn-delete" v-if="role.name !== 'Admin'">Supprimer</button>
          </div>
        </div>
        <p class="desc">{{ role.description }}</p>
        <div class="perms-summary">
          <strong>Permissions:</strong>
          <span v-if="role.permissions.length > 0">
            {{ role.permissions.length }} permissions actives
          </span>
          <span v-else>Aucune permission</span>
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
import { ref, onMounted } from 'vue'
import RoleModal from '../../components/roles/RoleModal.vue'

definePageMeta({
  permission: 'manage_roles'
})

const roles = ref([])
const availablePermissions = ref([])
const loading = ref(true)
const showModal = ref(false)
const currentRole = ref(null)
const { t } = useLang()
const { auth } = useAuth()

const fetchRoles = async () => {
  loading.value = true
  try {
    const res = await fetch('https://management.hoggari.com/backend/api.php?action=getRoles')
    const json = await res.json()
    
    if (json.success) {
      roles.value = json.data.roles
      availablePermissions.value = json.data.availablePermissions
    }

    console.log('roles.value: ', roles.value)
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
      fetchRoles()
    } else {
      alert(json.message)
    }
  } catch (e) {
    console.error(e)
  }
}

const deleteRole = async (id) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?')) return
  try {
    const res = await fetch('https://management.hoggari.com/backend/api.php?action=deleteRole', {
      method: 'POST',
      body: JSON.stringify({ id, token: auth.value.token })
    })
    const json = await res.json()
    if (json.success) {
      fetchRoles()
    } else {
      alert(json.message)
    }
  } catch (e) {
    console.error(e)
  }
}

onMounted(() => {
  fetchRoles()
})
</script>

<style scoped>
.roles-page {
  padding: 20px;
  max-width: 1000px;
  margin: 0 auto;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.roles-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}
.role-card {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.dark .role-card {
  background: #2a2a2a;
  color: white;
}
.role-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}
.btn-add {
  background: #28a745;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}
.btn-edit {
  background: #ffc107;
  border: none;
  padding: 5px 10px;
  border-radius: 3px;
  margin-right: 5px;
  cursor: pointer;
}
.btn-delete {
  background: #dc3545;
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 3px;
  cursor: pointer;
}
.desc {
  color: #666;
  font-size: 0.9em;
  margin-bottom: 10px;
}
.dark .desc {
  color: #aaa;
}
.perms-summary {
  font-size: 0.85em;
  color: #888;
}
</style>
