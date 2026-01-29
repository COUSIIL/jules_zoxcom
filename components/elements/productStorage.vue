<template>
  <div class="product-storage">

    <Message :isVisible="showMessage" :message="messageText" @ok="showMessage = false" />
    <Confirm :isVisible="showConfirm" :message="confirmMessage" @confirm="executeConfirm" @cancel="showConfirm = false" />

    <div class="header-section">
      <h2 class="title">Gestion du Stock</h2>
      <div class="actions">
        <gBtn :svg="icons.refresh" text="Rafraîchir" color="var(--color-greeny)" @click="refreshAll" />
        <gBtn :svg="icons.print" text="Imprimer QR" color="var(--color-greeny)" @click="printAll" />
        <gBtn :svg="icons.trashX" text="Supprimer tout" color="var(--color-rady)" @click="deleteAllStock" />
      </div>
    </div>
    
    <!-- Bulk & Filter Toolbar -->
    <div class="bulk-toolbar">
        <div class="filter-group">
             <InputText
                    type="text"
                    v-model="searchQuery"
                    placeHolder="Rechercher variante..."
                />
        </div>

        <div class="bulk-actions-group">
            <span class="bulk-label">Actions en masse :</span>
            <input type="number" v-model="bulkQty" placeholder="Qté" class="bulk-input" />
            <gBtn :svg="icons.plus" text="Ajouter à la liste" color="var(--color-blumy)" @click="bulkAddStock" :disabled="loading || !bulkQty" />
            <gBtn :svg="icons.x" text="Retirer de la liste" color="var(--color-rady)" @click="bulkWithdrawStock" :disabled="loading || !bulkQty" />
        </div>
    </div>

    <!-- Variant Grid Section -->
    <div class="stock-grid-container">
      <div class="grid-header sticky-header">
        <div class="col-name">Variante / Modèle</div>
        <div class="col-stock">En Stock</div>
        <div class="col-input">Quantité</div>
        <div class="col-action">Actions</div>
      </div>

      <div v-for="(item, index) in filteredRows" :key="index" class="grid-row">
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

      <div v-if="filteredRows.length === 0" class="empty-state">
        <span v-if="searchQuery">Aucun résultat pour "{{ searchQuery }}".</span>
        <span v-else>Aucune variante ou modèle disponible pour ce produit.</span>
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
import Confirm from './bloc/confirm.vue';
import Message from './bloc/message.vue';
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
const searchQuery = ref('');
const bulkQty = ref('');

const showConfirm = ref(false);
const confirmMessage = ref('');
const onConfirm = ref(null);
const showMessage = ref(false);
const messageText = ref('');

const executeConfirm = () => {
  if (onConfirm.value) onConfirm.value();
  showConfirm.value = false;
};

const triggerMessage = (msg) => {
  messageText.value = msg;
  showMessage.value = true;
};

// Helper to create a unique key for the input map
const getUniqueKey = (modelId, detailId) => `${modelId}-${detailId || 'null'}`;

const resizeSvg = (svg, width, height) => {
    if(!svg) return '';
    return svg.replace(/width="[^"]+"/, `width="${width}"`).replace(/height="[^"]+"/, `height="${height}"`);
}

const constructVariantName = (model, detail) => {
    let name = model.name || model.ref || 'Model';
    if (!detail) return name;

    const hasColor = !!model.activeColor || model.activeColor === '1';
    const hasSize = !!model.activeSize || model.activeSize === '1';

    let parts = [];
    if (hasColor && (detail.colorName || detail.color)) {
        parts.push(detail.colorName || detail.color);
    }
    if (hasSize && detail.size) {
        parts.push(detail.size);
    }

    if (parts.length > 0) {
        name += ' - ' + parts.join(' ');
    }
    return name;
};

// Compute counts from actual stockList
const stockCounts = computed(() => {
    const counts = {};
    stockList.value.forEach(s => {
        if (s.status === 'available') {
            const key = getUniqueKey(s.model_id, s.detail_id);
            counts[key] = (counts[key] || 0) + 1;
        }
    });
    return counts;
});

// Compute flattened list of variants/models
const variantRows = computed(() => {
    const list = [];
    if (!props.modelValue.models) return list;

    props.modelValue.models.forEach(m => {
        if (m.details && m.details.length > 0) {
            m.details.forEach(d => {
                const key = getUniqueKey(m.id, d.id);
                list.push({
                    uniqueKey: key,
                    modelId: m.id,
                    detailId: d.id,
                    name: constructVariantName(m, d),
                    qty: stockCounts.value[key] || 0,
                    img: d.image || m.imageUrls || props.modelValue.image
                });
            });
        } else {
             const key = getUniqueKey(m.id, null);
             list.push({
                uniqueKey: key,
                modelId: m.id,
                detailId: null,
                name: m.name || m.ref || 'Model',
                qty: stockCounts.value[key] || 0,
                img: m.imageUrls || props.modelValue.image
            });
        }
    });
    return list;
});

const filteredRows = computed(() => {
    if (!searchQuery.value) return variantRows.value;
    const lower = searchQuery.value.toLowerCase();
    return variantRows.value.filter(r => r.name.toLowerCase().includes(lower));
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

const performAdd = async (item, qty, silent = false) => {
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
        if (!result.success && !silent) {
            alert(result.message || 'Erreur lors de l\'ajout.');
        }
        return result.success;

    } catch (e) {
        console.error(e);
        if(!silent) alert('Erreur réseau.');
        return false;
    }
}

const addStock = async (item) => {
    const qty = parseInt(inputMap[item.uniqueKey]);
    if (!qty || qty <= 0) return triggerMessage('Veuillez entrer une quantité valide.');

    loading.value = true;
    const success = await performAdd(item, qty);
    if(success) {
        inputMap[item.uniqueKey] = '';
        await refreshAll();
    }
    loading.value = false;
};

const performWithdraw = async (item, qty, silent = false) => {
    // Local check for availability
    const availableItems = stockList.value.filter(s =>
        s.model_id == item.modelId &&
        s.detail_id == item.detailId &&
        s.status === 'available'
    );

    if (availableItems.length < qty) {
        if(!silent) triggerMessage(`Pas assez de stock disponible pour ${item.name} (Dispo: ${availableItems.length}).`);
        return false;
    }

    try {
        const idsToDelete = availableItems.slice(0, qty).map(s => s.id);

        const res = await fetch('https://management.hoggari.com/backend/api.php?action=deleteStock', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: idsToDelete })
        });

        const result = await res.json();
        if (!result.success && !silent) {
            triggerMessage(result.message || 'Erreur lors du retrait.');
        }
        return result.success;

    } catch (e) {
        console.error(e);
        if(!silent) triggerMessage('Erreur réseau.');
        return false;
    }
}

const withdrawStock = async (item) => {
    const qty = parseInt(inputMap[item.uniqueKey]);
    if (!qty || qty <= 0) return triggerMessage('Veuillez entrer une quantité valide.');

    confirmMessage.value = `Confirmez-vous le retrait de ${qty} unité(s) pour ${item.name} ? Cela supprimera les codes correspondants.`;
    onConfirm.value = async () => {
        loading.value = true;
        const success = await performWithdraw(item, qty);
        if(success) {
            inputMap[item.uniqueKey] = '';
            await refreshAll();
        }
        loading.value = false;
    };
    showConfirm.value = true;
};


const bulkAddStock = async () => {
    const qty = parseInt(bulkQty.value);
    if (!qty || qty <= 0) return triggerMessage('Quantité invalide');
    const targets = filteredRows.value;
    if(targets.length === 0) return;

    confirmMessage.value = `Ajouter ${qty} unités à ${targets.length} variantes affichées ? (Total: ${qty * targets.length})`;
    onConfirm.value = async () => {
        loading.value = true;
        let addedCount = 0;
        for (const item of targets) {
            const success = await performAdd(item, qty, true); // silent
            if(success) addedCount++;
        }
        await refreshAll();
        loading.value = false;
        triggerMessage(`Terminé. ${addedCount}/${targets.length} variantes mises à jour.`);
    };
    showConfirm.value = true;
}

const bulkWithdrawStock = async () => {
    const qty = parseInt(bulkQty.value);
    if (!qty || qty <= 0) return triggerMessage('Quantité invalide');
    const targets = filteredRows.value;
    if(targets.length === 0) return;

    confirmMessage.value = `Retirer ${qty} unités de ${targets.length} variantes affichées ? Attention, cette action est irréversible.`;
    onConfirm.value = async () => {
        loading.value = true;
        let withdrawnCount = 0;
        for (const item of targets) {
            const success = await performWithdraw(item, qty, true); // silent
            if(success) withdrawnCount++;
        }
        await refreshAll();
        loading.value = false;
        triggerMessage(`Terminé. ${withdrawnCount}/${targets.length} variantes mises à jour.`);
    };
    showConfirm.value = true;
}


const deleteSingleItem = async (id) => {
    confirmMessage.value = 'Supprimer ce code spécifique ?';
    onConfirm.value = async () => {
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
    showConfirm.value = true;
};

const refreshAll = async () => {
    await fetchStock();
    emit('refresh'); // Tell parent to refresh productData
};

const getVariantName = (stockItem) => {
    const m = props.modelValue.models?.find(x => x.id == stockItem.model_id);
    if (!m) return `Model ${stockItem.model_id}`;

    if (stockItem.detail_id && m.details) {
        const d = m.details.find(x => x.id == stockItem.detail_id);
        return constructVariantName(m, d);
    }
    return m.name || m.ref || 'Model';
};

const printAll = () => {
    window.print();
};

const deleteAllStock = async () => {
    if (!props.modelValue.id || props.modelValue.id <= 0) return;

    confirmMessage.value = "ATTENTION : Vous êtes sur le point de supprimer tout le stock DISPONIBLE pour ce produit. Cette action est irréversible. Continuer ?";
    onConfirm.value = async () => {
        loading.value = true;
        try {
            const res = await fetch('https://management.hoggari.com/backend/api.php?action=deleteStock', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    product_id: props.modelValue.id,
                    delete_all: true
                })
            });
            const result = await res.json();
            if (result.success) {
                triggerMessage(result.message || 'Stock supprimé avec succès.');
                await refreshAll();
            } else {
                triggerMessage(result.message || 'Erreur lors de la suppression.');
            }
        } catch (e) {
            console.error(e);
            triggerMessage('Erreur réseau.');
        } finally {
            loading.value = false;
        }
    };
    showConfirm.value = true;
}

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
    display: flex;
    flex-direction: column;
    width: calc(100% - 20px);
    margin-inline: 10px;
}
.dark .product-storage {
    background-color: var(--color-darkly);
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.title {
    font-size: 1.25rem;
    font-weight: bold;
}

.actions {
    display: flex !important;
    flex-wrap: wrap;
    gap: 10px;
}

/* Bulk Toolbar */
.bulk-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    background: var(--color-whizy);
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 20px;
    align-items: center;
    border: 1px solid var(--color-zioly2);
}
.dark .bulk-toolbar {
    background: var(--color-darkow);
}

.filter-group { flex: 1; min-width: 200px; }
.search-wrapper {
    display: flex; align-items: center;
    background: var(--color-whitly);
    border: 1px solid var(--color-zioly2);
    border-radius: 8px;
    padding: 0 10px;
    height: 40px;
}
.dark .search-wrapper { background: var(--color-darkly); }
.search-input {
    border: none; background: transparent;
    margin-left: 10px; width: 100%; height: 100%;
    outline: none; color: inherit; font-size: 14px;
}

.bulk-actions-group {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.bulk-label { font-size: 14px; font-weight: 600; white-space: nowrap; }
.bulk-input {
    width: 80px; height: 40px;
    border: 1px solid var(--color-zioly2);
    border-radius: 8px;
    padding: 0 10px;
    background: var(--color-whitly);
    color: inherit; text-align: center;
}
.dark .bulk-input { background: var(--color-darkly); }

/* Grid Styles */
.stock-grid-container {
    border: 1px solid var(--color-zioly2);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 30px;
    max-height: 600px;
    overflow-y: auto;
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
.sticky-header {
    position: sticky; top: 0; z-index: 10;
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

.empty-state {
    padding: 30px; text-align: center; color: var(--color-text-muted);
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

    .bulk-toolbar { flex-direction: column; align-items: stretch; }
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