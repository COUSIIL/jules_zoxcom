<template>
  <LoaderBlack v-if="!isMounted" width="80px"/>
  <div class="flex flex-col items-center">
    <div v-if="isMounted" class="grid w-11/12 grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4 p-5">
      <div class="p-4 transition-transform duration-200 transform rounded-lg shadow-md bg-whitly dark:bg-darkow hover:-translate-y-1">
        <h3 class="mb-2.5 text-lg font-semibold">{{ t('all orders') }}</h3>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('today') }}:</span> <strong>{{ ordersByDay[currentDay]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this week') }}:</span> <strong>{{ ordersByWeek[currentWeek]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this month') }}:</span> <strong>{{ ordersByMonth[currentMonth]?.length || 0 }}</strong></p>
      </div>

      <div class="p-4 transition-transform duration-200 transform border-l-4 border-green-500 rounded-lg shadow-md bg-whitly dark:bg-darkow hover:-translate-y-1">
        <h3 class="mb-2.5 text-lg font-semibold">{{ t('confirmed') }}</h3>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('today') }}:</span> <strong>{{ confirmByDay[currentDay]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this week') }}:</span> <strong>{{ confirmByWeek[currentWeek]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this month') }}:</span> <strong>{{ confirmByMonth[currentMonth]?.length || 0 }}</strong></p>
      </div>

      <div class="p-4 transition-transform duration-200 transform border-l-4 border-red-500 rounded-lg shadow-md bg-whitly dark:bg-darkow hover:-translate-y-1">
        <h3 class="mb-2.5 text-lg font-semibold">{{ t('canceled') }}</h3>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('today') }}:</span> <strong>{{ cancelByDay[currentDay]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this week') }}:</span> <strong>{{ cancelByWeek[currentWeek]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this month') }}:</span> <strong>{{ cancelByMonth[currentMonth]?.length || 0 }}</strong></p>
      </div>

      <div class="p-4 transition-transform duration-200 transform border-l-4 border-purple-500 rounded-lg shadow-md bg-whitly dark:bg-darkow hover:-translate-y-1">
        <h3 class="mb-2.5 text-lg font-semibold">{{ t('unreachable') }}</h3>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('today') }}:</span> <strong>{{ unreachableByDay[currentDay]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this week') }}:</span> <strong>{{ unreachableByWeek[currentWeek]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this month') }}:</span> <strong>{{ unreachableByMonth[currentMonth]?.length || 0 }}</strong></p>
      </div>

      <div class="p-4 transition-transform duration-200 transform border-l-4 border-yellow-500 rounded-lg shadow-md bg-whitly dark:bg-darkow hover:-translate-y-1">
        <h3 class="mb-2.5 text-lg font-semibold">{{ t('awaiting') }}</h3>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('today') }}:</span> <strong>{{ awaitByDay[currentDay]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this week') }}:</span> <strong>{{ awaitByWeek[currentWeek]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this month') }}:</span> <strong>{{ awaitByMonth[currentMonth]?.length || 0 }}</strong></p>
      </div>

      <div class="p-4 transition-transform duration-200 transform border-l-4 border-blue-500 rounded-lg shadow-md bg-whitly dark:bg-darkow hover:-translate-y-1">
        <h3 class="mb-2.5 text-lg font-semibold">{{ t('delivered') }}</h3>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('today') }}:</span> <strong>{{ deliverByDay[currentDay]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this week') }}:</span> <strong>{{ deliverByWeek[currentWeek]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this month') }}:</span> <strong>{{ deliverByMonth[currentMonth]?.length || 0 }}</strong></p>
      </div>

      <div class="p-4 transition-transform duration-200 transform border-l-4 border-teal-500 rounded-lg shadow-md bg-whitly dark:bg-darkow hover:-translate-y-1">
        <h3 class="mb-2.5 text-lg font-semibold">{{ t('completed') }}</h3>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('today') }}:</span> <strong>{{ completedByDay[currentDay]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this week') }}:</span> <strong>{{ completedByWeek[currentWeek]?.length || 0 }}</strong></p>
        <p class="flex justify-between my-1.5 text-sm"><span>{{ t('this month') }}:</span> <strong>{{ completedByMonth[currentMonth]?.length || 0 }}</strong></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useLang } from '~/composables/useLang'
import LoaderBlack from '../components/elements/animations/loaderBlack.vue';

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
    })

    log.value = "✅ Orders classified successfully"
  } catch (err) {
    console.error("❌ Erreur fetch:", err)
    log.value = "Erreur lors du chargement des commandes"
  }

  isMounted.value = true

}

function getWeekNumber(d) {
  const date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()))
  const dayNum = date.getUTCDay() || 7
  date.setUTCDate(date.getUTCDate() + 4 - dayNum)
  const yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1))
  return Math.ceil((((date - yearStart) / 86400000) + 1) / 7)
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