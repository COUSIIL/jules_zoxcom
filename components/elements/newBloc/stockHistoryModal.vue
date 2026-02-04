<template>
  <div v-if="isVisible" class="history-overlay" @click.self="$emit('close')">
    <div class="history-modal">
      <div class="header">
        <h3>Historique Code: {{ code }}</h3>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>
      <div class="content">
        <div v-if="loading" class="loader">Chargement...</div>
        <div v-else-if="history.length === 0" class="empty">Aucun historique.</div>
        <ul v-else class="history-list">
          <li v-for="(item, index) in history" :key="index">
            <div class="row-top">
                <span class="action">{{ formatAction(item.action) }}</span>
                <span class="date">{{ item.date }}</span>
            </div>
            <div class="row-bottom">
                <span class="status">
                    <span :class="['badge', item.old_status]">{{ item.old_status || '?' }}</span>
                    ➔
                    <span :class="['badge', item.new_status]">{{ item.new_status }}</span>
                </span>
                <span class="user" v-if="item.user">par {{ item.user }}</span>
                <span class="order" v-if="item.order_id">Ordre #{{ item.order_id }}</span>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  isVisible: Boolean,
  stockId: Number,
  code: String
});

const emit = defineEmits(['close']);

const history = ref([]);
const loading = ref(false);

watch(() => props.isVisible, (val) => {
  if (val && props.stockId) {
    fetchHistory();
  }
});

const fetchHistory = async () => {
  loading.value = true;
  try {
    const res = await fetch(`https://management.hoggari.com/backend/sql/get/stockHistory.php?stock_id=${props.stockId}`);
    const data = await res.json();
    if (data.success) {
      history.value = data.data;
    }
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const formatAction = (action) => {
    return action.replace(/_/g, ' ').toUpperCase();
}
</script>

<style scoped>
.history-overlay {
  position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.5); z-index: 4000;
  display: flex; justify-content: center; align-items: center;
}
.history-modal {
  background: var(--color-whity); width: 90%; max-width: 500px;
  border-radius: 12px; padding: 20px;
  max-height: 80vh; display: flex; flex-direction: column;
}
.dark .history-modal { background: var(--color-darkly); }

.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
.close-btn { background: none; border: none; font-size: 24px; cursor: pointer; color: inherit; }

.content { flex: 1; overflow-y: auto; }
.history-list { list-style: none; padding: 0; }
.history-list li {
    padding: 10px; border-bottom: 1px solid var(--color-zioly2);
}
.row-top { display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px; }
.row-bottom { display: flex; justify-content: space-between; font-size: 12px; align-items: center; flex-wrap: wrap; gap: 5px; }

.badge { padding: 2px 6px; border-radius: 4px; background: #eee; color: #333; }
.badge.available { background: #d1fae5; color: #065f46; }
.badge.reserved { background: #fff7ed; color: #9a3412; }
.badge.sold { background: #dbeafe; color: #1e40af; }
.badge.returned { background: #fee2e2; color: #991b1b; }

.user { font-style: italic; color: #666; }
.order { font-weight: 600; color: var(--color-blumy); }
.empty { text-align: center; padding: 20px; color: #888; }
</style>
