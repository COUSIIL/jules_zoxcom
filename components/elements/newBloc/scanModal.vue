<template>
  <div v-if="isVisible" class="scan-modal-overlay">
    <div class="scan-modal">
      <div class="header">
        <h3>Scanner les codes (Order #{{ order.id }})</h3>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>

      <div class="content">
        <!-- Input Mode -->
        <div class="input-section" v-if="!showCamera">
          <input
            ref="scanInput"
            type="text"
            v-model="scannedCode"
            placeholder="Scanner avec douchette..."
            @keyup.enter="processScan"
            :disabled="loading"
            autocomplete="off"
          />
          <button @click="processScan" :disabled="loading || !scannedCode">Valider</button>
          <button class="icon-btn" @click="startCamera" title="Ouvrir Caméra">📷</button>
        </div>

        <!-- Camera Mode -->
        <div class="camera-section" v-if="showCamera">
          <video ref="videoElement" autoplay playsinline class="camera-view"></video>
          <div class="camera-overlay">
             <div class="scanner-line"></div>
          </div>
          <button class="stop-camera-btn" @click="stopCamera">Fermer Caméra</button>
          <p v-if="cameraError" class="error-text">{{ cameraError }}</p>
        </div>

        <div v-if="message" :class="['message', messageType]">{{ message }}</div>

        <div class="items-list">
          <h4>Articles à scanner ({{ remainingCount }})</h4>
          <ul>
            <li v-for="(item, idx) in localItems" :key="idx" :class="{ 'scanned': item.scanned }">
              <span class="item-name">{{ item.name }}</span>
              <span class="item-status">
                <span v-if="item.scanned" class="badge sold">OK</span>
                <span v-else class="badge pending">En attente</span>
              </span>
              <span class="item-code">{{ item.unique_code || '---' }}</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="footer">
        <div class="progress">
           {{ scannedCount }} / {{ localItems.length }}
        </div>
        <div class="actions">
            <button class="btn-secondary" @click="shipWithoutScan">
              Ship without scan
            </button>
            <button class="btn-confirm" :disabled="remainingCount > 0" @click="finalize">
              Terminer & Expédier
            </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onUnmounted } from 'vue';

const props = defineProps({
  isVisible: Boolean,
  order: Object
});

const emit = defineEmits(['close', 'validated']);

const scannedCode = ref('');
const message = ref('');
const messageType = ref('');
const loading = ref(false);
const localItems = ref([]);
const scanInput = ref(null);

// Camera logic
const showCamera = ref(false);
const videoElement = ref(null);
const cameraError = ref('');
let barcodeDetector = null;
let scanInterval = null;

// Initialize items from Order logic
const initItems = () => {
    if (!props.order) return;

    const items = [];
    const assigned = [...(props.order.assigned_codes || [])];

    // Iterate through order items to build the required list
    // Handle both grouped structure and flat structure if necessary, depending on API response
    // Typically: order.items is an array of products, each containing 'items' (variants) or just flat props.

    // We assume the structure seen in order list: item.items contains variants.

    const orderItems = props.order.items || [];

    for (const group of orderItems) {
        if (group.items && group.items.length > 0) {
            for (const variant of group.items) {
                const qty = parseInt(variant.qty);
                const name = `${group.productName} ${variant.color_name || ''} ${variant.size || ''}`.trim();

                for (let i = 0; i < qty; i++) {
                    // Check if we have an assigned code for this variant
                    const matchIdx = assigned.findIndex(c =>
                        c.model_id == group.model_id &&
                        (c.detail_id == variant.indx || (!c.detail_id && !variant.indx))
                    );

                    let codeInfo = null;
                    if (matchIdx !== -1) {
                        codeInfo = assigned.splice(matchIdx, 1)[0];
                    }

                    items.push({
                        uniqueId: `${group.model_id}-${variant.indx}-${i}`, // temporary ID
                        product_id: group.product_id,
                        model_id: group.model_id,
                        detail_id: variant.indx,
                        name: name,
                        scanned: !!codeInfo,
                        unique_code: codeInfo ? codeInfo.unique_code : null,
                        status: codeInfo ? codeInfo.status : 'pending'
                    });
                }
            }
        } else {
             // Fallback for flat items (if any)
             const qty = parseInt(group.qty);
             const name = group.productName;
             for (let i = 0; i < qty; i++) {
                 const matchIdx = assigned.findIndex(c => c.model_id == group.model_id); // imprecise matching if detail missing
                 let codeInfo = null;
                 if (matchIdx !== -1) {
                        codeInfo = assigned.splice(matchIdx, 1)[0];
                 }
                 items.push({
                        uniqueId: `${group.model_id}-0-${i}`,
                        product_id: group.product_id,
                        model_id: group.model_id,
                        detail_id: 0,
                        name: name,
                        scanned: !!codeInfo,
                        unique_code: codeInfo ? codeInfo.unique_code : null,
                        status: codeInfo ? codeInfo.status : 'pending'
                 });
             }
        }
    }

    localItems.value = items;
};

const remainingCount = computed(() => localItems.value.filter(i => !i.scanned).length);
const scannedCount = computed(() => localItems.value.filter(i => i.scanned).length);

watch(() => props.isVisible, (val) => {
    if (val) {
        initItems();
        scannedCode.value = '';
        message.value = '';
        showCamera.value = false;
        nextTick(() => {
            if (scanInput.value) scanInput.value.focus();
        });
    } else {
        stopCamera();
    }
});

const processScan = async (codeOverride = null) => {
    const code = codeOverride || scannedCode.value.trim();
    if (!code) return;

    // Check if already scanned locally
    if (localItems.value.some(i => i.unique_code === code)) {
        message.value = "Ce code est déjà scanné.";
        messageType.value = "error";
        scannedCode.value = '';
        return;
    }

    loading.value = true;
    message.value = '';

    try {
        const res = await fetch('https://management.hoggari.com/backend/sql/update/validateScan.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: props.order.id,
                code: code
            })
        });

        const result = await res.json();

        if (result.success) {
            message.value = "Validé !";
            messageType.value = "success";

            // Find a matching slot in localItems that is NOT scanned
            // logic in backend ensures it matches requirement.
            // backend returned updated 'stock' object?

            // We need to find the specific item type this code belongs to.
            // Since backend verified it matches, we can look for the first unscanned item
            // that matches model/detail.

            // Wait, validateScan.php returns 'stock' details.
            if (result.stock) {
                 const s = result.stock;
                 // Find matching slot
                 const slot = localItems.value.find(i =>
                    !i.scanned &&
                    i.product_id == s.product_id &&
                    i.model_id == s.model_id &&
                    // loose comparison for detail because 0 vs null
                    (i.detail_id == s.detail_id || (i.detail_id == 0 && !s.detail_id))
                 );

                 if (slot) {
                     slot.scanned = true;
                     slot.unique_code = code;
                     slot.status = 'reserved';
                 } else {
                     // Should not happen if backend logic matches frontend logic
                     // Maybe partial match or infinite stock?
                     message.value = "Code validé mais pas de slot vide trouvé (Sync error)";
                 }
            } else {
                 // Fallback if backend didn't return stock details (legacy?)
                 // Try to match blindly? No, dangerous.
                 message.value = "Erreur: Détails du stock manquants.";
            }

            scannedCode.value = '';

        } else {
            message.value = result.message;
            messageType.value = "error";
        }
    } catch (e) {
        console.error(e);
        message.value = "Erreur réseau";
        messageType.value = "error";
    } finally {
        loading.value = false;
        if (!showCamera.value) {
            nextTick(() => {
                if (scanInput.value) scanInput.value.focus();
            });
        }
    }
};

// Camera Functions
const startCamera = async () => {
    if (!('BarcodeDetector' in window)) {
        cameraError.value = "Votre navigateur ne supporte pas la détection de codes-barres.";
        showCamera.value = true;
        return;
    }

    showCamera.value = true;
    cameraError.value = '';

    try {
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { exact: "environment" } }
        }).catch(() => navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } }));

        nextTick(() => {
            if (videoElement.value) {
                videoElement.value.srcObject = stream;

                barcodeDetector = new BarcodeDetector({ formats: ['qr_code', 'code_128', 'ean_13'] });

                scanInterval = setInterval(detectCode, 500); // Check every 500ms
            }
        });

    } catch (err) {
        console.error(err);
        cameraError.value = "Impossible d'accéder à la caméra.";
    }
};

const stopCamera = () => {
    if (videoElement.value && videoElement.value.srcObject) {
        videoElement.value.srcObject.getTracks().forEach(t => t.stop());
    }
    if (scanInterval) clearInterval(scanInterval);
    showCamera.value = false;
};

const detectCode = async () => {
    if (!videoElement.value || videoElement.value.readyState < 2) return;

    try {
        const barcodes = await barcodeDetector.detect(videoElement.value);
        if (barcodes.length > 0) {
            const code = barcodes[0].rawValue;
            // Debounce or confirm?
            // Just try to process.
            if (code && !loading.value) {
                // Flash effect could be added here
                await processScan(code);
                // Optional: pause scanning briefly?
            }
        }
    } catch (e) {
        console.error("Detection error:", e);
    }
};

onUnmounted(() => {
    stopCamera();
});

const finalize = () => {
    emit('validated');
    emit('close');
};

const shipWithoutScan = () => {
    // Logic: Just finalize without checking scans
    emit('validated');
    emit('close');
};

</script>

<style scoped>
.scan-modal-overlay {
    position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
    background: rgba(0,0,0,0.6);
    display: flex; justify-content: center; align-items: center;
    z-index: 3000;
}
.scan-modal {
    background: white; width: 90%; max-width: 500px;
    border-radius: 10px; padding: 20px;
    display: flex; flex-direction: column;
    max-height: 90vh;
}
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.close-btn { background: none; border: none; font-size: 24px; cursor: pointer; }

.input-section { display: flex; gap: 10px; margin-bottom: 15px; }
.input-section input { flex: 1; padding: 10px; font-size: 16px; border: 2px solid #ddd; border-radius: 5px; }
.icon-btn { font-size: 20px; background: none; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; padding: 0 10px; }

.camera-section { position: relative; margin-bottom: 15px; background: black; border-radius: 5px; overflow: hidden; height: 250px; }
.camera-view { width: 100%; height: 100%; object-fit: cover; }
.camera-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center; pointer-events: none; }
.scanner-line { width: 80%; height: 2px; background: red; box-shadow: 0 0 4px red; }
.stop-camera-btn { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.8); border: none; padding: 5px 15px; border-radius: 20px; cursor: pointer; }
.error-text { color: #ff5555; text-align: center; padding: 20px; }

.message { padding: 10px; margin-bottom: 10px; border-radius: 5px; text-align: center; }
.message.success { background: #d1fae5; color: #065f46; }
.message.error { background: #fee2e2; color: #991b1b; }

.items-list { flex: 1; overflow-y: auto; border-top: 1px solid #eee; padding-top: 10px; }
.items-list ul { list-style: none; padding: 0; }
.items-list li { display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid #f5f5f5; }
.items-list li.scanned { background: #f0fdf4; }

.item-name { font-weight: 500; font-size: 14px; flex: 1; }
.badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-left: 5px; text-transform: uppercase; }
.badge.sold { background: #dbeafe; color: #1e40af; }
.badge.pending { background: #fef3c7; color: #92400e; }
.item-code { font-family: monospace; font-size: 12px; color: #666; width: 80px; text-align: right; }

.footer { margin-top: 20px; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 10px; }
.actions { display: flex; gap: 10px; }
.btn-confirm { background: var(--color-greeny); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
.btn-confirm:disabled { background: #ccc; cursor: not-allowed; }
.btn-secondary { background: #eee; color: #333; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }
</style>
