<template>
  <div v-if="isVisible" class="overlay-modal" @click.self="$emit('close')">
    <div class="modal-box">
      <h3>{{ t('History') }} #{{ orderId }}</h3>
      <div v-if="loading" class="loading">
        <p>{{ t('loading...') }}</p>
      </div>
      <div v-else class="history-list">
        <div v-for="item in history" :key="item.id" class="history-item">
          <div class="row-header">
            <span class="user">
                <strong>{{ item.user }}</strong>
            </span>
            <span class="date">{{ item.created_at }}</span>
          </div>
          <div class="row-body">
            <span class="action-tag">{{ item.action }}:</span>
            <span class="value-text">{{ formatValue(item.value) }}</span>
          </div>
        </div>
        <div v-if="history.length === 0" class="empty">
          {{ t('No history found') }}
        </div>
      </div>
      <div class="modal-actions">
        <RectBtn :text="t('Close')" iconColor="#ff5555" svg="x" @click:ok="$emit('close')" :isSimple="true"/>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import RectBtn from './rectBtn.vue';

const props = defineProps({
  isVisible: Boolean,
  orderId: Number
});

const emit = defineEmits(['close']);

const history = ref([]);
const loading = ref(false);

const { t } = useLang();

const fetchHistory = async () => {
  if (!props.orderId) return;
  loading.value = true;
  try {
    const response = await fetch(`https://management.hoggari.com/backend/api.php?action=getOrderHistory&id=${props.orderId}`);
    const res = await response.json();
    if (res.success) {
      history.value = res.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

watch(() => props.isVisible, (val) => {
  if (val) {
    fetchHistory();
  }
});

const formatValue = (val) => {
    if (!val) return '';
    if (val.length > 50) return val.substring(0, 50) + '...';
    return val;
}

</script>

<style scoped>
.overlay-modal {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.5);
  display: flex; justify-content: center; align-items: center;
  z-index: 3000;
}
.modal-box {
  background: var(--color-whity);
  padding: 20px;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  gap: 15px;
}
.dark .modal-box {
  background: var(--color-darkly);
  color: var(--color-whity);
}
.history-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.history-item {
  background: var(--color-whizy);
  padding: 10px;
  border-radius: 8px;
  border-left: 4px solid var(--color-zioly);
}
.dark .history-item {
  background: var(--color-darkow);
}
.row-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #666;
  margin-bottom: 5px;
}
.dark .row-header {
  color: #aaa;
}
.row-body {
    font-size: 0.95rem;
}
.action-tag {
    font-weight: bold;
    margin-right: 5px;
    color: var(--color-zioly4);
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
}
</style>
