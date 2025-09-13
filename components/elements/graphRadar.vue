<template>
  <div class="cards-container">
    <div
      class="card"
    >
      <!-- Label -->
      <p class="card-label">{{ datasets.datasets.label }}</p>
      <!-- Chart -->
      <div class="chart-container">
        <Radar
          :data="{
            labels: datasets.labels,
            datasets: datasets.datasets
            }"
            :options="radarOptions"
        />
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Chart as ChartJS, Title, Tooltip, Legend, RadialLinearScale, PointElement, LineElement, Filler } from 'chart.js'
import { Radar } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, RadialLinearScale, PointElement, LineElement, Filler)

const props = defineProps({
  data: { type: [Object, Array], default: () => ({ data: { transactions: [] } }) }
})

const datasets = ref({
  labels: [],
  datasets: []
})

// 🎨 options style "stats joueur"
const radarOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    r: {
      angleLines: {
        color: '#aaa' // lignes radiales discrètes
      },
      grid: {
        color: '#aaa' // quadrillage léger
      },
      pointLabels: {
        color: '#ff5800', // labels des axes
        font: {
          size: 10,
          weight: 'bold'
        }
      },
      ticks: {
        backdropColor: 'transparent',
        color: '#aaa'
      }
    }
  },
  plugins: {
    legend: {
      labels: {
        color: '#aaa',
        font: { size: 14 }
      }
    }
  }
}

// 🔄 Mise à jour des données
watch(
  () => props.data,
  () => {
    const txs = props.data?.data?.data || (Array.isArray(props.data) ? props.data : [])

    if (txs[0]) {
      const newData = txs[0].datasets.map(ds => ({
        ...ds,
        total: ds.data.reduce((a, b) => a + b, 0)
      }))

      datasets.value = { labels: txs[0].labels, datasets: newData }
      console.log('datasets.value: ', datasets.value)
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
  gap: 1rem;
  margin: 20px;
}

.card {
  position: relative;
  background: var(--color-whitly);
  border-radius: 20px;
  padding: 1rem;
  width: 90%;
  height: 400px;
  backdrop-filter: blur(10px);
  box-shadow: 0 2px 4px rgba(0,0,0,0.3);

}
.dark .card {
    background: var(--color-darkly);

}


.card-label {
  text-align: center;
  font-weight: bold;
  margin-bottom: 0.5rem;
  font-size: 1.2rem;
}

.chart-container {
  position: relative;
  width: 100%;
  height: 300px;
}

.card-time {
  margin-top: 0.5rem;
  text-align: center;
  font-size: 0.9rem;
  color: #aaa;
}

.card-total {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 1.4rem;
  font-weight: bold;
  color: #ffcc00;
  text-shadow: 0 0 10px rgba(255, 204, 0, 0.7);
}
</style>
