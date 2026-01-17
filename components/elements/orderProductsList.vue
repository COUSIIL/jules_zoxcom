<template>
  <div class="order-product-list">
    <h3 class="title">Produits commandés</h3>

    <div
      v-for="(item, index) in localProducts"
      :key="item.id + '-' + index"
      class="product-lister"
    >
      <div class="left">
        <img :src="item.image" alt="" class="p-image" />

        <div class="infos">
          <h4 :title="item.productName">{{ item.productName }}</h4>
          

          <div v-if="item.items?.length" class="variants">
            
            <p v-for="v in item.items" :key="v.id" class="variant-line">
              <span v-if="v.promo" class="price">{{ v.promo }} DA</span>
              <span v-else-if="v.price" class="price">{{ v.price }} DA</span>
              <span v-else-if="v.total" class="price">{{ v.total }} DA</span>
              <span >Qty : <strong>{{ item.qty }}</strong></span>
              <span v-if="v.color_name">Couleur : <strong>{{ v.color_name }}</strong></span>
              <span v-if="v.size"> Taille : <strong>{{ v.size }}</strong></span>
              <!--span v-if="v.qty"> Stock: {{ v.qty }}</span-->
            </p>
          </div>
        </div>
      </div>

      <div class="actions">
        <RectBtn style="width: 10%;" svg="x" iconColor="#ff5555" @click:ok="onDelete(index)" />

        <RectBtn style="width: 10%;" svg="edit" @click:ok="onSelect(index)" />

      </div>
    </div>

    <!-- Bouton + Ajouter un produit -->
    <button class="add-btn" @click="addProduct">
      + add product
    </button>

    <!-- PRODUCT SELECTOR AS MODAL (overlay) -->
    <transition name="fade">
      <div
        v-if="editingIndex !== null"
        class="overlay"
        @click.self="closeEditor"
      >
        <div class="modal">

          <header class="modal-head">
            <h4>
              {{ editingIndex === -1 ? "Ajouter un produit" : "Modifier le produit" }}
            </h4>
            <button class="close" @click="closeEditor">✕</button>
          </header>

          <section class="modal-body">
            <ProductSelector
              :initialItem="editingIndex === -1 ? null : localProducts[editingIndex]"
              :allProducts="allProducts"
              @cancel="closeEditor"
              @save="handleSave"
            />
          </section>

        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import ProductSelector from './productSelector.vue'
import RectBtn from './newBloc/rectBtn.vue';

const props = defineProps({
  products: { type: Array, default: () => [] },
  allProducts: { type: Object, default: () => {} }
})


const emit = defineEmits(['update:products'])

const editingIndex = ref(null)

const onSelect = (i) => {
  editingIndex.value = i
}

const onDelete = (i) => {
  localProducts.value.splice(i, 1)
}

// Ajouter un nouveau produit
const addProduct = () => {
  editingIndex.value = -1 // -1 = mode ajout
}

const closeEditor = () => {
  editingIndex.value = null
}

/**
 * newItem: objet complet qui remplacera props.products[i]
 * structure attendue : { id, productName, image, price, qty, items: [...] }
 */
const handleSave = (newItem) => {
  if (editingIndex.value === -1) {
    // ➕ AJOUT
    localProducts.value.push(newItem)
  } else {
    // ✏️ MODIFICATION
    localProducts.value[editingIndex.value] = newItem
  }

  emit('update:products', [...localProducts.value])
  editingIndex.value = null
}


const localProducts = ref([...props.products])

// mettre à jour localProducts si props.products change
watch(() => props.products, (newVal) => {
  localProducts.value = [...newVal]
})


</script>

<style scoped>
.order-product-list {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 320px;
  overflow-y: auto;
  padding-right: 6px;
  padding-bottom: 6px;
}

/* scrollbar */
.order-product-list::-webkit-scrollbar { width: 6px; }
.order-product-list::-webkit-scrollbar-thumb { background: #cfcfcf; border-radius: 8px; }

.title { font-size: 18px; font-weight: 700; margin-bottom: 6px; }

.product-lister {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--color-whity);
  padding: 10px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(2,8,23,0.04);
  gap: 12px;
  width: 100%;
}
.dark .product-lister {
  background: var(--color-darky);
}

.left { display:flex; align-items:center; gap:12px; width: calc(100% - 96px); }
.p-image {
  min-width: 60px; max-width: 60px; height: 60px; border-radius: 10px; object-fit: cover;
}

.infos { display:flex; flex-direction:column; overflow:hidden; min-width: 0; }
.infos h4 {
  font-size: 15px; font-weight:600; margin:0; text-overflow: ellipsis; overflow:hidden; white-space:nowrap;
}
.price { color: var(--color-primary, #1976d2); font-weight:700; margin-top:6px; }
.qty { margin: 6px 0 0 0; font-size: 13px;}

.variants { width: 100%; margin-top:6px; }
.variant-line { width: 100%; margin:0; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display: flex; justify-content: center; align-items: center; flex-direction: column; }
.varient-line span {
  width: 100%;
}

.actions { 
  width:84px; 
  display:flex; 
  justify-content:center; 
  align-items:center; 
  flex-direction:column; 
  gap:6px; 
}


/* MODAL / OVERLAY */
.overlay {
  position: fixed;
  inset: 0;
  display: grid;
  place-items: center;
  background: rgba(2,8,23,0.45);
  z-index: 3000;
  padding: 16px;
}
.modal {
  width: 100%;
  max-width: 980px;
  max-height: 90vh;
  overflow: auto;
  background: var(--color-whitly);
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(2,8,23,0.4);
  display:flex;
  flex-direction:column;
}
.dark .modal {
  background: var(--color-darky);
}
.modal-head {
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:12px 16px;
  border-bottom:1px solid #f0f0f0;
}
.modal-head h4 { margin:0; font-size:16px; }
.modal-head .close {
  background:transparent; border:none; font-size:18px; cursor:pointer;
}
.modal-body { padding: 14px; }

/* transition */
.fade-enter-active, .fade-leave-active { transition: opacity .15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.add-btn {
  width: 100%;
  margin-top: 8px;
  padding: 10px;
  border: 2px dashed #1a73e8;
  background: transparent;
  color: #1a73e8;
  font-weight: 600;
  border-radius: 12px;
  cursor: pointer;
  transition: 0.2s;
}

.add-btn:hover {
  background: rgba(26,115,232,0.08);
}

</style>
