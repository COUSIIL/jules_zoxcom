<template>

  <div class="filter-wrapper" @click.self="emit('close')">
    <div class="filterBox">

      <div class="rawBox">
        <h1>{{ t('Filter Orders') }}</h1>
        <Btn svg="x" iconColor="#ff5555" @click:ok="emit('close')" :isSimple="true" />
      </div>

      <div class="scroll-container">

        <!-- ✅ GRID des statuts -->
        <label class="section-label">{{ t('Status') }}</label>
        <div class="status-grid">
            <div
            v-for="st in statusMap"
            :key="st.value"
            class="boxColumn"
            @click="selectStatus(st.value)"
            >
                <div class="status-item" :class="['status-' + st.value, { active: filters.status === st.value }]">
                    <div v-if="st.svg" v-html="resizeSvg(st.svg, 24, 24)"></div>
                </div>
                <div style="font-size: 11px; margin-top: 5px; text-align:center;">
                    {{ t(st.label) }}
                </div>

            </div>
        </div>

        <div class="divider"></div>

        <!-- Inputs -->
        <div class="inputs-grid">

            <div class="input-group full-width">
                <label>{{ t('Date Period') }}</label>
                <VueDatePicker
                    v-model="dateRange"
                    range
                    :enable-time-picker="false"
                    :dark="isDark"
                    format="yyyy-MM-dd"
                    placeholder="Select Date Range"
                />
            </div>

            <div class="input-group">
                <label>{{ t('Wilaya') }}</label>
                <select v-model="filters.wilaya" class="custom-select">
                    <option value="">{{ t('All Wilayas') }}</option>
                    <option v-for="w in wilayas" :key="w.wilaya_id" :value="w.wilaya_name">
                        {{ w.wilaya_id }} - {{ w.wilaya_name }}
                    </option>
                </select>
            </div>
            <div class="input-group">
                <label>{{ t('Commune') }}</label>
                <input type="text" v-model="filters.commune" :placeholder="t('Commune')" class="custom-input"/>
            </div>

            <div class="input-group">
                <label>{{ t('Delivery Company') }}</label>
                <select v-model="filters.method" class="custom-select">
                    <option value="">{{ t('All') }}</option>
                    <option value="yalidine">Yalidine</option>
                    <option value="guepex">Guepex</option>
                    <option value="anderson">Anderson</option>
                    <option value="ups">UPS</option>
                    <option value="zr">ZR Express</option>
                </select>
            </div>

            <div class="input-group">
                <label>{{ t('Price Range') }}</label>
                <div class="price-range">
                    <input type="number" v-model="filters.min_price" placeholder="Min" class="custom-input"/>
                    <span>-</span>
                    <input type="number" v-model="filters.max_price" placeholder="Max" class="custom-input"/>
                </div>
            </div>

        </div>

      </div>

      <div class="actions">
          <Btn :text="t('Apply Filters')" svg="check" @click:ok="applyFilters" style="width: 100%;" />
          <button class="reset-btn" @click="resetFilters">{{ t('Reset') }}</button>
      </div>

    </div>
  </div>

</template>

<script setup>
import { ref, watch, computed } from 'vue'
import Btn from './elements/newBloc/rectBtn.vue'
import icons from '~/public/icons.json'
import iconsFilled from '~/public/iconsFilled.json'
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import { useLang } from '~/composables/useLang'

const { t } = useLang()

const emit = defineEmits(['close', 'selected'])
const props = defineProps({
    wilayas: { type: Array, default: () => [] }
})

// Detect Dark Mode (assuming class 'dark' on html or body)
const isDark = ref(false)
if (process.client) {
    const observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains('dark');
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    isDark.value = document.documentElement.classList.contains('dark');
}

const filters = ref({
    status: 'all',
    start_date: '',
    end_date: '',
    wilaya: '',
    commune: '',
    method: '',
    min_price: '',
    max_price: ''
})

const dateRange = ref(null)

// Watch dateRange to update start/end
watch(dateRange, (newVal) => {
    if (newVal && Array.isArray(newVal) && newVal.length === 2 && newVal[0] && newVal[1]) {
        filters.value.start_date = newVal[0].toISOString().split('T')[0]
        filters.value.end_date = newVal[1].toISOString().split('T')[0]
    } else {
        filters.value.start_date = ''
        filters.value.end_date = ''
    }
})

const resizeSvg = (svg, width, height) => {
    if(!svg) return ''
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
}

const statusMap = ref([
  { label: 'All', value: 'all', svg: icons['list'] },
  { label: 'Confirmed', value: 'confirmed', svg: iconsFilled['thumb-up'] },
  { label: 'Canceled', value: 'canceled', svg: iconsFilled['x'] },
  { label: 'Pending', value: 'waiting', svg: iconsFilled['alarm'] },
  { label: 'Completed', value: 'completed', svg: iconsFilled['check'] },
  { label: 'Unreaching', value: 'unreaching', svg: iconsFilled['phone'] },
  { label: 'Returned', value: 'returned', svg: iconsFilled['back'] },
  { label: 'Shipping', value: 'shipping', svg: iconsFilled['truck'] },
])

const selectStatus = (val) => {
    filters.value.status = val
}

const applyFilters = () => {
    emit('selected', { ...filters.value })
    emit('close')
}

const resetFilters = () => {
    filters.value = {
        status: 'all',
        start_date: '',
        end_date: '',
        wilaya: '',
        commune: '',
        method: '',
        min_price: '',
        max_price: ''
    }
    dateRange.value = null
}

</script>

<style scoped>

/* ✅ Wrapper */
.filter-wrapper {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  display: flex; justify-content: center; align-items: center;
  background-color: rgba(20, 20, 20, 0.5);
  backdrop-filter: blur(4px);
  z-index: 3000;
  padding: 20px;
}

/* ✅ Box */
.filterBox {
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  background-color: var(--color-whitly);
  padding: 25px;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.3);
  display: flex; flex-direction: column; gap: 15px;
}

.dark .filterBox { background-color: var(--color-darkly); }

.scroll-container {
    overflow-y: auto;
    flex: 1;
    padding-right: 5px;
}

.rawBox {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.rawBox h1 { font-size: 1.5rem; font-weight: 700; }

.section-label {
    font-weight: 700; margin-bottom: 15px; display: block;
    color: var(--color-zioly4);
    font-size: 1.1rem;
}

/* ✅ Grille des cercles */
.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
  gap: 15px;
  width: 100%;
}

/* ✅ Boutons circulaires */
.status-item {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8rem;
  text-align: center;
  font-weight: 600;
  cursor: pointer;
  transition: transform .2s, box-shadow .2s;
  color: #fff;
}

.status-item:hover {
  transform: scale(1.1);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.status-item.active {
    border: 3px solid var(--color-darky);
    transform: scale(1.15);
}
.dark .status-item.active {
    border: 3px solid var(--color-whitly);
}

.boxColumn {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    cursor: pointer;
}

.divider {
    height: 1px;
    background-color: #eee;
    margin-block: 25px;
}
.dark .divider {
    background-color: #333;
}

/* Inputs */
.inputs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.input-group.full-width {
    grid-column: span 2;
}

.input-group label {
    font-size: 0.9rem;
    font-weight: 600;
    color: #666;
}
.dark .input-group label { color: #aaa; }

.custom-input, .custom-select {
    padding: 10px 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    background: var(--color-whizy);
    color: var(--color-darky);
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s;
}
.custom-input:focus, .custom-select:focus {
    border-color: var(--color-zioly2);
}

.dark .custom-input, .dark .custom-select {
    border: 1px solid #444;
    background: var(--color-darkow);
    color: var(--color-whity);
}

.price-range {
    display: flex;
    align-items: center;
    gap: 10px;
}
.price-range span { color: #888; }

.actions {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.reset-btn {
    background: transparent;
    border: none;
    color: #888;
    cursor: pointer;
    font-size: 0.9rem;
    text-decoration: underline;
}

/* ✅ Couleurs dynamiques */
.status-all   { background-color: var(--color-zioly4) }
.status-confirmed   { background-color: var(--color-blumy) }
.status-shipping    { background-color: var(--color-yelly) }
.status-waiting     { background-color: var(--color-rangy) }
.status-pending     { background-color: var(--color-rangy) }
.status-unreaching  { background-color: var(--color-gorry) }
.status-returned    { background-color: var(--color-ioly) }
.status-canceled    { background-color: var(--color-rady) }
.status-completed   { background-color: var(--color-greeny) }

@media (max-width: 600px) {
    .inputs-grid {
        grid-template-columns: 1fr;
    }
    .input-group.full-width {
        grid-column: span 1;
    }
}
</style>