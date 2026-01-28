<template>
  <div class="product-storage">
    <div class="header-section">
      <h2 class="title">Gestion du Stock</h2>
      <div class="actions">
        <gBtn :svg="icons.refresh" text="Rafraîchir" color="var(--color-greeny)" @click="refreshAll" />
        <gBtn :svg="icons.print" text="Imprimer QR" color="var(--color-greeny)" @click="printAll" />
      </div>
    </div>
    

    <!-- Variant Grid Section -->
    <div class="stock-grid-container">
      <div class="grid-header">
        <div class="col-name">Variante / Modèle</div>
        <div class="col-stock">En Stock</div>
        <div class="col-input">Quantité</div>
        <div class="col-action">Actions</div>
      </div>

      <div v-for="(item, index) in variantRows" :key="index" class="grid-row">
        <div class="col-name">
            <div class="variant-info">
                <img :src="item.img" class="variant-img" onerror="this.style.display='none'"/>
                <span>{{ item.name }}</span>
            </div>
        </div>
        <div class="col-stock">
            <span class="stock-badge" :class="{ 'low-stock': item.qty < 5, 'out-stock': item.qty == 0 }">
                {{ item.qty }}
            </span>
        </div>
        <div class="col-input">
            <InputText
                type="number"
                v-model="inputMap[item.uniqueKey]"
                placeHolder="Qté"
                class="qty-input"
            />
        </div>
        <div class="col-action actions-group">
            <gBtn
                :svg="icons.plus"
                color="var(--color-blumy)"
                @click="addStock(item)"
                class="action-btn"
                :disabled="loading"
            />
            <gBtn
                :svg="icons.x"
                color="var(--color-rady)"
                @click="withdrawStock(item)"
                class="action-btn"
                :disabled="loading"
            />
        </div>
      </div>

      <div v-if="variantRows.length === 0" class="empty-state">
        Aucune variante ou modèle disponible pour ce produit.
      </div>
    </div>

    <!-- Existing Codes List -->
    <div class="codes-section">
      <h3>Codes Générés ({{ stockList.length }})</h3>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>QR</th>
              <th>Code Unique</th>
              <th>Variante</th>
              <th>Statut</th>
              <th>Ref Commande</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="stock in stockList" :key="stock.id">
              <td>
                <canvas :ref="(el) => setCanvasRef(el, stock.unique_code)" width="50" height="50"></canvas>
              </td>
              <td class="code-text">{{ stock.unique_code }}</td>
              <td>{{ getVariantName(stock) }}</td>
              <td>
                 <span class="status-badge" :class="stock.status">{{ stock.status }}</span>
              </td>
              <td>{{ stock.order_ref || '-' }}</td>
              <td>
                 <gBtn
                    v-if="stock.status === 'available'"
                    :svg="icons.x"
                    color="var(--color-rady)"
                    @click="deleteSingleItem(stock.id)"
                    style="width: 32px; height: 32px; padding: 0;"
                 />
              </td>
            </tr>
             <tr v-if="stockList.length === 0">
              <td colspan="6" style="text-align: center; padding: 20px; color: #888;">Aucun code généré.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Print Area (Teleported to body to ensure clean printing) -->
    <Teleport to="body">
      <div id="print-area" class="print-only">
        <div v-for="stock in stockList" :key="'print-'+stock.id" class="print-item">
          <div class="print-qr">
             <img :src="stock.qrDataUrl" v-if="stock.qrDataUrl" />
          </div>
          <div class="print-info">
            <div class="print-code">{{ stock.unique_code }}</div>
            <div class="print-variant">{{ getVariantName(stock) }}</div>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, reactive } from 'vue';
import QRCode from 'qrcode';
import gBtn from './bloc/gBtn.vue';
import InputText from './bloc/inputText.vue';
import icons from '~/public/icons.json';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['refresh']);

const stockList = ref([]);
const loading = ref(false);
const inputMap = reactive({}); // Stores input qty for each variant key

// Helper to create a unique key for the input map
const getUniqueKey = (modelId, detailId) => `${modelId}-${detailId || 'null'}`;

// Compute flattened list of variants/models
const variantRows = computed(() => {
    const list = [];
    if (!props.modelValue.models) return list;

    props.modelValue.models.forEach(m => {
        if (m.details && m.details.length > 0) {
            m.details.forEach(d => {
                list.push({
                    uniqueKey: getUniqueKey(m.id, d.id),
                    modelId: m.id,
                    detailId: d.id,
                    name: `${m.name || m.ref || 'Model'} - ${d.colorName || d.color} ${d.size || ''}`,
                    qty: d.quantity || 0, // Ensure 'quantity' field is correct from backend
                    img: d.image || m.imageUrls || props.modelValue.image
                });
            });
        } else {
             list.push({
                uniqueKey: getUniqueKey(m.id, null),
                modelId: m.id,
                detailId: null,
                name: m.name || m.ref || 'Model',
                qty: m.quantity || 0,
                img: m.imageUrls || props.modelValue.image
            });
        }
    });
    return list;
});

// Canvas Refs for QR
const setCanvasRef = (el, code) => {
  if (el) {
    QRCode.toCanvas(el, code, { width: 64, margin: 1 }, (error) => {
      if (error) console.error(error);
    });
  }
};

// API Actions
const fetchStock = async () => {
  if (!props.modelValue.id || props.modelValue.id === -1) return;

  loading.value = true;
  try {
    const res = await fetch(`https://management.hoggari.com/backend/api.php?action=getStock&product_id=${props.modelValue.id}`);
    const data = await res.json();
    if (data.success) {
      // Generate Data URLs for print
       const processed = await Promise.all(data.data.map(async (item) => {
        try {
          item.qrDataUrl = await QRCode.toDataURL(item.unique_code, { width: 100, margin: 1 });
        } catch (e) {
          item.qrDataUrl = '';
        }
        return item;
      }));
      stockList.value = processed;
    }
  } catch (e) {
    console.error('Error fetching stock:', e);
  } finally {
    loading.value = false;
  }
};

const addStock = async (item) => {
    const qty = parseInt(inputMap[item.uniqueKey]);
    if (!qty || qty <= 0) return alert('Veuillez entrer une quantité valide.');

    loading.value = true;
    try {
        const payload = {
            product_id: props.modelValue.id,
            model_id: item.modelId,
            detail_id: item.detailId,
            qty: qty
        };

        const res = await fetch('https://management.hoggari.com/backend/api.php?action=postStock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        if (result.success) {
            inputMap[item.uniqueKey] = ''; // Reset input
            await refreshAll();
        } else {
            alert(result.message || 'Erreur lors de l\'ajout.');
        }

    } catch (e) {
        console.error(e);
        alert('Erreur réseau.');
    } finally {
        loading.value = false;
    }
};

const withdrawStock = async (item) => {
    const qty = parseInt(inputMap[item.uniqueKey]);
    if (!qty || qty <= 0) return alert('Veuillez entrer une quantité valide.');

    // Local check for availability
    const availableItems = stockList.value.filter(s =>
        s.model_id == item.modelId &&
        s.detail_id == item.detailId &&
        s.status === 'available'
    );

    if (availableItems.length < qty) {
        return alert(`Pas assez de stock disponible (Codes générés: ${availableItems.length}). Impossible de retirer ${qty}.`);
    }

    if (!confirm(`Confirmez-vous le retrait de ${qty} unité(s) pour ${item.name} ? Cela supprimera les codes correspondants.`)) return;

    loading.value = true;
    try {
        const idsToDelete = availableItems.slice(0, qty).map(s => s.id);

        const res = await fetch('https://management.hoggari.com/backend/api.php?action=deleteStock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: idsToDelete })
        });

        const result = await res.json();
        if (result.success) {
            inputMap[item.uniqueKey] = '';
            await refreshAll();
        } else {
            alert(result.message || 'Erreur lors du retrait.');
        }

    } catch (e) {
        console.error(e);
        alert('Erreur réseau.');
    } finally {
        loading.value = false;
    }
};

const deleteSingleItem = async (id) => {
    if (!confirm('Supprimer ce code spécifique ?')) return;

    try {
        const res = await fetch('https://management.hoggari.com/backend/api.php?action=deleteStock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        if (await res.json().then(r => r.success)) {
            await refreshAll();
        }
    } catch (e) { console.error(e); }
};

const refreshAll = async () => {
    await fetchStock();
    emit('refresh'); // Tell parent to refresh productData
};

const getVariantName = (stockItem) => {
    const m = props.modelValue.models?.find(x => x.id == stockItem.model_id);
    if (!m) return `Model ${stockItem.model_id}`;

    let name = m.name || m.ref || 'Model';
    if (stockItem.detail_id && m.details) {
        const d = m.details.find(x => x.id == stockItem.detail_id);
        if (d) name += ` - ${d.colorName || d.color} ${d.size || ''}`;
    }
    return name;
};

const printAll = () => {
    window.print();
};

onMounted(() => {
    if (props.modelValue.id > 0) fetchStock();
});

watch(() => props.modelValue.id, (v) => {
    if (v > 0) fetchStock();
});

</script>

<style>
/* Global print styles to hide everything except the print-area */
@media print {
    body > *:not(#print-area) {
        display: none !important;
    }
    body {
        background-color: white;
        margin: 0;
        padding: 0;
    }
}
</style>

<style scoped>
.product-storage {
    padding: 20px;
    background-color: var(--color-whitly);
    border-radius: 12px;
    margin-top: 100px;
}
.dark .product-storage {
    background-color: var(--color-darkly);
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.title {
    font-size: 1.25rem;
    font-weight: bold;
    width: 100%;
}

.actions {
    display: flex !important;
    flex-wrap: wrap;
    gap: 10px;
}

/* Grid Styles */
.stock-grid-container {
    border: 1px solid var(--color-zioly2);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
}

.grid-header {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    background-color: var(--color-zioly1);
    padding: 12px 16px;
    font-weight: 600;
    font-size: 14px;
    color: var(--color-text-muted);
}
.dark .grid-header {
    background-color: var(--color-zioly2);
}

.grid-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr;
    padding: 12px 16px;
    align-items: center;
    border-bottom: 1px solid var(--color-zioly2);
    transition: background-color 0.2s;
}

.grid-row:last-child {
    border-bottom: none;
}

.grid-row:hover {
    background-color: var(--color-whizy);
}
.dark .grid-row:hover {
    background-color: var(--color-darkow);
}

.variant-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.variant-img {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    object-fit: cover;
    background-color: #eee;
}

.stock-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background-color: var(--color-whiby);
    font-weight: 700;
    min-width: 30px;
    text-align: center;
}

.stock-badge.low-stock {
    color: #eab308;
    background-color: #fef9c3;
}

.stock-badge.out-stock {
    color: #ef4444;
    background-color: #fee2e2;
}

.qty-input {
    max-width: 100px;
}

.actions-group {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
}

/* List Styles */
.codes-section h3 {
    margin-bottom: 15px;
    font-size: 1.1rem;
    font-weight: 600;
}

.table-container {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--color-zioly2);
    font-size: 14px;
}

th {
    background-color: var(--color-zioly1);
    font-weight: 600;
}
.dark th {
    background-color: var(--color-zioly2);
}

.code-text {
    font-family: monospace;
    font-weight: 700;
    color: var(--color-primary);
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 700;
}

.status-badge.available { background: #d1fae5; color: #065f46; }
.status-badge.sold { background: #dbeafe; color: #1e40af; }
.status-badge.returned { background: #fee2e2; color: #991b1b; }
.status-badge.removed { background: #f3f4f6; color: #374151; }

/* Responsive */
@media (max-width: 768px) {
    .grid-header { display: none; }
    .grid-row {
        grid-template-columns: 1fr;
        gap: 10px;
        padding: 16px;
    }
    .col-name, .col-stock, .col-input, .col-action {
        width: 100%;
        justify-content: space-between;
        display: flex;
        align-items: center;
    }
    .col-input { max-width: 100%; }
    .qty-input { width: 100% !important; max-width: 100%; }
}

/* Print */
.print-only { display: none; }

@media print {
    #print-area {
        display: flex !important;
        flex-wrap: wrap;
        gap: 20px;
        width: 100%;
        padding: 20px;
    }
    .print-item {
        width: 150px; border: 1px solid #ccc; padding: 10px;
        display: flex; 
        flex-direction: column; 
        align-items: center;
        page-break-inside: avoid;
    }
    .print-qr img { width: 100px; height: 100px; }
    .print-code { font-family: monospace; font-size: 10px; margin-top: 5px; text-align: center; }
    .print-variant { font-size: 10px; text-align: center; }
}
</style>
