<template>
  <div class="stat-card">
    <div class="controls">
      <!-- Sélecteur de période -->
      <select v-model="period" class="period-select">
        <option value="hour">Par heure</option>
        <option value="day">Par jour</option>
        <option value="week">Par semaine</option>
        <option value="month">Par mois</option>
        <option value="year">Par année</option>
      </select>

      <!-- Sélecteur de plage -->
      <VueDatePicker 
        v-model="dateRange" 
        range multi-calendars
        format="dd/MM/yyyy" 
        placeholder="Choisir une plage de dates"
        :enable-time-picker="false"
        class="date-range"
      />
    </div>

    <div class="chart-wrapper">
      <Line
        id="transactions-chart"
        :options="chartOptions"
        :data="chartDataRef"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Chart as ChartJS, Title, Tooltip, Legend, CategoryScale, LinearScale, LineElement, PointElement } from 'chart.js'
import { Line } from 'vue-chartjs'
import VueDatePicker from "@vuepic/vue-datepicker"
import "@vuepic/vue-datepicker/dist/main.css"
ChartJS.register(Title, Tooltip, Legend, CategoryScale, LinearScale, LineElement, PointElement)

// props
const props = defineProps({
  transactions: { type: [Object, Array], default: () => ({ data: { transactions: [] } }) }
})

// période choisie
const period = ref('day')

// plage sélectionnée (vue-datepicker)
const dateRange = ref([null, null]) 

/**
 * safeParseDate:
 * - transforme "2025-09-05 14:10:42" => "2025-09-05T14:10:42"
 * - ajoute "T00:00:00" si seule la date est fournie
 */
function safeParseDate(str) {
  if (!str) return null
  let s = String(str).trim()
  if (s.includes(' ') && !s.includes('T')) s = s.replace(' ', 'T')
  if (!s.includes('T')) s = s + 'T00:00:00'
  const d = new Date(s)
  return isNaN(d) ? null : d
}

function startOfHour(d) { const t = new Date(d); t.setMinutes(0,0,0); return t }
function startOfDay(d)  { const t = new Date(d); t.setHours(0,0,0,0); return t }
function startOfMonth(d){ return new Date(d.getFullYear(), d.getMonth(), 1, 0,0,0,0) }
function startOfYear(d) { return new Date(d.getFullYear(), 0, 1, 0,0,0,0) }
function startOfISOWeek(d){
  const tmp = new Date(d)
  const day = (tmp.getDay() + 6) % 7
  tmp.setDate(tmp.getDate() - day)
  tmp.setHours(0,0,0,0)
  return tmp
}
function getISOWeekNumber(d) {
  const date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()))
  const day = date.getUTCDay() || 7
  date.setUTCDate(date.getUTCDate() + 4 - day)
  const yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1))
  return Math.ceil((((date - yearStart) / 86400000) + 1) / 7)
}

// signedAmount: opening/in => +, out => -
function signedAmount(tx) {
  const a = Number(tx?.amount) || 0
  return tx?.kind === 'out' ? -a : a
}

function formatLabelFromTs(ts, p) {
  const d = new Date(Number(ts))
  if (p === 'hour') return `${String(d.getHours()).padStart(2,'0')}:00`
  if (p === 'day') return d.toLocaleDateString('fr-FR')
  if (p === 'week') return `S${getISOWeekNumber(d)}`
  if (p === 'month') return `${d.toLocaleString('fr-FR',{month:'short'})} ${d.getFullYear()}`
  return String(d.getFullYear())
}

// chart data ref (Chart.js lit bien une ref qui change)
const chartDataRef = ref({
  labels: [],
  datasets: [{
    label: 'Transactions',
    data: [],
    borderColor: '#3d8bff',
    backgroundColor: 'rgba(61, 139, 255, 0.15)',
    tension: 0.35,
    fill: true,
    pointBackgroundColor: []
  }]
})

// chart options
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true },
    tooltip: {
      callbacks: {
        label(ctx) {
          const v = ctx.parsed?.y ?? ctx.parsed ?? 0
          return v.toLocaleString('fr-FR') + ' DA'
        }
      }
    }
  },
  scales: {
    x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 20 } },
    y: { ticks: { callback: v => Number(v).toLocaleString('fr-FR') } }
  }
}

/**
 * updateChart: agrège + filtre + met à jour chartDataRef
 */
watch(
  [() => props.transactions, period, dateRange],
  () => {
    const txs = props.transactions?.data?.transactions || (Array.isArray(props.transactions) ? props.transactions : [])

    // construire les bornes temporelles
    const [start, end] = dateRange.value
    const startFilter = start ? new Date(start) : null
    const endFilter   = end   ? new Date(end)   : null

    const map = new Map()

    for (const tx of txs) {
      const d = safeParseDate(tx.tx_date)
      if (!d) continue

      // filtres début/fin
      if (startFilter && d < startFilter) continue
      if (endFilter && d > endFilter) continue

      // choisir l’ancre selon la période
      let anchor
      if (period.value === 'hour') anchor = startOfHour(d)
      else if (period.value === 'day') anchor = startOfDay(d)
      else if (period.value === 'week') anchor = startOfISOWeek(d)
      else if (period.value === 'month') anchor = startOfMonth(d)
      else anchor = startOfYear(d)

      const key = anchor.getTime()
      map.set(key, (map.get(key) || 0) + signedAmount(tx))
    }

    // tri chronologique
    const entries = Array.from(map.entries()).sort((a,b) => a[0] - b[0])
    const labels = entries.map(([ts]) => formatLabelFromTs(ts, period.value))
    const values = entries.map(([,v]) => v)

    chartDataRef.value = {
      labels,
      datasets: [{
        label: 'Transactions',
        data: values,
        borderColor: '#3d8bff',
        backgroundColor: 'rgba(61, 139, 255, 0.15)',
        tension: 0.35,
        fill: true,
        pointBackgroundColor: values.map(v => v >= 0 ? '#1ecb6b' : '#ff6a3d'),
        pointRadius: 5,
        spanGaps: true
      }]
    }
  },
  { immediate: true, deep: true }
)
</script>


<style scoped>
.controls { display:flex; gap:10px; justify-content: right; align-items:center; margin-bottom:8px; }
.period-select {
  padding: 4px 8px;
  border-radius: 6px;
  background: var(--color-whity);
  font-size: 0.9rem;
}
.dark .period-select {
  background: var(--color-darkly);
}

/* fixe la hauteur du wrapper pour éviter l'expansion infinie */
.chart-wrapper { height: 360px; max-width: 100%; }

/* forcer canvas à remplir le wrapper */
#transactions-chart { width: 100%; height: 100% !important; }

.stat-card {
  background: var(--color-whitly);
  border-radius: 8px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.18);
  padding: 5px;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin: 10px auto;
}
.dark .stat-card { background: var(--color-darkow); }

.stat-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.stat-card-title {
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
}

.stat-card-chart {
  display: flex;
  justify-content: center;
  align-items: center;
}

.period-select, .date-select {
  padding: 4px 8px;
  border-radius: 6px;
  border:1px solid #ccc;
  background: var(--color-whity, #fff);
  font-size: 0.9rem;
}
.dark .period-select, .dark .date-select {
  background: var(--color-darkly);
}
</style>
