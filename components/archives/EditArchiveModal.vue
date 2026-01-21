<template>
  <div v-if="isOpen" class="modal-overlay" @click.self="close">
    <div class="modal-content">
      <div class="modal-header">
        <h3>{{ t('edit archived order') }} #{{ localOrder.id }}</h3>
        <button class="close-btn" @click="close">&times;</button>
      </div>

      <div class="modal-body">
        <div class="form-group">
          <label>{{ t('customer name') }}</label>
          <input v-model="localOrder.name" type="text" class="input-std" />
        </div>

        <div class="form-group">
          <label>{{ t('phone') }}</label>
          <input v-model="localOrder.phone" type="text" class="input-std" />
        </div>

        <div class="row">
          <div class="form-group half">
            <label>{{ t('wilaya') }}</label>
            <input v-model="localOrder.deliveryZone" type="text" class="input-std" />
          </div>
          <div class="form-group half">
             <label>{{ t('commune') }}</label>
            <input v-model="localOrder.sZone" type="text" class="input-std" />
          </div>
        </div>

        <div class="form-group">
          <label>{{ t('address') }}</label>
          <input v-model="localOrder.mZone" type="text" class="input-std" />
        </div>

        <div class="row">
            <div class="form-group half">
                <label>{{ t('status') }}</label>
                <select v-model="localOrder.status" class="input-std">
                    <option v-for="s in statuses" :key="s" :value="s">{{ t(s) }}</option>
                </select>
            </div>
             <div class="form-group half">
                <label>{{ t('delivery method') }}</label>
                <select v-model="localOrder.method" class="input-std">
                    <option value="Yalidine">Yalidine</option>
                    <option value="ZR Express">ZR Express</option>
                    <option value="Guepex">Guepex</option>
                    <option value="Ups">Ups</option>
                    <option value="Anderson">Anderson</option>
                    <option value="58 Wilaya">58 Wilaya</option>
                </select>
            </div>
        </div>

         <div class="form-group">
          <label>{{ t('delivery fees') }}</label>
          <input v-model.number="localOrder.deliveryValue" type="number" class="input-std" />
        </div>

        <div class="form-group">
            <label>{{ t('note') }}</label>
            <textarea v-model="noteText" class="input-std area" rows="3"></textarea>
            <small>Combined note text</small>
        </div>

      </div>

      <div class="modal-footer">
        <RectBtn :text="t('cancel')" :isSimple="true" @click="close" />
        <RectBtn :text="t('save')" @click="save" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useLang } from '../../composables/useLang';
import RectBtn from '../../components/elements/newBloc/rectBtn.vue';

const props = defineProps({
  isOpen: Boolean,
  order: Object
});

const emit = defineEmits(['close', 'save']);
const { t } = useLang();

const localOrder = ref({});
const noteText = ref('');

const statuses = [
  'canceled', 'waiting', 'pending', 'confirmed',
  'completed', 'shipping', 'unreaching', 'returned'
];

watch(() => props.order, (newVal) => {
  if (newVal) {
    localOrder.value = JSON.parse(JSON.stringify(newVal)); // Deep copy
    // Handle note: convert array to string for editing
    if (Array.isArray(localOrder.value.note)) {
        noteText.value = localOrder.value.note.map(n => n.text).join('\n');
    } else {
        noteText.value = localOrder.value.note || '';
    }
  }
}, { immediate: true });

const close = () => {
  emit('close');
};

const save = () => {
  // reconstruct note array
  // We just create one simple note object or preserve structure?
  // Simple: overwrite with one note entry or split by newline
  const notes = noteText.value.split('\n').filter(t => t.trim() !== '').map(text => ({
      text,
      user: 'admin', // default
      color: '',
      isClientNote: false
  }));

  localOrder.value.note = JSON.stringify(notes); // stringify for the backend if it expects JSON string or array?
  // getArchivedOrders.js: updateArchivedOrder sends JSON body.
  // backend/sql/update/archivedOrder.php: `addUpdate('note', ...)`
  // The backend just saves what we send.
  // BUT the frontend list expects an Array (because we parse it in composable).
  // AND the DB stores a string.

  // Strategy:
  // 1. Send the Array to the backend in `updateArchivedOrder` in composable.
  // 2. Composable sends JSON.stringify'd body.
  // 3. Backend receives JSON object. `addUpdate` treats it.
  //    If `note` is an array in the payload, PHP receives an array.
  //    `addUpdate` uses `s` type. Array to string conversion in PHP might fail or be "Array".
  //    Check backend script: `addUpdate` binds as `?`.

  // Correction: In `backend/sql/update/archivedOrder.php`, I used `addUpdate` which prepares a bind param.
  // If I send an array, I must json_encode it in the Backend OR send a string from frontend.
  // Let's send a JSON string from frontend to be safe.

  // So:
  localOrder.value.note = JSON.stringify(notes);

  emit('save', localOrder.value);
};

</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(2px);
}

.modal-content {
  background: var(--color-background); /* standard var? */
  background-color: #fff;
  padding: 20px;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  gap: 15px;
  max-height: 90vh;
  overflow-y: auto;
}

[data-theme='dark'] .modal-content {
    background-color: #1e1e1e;
    color: #fff;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #ddd;
  padding-bottom: 10px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.row {
    display: flex;
    gap: 10px;
}
.half {
    width: 50%;
}

label {
  font-size: 12px;
  font-weight: bold;
  color: #666;
}

.input-std {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  outline: none;
  background: transparent;
}

.area {
    resize: vertical;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  border-top: 1px solid #ddd;
  padding-top: 10px;
}
</style>
