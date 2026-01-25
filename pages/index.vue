<template>
  <LoaderBlack v-if="!isMounted" width="80px"/>
  
  <div style="display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 1400px; margin: 0 auto;">

    <!-- Pinned Orders Section -->
    <div v-if="pinnedOrders.length > 0" class="pinned-section">
      <h3>📌 {{ t('Pinned Orders') }}</h3>
      <div class="pinned-grid">
        <div v-for="order in pinnedOrders" :key="order.id" class="pinned-card">
          <div class="pinned-header">
            <span class="pinned-id">Order #{{ order.id }}</span>
            <button @click="unpin(order.id)" class="unpin-btn" title="Unpin">✕</button>
          </div>
          <div class="pinned-reason">"{{ order.pin_reason }}"</div>
          <div class="pinned-info">
             <span class="p-name">{{ order.name }}</span>
             <span class="p-price">{{ order.total }} DA</span>
          </div>
          <div class="pinned-status" :class="'status-' + order.status">{{ order.status }}</div>
        </div>
      </div>
    </div>

    <!-- Dashboard KPIs (New) -->
    <div class="kpi-grid" v-if="dashboardStats">
        <StatsPeriodCard
            :title="t('Daily Stats')"
            type="day"
            :stats="dashboardStats.today"
            v-model="filters.day"
        />
        <StatsPeriodCard
            :title="t('Weekly Stats')"
            type="week"
            :stats="dashboardStats.week"
            v-model="filters.week"
        />
        <StatsPeriodCard
            :title="t('Monthly Stats')"
            type="month"
            :stats="dashboardStats.month"
            v-model="filters.month"
        />
    </div>

    <!-- Analysis Section -->
    <div class="analysis-section" v-if="analysisData">
      <div class="filters-bar">
        <h3>📊 {{ t('Analysis') }}</h3>
        <div class="controls">
            <select v-model="granularity" class="granularity-select">
                <option value="day">{{ t('By Day') }}</option>
                <option value="week">{{ t('By Week') }}</option>
                <option value="month">{{ t('By Month') }}</option>
            </select>
            <div class="datepicker-wrapper">
                <VueDatePicker v-model="dateRange" range :dark="isDark" :enable-time-picker="false" />
            </div>
        </div>
      </div>

      <div class="charts-grid">
         <!-- Trend Chart -->
         <div class="chart-card full-width">
            <h4>{{ t('Orders Trend') }}</h4>
            <div class="chart-inner">
                <GraphBar :data="trendChartData" height="300px" />
            </div>
         </div>

         <!-- Wilayas Chart -->
         <div class="chart-card">
            <h4>{{ t('Top Wilayas') }} ({{ t('Completed') }})</h4>
            <div class="chart-inner">
                <GraphBar :data="wilayaChartData" height="300px" />
            </div>
         </div>

         <!-- Products Chart -->
         <div class="chart-card">
            <h4>{{ t('Top Products') }}</h4>
            <div class="chart-inner">
                <GraphBar :data="productChartData" height="300px" />
            </div>
         </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useLang } from '~/composables/useLang'
import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
import GraphBar from '../components/elements/graphBar.vue';
import StatsPeriodCard from '../components/elements/newBloc/statsPeriodCard.vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

const { t } = useLang()
const isMounted = ref(false)
const isLoading = ref(true)
const isDark = ref(false)

// State
const pinnedOrders = ref([])
const dashboardStats = ref(null)
const analysisData = ref(null)

// Filters for Analysis
const now = new Date()
const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1)
const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0)
const dateRange = ref([startOfMonth, endOfMonth])
const granularity = ref('day')

// Filters for KPI Cards
const filters = ref({
    day: getFormattedDate(now),
    week: getFormattedWeek(now),
    month: getFormattedMonth(now)
})

onMounted(async () => {
    // Detect dark mode
    if (typeof window !== 'undefined') {
        const checkDark = () => document.documentElement.classList.contains('dark') || document.body.classList.contains('dark')
        isDark.value = checkDark()
        const observer = new MutationObserver(() => isDark.value = checkDark())
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
    }

    await Promise.all([
        getPinnedOrders(),
        getStatistics()
    ])
    isMounted.value = true
    isLoading.value = false
})

// Watchers
watch([dateRange, granularity], () => {
    getStatistics()
})

watch(() => filters.value, () => {
    // Debounce could be added here if needed, but for now direct call
    getStatistics()
}, { deep: true })


async function getStatistics() {
    try {
        let url = 'https://management.hoggari.com/backend/api.php?action=getStatistics'

        // Analysis Range
        if (dateRange.value && dateRange.value[0] && dateRange.value[1]) {
            const start = formatDate(dateRange.value[0])
            const end = formatDate(dateRange.value[1])
            url += `&startDate=${start}&endDate=${end}`
        }

        url += `&granularity=${granularity.value}`

        // KPI Filters
        // Day
        url += `&filterDay=${filters.value.day}`

        // Month (Add -01 to make it a date)
        url += `&filterMonth=${filters.value.month}-01`

        // Week (Convert YYYY-Www to a date in that week)
        const weekDate = getDateFromWeekStr(filters.value.week)
        url += `&filterWeek=${weekDate}`

        const response = await fetch(url)
        const result = await response.json()

        if (result.success) {
            dashboardStats.value = result.dashboard
            analysisData.value = result.analysis
        }
    } catch (e) {
        console.error("Error fetching stats:", e)
    }
}

async function getPinnedOrders() {
    try {
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=getPinnedOrders')
        if (response.ok) {
            const result = await response.json()
            if (result.success) {
                pinnedOrders.value = result.data
            }
        }
    } catch (e) {
        console.error(e)
    }
}

async function unpin(id) {
    if(!confirm(t('Unpin this order?'))) return;
    try {
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=unpinOrder', {
            method: 'POST',
            body: JSON.stringify({ order_id: id })
        })
        const res = await response.json()
        if (res.success) {
            pinnedOrders.value = pinnedOrders.value.filter(o => o.id !== id)
        }
    } catch (e) {
        console.error(e)
    }
}

// Helpers
function formatDate(d) {
    return d.toISOString().split('T')[0]
}

function getFormattedDate(d) {
    return d.toISOString().split('T')[0]
}

function getFormattedMonth(d) {
    return d.toISOString().slice(0, 7) // YYYY-MM
}

function getFormattedWeek(d) {
    // Return YYYY-Www
    const date = new Date(d.getTime());
    date.setHours(0, 0, 0, 0);
    date.setDate(date.getDate() + 3 - (date.getDay() + 6) % 7);
    const week1 = new Date(date.getFullYear(), 0, 4);
    const weekNumber = 1 + Math.round(((date.getTime() - week1.getTime()) / 86400000 - 3 + (week1.getDay() + 6) % 7) / 7);
    return `${date.getFullYear()}-W${weekNumber.toString().padStart(2, '0')}`;
}

function getDateFromWeekStr(str) {
    // Input: "2023-W42"
    if (!str || !str.includes('-W')) return getFormattedDate(new Date())

    const [y, w] = str.split('-W')
    const year = parseInt(y)
    const week = parseInt(w)

    // Simple calc to get the Monday of that week
    // 4th Jan is always in week 1
    const d = new Date(year, 0, 4);
    const dayNum = d.getDay() || 7;
    d.setDate(d.getDate() + 4 - dayNum); // Adjust to Thursday of week 1

    // Add (week - 1) weeks
    d.setDate(d.getDate() + (week - 1) * 7);

    // Set to Monday
    const finalDay = d.getDay() || 7;
    d.setDate(d.getDate() - finalDay + 1);

    return getFormattedDate(d);
}


// Charts Data
const trendChartData = computed(() => {
    if (!analysisData.value?.trend) return { labels: [], datasets: [] }
    const trend = analysisData.value.trend
    return {
        labels: trend.map(item => item.date_label),
        datasets: [{
            label: t('Orders'),
            data: trend.map(item => item.count),
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    }
})

const wilayaChartData = computed(() => {
    if (!analysisData.value?.topWilayas) return { labels: [], datasets: [] }
    const items = analysisData.value.topWilayas
    return {
        labels: items.map(i => i.delivery_zone),
        datasets: [{
            label: t('Completed Orders'),
            data: items.map(i => i.count),
            backgroundColor: 'rgba(22, 160, 133, 0.6)',
            borderColor: 'rgba(22, 160, 133, 1)',
            borderWidth: 1
        }]
    }
})

const productChartData = computed(() => {
    if (!analysisData.value?.topProducts) return { labels: [], datasets: [] }
    const items = analysisData.value.topProducts
    return {
        labels: items.map(i => i.product_name),
        datasets: [{
            label: t('Sold Qty'),
            data: items.map(i => i.count),
            backgroundColor: 'rgba(153, 102, 255, 0.6)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
        }]
    }
})
</script>

<style scoped>
.kpi-grid {
    width: 90%;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    padding: 20px;
}

/* Analysis Section */
.analysis-section {
    width: 90%;
    margin-top: 20px;
    padding: 0 20px 40px 20px;
}
.filters-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}
.filters-bar h3 {
    font-size: 1.4rem;
    font-weight: 600;
}
.controls {
    display: flex;
    gap: 10px;
    align-items: center;
}
.granularity-select {
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #ddd;
    background: var(--color-whitly);
    color: var(--color-text);
}
.dark .granularity-select {
    background: var(--color-darkow);
    border: 1px solid #444;
}
.datepicker-wrapper {
    width: 250px;
}

.charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}
.chart-card {
    background: var(--color-whitly);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}
.dark .chart-card {
    background: var(--color-darkow);
}
.chart-card h4 {
    margin-bottom: 15px;
    font-size: 1.1rem;
    opacity: 0.8;
}
.chart-inner {
    position: relative;
    width: 100%;
}
.full-width {
    grid-column: span 2;
}
@media (max-width: 900px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
    .full-width {
        grid-column: span 1;
    }
}


/* Pinned Section */
.pinned-section {
  width: 90%;
  background: var(--color-whity);
  margin-bottom: 20px;
  padding: 15px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.dark .pinned-section {
  background: var(--color-darkly);
}
.pinned-section h3 {
  font-size: 1.2rem;
  margin-bottom: 15px;
  color: var(--color-zioly4);
}
.pinned-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 15px;
}
.pinned-card {
  background: var(--color-whizy);
  border-radius: 8px;
  padding: 10px;
  border: 1px solid #eee;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 5px;
}
.dark .pinned-card {
  background: var(--color-darkow);
  border: 1px solid #333;
}
.pinned-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
}
.unpin-btn {
  background: transparent;
  border: none;
  color: #ff5555;
  cursor: pointer;
  font-size: 1.1rem;
}
.pinned-reason {
  font-style: italic;
  font-size: 0.9rem;
  color: #666;
  margin-bottom: 5px;
}
.dark .pinned-reason { color: #aaa; }

.pinned-info {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
}
.p-name { font-weight: 600; }
.pinned-status {
  font-size: 0.75rem;
  padding: 2px 6px;
  border-radius: 4px;
  align-self: flex-start;
  margin-top: 5px;
  text-transform: uppercase;
  color: #fff;
}
.status-confirmed { background: #2ecc71; }
.status-canceled { background: #e74c3c; }
.status-waiting { background: #f39c12; }
.status-shipping { background: #f1c40f; }
.status-completed { background: #16a085; }
.status-unreaching { background: #9b59b6; }
</style>
