<template>
  <Confirm :isVisible="showConfirm" @confirm="remouveReminder()"
    @cancel="cancelConfirmDelete" />
  <div v-if="reminder" class="reminder-box">
    <!-- 🕒 Date du rappel -->
     <div>
      <RectBtn iconColor="#ff5555" svg="trashX" @click:ok="showConfirmDelete(reminder.id)" :isSimple="true" />
     </div>
    <div class="reminder-header">
      <div class="reminder-date">
        🕒 {{ formatDate(reminder.reminder_date) }}
      </div>
      <div class="reminder-meta">
        <span>ID: {{ reminder.id }}</span>
        <span v-if="reminder.work">• Travail #{{ reminder.work }}</span>
      </div>
    </div>

    <!-- 📝 Liste des notes -->
    <div v-if="notes.length" class="reminder-notes">
      <div
        v-for="(note, index) in notes"
        :key="index"
        class="reminder-note"
        :style="{ backgroundColor: note.color || '#fff7b0' }"
      >
        <img
          v-if="note.profile_image"
          class="note-profile"
          :src="getProfileImage(note.profile_image)"
          alt="profile"
        />
        <div class="note-content">
          <div class="note-text">{{ note.text }}</div>
          <div class="note-user">@{{ note.user }}</div>
        </div>
      </div>
    </div>

    <!-- ⏰ Date de création -->
    <div class="reminder-footer">
      Créé le {{ formatDate(reminder.data_time) }}
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import RectBtn from './rectBtn.vue';
import Confirm from '../bloc/confirm.vue';

import { useReminder } from '../../../composables/reminder';
const { remouveRemind, orderIdRemindRemouved } = useReminder();

const props = defineProps({
  reminder: { type: Object, required: true },
  orderId: { type: Number, required: true }
})

const emit = defineEmits(['deleted'])


// ✅ notes est un tableau issu du JSON stringifié
const notes = computed(() => {
  try {
    return JSON.parse(props.reminder.note || '[]')
  } catch {
    return []
  }
})

// Formatage lisible de la date
const formatDate = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const showConfirm = ref(false)
const idToEdit = ref(0)


const showConfirmDelete = (id) => {
  idToEdit.value = id
  showConfirm.value = true
}

const cancelConfirmDelete = () => {
  idToEdit.value = 0
  showConfirm.value = false
}

watch(orderIdRemindRemouved.value, (newLoad) => {
  if(newLoad) {
    emit('deleted', newLoad)
  }

});

const remouveReminder = async () => {
  await remouveRemind(idToEdit.value, props.orderId)
  showConfirm.value = false
  if(orderIdRemindRemouved.value || orderIdRemindRemouved.value == 0) {
    emit('deleted', orderIdRemindRemouved.value)
  }
  
}




// Construit le chemin image du profil (adapter selon ton backend)
const getProfileImage = (filename) => `https://management.hoggari.com/uploads/profile/${filename}`
</script>

<style scoped>
.reminder-box {
  background: #fffbe6;
  border: 1px solid #e6e6e6;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.05);
}

.dark .reminder-box {
  background: #131002;
  border: 1px solid #131002;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}


.reminder-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  font-size: 14px;
}

.reminder-meta {
  font-size: 12px;
  color: #777;
}

.reminder-notes {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.reminder-note {
  display: flex;
  align-items: center;
  border-radius: 10px;
  padding: 6px 10px;
  box-shadow: inset 0 0 2px rgba(0,0,0,0.1);
}

.note-profile {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  margin-right: 8px;
  object-fit: cover;
}

.note-content {
  display: flex;
  flex-direction: column;
}

.note-text {
  font-size: 13px;
  font-weight: 500;
  color: #333;
}

.note-user {
  font-size: 11px;
  color: #666;
}

.reminder-footer {
  font-size: 11px;
  color: #999;
  text-align: right;
  margin-top: 4px;
}
</style>
