<template>
  <div class="cards-container">
    <div
      v-for="(ds, i) in datasets.datasets"
      :key="i"
      class="card"
    >
      <!-- Label -->
      <p class="card-label">{{ ds.label }}</p>

      <!-- Chart -->
      <div v-if="ds.total > 0" class="chart-container">
        <Doughnut
          :data="{
            labels: datasets.labels,
            datasets: [ds]
          }"
        />
      </div>

      <!-- Temps -->
      <p class="card-time">{{ ds.time }}</p>

      <!-- Total flottant -->
      <div class="card-total">{{ ds.total }}</div>
    </div>
  </div>
</template>



<script setup>

import { ref } from 'vue'
import { Chart as ChartJS, Title, Tooltip, Legend, CategoryScale, LinearScale, LineElement, PointElement, ArcElement } from 'chart.js'
import { Doughnut } from 'vue-chartjs'
//import VueDatePicker from "@vuepic/vue-datepicker"
//import "@vuepic/vue-datepicker/dist/main.css"
ChartJS.register(Title, Tooltip, Legend, CategoryScale, LinearScale, LineElement, PointElement, ArcElement)

const props = defineProps({
  data: { type: [Object, Array], default: () => ({ data: { transactions: [] } }) }
})


const datasets = ref({
    // These labels appear in the legend and in the tooltips when hovering different arcs
    labels: [

    ],
    datasets: [{
        data: [],
        backgroundColor: []
    }]

    
})

/*function getISOWeek(date) {
  // retourne { year, week } (ISO week)
  const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()))
  const dayNum = d.getUTCDay() || 7
  d.setUTCDate(d.getUTCDate() + 4 - dayNum)
  const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
  const weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7)
  return { year: d.getUTCFullYear(), week: weekNo }
}*/



watch(
  () => props.data,
  () => {
    const txs = props.data?.data?.data || (Array.isArray(props.data) ? props.data : [])


    var newData = []
    // debug lisible (optionnel) : console.log(JSON.stringify(labelsLength2))
    if(txs[0]) {

        for(let i in txs[0].datasets) {
            newData.push(txs[0].datasets[i])
        }

        datasets.value = {labels: txs[0].labels, datasets: newData}

    }
    

  },
  { immediate: true, deep: true }
)







</script>



<style scoped>
.cards-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 2rem;
  margin-top: 20px;
}

/* Style glassmorphism */
.card {
  position: relative;
  width: 280px;
  height: 350px;
  padding: 20px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.25);
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35);
}

/* Gradient lumineux en fond */
.card::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 20px;
  background: linear-gradient(
    135deg,
    var(--color-greny, #4ade80) 0%,
    var(--color-tioly, #60a5fa) 50%,
    var(--color-rangy, #facc15) 100%
  );
  opacity: 0.25;
  filter: blur(25px);
  z-index: 0;
}

/* Texte */
.card-label {
  position: relative;
  z-index: 1;
  font-size: 1.1rem;
  font-weight: 600;
  color: #222;
  margin-bottom: 10px;
}

.chart-container {
  position: relative;
  z-index: 1;
  height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-time {
  position: relative;
  z-index: 1;
  margin-top: 10px;
  font-size: 0.9rem;
  text-align: center;
  color: #333;
}

.card-total {
  position: absolute;
  top: 62%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 2rem;
  font-weight: 800;
  color: #111;
  text-shadow: 0 0 10px rgb(255, 255, 255);
  pointer-events: none;
  z-index: 2;
}

/* 🌙 Mode dark */
.dark .card {
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 8px 25px rgb(0, 0, 0);
}

.dark .card::before {
  opacity: 0.15;
}

.dark .card-label {
  color: #f0f0f0;
}

.dark .card-time {
  color: #aaa;
}

.dark .card-total {
  color: #fff;
  text-shadow: 0 0 10px rgba(0, 0, 0, 0.8);
}
</style>
