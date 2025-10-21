<template>
    <div class="order-actions">
        <button class="btn-action print" @click="printPage">🖨️ Imprimer</button>
        <button class="btn-action pdf" @click="downloadPDF">📄 Télécharger PDF</button>
    </div>
    <div class="order-page">
        <div class="order-header">
        <h1 class="order-title">🛒 Commande #{{ data?.id }}</h1>
        
        </div>

    <!-- Infos client -->
        <section class="order-section">
        <h2 class="section-title">Client</h2>
        <div class="order-info">
            <p><strong>Nom:</strong> {{ data?.name }}</p>
            <p><strong>Téléphone:</strong> {{ data?.phone }}</p>
            <p><strong>Adresse:</strong> {{ data?.sZone }}, {{ data?.mZone }}, {{ data?.deliveryZone }}</p>
            <p><strong>Pays:</strong> {{ data?.country }}</p>
            <p><strong>IP:</strong> {{ data?.ip }}</p>
        </div>
        </section>

        <!-- Livraison -->
        <section class="order-section">
        <h2 class="section-title">Livraison</h2>
        <div class="order-info">
            <p><strong>Méthode:</strong> {{ data?.method }}</p>
            <p><strong>Zone:</strong> {{ data?.deliveryZone }}</p>
            <p><strong>Frais livraison:</strong> {{ data?.deliveryValue }} DZD</p>
            <p><strong>Statut:</strong> 
            <span :class="['status', data?.status]">{{ data?.status }}</span>
            </p>
        </div>
        </section>

        <!-- Produits -->
        <section class="order-section">
        <h2 class="section-title">Produits</h2>
        <div 
            v-for="(item, index) in data?.items" 
            :key="index" 
            class="order-item"
        >
            <img :src="item.image" alt="" class="item-image" />
            <div class="item-details">
            <h3 class="item-name">{{ item.productName }}</h3>
            <p><strong>Prix:</strong> {{ item.price }} DZD</p>
            <p><strong>Quantité:</strong> {{ item.qty }}</p>
            <p><strong>Réf:</strong> {{ item.ref || '-' }}</p>
            <div v-if="item.items?.length" class="sub-items">
                <h4>Détails</h4>
                <ul>
                <li v-for="(sub, idx) in item.items" :key="idx">
                    {{ sub.size }} {{ sub.color_name || sub.color }} × {{ sub.qty }}
                    — <span>{{ sub.total }} DZD</span>
                </li>
                </ul>
            </div>
            </div>
        </div>
        </section>

        <!-- Résumé -->
        <section class="order-section summary">
        <h2 class="section-title">Résumé</h2>
        <p><strong>Total articles:</strong> {{ data?.totalQty }}</p>
        <p><strong>Total commande:</strong> {{ data?.total }} DZD</p>
        <p><strong>Remise:</strong> {{ data?.discount || '—' }}</p>
        <p><strong>Date:</strong> {{ data?.create }}</p>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue"
import { useRoute } from "vue-router"
import { useOrder } from "../../composables/useOrder.js"

const { $html2pdf } = useNuxtApp()

const route = useRoute()
const orderId = ref(null)

const { data, getOrder } = useOrder()

onMounted(() => {
  orderId.value = route.params.id
  getOrder(orderId.value)
})

watch(
  () => route.params.id,
  (newId) => {
    console.log('orderId.value: ', newId)
    orderId.value = newId
    getOrder(orderId.value)
  }
)


const printPage = () => {
  window.print()
}

const downloadPDF = () => {
  const element = document.querySelector(".order-page")
  if (!element) return

  const opt = {
    margin: 0.5,
    filename: `commande-${orderId.value}.pdf`,
    image: { type: "jpeg", quality: 0.98 },
    html2canvas: { scale: 2 },
    jsPDF: { unit: "in", format: "a4", orientation: "portrait" }
  }

  $html2pdf()
    .set(opt)
    .from(element)
    .save()
}
</script>


<style scoped>
.order-page {
  font-family: var(--font-primary);
  padding: var(--space-lg);
  border-radius: var(--border-radius-md);
  box-shadow: var(--shadow-md);
  color: var(--color-darkow);
  max-width: 900px;
  margin: auto;
}

.dark .order-page {
  color: var(--color-whitly);
}

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: var(--space-sm);
  margin-bottom: var(--space-lg);
}

.order-title {
  font-size: 1.6rem;
  font-weight: bold;
}

.order-actions {
    width: 100%;
    height: 100px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--space-sm);
}

.btn-action {
  padding: 8px 14px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.3s ease;
}

.btn-action.print {
  background: var(--color-yelly20);
  color: var(--color-yelly);
}
.btn-action.pdf {
  background: var(--color-green-20);
  color: var(--color-greny);
}

.btn-action:hover {
  opacity: 0.85;
  transform: translateY(-1px);
}

.order-section {
  margin-bottom: var(--space-lg);
  padding: var(--space-md);
  border-radius: 10px;
  background: var(--color-whitly);
}

.dark .order-section {
  background: var(--color-darkow);
}

.section-title {
  font-size: 1.2rem;
  font-weight: 700;
  margin-bottom: var(--space-sm);
  border-left: 4px solid var(--color-primary);
  padding-left: var(--space-sm);
}

.order-info p {
  margin: var(--space-xs) 0;
}

.order-item {
  display: flex;
  align-items: flex-start;
  gap: var(--space-md);
  padding: var(--space-sm);
  border-bottom: 1px solid var(--color-gorry20);
  flex-wrap: wrap;
}

.item-image {
  width: 80px;
  height: 80px;
  object-fit: cover;
  border-radius: 8px;
  background: var(--color-whiby);
}

.item-details {
  align-items: flex-start;
  min-width: 200px;
}

.item-name {
  font-size: 1rem;
  font-weight: bold;
}

.sub-items {
  margin-top: var(--space-sm);
  font-size: 0.9rem;
  color: var(--color-gorry);
}

.status {
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
}

.status.waiting {
  background: var(--color-yelly20);
  color: var(--color-yelly);
}
.status.confirmed {
  background: var(--color-green-20);
  color: var(--color-greny);
}
.status.cancelled {
  background: var(--color-rady20);
  color: var(--color-rady);
}

.summary {
  background: var(--color-whizy);
  font-weight: bold;
}

.dark .summary {
  background: var(--color-darkiw);
}

/* Responsive */
@media (max-width: 768px) {
  .order-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .order-item {
    flex-direction: column;
    align-items: flex-start;
  }
  .item-image {
    width: 100%;
    height: auto;
    max-height: 200px;
  }
}
</style>
