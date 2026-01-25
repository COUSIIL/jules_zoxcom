<template>
  <div class="stats-card">
    <div class="header">
      <div class="title-row">
          <h3>{{ title }}</h3>
          <div class="icon-bg">
              <slot name="icon"></slot>
          </div>
      </div>
      <input
        :type="inputType"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="date-input"
      />
    </div>

    <div class="main-stat">
      <span class="label">{{ t('Total Orders') }}</span>
      <span class="value">{{ stats?.total || 0 }}</span>
    </div>

    <div class="stats-grid">
      <div class="stat-item completed">
        <span class="lbl">{{ t('Completed') }}</span>
        <span class="val">{{ stats?.completed || 0 }}</span>
      </div>
      <div class="stat-item confirmed">
        <span class="lbl">{{ t('Confirmed') }}</span>
        <span class="val">{{ stats?.confirmed || 0 }}</span>
      </div>
      <div class="stat-item returned">
        <span class="lbl">{{ t('Returned') }}</span>
        <!-- Combining Canceled + Unreachable (Unreaching in DB) -->
        <span class="val">{{ (stats?.canceled || 0) + (stats?.unreachable || 0) }}</span>
      </div>
      <div class="stat-item other">
        <span class="lbl">{{ t('Other') }}</span>
        <!-- Combining Awaiting + Delivered -->
        <span class="val">{{ (stats?.awaiting || 0) + (stats?.delivered || 0) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useLang } from '~/composables/useLang'

const { t } = useLang()

const props = defineProps({
  title: {
    type: String,
    default: ''
  },
  stats: {
    type: Object,
    default: () => ({})
  },
  type: {
    type: String, // 'day', 'week', 'month'
    default: 'day'
  },
  modelValue: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue'])

const inputType = computed(() => {
    if (props.type === 'week') return 'week'
    if (props.type === 'month') return 'month'
    return 'date'
})
</script>

<style scoped>
.stats-card {
  background: var(--color-whitly);
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
  gap: 15px;
  transition: transform 0.2s;
}
.dark .stats-card {
  background: var(--color-darkow);
}
.stats-card:hover {
    transform: translateY(-2px);
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 5px;
}
.title-row h3 {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0;
}

.date-input {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 0.85rem;
    background: transparent;
    color: var(--color-text);
    outline: none;
    cursor: pointer;
}
.dark .date-input {
    border-color: #444;
}

.main-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--color-whizy);
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 5px;
}
.dark .main-stat {
    background: rgba(255,255,255,0.05);
}
.main-stat .label {
    font-weight: 600;
    opacity: 0.8;
}
.main-stat .value {
    font-size: 1.4rem;
    font-weight: 800;
}

.stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    padding: 10px;
    border-radius: 8px;
    border-left: 4px solid #ccc;
    background: rgba(0,0,0,0.02);
}
.dark .stat-item {
    background: rgba(255,255,255,0.03);
}

.stat-item .lbl {
    font-size: 0.75rem;
    text-transform: uppercase;
    opacity: 0.7;
    margin-bottom: 4px;
}
.stat-item .val {
    font-size: 1.1rem;
    font-weight: 700;
}

.completed { border-color: #16a085; }
.completed .val { color: #16a085; }

.confirmed { border-color: #2ecc71; }
.confirmed .val { color: #2ecc71; }

.returned { border-color: #e74c3c; }
.returned .val { color: #e74c3c; }

.other { border-color: #3498db; }
.other .val { color: #3498db; }

</style>
