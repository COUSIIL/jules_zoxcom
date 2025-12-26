<template>

  <div class="filter-wrapper">
    <div class="filterBox">

      <div class="rawBox">
        <h1>{{ t('filter') }}</h1>
        <Btn svg="x" iconColor="#ff5555" @click:ok="emit('close')" />
      </div>

      <!-- ✅ GRID des statuts -->
      <div class="status-grid">
        <div 
          v-for="st in statusMap" 
          :key="st.value"
          class="boxColumn"
          
          @click="emitStatus(st.value)"
        >   
            <div class="status-item" :class="'status-' + st.value">

            </div>
            <div>
                {{ t(st.label) }}
            </div>
          
        </div>
      </div>

    </div>
  </div>

</template>

<script setup>
import Btn from './elements/newBloc/rectBtn.vue'
const { t } = useLang()

const emit = defineEmits(['close', 'selected'])

const statusMap = ref([
    { label: 'All', value: 'all' },
  { label: 'Confirmed', value: 'confirmed' },
  { label: 'Canceled', value: 'canceled' },
  { label: 'Pending', value: 'waiting' },
  { label: 'Completed', value: 'completed' },
  { label: 'Unreaching', value: 'unreaching' },
  { label: 'Returned', value: 'returned' },
  { label: 'Shipping', value: 'shipping' },
])

const emitStatus = (vl) => {
    emit('selected', vl)
    emit('close')
}
</script>

<style>

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
  width: min(90%, 350px);
  background-color: var(--color-whitly);
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.25);
  display: flex; flex-direction: column; gap: 20px;
}

.dark .filterBox { background-color: var(--color-darkly); }

/* ✅ Grille des cercles */
.status-grid {
  display: grid;

  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: 15px;
  width: 100%;
}

/* ✅ Boutons circulaires */
.status-item {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .8rem;
  text-align: center;
  font-weight: 600;
  cursor: pointer;
  transition: transform .2s, box-shadow .2s;
}

.status-item:hover {
  transform: scale(1.08);
  box-shadow: 0 3px 10px rgba(0,0,0,0.25);
}

.boxColumn {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
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
