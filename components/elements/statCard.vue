<template>
  <div class="stat-card">
    <div class="stat-card-header">
      <div class="stat-card-title">Revenus ({{ periodLabel }})</div>

      <!-- Sélecteur de période -->
      <select v-model="period" class="period-select">
        <option value="hour">Heure</option>
        <option value="day">Jour</option>
        <option value="week">Semaine</option>
        <option value="month">Mois</option>
        <option value="year">Année</option>
      </select>
    </div>

    <div class="stat-card-chart">
      <svg class="linechart" viewBox="0 0 360 140" preserveAspectRatio="xMidYMid meet">
        <defs>
          <linearGradient id="lineGradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#3d8bff"/>
            <stop offset="100%" stop-color="#1ecb6b"/>
          </linearGradient>
          <linearGradient id="areaGradient" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#3d8bff" stop-opacity="0.3"/>
            <stop offset="100%" stop-color="#1ecb6b" stop-opacity="0"/>
          </linearGradient>
          <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
            <feGaussianBlur result="coloredBlur" stdDeviation="3" />
            <feMerge>
              <feMergeNode in="coloredBlur"/>
              <feMergeNode in="SourceGraphic"/>
            </feMerge>
          </filter>
        </defs>

        <!-- Axe zéro -->
        <line
          v-if="hasData"
          :x1="xMin" :x2="xMax"
          :y1="yZero" :y2="yZero"
          stroke="currentColor" stroke-width="1" stroke-dasharray="3 4"
        />

        <!-- Aire sous la courbe -->
        <path v-if="hasData" fill="url(#areaGradient)" :d="areaPath" />

        <!-- Ligne -->
        <polyline v-if="hasData"
          filter="url(#glow)"
          :points="points"
          stroke-width="4"
          stroke="url(#lineGradient)"
          fill="none"
        />

        <!-- Points + tooltips -->
        <g v-for="(val, index) in values" :key="index" class="dot-group">
          <circle
            :fill="index % 2 === 0 ? '#3d8bff' : '#1ecb6b'"
            r="6"
            :cx="xMin + index * stepX"
            :cy="getY(val)">
          </circle>
          <g class="tooltip">
            <rect
              opacity="0.92" fill="#232733" rx="8"
              height="34" width="110"
              :y="getY(val) - 44"
              :x="xMin + index * stepX - 55"/>
            <text
              font-weight="500" font-size="13" fill="#fff" text-anchor="middle"
              :y="getY(val) - 22"
              :x="xMin + index * stepX">
              {{ formatNumber(val) }}
            </text>
          </g>
        </g>

        <!-- Labels X -->
        <g fill="#b0b6c3" font-size="12" class="x-labels">
          <text v-for="(label, i) in labels" :key="i"
                text-anchor="middle"
                y="135"
                :x="xMin + i * stepX">
            {{ label }}
          </text>
        </g>
      </svg>
    </div>

    <div class="stat-card-legend">
      <div class="legend-item">
        <span>Revenu moyen ({{ periodLabel }})</span>
      </div>
      <div class="legend-item">
        <span class="legend-value">{{ formatNumber(avgRevenue) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  transactions: { type: Array, default: [] },
})

const period = ref('month')
const periodLabel = computed(() => ({
  hour: 'heure', day: 'jour', week: 'semaine', month: 'mois', year: 'année'
}[period.value]))

const monthShort = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]

function startOfHour(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate(), d.getHours()) }
function startOfDay(d) { return new Date(d.getFullYear(), d.getMonth(), d.getDate()) }
function startOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1) }
function startOfYear(d) { return new Date(d.getFullYear(), 0, 1) }
function startOfISOWeek(d) {
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
function getSignedAmount(tx) {
  const a = Number(tx.amount)
  return tx.kind === 'out' ? -a : a
}
function formatNumber(n) {
  return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 2 }).format(n)
}

const series = computed(() => {
  const bucket = new Map()
  for (const tx of props.transactions) {
    const d = new Date(tx.tx_date)
    if (isNaN(d)) continue

    let anchor
    if (period.value === 'hour') anchor = startOfHour(d)
    else if (period.value === 'day') anchor = startOfDay(d)
    else if (period.value === 'week') anchor = startOfISOWeek(d)
    else if (period.value === 'month') anchor = startOfMonth(d)
    else anchor = startOfYear(d)

    const key = anchor.getTime()
    bucket.set(key, (bucket.get(key) || 0) + getSignedAmount(tx))
  }

  const entries = Array.from(bucket.entries()).sort((a,b) => a[0]-b[0])
  return entries.map(([ts, val]) => {
    const d = new Date(ts)
    let label = ''
    if (period.value === 'hour') {
      label = `${d.getHours()}h`
    } else if (period.value === 'day') {
      label = d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
    } else if (period.value === 'week') {
      label = `S${getISOWeekNumber(d)}`
    } else if (period.value === 'month') {
      label = monthShort[d.getMonth()]
    } else {
      label = String(d.getFullYear())
    }
    return { ts, label, val }
  })
})

const labels = computed(() => series.value.map(s => s.label))
const values = computed(() => series.value.map(s => s.val))
const hasData = computed(() => values.value.length > 0)

const viewW = 360, viewH = 140
const xMin = 15, xMax = 345
const yTop = 10, yBottom = 120
const xSpan = xMax - xMin, ySpan = yBottom - yTop

const stepX = computed(() => {
  const n = values.value.length
  return n > 1 ? xSpan / (n - 1) : 0
})

const domainMin = computed(() => Math.min(0, ...(values.value.length ? values.value : [0])))
const domainMax = computed(() => Math.max(0, ...(values.value.length ? values.value : [0])))

function getY(v) {
  const min = domainMin.value, max = domainMax.value
  if (max - min === 0) return (yTop + yBottom) / 2
  const t = (v - min) / (max - min)
  return yBottom - t * ySpan
}
const yZero = computed(() => getY(0))

const points = computed(() => values.value.map((v,i) =>
  `${xMin + i * stepX.value},${getY(v)}`
).join(' '))

const areaPath = computed(() => {
  const n = values.value.length
  if (!n) return ''
  const firstX = xMin, lastX = xMin + (n - 1) * stepX.value
  let d = `M${firstX},${yZero.value} `
  values.value.forEach((v,i) => {
    d += `L${xMin + i * stepX.value},${getY(v)} `
  })
  d += `L${lastX},${yZero.value} Z`
  return d
})

const avgRevenue = computed(() => {
  if (!values.value.length) return 0
  return values.value.reduce((a,b)=>a+b,0) / values.value.length
})
</script>

<style scoped>
/* Garde tes styles, j’ai juste ajouté l’input .period-select et réglé la largeur max du chart */
.stat-card {
  background: var(--color-whitly);
  border-radius: 8px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.18);
  padding: 10px;
  width: 90%;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  margin: 20px auto;
}
.dark .stat-card { background: var(--color-darkow); }

.stat-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.stat-card-title {
  font-family: "Inter", sans-serif;
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
.linechart { width: 100%; max-width: 600px; height: auto; overflow: visible;}

.dot-group .tooltip { opacity: 0; pointer-events: none; transition: opacity 0.2s; }
.dot-group:hover .tooltip { opacity: 1; }
.tooltip rect {
  filter: drop-shadow(0 4px 16px rgba(0,0,0,0.22));
  opacity: 0.92; rx: 8; fill: #232733; stroke: #232733; stroke-width: 1.2;

}
.tooltip text {
  font-family: "Inter", sans-serif; font-size: 15px; font-weight: 500;
  fill: #fff; letter-spacing: 0.2px;
  text-shadow: 0 2px 8px rgba(0,0,0,0.18);
}

.stat-card-legend {
  margin-top: 0.5rem; display: flex;
  flex-direction: column; gap: 0.6rem;
}
.legend-item {
  display: flex; align-items: center;
  justify-content: space-between;
  font-size: 1rem; color: #b0b6c3;
}
.legend-item:first-child {
  font-size: 0.95rem; color: #b8c0cc; font-weight: 400;
  letter-spacing: 0.01em;
}

.legend-value {
  font-size: 2rem; color: #3d8bff;
  font-weight: 600; margin-left: 0.5rem;
}

.period-select {
  padding: 4px 8px;
  border-radius: 6px;
  background: var(--color-whity);
  font-size: 0.9rem;
}
.dark .period-select {
  background: var(--color-darkly);
}


.x-labels text {
  font-family: "Inter", sans-serif;
  font-size: 12px;
  font-weight: 500;
  fill: var(--color-darkly);
  transform: rotate(-65deg);
  transform-box: fill-box;
  transform-origin: center;
}
.dark .x-labels text{
    fill: var(--color-whity);
}
</style>
