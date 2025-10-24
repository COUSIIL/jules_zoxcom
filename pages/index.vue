<template>
  <LoaderBlack v-if="!isMounted" width="80px"/>
  
  <div style="display: flex; flex-direction: column; align-items: center;">

    <div>
      <Donut :data="fullData"/>
    </div>

    <div style="width: 100%;">
      <Radar :data="fullData2"/>
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
import { ref, onMounted, nextTick } from 'vue'
import { useLang } from '~/composables/useLang'
import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
import Donut from '../components/elements/graphDonut.vue';
import Radar from '../components/elements/graphRadar.vue';

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
const returnByDay = ref({})
const confirmWilayaByDay = ref([])
const cancelWilayaByDay = ref({})
const deliverWilayaByDay = ref({})
const unreachableWilayaByDay = ref({})
const awaitWilayaByDay = ref({})
const completedWilayaByDay = ref({})
const returnWilayaByDay = ref({})

const ordersByWeek = ref({})
const confirmByWeek = ref({})
const cancelByWeek = ref({})
const deliverByWeek = ref({})
const unreachableByWeek = ref({})
const awaitByWeek = ref({})
const completedByWeek = ref({})
const returnByWeek = ref({})
const confirmWilayaByWeek = ref({})
const cancelWilayaByWeek = ref({})
const deliverWilayaByWeek = ref({})
const unreachableWilayaByWeek = ref({})
const awaitWilayaByWeek = ref({})
const completedWilayaByWeek = ref({})
const returnWilayaByWeek = ref({})


const ordersByMonth = ref({})
const confirmByMonth = ref({})
const cancelByMonth = ref({})
const deliverByMonth = ref({})
const unreachableByMonth = ref({})
const awaitByMonth = ref({})
const completedByMonth = ref({})
const returnByMonth= ref({})
const confirmWilayaByMonth = ref({})
const cancelWilayaByMonth = ref({})
const deliverWilayaByMonth = ref({})
const unreachableWilayaByMonth = ref({})
const awaitWilayaByMonth = ref({})
const completedWilayaByMonth = ref({})
const returnWilayaByMonth= ref({})


const fullData = ref({})
const fullData2 = ref({})

const perDay = ref({})
const perWeek = ref({})
const perMonth = ref({})

const dayKey = ref()
const weekKey = ref()
const monthKey = ref()

const total1 = ref()
const total2 = ref()
const total3 = ref()

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

      const localDate = toAlgerDate(d) 

      // --- clé jour
      dayKey.value = localDate.toLocaleDateString("fr-CA")

      if (!ordersByDay.value[dayKey.value]) ordersByDay.value[dayKey.value] = []
      ordersByDay.value[dayKey.value].push(order)

      if (order.status === 'completed') {
        if (!completedByDay.value[dayKey.value]) completedByDay.value[dayKey.value] = []
        completedByDay.value[dayKey.value].push(order)

      } else if (order.status === 'waiting' || order.status === 'pending') {
        if (!awaitByDay.value[dayKey.value]) awaitByDay.value[dayKey.value] = []
        awaitByDay.value[dayKey.value].push(order)
      } else if (order.status === 'canceled') {
        if (!cancelByDay.value[dayKey.value]) cancelByDay.value[dayKey.value] = []
        cancelByDay.value[dayKey.value].push(order)
      } else if (order.status === 'shipping') {
        if (!deliverByDay.value[dayKey.value]) deliverByDay.value[dayKey.value] = []
        deliverByDay.value[dayKey.value].push(order)
      } else if (order.status === 'unreaching') {
        if (!unreachableByDay.value[dayKey.value]) unreachableByDay.value[dayKey.value] = []
        unreachableByDay.value[dayKey.value].push(order)
      } else if (order.status === 'confirmed') {
        if (!confirmByDay.value[dayKey.value]) confirmByDay.value[dayKey.value] = []
        confirmByDay.value[dayKey.value].push(order)
      }

      // --- clé semaine
      

      weekKey.value = `${localDate.getFullYear()}-W${getWeekNumber(localDate)}`
      if (!ordersByWeek.value[weekKey.value]) ordersByWeek.value[weekKey.value] = []
      ordersByWeek.value[weekKey.value].push(order)

      if (order.status === 'completed') {
        if (!completedByWeek.value[weekKey.value]) completedByWeek.value[weekKey.value] = []
        completedByWeek.value[weekKey.value].push(order)
      } else if (order.status === 'waiting' || order.status === 'pending') {
        if (!awaitByWeek.value[weekKey.value]) awaitByWeek.value[weekKey.value] = []
        awaitByWeek.value[weekKey.value].push(order)
      } else if (order.status === 'canceled') {
        if (!cancelByWeek.value[weekKey.value]) cancelByWeek.value[weekKey.value] = []
        cancelByWeek.value[weekKey.value].push(order)
      } else if (order.status === 'shipping') {
        if (!deliverByWeek.value[weekKey.value]) deliverByWeek.value[weekKey.value] = []
        deliverByWeek.value[weekKey.value].push(order)
      } else if (order.status === 'unreaching') {
        if (!unreachableByWeek.value[weekKey.value]) unreachableByWeek.value[weekKey.value] = []
        unreachableByWeek.value[weekKey.value].push(order)
      } else if (order.status === 'confirmed') {
        if (!confirmByWeek.value[weekKey.value]) confirmByWeek.value[weekKey.value] = []
        confirmByWeek.value[weekKey.value].push(order)
      }

      // --- clé mois
      monthKey.value = `${localDate.getFullYear()}-${String(localDate.getMonth() + 1).padStart(2, '0')}`
      if (!ordersByMonth.value[monthKey.value]) ordersByMonth.value[monthKey.value] = []
      ordersByMonth.value[monthKey.value].push(order)

      if (order.status === 'completed') {
        if (!completedByMonth.value[monthKey.value]) completedByMonth.value[monthKey.value] = []
        completedByMonth.value[monthKey.value].push(order)
      } else if (order.status === 'waiting' || order.status === 'pending') {
        if (!awaitByMonth.value[monthKey.value]) awaitByMonth.value[monthKey.value] = []
        awaitByMonth.value[monthKey.value].push(order)
      } else if (order.status === 'canceled') {
        if (!cancelByMonth.value[monthKey.value]) cancelByMonth.value[monthKey.value] = []
        cancelByMonth.value[monthKey.value].push(order)
      } else if (order.status === 'shipping') {
        if (!deliverByMonth.value[monthKey.value]) deliverByMonth.value[monthKey.value] = []
        deliverByMonth.value[monthKey.value].push(order)
      } else if (order.status === 'unreaching') {
        if (!unreachableByMonth.value[monthKey.value]) unreachableByMonth.value[monthKey.value] = []
        unreachableByMonth.value[monthKey.value].push(order)
      } else if (order.status === 'confirmed') {
        if (!confirmByMonth.value[monthKey.value]) confirmByMonth.value[monthKey.value] = []
        confirmByMonth.value[monthKey.value].push(order)
      }

      
      total1.value = getTotal([
        completedByDay.value[dayKey.value],
        awaitByDay.value[dayKey.value],
        cancelByDay.value[dayKey.value],
        deliverByDay.value[dayKey.value],
        unreachableByDay.value[dayKey.value],
        confirmByDay.value[dayKey.value]
      ])

      total2.value = getTotal([
        completedByWeek.value[weekKey.value],
        awaitByWeek.value[weekKey.value],
        cancelByWeek.value[weekKey.value],
        deliverByWeek.value[weekKey.value],
        unreachableByWeek.value[weekKey.value],
        confirmByWeek.value[weekKey.value]
      ])

      total3.value = getTotal([
        completedByMonth.value[monthKey.value],
        awaitByMonth.value[monthKey.value],
        cancelByMonth.value[monthKey.value],
        deliverByMonth.value[monthKey.value],
        unreachableByMonth.value[monthKey.value],
        confirmByMonth.value[monthKey.value]
      ])
      
    perDay.value = resolveCssVars(
      {time: dayKey.value,
        label: 'perDay',
        total: total1.value,
        data: [completedByDay.value[dayKey.value]?.length || 0, awaitByDay.value[dayKey.value]?.length || 0, cancelByDay.value[dayKey.value]?.length || 0, deliverByDay.value[dayKey.value]?.length || 0, unreachableByDay.value[dayKey.value]?.length || 0, confirmByDay.value[dayKey.value]?.length || 0, 0],
      backgroundColor: ["var(--color-greny)", "var(--color-rangy)", "var(--color-rady)", "var(--color-tioly)", "var(--color-garry)", "var(--color-zioly3)"]
      },

    )

    perWeek.value = resolveCssVars(
      {time: weekKey.value,
        label: 'perWeek',
        total: total2.value,
        data: [completedByWeek.value[weekKey.value]?.length || 0, awaitByWeek.value[weekKey.value]?.length || 0, cancelByWeek.value[weekKey.value]?.length || 0, deliverByWeek.value[weekKey.value]?.length || 0, unreachableByWeek.value[weekKey.value]?.length || 0, confirmByWeek.value[weekKey.value]?.length || 0, 0],
        backgroundColor: ["var(--color-greny)", "var(--color-rangy)", "var(--color-rady)", "var(--color-tioly)", "var(--color-garry)", "var(--color-zioly3)"]
      },

    )

    perMonth.value = resolveCssVars(
      {time: monthKey.value,
        label: 'perMonth',
        total: total3.value,
        data: [completedByMonth.value[monthKey.value]?.length || 0, awaitByMonth.value[monthKey.value]?.length || 0, cancelByMonth.value[monthKey.value]?.length || 0, deliverByMonth.value[monthKey.value]?.length || 0, unreachableByMonth.value[monthKey.value]?.length || 0, confirmByMonth.value[monthKey.value]?.length || 0, 0],
        backgroundColor: ["var(--color-greny)", "var(--color-rangy)", "var(--color-rady)", "var(--color-tioly)", "var(--color-garry)", "var(--color-zioly3)"]
      },

    )

    })

    

    const listStatus = ['completed', 'await', 'cancel', 'deliver', 'unreachable', 'confirm', 'return']
    const listDataSet = [perDay.value, perWeek.value, perMonth.value]

    collectAllWilayas()

    await nextTick()
    var wilayaNameCompleted = []
    var wilayaValueCompleted = []

    var wilayaNameAwait = []
    var wilayaValueAwait = []

    var wilayaNameCancel = []
    var wilayaValueCancel = []

    var wilayaNameDeliver = []
    var wilayaValueDeliver = []

    var wilayaNameUnreachable = []
    var wilayaValueUnreachable = []

    var wilayaNameConfirm = []
    var wilayaValueConfirm = []

    var wilayaNameReturn = []
    var wilayaValueReturn = []

    
    
    for(let ind in completedWilayaByMonth.value) {
      
      const zone = completedWilayaByMonth.value[ind].labels;
      if (!zone) continue;

      const found = wilayaNameCompleted.find(el => el.labels === zone);
      if (!found) {
        wilayaNameCompleted.push(zone);
      }
    }
    
    for(let ind in awaitWilayaByMonth.value) {
      
      const zone = awaitWilayaByMonth.value[ind].labels;
      if (!zone) continue;

      const found = wilayaNameCompleted.find(el => el.labels === zone);
      if (!found) {
        wilayaNameCompleted.push(zone);
      }
    }

    for(let ind in cancelWilayaByMonth.value) {
      
      const zone = cancelWilayaByMonth.value[ind].labels;
      if (!zone) continue;

      const found = wilayaNameCompleted.find(el => el.labels === zone);
      if (!found) {
        wilayaNameCompleted.push(zone);
      }
    }

    for(let ind in deliverWilayaByMonth.value) {
      
      const zone = deliverWilayaByMonth.value[ind].labels;
      if (!zone) continue;

      const found = wilayaNameCompleted.find(el => el.labels === zone);
      if (!found) {
        wilayaNameCompleted.push(zone);
      }
    }
    
    for(let ind in unreachableWilayaByMonth.value) {
      
      const zone = unreachableWilayaByMonth.value[ind].labels;
      if (!zone) continue;

      const found = wilayaNameCompleted.find(el => el.labels === zone);
      if (!found) {
        wilayaNameCompleted.push(zone);
      }
    }

    for(let ind in confirmWilayaByMonth.value) {
      
      const zone = confirmWilayaByMonth.value[ind].labels;
      if (!zone) continue;

      const found = wilayaNameCompleted.find(el => el.labels === zone);
      if (!found) {
        wilayaNameCompleted.push(zone);
      }
    }

    for(let ind in returnWilayaByMonth.value) {
      
      const zone = returnWilayaByMonth.value[ind].labels;
      if (!zone) continue;

      const found = wilayaNameCompleted.find(el => el.labels === zone);
      if (!found) {
        wilayaNameCompleted.push(zone);
      }
    }

    for(var listed in wilayaNameCompleted) {
      if(completedWilayaByMonth.value[listed]) {
        wilayaValueCompleted.push(completedWilayaByMonth.value[listed].data)
      } else {
        wilayaValueCompleted.push(0)
      }
      if(awaitWilayaByMonth.value[listed]) {
        wilayaValueAwait.push(awaitWilayaByMonth.value[listed].data)
      } else {
        wilayaValueAwait.push(0)
      }
      if(deliverWilayaByMonth.value[listed]) {
        wilayaValueDeliver.push(deliverWilayaByMonth.value[listed].data)
      } else {
        wilayaValueDeliver.push(0)
      }
      if(cancelWilayaByMonth.value[listed]) {
        wilayaValueCancel.push(cancelWilayaByMonth.value[listed].data)
      } else {
        wilayaValueCancel.push(0)
      }
      if(unreachableWilayaByMonth.value[listed]) {
        wilayaValueUnreachable.push(unreachableWilayaByMonth.value[listed].data)
      } else {
        wilayaValueUnreachable.push(0)
      }
      if(confirmWilayaByMonth.value[listed]) {
        wilayaValueConfirm.push(confirmWilayaByMonth.value[listed].data)
      } else {
        wilayaValueConfirm.push(0)
      }
      if(returnWilayaByMonth.value[listed]) {
        wilayaValueReturn.push(returnWilayaByMonth.value[listed].data)
      } else {
        wilayaValueReturn.push(0)
      }
    }

    //wilayaNameCompleted.push(completedWilayaByMonth.value[ind].labels)
    //wilayaValueCompleted.push(completedWilayaByMonth.value[ind].data)


    fullData2.value = [
      {labels: wilayaNameCompleted, 
        datasets: [
          {
            label: 'completed', data: wilayaValueCompleted, backgroundColor: colorTransformer('var(--color-greny20)'), borderColor: colorTransformer('var(--color-greny)'), fill: true,
          },
          {
            label: 'await', data: wilayaValueAwait, backgroundColor: colorTransformer('var(--color-rangy20)'), borderColor: colorTransformer('var(--color-rangy)'),fill: true,
          },
          {
            label: 'cancel', data: wilayaValueCancel, backgroundColor: colorTransformer('var(--color-rady20)'), borderColor: colorTransformer('var(--color-rady)'), fill: true,
          },
          {
            label: 'deliver', data: wilayaValueDeliver, backgroundColor: colorTransformer('var(--color-zioly220)'), borderColor: colorTransformer('var(--color-zioly2)'),fill: true,
          },
          {
            label: 'unreachable', data: wilayaValueUnreachable, backgroundColor: colorTransformer('var(--color-garry20)'), borderColor: colorTransformer('var(--color-garry)'),fill: true,
          },
          {
            label: 'confirm', data: wilayaValueConfirm, backgroundColor: colorTransformer('var(--color-blue-20)'), borderColor: colorTransformer('var(--color-blue)'), fill: true,
          },
          {
            label: 'return', data: wilayaValueReturn, backgroundColor: colorTransformer('var(--color-zioly420)'), borderColor: colorTransformer('var(--color-zioly4)'),fill: true,
          },
        ]
      }
    ]

    fullData.value = [{labels: listStatus, datasets: listDataSet}]
    

    log.value = "✅ Orders classified successfully"
  } catch (err) {
    console.error("❌ Erreur fetch:", err)
    log.value = "Erreur lors du chargement des commandes"
  }

  isMounted.value = true

}

function toAlgerDate(date) {
  // Convertir en string locale (dans le fuseau Alger) puis recréer un Date
  return new Date(date.toLocaleString("en-US", { timeZone: "Africa/Algiers" }))
}


function getTotal(values) {
  return values.reduce((sum, v) => sum + (v?.length || 0), 0)
}

function collectAllWilayas() {
  const sources = [
    { from: completedByDay.value[dayKey.value],   to: completedWilayaByDay },
    { from: confirmByDay.value[dayKey.value],     to: confirmWilayaByDay },
    { from: cancelByDay.value[dayKey.value],      to: cancelWilayaByDay },
    { from: deliverByDay.value[dayKey.value],     to: deliverWilayaByDay },
    { from: unreachableByDay.value[dayKey.value], to: unreachableWilayaByDay },
    { from: awaitByDay.value[dayKey.value],       to: awaitWilayaByDay },
      // ---- SEMAINE ----
    { from: completedByWeek.value[weekKey.value],   to: completedWilayaByWeek },
    { from: confirmByWeek.value[weekKey.value],     to: confirmWilayaByWeek },
    { from: cancelByWeek.value[weekKey.value],      to: cancelWilayaByWeek },
    { from: deliverByWeek.value[weekKey.value],     to: deliverWilayaByWeek },
    { from: unreachableByWeek.value[weekKey.value], to: unreachableWilayaByWeek },
    { from: awaitByWeek.value[weekKey.value],       to: awaitWilayaByWeek },

    // ---- MOIS ----
    { from: completedByMonth.value[monthKey.value],   to: completedWilayaByMonth },
    { from: confirmByMonth.value[monthKey.value],     to: confirmWilayaByMonth },
    { from: cancelByMonth.value[monthKey.value],      to: cancelWilayaByMonth },
    { from: deliverByMonth.value[monthKey.value],     to: deliverWilayaByMonth },
    { from: unreachableByMonth.value[monthKey.value], to: unreachableWilayaByMonth },
    { from: awaitByMonth.value[monthKey.value],       to: awaitWilayaByMonth },
  ];

  for (const { from, to } of sources) {
    // reset pour éviter doublons
    to.value = [];

    // si aucune donnée ce jour → passer
    if (!Array.isArray(from)) continue;

    for (const item of from) {
      const zone = item?.deliveryZone;
      if (!zone) continue;

      const found = to.value.find(el => el.labels === zone);
      if (!found) {
        to.value.push({ labels: zone, data: 1 });
      } else {
        found.data++;
      }
    }

  }
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

function colorTransformer(item) {
  if (typeof item === 'string') {
    return resolveColorToHex(item);
  }

  // objet avec backgroundColor (string ou array)
  if (item && item.backgroundColor) {
    if (Array.isArray(item.backgroundColor)) {
      item.backgroundColor = item.backgroundColor.map(c => resolveColorToHex(c));
    } else if (typeof item.backgroundColor === 'string') {
      item.backgroundColor = resolveColorToHex(item.backgroundColor);
    }
  }
  return item;
}

/* ---------- helpers ---------- */

function resolveColorToHex(color) {
  if (!color) return color;
  color = color.trim();

  // var(--name)
  if (color.startsWith('var(')) {
    const varName = color.match(/var\((--[^)]+)\)/)?.[1];
    if (varName) {
      const cssVal = getComputedStyle(document.documentElement).getPropertyValue(varName).trim();
      if (cssVal) color = cssVal;
      else return color; // variable non définie -> renvoyer l'original
    }
  }

  // Normaliser via un élément DOM pour gérer noms, hsl, hex, etc.
  let computed = null;
  try {
    const el = document.createElement('div');
    el.style.cssText = 'position:absolute;left:-9999px;visibility:hidden;';
    el.style.color = color;
    document.body.appendChild(el);
    computed = getComputedStyle(el).color; // normalement "rgb(...)" ou "rgba(...)"
    document.body.removeChild(el);
  } catch (e) {
    computed = color;
  }

  // parser rgb(a)
  const rgba = parseRgbString(computed);
  if (rgba) {
    const rgbComposited = compositeOverWhite(rgba);
    return rgbToHexString(rgbComposited);
  }

  // si c'est un hex (shorthand ou avec alpha)
  const hexMatch = color.match(/^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i);
  if (hexMatch) {
    let hex = hexMatch[1];
    if (hex.length === 3) {
      hex = hex.split('').map(ch => ch + ch).join('');
      return '#' + hex.toLowerCase();
    }
    if (hex.length === 4) { // rgba shorthand -> composite alpha
      const r = parseInt(hex[0] + hex[0], 16);
      const g = parseInt(hex[1] + hex[1], 16);
      const b = parseInt(hex[2] + hex[2], 16);
      const a = parseInt(hex[3] + hex[3], 16) / 255;
      return rgbToHexString(compositeOverWhite({ r, g, b, a }));
    }
    if (hex.length === 6) return '#' + hex.toLowerCase();
    if (hex.length === 8) { // rgba hex -> composite alpha
      const r = parseInt(hex.slice(0, 2), 16);
      const g = parseInt(hex.slice(2, 4), 16);
      const b = parseInt(hex.slice(4, 6), 16);
      const a = parseInt(hex.slice(6, 8), 16) / 255;
      return rgbToHexString(compositeOverWhite({ r, g, b, a }));
    }
  }

  // fallback : on renvoie la valeur d'origine si tout échoue
  return color;
}

function parseRgbString(rgbStr) {
  if (!rgbStr) return null;
  const m = rgbStr.match(/rgba?\(([^)]+)\)/i);
  if (!m) return null;
  const parts = m[1].split(',').map(p => p.trim());
  const r = Number(parts[0]);
  const g = Number(parts[1]);
  const b = Number(parts[2]);
  const a = parts[3] === undefined ? 1 : parseFloat(parts[3]);
  if ([r, g, b].some(v => isNaN(v))) return null;
  return { r, g, b, a };
}

// composite alpha sur fond blanc (si alpha < 1)
function compositeOverWhite({ r, g, b, a = 1 }) {
  if (a >= 1) return { r: Math.round(r), g: Math.round(g), b: Math.round(b) };
  const rr = Math.round((1 - a) * 255 + a * r);
  const gg = Math.round((1 - a) * 255 + a * g);
  const bb = Math.round((1 - a) * 255 + a * b);
  return { r: rr, g: gg, b: bb };
}

function rgbToHexString({ r, g, b }) {
  return (
    '#' +
    [r, g, b]
      .map(n => {
        const v = Math.max(0, Math.min(255, Math.round(n)));
        return v.toString(16).padStart(2, '0');
      })
      .join('')
      .toLowerCase()
  );
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