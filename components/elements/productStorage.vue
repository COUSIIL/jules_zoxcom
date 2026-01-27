<template>
  <div class="product-storage">
    <div class="header-section">
      <h2 class="title">Stock Management</h2>
      <div class="actions">
        <button class="btn btn-primary" @click="fetchStock">Refresh</button>
        <button class="btn btn-secondary" @click="printAll">Print All QR</button>
      </div>
    </div>

    <!-- Add Stock Section -->
    <div class="add-stock-card">
      <h3>Generate Stock</h3>
      <div class="form-row">
        <div class="form-group">
          <label>Variant / Model</label>
          <select v-model="selectedOption" class="input-select">
            <option :value="null" disabled>Select a variant</option>
            <template v-for="model in props.modelValue.models" :key="model.id">
              <!-- If model has no details, show model -->
              <option v-if="!model.details || model.details.length === 0" :value="{ modelId: model.id, detailId: null }">
                {{ model.name || model.ref || 'Model ' + model.id }}
              </option>
              <!-- If model has details, show variants -->
              <template v-else>
                <option v-for="detail in model.details" :key="detail.id" :value="{ modelId: model.id, detailId: detail.id }">
                  {{ model.name || model.ref }} - {{ detail.colorName || detail.color }} / {{ detail.size }}
                </option>
              </template>
            </template>
          </select>
        </div>
        <div class="form-group">
          <label>Quantity</label>
          <input type="number" v-model.number="addQty" min="1" class="input-number" />
        </div>
        <button class="btn btn-success" @click="generateStock" :disabled="loading || !selectedOption || addQty <= 0">
          {{ loading ? 'Generating...' : 'Generate' }}
        </button>
      </div>
    </div>

    <!-- Stock List -->
    <div class="stock-list">
      <h3>Existing Codes ({{ stockList.length }})</h3>

      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>QR</th>
              <th>Code</th>
              <th>Variant</th>
              <th>Status</th>
              <th>Order Ref</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in stockList" :key="item.id" :class="item.status">
              <td>
                <canvas :ref="(el) => setCanvasRef(el, item.unique_code)" width="50" height="50"></canvas>
              </td>
              <td class="code-text">{{ item.unique_code }}</td>
              <td>
                {{ getVariantName(item) }}
              </td>
              <td>
                <span class="status-badge" :class="item.status">{{ item.status }}</span>
              </td>
              <td>{{ item.order_ref || '-' }}</td>
              <td>
                <button v-if="item.status === 'available'" @click="deleteItem(item.id)" class="btn-icon delete">
                  🗑️
                </button>
              </td>
            </tr>
            <tr v-if="stockList.length === 0">
              <td colspan="6" style="text-align: center; padding: 20px;">No stock codes generated yet.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Print Area (Hidden usually, visible during print) -->
    <div id="print-area" class="print-only">
      <div v-for="item in stockList" :key="'print-'+item.id" class="print-item">
        <div class="print-qr">
           <!-- We will copy canvas content here or regenerate -->
           <img :src="getQrDataUrl(item.unique_code)" />
        </div>
        <div class="print-info">
          <div class="print-code">{{ item.unique_code }}</div>
          <div class="print-variant">{{ getVariantName(item) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import QRCode from 'qrcode';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  }
});

const stockList = ref([]);
const loading = ref(false);
const selectedOption = ref(null);
const addQty = ref(1);
const canvasRefs = ref(new Map());

const setCanvasRef = (el, code) => {
  if (el) {
    QRCode.toCanvas(el, code, { width: 64, margin: 1 }, (error) => {
      if (error) console.error(error);
    });
  }
};

const getQrDataUrl = (code) => {
  // Synchronous generation for print view (using data URL)
  // QRCode.toDataURL is async usually, but we can hack it or use a pre-generated map.
  // Ideally we generate these when list loads.
  // For simplicity, let's assume we can get it.
  // Actually, let's use a method that returns a promise but we can't await in template.
  // Better approach: Store dataUrl in stockList items.
  const item = stockList.value.find(i => i.unique_code === code);
  return item ? item.qrDataUrl : '';
};

const fetchStock = async () => {
  if (!props.modelValue.id || props.modelValue.id === -1) return;

  loading.value = true;
  try {
    const res = await fetch(`/backend/api.php?action=getStock&product_id=${props.modelValue.id}`);
    const data = await res.json();
    if (data.success) {
      // Generate QR Data URLs for printing
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

const generateStock = async () => {
  if (!selectedOption.value || addQty.value <= 0) return;

  loading.value = true;
  try {
    const payload = {
      product_id: props.modelValue.id,
      model_id: selectedOption.value.modelId,
      detail_id: selectedOption.value.detailId,
      qty: addQty.value
    };

    const res = await fetch('/backend/api.php?action=postStock', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await res.json();
    if (result.success) {
      await fetchStock();
      addQty.value = 1;
    } else {
      alert(result.message);
    }
  } catch (e) {
    console.error(e);
    alert('Error generating stock');
  } finally {
    loading.value = false;
  }
};

const deleteItem = async (id) => {
  if (!confirm('Are you sure you want to delete this stock code? It will decrease the quantity count.')) return;

  try {
    const res = await fetch('/backend/api.php?action=deleteStock', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const result = await res.json();
    if (result.success) {
      await fetchStock();
    } else {
      alert(result.message);
    }
  } catch (e) {
    alert('Network error');
  }
};

const getVariantName = (item) => {
  // Find model/detail name from props.modelValue
  if (!props.modelValue.models) return 'Unknown';

  const model = props.modelValue.models.find(m => m.id == item.model_id);
  if (!model) return 'Model ' + item.model_id;

  let name = model.name || model.ref || 'Model';

  if (item.detail_id) {
    if (model.details) {
      const detail = model.details.find(d => d.id == item.detail_id);
      if (detail) {
        name += ` - ${detail.colorName || detail.color || ''} ${detail.size || ''}`;
      }
    }
  }

  return name;
};

const printAll = () => {
  window.print();
};

onMounted(() => {
  fetchStock();
});

watch(() => props.modelValue.id, (newId) => {
  if (newId > 0) fetchStock();
});

</script>

<style scoped>
.product-storage {
  padding: 20px;
  background: #fff;
  border-radius: 8px;
}

.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.title {
  font-size: 1.5rem;
  font-weight: bold;
}

.actions {
  display: flex;
  gap: 10px;
}

.add-stock-card {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
  border: 1px solid #e5e7eb;
}

.form-row {
  display: flex;
  gap: 20px;
  align-items: flex-end;
  margin-top: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
  flex: 1;
}

.input-select, .input-number {
  padding: 10px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
}

.btn {
  padding: 10px 20px;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}

.btn-primary { background: #3b82f6; color: white; }
.btn-success { background: #10b981; color: white; }
.btn-secondary { background: #6b7280; color: white; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }

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
  border-bottom: 1px solid #e5e7eb;
}

th {
  background: #f3f4f6;
  font-weight: 600;
}

.code-text {
  font-family: monospace;
  font-weight: bold;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 99px;
  font-size: 12px;
  text-transform: uppercase;
  font-weight: 600;
}

.status-badge.available { background: #d1fae5; color: #065f46; }
.status-badge.sold { background: #dbeafe; color: #1e40af; }
.status-badge.returned { background: #fee2e2; color: #991b1b; }
.status-badge.removed { background: #f3f4f6; color: #374151; }

.btn-icon {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.2rem;
}

.print-only {
  display: none;
}

@media print {
  body * {
    visibility: hidden;
  }
  .product-storage, .product-storage * {
    visibility: hidden;
  }
  #print-area, #print-area * {
    visibility: visible;
  }
  #print-area {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 20px;
  }
  .print-item {
    border: 1px solid #ccc;
    padding: 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    page-break-inside: avoid;
    width: 150px;
  }
  .print-qr img {
    width: 100px;
    height: 100px;
  }
  .print-code {
    font-family: monospace;
    font-size: 10px;
    margin-top: 5px;
    text-align: center;
    word-break: break-all;
  }
  .print-variant {
    font-size: 10px;
    text-align: center;
  }
}
</style>
