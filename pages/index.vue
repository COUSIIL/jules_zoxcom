<template>
  <LoaderBlack v-if="!isMounted" width="80px"/>
  <div style="display: flex; flex-direction: column; align-items: center;">

    <div>
      <Donut :data="fullData"/>
    </div>

  
    <div v-if="isMounted" class="orders-dashboard">
      <div class="order-box">
        <h3>{{ t('all orders') }}</h3>
        <p>{{ t('today') }}: <span>{{ ordersByDay[currentDay]?.length || 0 }}</span></p>
        <p>{{ t('this week') }}: <span>{{ ordersByWeek[currentWeek]?.length || 0 }}</span></p>
        <p>{{ t('this month') }}: <span>{{ ordersByMonth[currentMonth]?.length || 0 }}</span></p>
      </div>

      <div class="order-box confirmed">
        <h3>{{ t('confirmed') }}</h3>
        <p>{{ t('today') }}: <span>{{ confirmByDay[currentDay]?.length || 0 }}</span></p>
        <p>{{ t('this week') }}: <span>{{ confirmByWeek[currentWeek]?.length || 0 }}</span></p>
        <p>{{ t('this month') }}: <span>{{ confirmByMonth[currentMonth]?.length || 0 }}</span></p>
      </div>

      <div class="order-box canceled">
        <h3>{{ t('canceled') }}</h3>
        <p>{{ t('today') }}: <span>{{ cancelByDay[currentDay]?.length || 0 }}</span></p>
        <p>{{ t('this week') }}: <span>{{ cancelByWeek[currentWeek]?.length || 0 }}</span></p>
        <p>{{ t('this month') }}: <span>{{ cancelByMonth[currentMonth]?.length || 0 }}</span></p>
      </div>

      <div class="order-box unreachable">
        <h3>{{ t('unreachable') }}</h3>
        <p>{{ t('today') }}: <span>{{ unreachableByDay[currentDay]?.length || 0 }}</span></p>
        <p>{{ t('this week') }}: <span>{{ unreachableByWeek[currentWeek]?.length || 0 }}</span></p>
        <p>{{ t('this month') }}: <span>{{ unreachableByMonth[currentMonth]?.length || 0 }}</span></p>
      </div>

      <div class="order-box awaiting">
        <h3>{{ t('awaiting') }}</h3>
        <p>{{ t('today') }}: <span>{{ awaitByDay[currentDay]?.length || 0 }}</span></p>
        <p>{{ t('this week') }}: <span>{{ awaitByWeek[currentWeek]?.length || 0 }}</span></p>
        <p>{{ t('this month') }}: <span>{{ awaitByMonth[currentMonth]?.length || 0 }}</span></p>
      </div>

      <div class="order-box delivered">
        <h3>{{ t('delivered') }}</h3>
        <p>{{ t('today') }}: <span>{{ deliverByDay[currentDay]?.length || 0 }}</span></p>
        <p>{{ t('this week') }}: <span>{{ deliverByWeek[currentWeek]?.length || 0 }}</span></p>
        <p>{{ t('this month') }}: <span>{{ deliverByMonth[currentMonth]?.length || 0 }}</span></p>
      </div>

      <div class="order-box completed">
        <h3>{{ t('completed') }}</h3>
        <p>{{ t('today') }}: <span>{{ completedByDay[currentDay]?.length || 0 }}</span></p>
        <p>{{ t('this week') }}: <span>{{ completedByWeek[currentWeek]?.length || 0 }}</span></p>
        <p>{{ t('this month') }}: <span>{{ completedByMonth[currentMonth]?.length || 0 }}</span></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useLang } from '~/composables/useLang'
import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
import Donut from '../components/elements/graphDonut.vue';

const { t } = useLang()
const isMounted = ref(false)
const log = ref('')

// --- regroupements
const ordersByDay = ref({})
const confirmByDay = ref({})
const cancelByDay = ref({})
const deliverByDay = ref({})
const unreachableByDay = ref({})
const awaitByDay = ref({})
const completedByDay = ref({})

const ordersByWeek = ref({})
const confirmByWeek = ref({})
const cancelByWeek = ref({})
const deliverByWeek = ref({})
const unreachableByWeek = ref({})
const awaitByWeek = ref({})
const completedByWeek = ref({})

const ordersByMonth = ref({})
const confirmByMonth = ref({})
const cancelByMonth = ref({})
const deliverByMonth = ref({})
const unreachableByMonth = ref({})
const awaitByMonth = ref({})
const completedByMonth = ref({})

const fullData = ref({})

const perDay = ref({})
const perWeek = ref({})
const perMonth = ref({})

// --- clés actuelles
const now = new Date()
const currentDay = now.toISOString().split("T")[0]
const currentWeek = `${now.getFullYear()}-W${getWeekNumber(now)}`
const currentMonth = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`

onMounted(() => {
  getOrder()
})

async function getOrder() {
  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=getOrders')
    if (!response.ok) {
      log.value = '❌ error in getting response category'
      return
    }

    const result = await response.json()
    if (!result.success) {
      log.value = result.message
      return
    }

    const orders = result.data.map(order => ({
      ...order,
      date: new Date(order.create)
    }))

    

    orders.forEach(order => {
      const d = order.date

      // --- clé jour
      const dayKey = d.toISOString().split("T")[0]
      if (!ordersByDay.value[dayKey]) ordersByDay.value[dayKey] = []
      ordersByDay.value[dayKey].push(order)

      if (order.status === 'completed') {
        if (!completedByDay.value[dayKey]) completedByDay.value[dayKey] = []
        completedByDay.value[dayKey].push(order)
      } else if (order.status === 'await' || order.status === 'pending') {
        if (!awaitByDay.value[dayKey]) awaitByDay.value[dayKey] = []
        awaitByDay.value[dayKey].push(order)
      } else if (order.status === 'canceled') {
        if (!cancelByDay.value[dayKey]) cancelByDay.value[dayKey] = []
        cancelByDay.value[dayKey].push(order)
      } else if (order.status === 'shipping') {
        if (!deliverByDay.value[dayKey]) deliverByDay.value[dayKey] = []
        deliverByDay.value[dayKey].push(order)
      } else if (order.status === 'unreaching') {
        if (!unreachableByDay.value[dayKey]) unreachableByDay.value[dayKey] = []
        unreachableByDay.value[dayKey].push(order)
      } else if (order.status === 'confirmed') {
        if (!confirmByDay.value[dayKey]) confirmByDay.value[dayKey] = []
        confirmByDay.value[dayKey].push(order)
      }

      // --- clé semaine
      const weekKey = `${d.getFullYear()}-W${getWeekNumber(d)}`
      if (!ordersByWeek.value[weekKey]) ordersByWeek.value[weekKey] = []
      ordersByWeek.value[weekKey].push(order)

      if (order.status === 'completed') {
        if (!completedByWeek.value[weekKey]) completedByWeek.value[weekKey] = []
        completedByWeek.value[weekKey].push(order)
      } else if (order.status === 'await' || order.status === 'pending') {
        if (!awaitByWeek.value[weekKey]) awaitByWeek.value[weekKey] = []
        awaitByWeek.value[weekKey].push(order)
      } else if (order.status === 'canceled') {
        if (!cancelByWeek.value[weekKey]) cancelByWeek.value[weekKey] = []
        cancelByWeek.value[weekKey].push(order)
      } else if (order.status === 'shipping') {
        if (!deliverByWeek.value[weekKey]) deliverByWeek.value[weekKey] = []
        deliverByWeek.value[weekKey].push(order)
      } else if (order.status === 'unreaching') {
        if (!unreachableByWeek.value[weekKey]) unreachableByWeek.value[weekKey] = []
        unreachableByWeek.value[weekKey].push(order)
      } else if (order.status === 'confirmed') {
        if (!confirmByWeek.value[weekKey]) confirmByWeek.value[weekKey] = []
        confirmByWeek.value[weekKey].push(order)
      }

      // --- clé mois
      const monthKey = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`
      if (!ordersByMonth.value[monthKey]) ordersByMonth.value[monthKey] = []
      ordersByMonth.value[monthKey].push(order)

      if (order.status === 'completed') {
        if (!completedByMonth.value[monthKey]) completedByMonth.value[monthKey] = []
        completedByMonth.value[monthKey].push(order)
      } else if (order.status === 'await' || order.status === 'pending') {
        if (!awaitByMonth.value[monthKey]) awaitByMonth.value[monthKey] = []
        awaitByMonth.value[monthKey].push(order)
      } else if (order.status === 'canceled') {
        if (!cancelByMonth.value[monthKey]) cancelByMonth.value[monthKey] = []
        cancelByMonth.value[monthKey].push(order)
      } else if (order.status === 'shipping') {
        if (!deliverByMonth.value[monthKey]) deliverByMonth.value[monthKey] = []
        deliverByMonth.value[monthKey].push(order)
      } else if (order.status === 'unreaching') {
        if (!unreachableByMonth.value[monthKey]) unreachableByMonth.value[monthKey] = []
        unreachableByMonth.value[monthKey].push(order)
      } else if (order.status === 'confirmed') {
        if (!confirmByMonth.value[monthKey]) confirmByMonth.value[monthKey] = []
        confirmByMonth.value[monthKey].push(order)
      }

      
      const total1 = getTotal([
        completedByDay.value[dayKey],
        awaitByDay.value[dayKey],
        cancelByDay.value[dayKey],
        deliverByDay.value[dayKey],
        unreachableByDay.value[dayKey],
        confirmByDay.value[dayKey]
      ])

      const total2 = getTotal([
        completedByWeek.value[weekKey],
        awaitByWeek.value[weekKey],
        cancelByWeek.value[weekKey],
        deliverByWeek.value[weekKey],
        unreachableByWeek.value[weekKey],
        confirmByWeek.value[weekKey]
      ])

      const total3 = getTotal([
        completedByMonth.value[monthKey],
        awaitByMonth.value[monthKey],
        cancelByMonth.value[monthKey],
        deliverByMonth.value[monthKey],
        unreachableByMonth.value[monthKey],
        confirmByMonth.value[monthKey]
      ])

    perDay.value = resolveCssVars(
      {time: dayKey,
        label: 'perDay',
        total: total1,
        data: [completedByDay.value[dayKey]?.length || 0, awaitByDay.value[dayKey]?.length || 0, cancelByDay.value[dayKey]?.length || 0, deliverByDay.value[dayKey]?.length || 0, unreachableByDay.value[dayKey]?.length || 0, confirmByDay.value[dayKey]?.length || 0, 0],
      backgroundColor: ["var(--color-greny)", "var(--color-rangy)", "var(--color-rady)", "var(--color-tioly)", "var(--color-garry)", "var(--color-zioly3)"]
      },

    )

    perWeek.value = resolveCssVars(
      {time: weekKey,
        label: 'perWeek',
        total: total2,
        data: [completedByWeek.value[weekKey]?.length || 0, awaitByWeek.value[weekKey]?.length || 0, cancelByWeek.value[weekKey]?.length || 0, deliverByWeek.value[weekKey]?.length || 0, unreachableByWeek.value[weekKey]?.length || 0, confirmByWeek.value[weekKey]?.length || 0, 0],
        backgroundColor: ["var(--color-greny)", "var(--color-rangy)", "var(--color-rady)", "var(--color-tioly)", "var(--color-garry)", "var(--color-zioly3)"]
      },

    )

    perMonth.value = resolveCssVars(
      {time: monthKey,
        label: 'perMonth',
        total: total3,
        data: [completedByMonth.value[monthKey]?.length || 0, awaitByMonth.value[monthKey]?.length || 0, cancelByMonth.value[monthKey]?.length || 0, deliverByMonth.value[monthKey]?.length || 0, unreachableByMonth.value[monthKey]?.length || 0, confirmByMonth.value[monthKey]?.length || 0, 0],
        backgroundColor: ["var(--color-greny)", "var(--color-rangy)", "var(--color-rady)", "var(--color-tioly)", "var(--color-garry)", "var(--color-zioly3)"]
      },

    )

    })

    const listStatus = ['completed', 'await', 'cancel', 'deliver', 'unreachable', 'confirm', 'return']
    const listDataSet = [perDay.value, perWeek.value, perMonth.value]
    console.log('listDataSet: ', listDataSet)

    

    fullData.value = [{labels: listStatus, datasets: listDataSet}]
    

    log.value = "✅ Orders classified successfully"
  } catch (err) {
    console.error("❌ Erreur fetch:", err)
    log.value = "Erreur lors du chargement des commandes"
  }

  isMounted.value = true

}

function getTotal(values) {
  return values.reduce((sum, v) => sum + (v?.length || 0), 0)
}

function resolveCssVars(item) {
  if (item.backgroundColor) {
    item.backgroundColor = item.backgroundColor.map(color => {
      if (color.startsWith("var(")) {
        const varName = color.match(/var\((--[^)]+)\)/)?.[1]
        if (varName) {
          return getComputedStyle(document.documentElement)
            .getPropertyValue(varName)
            .trim()
        }
      }
      return color
    })
  }
  return item
}




function getWeekNumber(d) {
  const start = new Date(d.getFullYear(), 0, 1) // 1er janvier
  const diff = Math.floor((d - start) / 86400000) // nb de jours depuis début année
  return Math.floor(diff / 7) + 1 // semaine = jours écoulés / 7
}




/*async function sendEmail() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=sendEmailOrder', {
      method: 'GET',
    });
    if (!response.ok) {
      log.value = 'error in getting response category'
    }
    const result = await response.json();
    if (result.success) {
      log.value = result.message
    } else {
      log.value = result.message
    }
}



async function chatWithDeepSeek() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=chatDeepSeek', {
      method: 'GET',
    });
    if (!response.ok) {
      console.error('error in getting response category');
    }
    const result = await response.json();
    console.log('result: ', result);
}

async function chatGpt() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=chatGPT', {
      method: 'GET',
    });
    if (!response.ok) {
      console.error('error in getting response category');
    }
    const result = await response.json();
    console.log('result: ', result);
}

async function chatMistral() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=chatMistral', {
      method: 'POST',
      body: JSON.stringify({ message: "vous êtes qui ?" })
    });
    if (!response.ok) {
      console.error('error in getting response category');
    }
    const result = await response.json();
}

async function chatGemini() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=chatGemini', {
      method: 'POST',
      body: JSON.stringify({ message: "vous êtes qui ?" })
    });
    if (!response.ok) {
      console.error('error in getting response category');
    }
    const result = await response.json();
    console.log('result: ', result)
}*/
</script>

<style scoped>
.orders-dashboard {
  width: 90%;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
  padding: 20px;
}

.order-box {
  background: var(--color-whitly);
  border-radius: 10px;
  padding: 15px 20px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  transition: transform .2s;
}
.dark .order-box {
  background: var(--color-darkow);
}
.order-box:hover {
  transform: translateY(-3px);
}

.order-box h3 {
  font-size: 1.1rem;
  margin-bottom: 10px;
  font-weight: 600;
}

.order-box p {
  font-size: 0.9rem;
  margin: 6px 0;
  display: flex;
  justify-content: space-between;
}

.order-box span {
  font-weight: bold;
}

/* Couleurs par type */
.confirmed { border-left: 5px solid #2ecc71; }
.canceled { border-left: 5px solid #e74c3c; }
.unreachable { border-left: 5px solid #9b59b6; }
.awaiting { border-left: 5px solid #f39c12; }
.delivered { border-left: 5px solid #3498db; }
.completed { border-left: 5px solid #16a085; }
</style>