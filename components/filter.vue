<template>

  <div class="filter-wrapper">
    <div class="filterBox">

      <div class="rawBox">
        <h1>{{ t('filter') }}</h1>
        <Btn svg="x" iconColor="#ff5555" @click:ok="emit('close')" />
      </div>

      <div class="scroll-container">

        <!-- ✅ GRID des statuts -->
        <label style="font-weight: bold; margin-bottom: 10px; display: block;">{{ t('Status') }}</label>
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
                <div style="font-size: 12px; margin-top: 5px;">
                    {{ t(st.label) }}
                </div>

            </div>
        </div>

        <div class="divider"></div>

        <!-- Inputs -->
        <div class="inputs-grid">

            <div class="input-group">
                <label>{{ t('Date Start') }}</label>
                <input type="date" v-model="filters.start_date" />
            </div>
            <div class="input-group">
                <label>{{ t('Date End') }}</label>
                <input type="date" v-model="filters.end_date" />
            </div>

            <div class="input-group">
                <label>{{ t('Wilaya') }}</label>
                <input type="text" v-model="filters.wilaya" :placeholder="t('Wilaya')" />
            </div>
            <div class="input-group">
                <label>{{ t('Commune') }}</label>
                <input type="text" v-model="filters.commune" :placeholder="t('Commune')" />
            </div>

            <div class="input-group">
                <label>{{ t('Society') }}</label>
                <input type="text" v-model="filters.method" :placeholder="t('Delivery Company')" />
            </div>

            <div class="input-group">
                <label>{{ t('Min Price') }}</label>
                <input type="number" v-model="filters.min_price" placeholder="0" />
            </div>
            <div class="input-group">
                <label>{{ t('Max Price') }}</label>
                <input type="number" v-model="filters.max_price" placeholder="Max" />
            </div>

        </div>

      </div>

      <div class="actions">
          <Btn :text="t('Apply')" svg="check" @click:ok="applyFilters" style="width: 100%;" />
      </div>

    </div>
  </div>

</template>

<script setup>
import { ref } from 'vue'
import Btn from './elements/newBloc/rectBtn.vue'
import icons from '~/public/icons.json'
import iconsFilled from '~/public/iconsFilled.json'

const { t } = useLang()

const emit = defineEmits(['close', 'selected'])

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

const resizeSvg = (svg, width, height) => {
    if(!svg) return ''
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
}

const statusMap = ref([
  { label: 'All', value: 'all', svg: icons['list'] }, // Example icon
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

</script>

<style scoped>

/* ✅ Wrapper */
.filter-wrapper {
  position: fixed;
  top: 0; left: 0;
  width: 100vw; height: 100vh;
  display: flex; justify-content: center; align-items: center;
  background-color: rgba(20, 20, 20, 0.3);
  backdrop-filter: blur(2px);
  z-index: 1000;
  padding: 20px;
}

/* ✅ Box */
.filterBox {
  width: min(95%, 500px);
  max-height: 90vh;
  background-color: var(--color-whitly);
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.25);
  display: flex; flex-direction: column; gap: 10px;
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

/* ✅ Grille des cercles */
.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
  gap: 10px;
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
  transform: scale(1.08);
  box-shadow: 0 3px 10px rgba(0,0,0,0.25);
}

.status-item.active {
    border: 3px solid var(--color-darky);
    transform: scale(1.1);
}
.dark .status-item.active {
    border: 3px solid var(--color-whitly);
}

.boxColumn {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

.divider {
    height: 1px;
    background-color: #eee;
    margin-block: 20px;
}
.dark .divider {
    background-color: #333;
}

/* Inputs */
.inputs-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.input-group label {
    font-size: 12px;
    font-weight: bold;
    color: #666;
}

.input-group input {
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background: var(--color-whizy);
    color: var(--color-darky);
}
.dark .input-group input {
    border: 1px solid #444;
    background: var(--color-darkow);
    color: var(--color-whity);
}

.actions {
    margin-top: 10px;
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

</style>