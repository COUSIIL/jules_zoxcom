<template>
  <div class="containerOrder">
    <div class="header-actions">
        <RectBtn text="Retour" svg="back" @click:ok="goBack" :isSimple="true" />
        <div class="right-actions">
             <RectBtn text="Imprimer" svg="print" @click:ok="() => generateBordereau('print')" :isSimple="true" />
             <RectBtn text="PDF" svg="pdf" @click:ok="() => generateBordereau('save')" :isSimple="true" />
        </div>
    </div>

    <div v-if="localLoading" class="loading-container">
        <Loader width="80px" />
    </div>

    <div v-else-if="data" class="order-content" id="printable-order">
      <!-- Header Section -->
      <div class="order-banner status-banner" :class="`status-${data.status?.toLowerCase() || 'pending'}`">
        <h1>Commande #{{ data.id }}</h1>
        <span class="status-badge">{{ data.status }}</span>
      </div>

      <div class="grid-layout">
        <!-- Client Info -->
        <div class="card client-card">
          <div class="card-header">
            <h3>👤 Client</h3>
          </div>
          <div class="card-body">
            <p><strong>Nom:</strong> {{ data.name }}</p>
            <p><strong>Téléphone:</strong> <a :href="`tel:${data.phone}`">{{ data.phone }}</a></p>
            <p><strong>Adresse:</strong> {{ data.mZone }}</p>
            <p><strong>Commune:</strong> {{ data.sZone }}</p>
            <p><strong>Wilaya:</strong> {{ data.deliveryZone }}</p>
          </div>
        </div>

        <!-- Delivery Info -->
        <div class="card delivery-card">
          <div class="card-header">
            <h3>🚚 Livraison</h3>
          </div>
          <div class="card-body">
            <p><strong>Méthode:</strong> {{ data.method }}</p>
            <p><strong>Type:</strong> {{ data.type == '0' ? 'À Domicile' : 'Stop Desk' }}</p>
            <p><strong>Frais:</strong> {{ data.deliveryValue }} DZD</p>
            <p><strong>Tracking:</strong> {{ data.tracking || '—' }}</p>
            <p><strong>Date:</strong> {{ data.create }}</p>
          </div>
        </div>
      </div>

      <!-- Products -->
      <div class="card products-card">
        <div class="card-header">
          <h3>📦 Produits</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Produit</th>
                            <th>Détails</th>
                            <th>Qté</th>
                            <th>Prix</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in data.items" :key="index">
                            <td>
                                <img :src="item.image" class="product-thumb" alt="Product" />
                            </td>
                            <td class="product-name">{{ item.productName }}</td>
                            <td>
                                <ul v-if="item.items && item.items.length" class="variations-list">
                                    <li v-for="(sub, sIdx) in item.items" :key="sIdx">
                                        {{ sub.color_name || sub.color }} - {{ sub.size }} (x{{ sub.qty }})
                                    </li>
                                </ul>
                                <span v-else>—</span>
                            </td>
                            <td>{{ item.qty }}</td>
                            <td>{{ item.price }} DZD</td>
                            <td>{{ item.items && item.items.length ? item.items.reduce((acc, curr) => acc + parseFloat(curr.total), 0) : (item.price * item.qty) }} DZD</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="card notes-card" v-if="notesText">
        <div class="card-header">
            <h3>📝 Notes</h3>
        </div>
        <div class="card-body note-content">
            {{ notesText }}
        </div>
      </div>

      <!-- Summary -->
      <div class="summary-section">
        <div class="summary-box">
            <div class="summary-row">
                <span>Total Produits:</span>
                <span>{{ data.totalQty }}</span>
            </div>
            <div class="summary-row">
                <span>Livraison:</span>
                <span>{{ data.deliveryValue }} DZD</span>
            </div>
            <div class="summary-row total-row">
                <span>Total à Payer:</span>
                <span>{{ data.total }} DZD</span>
            </div>
        </div>
      </div>

    </div>

    <div v-else class="error-container">
        <p>Commande introuvable ou erreur de chargement.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useOrder } from "../../composables/useOrder.js"
import RectBtn from '../../components/elements/newBloc/rectBtn.vue'
import Loader from '../../components/elements/animations/loaderBlack.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import './main.css'

const route = useRoute()
const router = useRouter()
const orderId = ref(null)

const { data, getOrder } = useOrder()

const localLoading = ref(true)

const goBack = () => {
  router.back()
}

onMounted(async () => {
  orderId.value = route.params.id
  localLoading.value = true
  await getOrder(orderId.value)
  localLoading.value = false
})

const notesText = computed(() => {
    if (!data.value?.note) return '';
    try {
        let notes = data.value.note;
        if (typeof notes === 'string') {
            // Check if it's JSON
            if (notes.startsWith('[') || notes.startsWith('{')) {
                notes = JSON.parse(notes);
            } else {
                return notes;
            }
        }
        if (Array.isArray(notes)) {
            return notes.map(n => n.text).join('\n');
        } else if (typeof notes === 'object') {
            return notes.text || '';
        }
        return '';
    } catch (e) {
        return data.value.note || '';
    }
});


const generateBordereau = (action = 'save') => {
  if (!data.value) return;

  const doc = new jsPDF();
  const order = data.value;

  // -- Fonts --
  doc.setFont("helvetica", "bold");
  doc.setFontSize(22);
  doc.text("BORDEREAU DE LIVRAISON", 105, 20, { align: "center" });

  doc.setFontSize(10);
  doc.setFont("helvetica", "normal");
  doc.text("Généré le: " + new Date().toLocaleDateString('fr-FR'), 105, 26, { align: "center" });

  // -- Header Box (Expediteur / Destinataire) --

  // Destinataire (Right side usually for window envelopes, or Left. Let's put standard format)
  // Box for Recipient
  doc.setDrawColor(0);
  doc.setFillColor(245, 245, 245);
  doc.rect(110, 35, 90, 45, "F");

  doc.setFont("helvetica", "bold");
  doc.setFontSize(12);
  doc.text("DESTINATAIRE:", 115, 42);

  doc.setFont("helvetica", "normal");
  doc.setFontSize(11);
  doc.text(`${order.name}`, 115, 50);
  doc.text(`${order.phone}`, 115, 56);

  const addressLines = doc.splitTextToSize(`${order.mZone}, ${order.sZone}`, 80);
  doc.text(addressLines, 115, 62);
  doc.setFont("helvetica", "bold");
  doc.text(`${order.deliveryZone}`, 115, 62 + (addressLines.length * 5));

  // Order Info (Left side)
  doc.setFontSize(11);
  doc.text(`Commande N°: ${order.id}`, 14, 42);
  doc.text(`Date: ${order.create}`, 14, 48);
  doc.text(`Type: ${order.type == '0' ? 'À Domicile' : 'Stop Desk'}`, 14, 54);
  if(order.tracking) doc.text(`Tracking: ${order.tracking}`, 14, 60);

  // -- IMPORTANT: Amount to Collect --
  doc.setDrawColor(0);
  doc.setLineWidth(0.5);
  doc.rect(14, 70, 80, 15);
  doc.setFontSize(14);
  doc.setFont("helvetica", "bold");
  doc.text(`MONTANT À PAYER:`, 16, 80);
  doc.text(`${order.total} DZD`, 92, 80, { align: "right" });


  // -- Items Table --
  const tableColumn = ["Produit", "Variante", "Qté", "Total (DZD)"];
  const tableRows = [];

  order.items.forEach(item => {
    let variantText = "-";
    if (item.items && item.items.length > 0) {
        variantText = item.items.map(sub => `${sub.color_name || sub.color || ''} ${sub.size || ''}`).join(', ');
    }

    const itemTotal = item.items && item.items.length
        ? item.items.reduce((acc, curr) => acc + parseFloat(curr.total), 0)
        : (parseFloat(item.price) * parseInt(item.qty));

    const productData = [
      item.productName,
      variantText,
      item.qty,
      itemTotal.toFixed(2)
    ];
    tableRows.push(productData);
  });

  autoTable(doc, {
    head: [tableColumn],
    body: tableRows,
    startY: 95,
    theme: 'grid',
    headStyles: { fillColor: [40, 40, 40] },
    styles: { fontSize: 10 },
  });

  let finalY = doc.lastAutoTable.finalY || 95;

  // -- Notes --
  if (notesText.value) {
      finalY += 10;
      doc.setFont("helvetica", "bold");
      doc.setFontSize(11);
      doc.text("Instructions / Notes:", 14, finalY);
      doc.setFont("helvetica", "normal");
      doc.setFontSize(10);
      const noteLines = doc.splitTextToSize(notesText.value, 180);
      doc.text(noteLines, 14, finalY + 6);
  }

  // Footer
  const pageCount = doc.internal.getNumberOfPages();
  for(let i = 1; i <= pageCount; i++) {
    doc.setPage(i);
    doc.setFontSize(8);
    doc.text('Merci de votre confiance.', 105, 290, { align: 'center' });
  }

  if (action === 'print') {
      doc.autoPrint();
      window.open(doc.output('bloburl'), '_blank');
  } else {
      doc.save(`Bordereau_Commande_${order.id}.pdf`);
  }
}
</script>

<style scoped>
.containerOrder {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
}

.order-content {
    width: 100%;

}


.header-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.right-actions {
    display: flex;
    gap: 10px;
}

.order-banner {
    padding: 20px;
    border-radius: 12px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.status-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
    text-transform: uppercase;
}

.status-confirmed { background-color: var(--color-blumy); }
.status-shipping { background-color: var(--color-yelly); }
.status-waiting { background-color: var(--color-rangy); }
.status-completed { background-color: var(--color-greeny); }
.status-canceled { background-color: var(--color-rady); }
.status-pending { background-color: var(--color-gorry); }

.grid-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .grid-layout {
        grid-template-columns: 1fr 1fr;
    }
}

.card {
    background: var(--color-whitly);
    border-radius: 14px;
    box-shadow: 2px 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
    margin-bottom: 20px;
}
.dark .card {
    background: var(--color-darkly);
    box-shadow: 2px 2px 10px rgba(0,0,0,0.3);
}

.card-header {
    padding: 15px 20px;
    border-bottom: 1px solid var(--color-zioly4);
    font-weight: bold;
    font-size: 1.1em;
}

.card-body {
    padding: 20px;
}

.card-body p {
    margin-bottom: 8px;
    line-height: 1.5;
}

.products-table {
    width: 100%;
    min-width: 700px; /* ajuste selon le nombre de colonnes */
    border-collapse: collapse;
}

.products-table th, .products-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--color-zioly4);
    white-space: nowrap;
}


.product-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
}

.summary-section {
    display: flex;
    justify-content: flex-end;
}

.summary-box {
    width: 100%;
    max-width: 400px;
    background: var(--color-whity);
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.dark .summary-box {
    background: var(--color-darkly);
}

.summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 1.1em;
}

.total-row {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 2px solid var(--color-zioly4);
    font-weight: 800;
    font-size: 1.4em;
    color: var(--color-greeny);
}

.loading-container, .error-container {
    display: flex;
    justify-content: center;
    padding: 50px;
}

.variations-list {
    list-style: none;
    padding: 0;
    margin: 0;
    font-size: 0.85em;
    color: var(--color-zioly2);
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}



@media print {
    .header-actions { display: none; }
    .containerOrder { width: 100%; max-width: none; padding: 0; }
    .card { box-shadow: none; border: 1px solid #ddd; }
}
</style>
