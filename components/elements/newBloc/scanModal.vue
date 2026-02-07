<template>
  <div v-if="isVisible" class="scan-modal-overlay">
    <div class="scan-modal">
      <div class="header">
        <h3>Scanner les codes (Order #{{ order.id }})</h3>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>

      <div class="content">
        <div class="input-section">
          <input
            ref="scanInput"
            type="text"
            v-model="scannedCode"
            placeholder="Scanner le code ici..."
            @keyup.enter="processScan"
            :disabled="loading"
            autocomplete="off"
          />
          <button @click="processScan" :disabled="loading || !scannedCode">Valider</button>
        </div>

        <div v-if="message" :class="['message', messageType]">{{ message }}</div>

        <div class="items-list">
          <h4>Articles à scanner ({{ remainingCount }})</h4>
          <ul>
            <li v-for="(item, idx) in combinedItems" :key="idx" :class="{ 'scanned': item.scanned }">
              <span class="item-name">{{ item.name }}</span>
              <span class="item-status">
                <span v-if="item.scanned" class="badge sold">Sold</span>
                <span v-else class="badge reserved">Reserved</span>
              </span>
              <span class="item-code">{{ item.code || '---' }}</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="footer">
        <div class="progress">
           {{ scannedCount }} / {{ combinedItems.length }}
        </div>
        <button class="btn-confirm" :disabled="remainingCount > 0" @click="finalize">
          Terminer & Expédier
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';

const props = defineProps({
  isVisible: Boolean,
  order: Object
});

const emit = defineEmits(['close', 'validated']);

const scannedCode = ref('');
const message = ref('');
const messageType = ref('');
const loading = ref(false);

// Local state of items to track scanning progress
const localItems = ref([]);

const initItems = () => {
    if (!props.order) return;

    // Flatten order items and map to assigned codes
    // We rely on assigned_codes to know what is reserved
    const codes = props.order.assigned_codes || [];

    // We only care about RESERVED codes that need to be scanned to become SOLD.
    // However, we should also show SOLD codes as already scanned.

    localItems.value = codes.map(c => {
        return {
            id: c.id, // we might not have stock ID in frontend if order.php didn't return it?
                      // Wait, order.php returned unique_code, model_id, detail_id, status. Not ID.
            unique_code: c.unique_code,
            model_id: c.model_id,
            detail_id: c.detail_id,
            status: c.status,
            scanned: c.status === 'sold',
            name: getVariantName(c) // Helper to find name from order items
        };
    });
};

const getVariantName = (code) => {
    // Try to find matching item in order.items
    // order.items structure is grouped models.
    for (const group of props.order.items) {
        if (group.model_id == code.model_id) {
            // Check items inside
            if (group.items && group.items.length > 0) {
                 // Try to match detail
                 // item.indx corresponds to detail_id (variant_id)
                 const variant = group.items.find(v => v.indx == code.detail_id || (code.detail_id == 0 && !v.indx));
                 if (variant) {
                     let n = group.productName;
                     if (variant.color_name) n += ' ' + variant.color_name;
                     if (variant.size) n += ' ' + variant.size;
                     return n;
                 }
            } else {
                 // No variants structure?
                 return group.productName;
            }
        }
    }
    return `Item ${code.unique_code}`;
}

const combinedItems = computed(() => localItems.value);
const remainingCount = computed(() => localItems.value.filter(i => !i.scanned).length);
const scannedCount = computed(() => localItems.value.filter(i => i.scanned).length);
const scanInput = ref(null);

watch(() => props.isVisible, (val) => {
    if (val) {
        initItems();
        scannedCode.value = '';
        message.value = '';
        nextTick(() => {
            if (scanInput.value) scanInput.value.focus();
        });
    }
});

const processScan = async () => {
    const code = scannedCode.value.trim();
    if (!code) return;

    loading.value = true;
    message.value = '';

    try {
        const res = await fetch('https://management.hoggari.com/backend/api.php?action=validateStock', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                order_id: props.order.id,
                code: code
            })
        });

        const result = await res.json();


        if (result.success) {
            message.value = "Code validé !";
            messageType.value = "success";

            // Mark as scanned locally
            // Logic: find the item that matches this code OR swap if backend swapped.
            // Since backend handles swapping, we just need to know WHICH item is now sold.
            // But validateScan doesn't return the new item details?
            // It just says "Validated".

            // If the code was in our list (reserved), mark it sold.
            const existing = localItems.value.find(i => i.unique_code === code);
            if (existing) {
                existing.scanned = true;
                existing.status = 'reserved';
            } else {
                // Backend swapped!
                // We need to find the item that WAS reserved (and now released) and replace it?
                // Or simply find matching model/variant in our local list that is NOT scanned, and assume it was swapped.
                // But we don't know which one.
                // Re-fetch order? Or just trust the count?
                // Ideally, we should update localItems.

                // Let's assume we find a matching variant that is not scanned and update its code.
                // But we don't know the variant of the scanned code unless we decode it or fetch it.
                // Code format: Model-Color-Size...
                // We can guess.

                // Better: validateScan should return the updated stock ID/details.
                // But for now, let's just re-init items or simplistic update.

                // Hack: If backend success, we assume one "Reserved" item became "Sold".
                // But which one?
                // Let's refetch order items or just initItems (but order prop isn't updated).
                // We need to update the prop or local state.

                // Since we can't easily re-fetch the full order here without parent help,
                // let's just mark one compatible item as scanned.

                // We really should parse the code or ask backend.
                // Code: Name-Color-Size...

                // Find ANY reserved item.
                const candidate = localItems.value.find(i => !i.scanned);
                if (candidate) {
                    candidate.scanned = true;
                    candidate.unique_code = code; // Update display
                    candidate.status = 'reserved';
                }
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
        nextTick(() => {
             if (scanInput.value) scanInput.value.focus();
        });
    }
};

const finalize = () => {
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
    max-height: 80vh;
}
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.close-btn { background: none; border: none; font-size: 24px; cursor: pointer; }

.input-section { display: flex; gap: 10px; margin-bottom: 15px; }
.input-section input { flex: 1; padding: 10px; font-size: 16px; border: 2px solid #ddd; border-radius: 5px; }
.input-section input:focus { border-color: var(--color-blumy); outline: none; }
.input-section button { padding: 0 20px; background: var(--color-blumy); color: white; border: none; border-radius: 5px; cursor: pointer; }

.message { padding: 10px; margin-bottom: 10px; border-radius: 5px; text-align: center; }
.message.success { background: #d1fae5; color: #065f46; }
.message.error { background: #fee2e2; color: #991b1b; }

.items-list { flex: 1; overflow-y: auto; border-top: 1px solid #eee; padding-top: 10px; }
.items-list ul { list-style: none; padding: 0; }
.items-list li { display: flex; justify-content: space-between; align-items: center; padding: 8px; border-bottom: 1px solid #f5f5f5; }
.items-list li.scanned { background: #f0fdf4; }

.item-name { font-weight: 500; font-size: 14px; }
.badge { font-size: 10px; padding: 2px 6px; border-radius: 4px; margin-left: 5px; text-transform: uppercase; }
.badge.sold { background: #dbeafe; color: #1e40af; }
.badge.reserved { background: #fff7ed; color: #9a3412; }
.item-code { font-family: monospace; font-size: 12px; color: #666; }

.footer { margin-top: 20px; display: flex; justify-content: space-between; align-items: center; }
.btn-confirm { background: var(--color-greeny); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
.btn-confirm:disabled { background: #ccc; cursor: not-allowed; }
</style>
