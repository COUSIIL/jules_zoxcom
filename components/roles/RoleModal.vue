<template>
  <div v-if="isVisible" class="modal-overlay" @click.self="close">
    <div class="modal-content">
      <h2>{{ role?.id ? 'Modifier le Rôle' : 'Nouveau Rôle' }}</h2>

      <div class="form-group">
        <label>Nom du rôle</label>
        <input v-model="form.name" type="text" placeholder="Ex: Manager">
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea v-model="form.description" placeholder="Description courte..."></textarea>
      </div>

      <div class="permissions-section">
        <h3>Permissions</h3>
        <div class="permissions-grid">
          <div v-for="perm in availablePermissions" :key="perm.slug" class="perm-item">
            <input
              type="checkbox"
              :id="perm.slug"
              :value="perm.slug"
              v-model="form.permissions"
            >
            <label :for="perm.slug">{{ perm.name }}</label>
          </div>
        </div>
      </div>

      <div class="actions">
        <button @click="close" class="btn-cancel">Annuler</button>
        <button @click="save" class="btn-save" :disabled="loading">
          {{ loading ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  isVisible: Boolean,
  role: Object, // Role object (empty if new)
  availablePermissions: Array
})

const emit = defineEmits(['close', 'save'])

const form = ref({
  id: null,
  name: '',
  description: '',
  permissions: []
})
const loading = ref(false)

watch(() => props.role, (newRole) => {
  if (newRole) {
    form.value = {
      id: newRole.id || null,
      name: newRole.name || '',
      description: newRole.description || '',
      permissions: newRole.permissions || []
    }
  } else {
    form.value = { id: null, name: '', description: '', permissions: [] }
  }
}, { immediate: true })

const close = () => {
  emit('close')
}

const save = async () => {
  loading.value = true
  await emit('save', form.value)
  loading.value = false
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 500px;
  max-width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}
.dark .modal-content {
  background: #333;
  color: white;
}
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}
.form-group input, .form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.permissions-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 10px;
}
.perm-item {
  display: flex;
  align-items: center;
  gap: 5px;
}
.actions {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
.btn-save {
  background: #007bff;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.btn-cancel {
  background: #6c757d;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>
