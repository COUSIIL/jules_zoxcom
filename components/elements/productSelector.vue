<template>
  <div class="product-selector-wrap">
    <div class="steps">

      <!-- STEP 1 : PRODUITS -->
      <section class="step">
        <h3>1 — Produit</h3>
        <div class="grid products-grid">
          <div
            v-for="p in activeProducts"
            :key="p.id"
            :class="['card', {selector: selectorProduct && selectorProduct.id === p.id}]"
            @click="pickProduct(p)"
          >
            <img :src="p.image" class="card-img" />
            <div class="meta">
              <div class="name" :title="p.name">{{ p.name }}</div>
              <div class="price">{{ firstModelPrice(p) }} DA</div>
            </div>
          </div>
        </div>
      </section>

      <!-- STEP 2 : MODELS -->
      <section v-if="selectorProduct" class="step">
        <h3>2 — Modèle</h3>
        <div class="grid models-grid">
          <div
            v-for="m in activeModels"
            :key="m.id"
            :class="['card small', {selector: selectorModel && selectorModel.id === m.id}]"
            @click="pickModel(m)"
          >
            <img :src="m.image || selectorProduct.image" class="card-img" />
            <div class="meta">
              <div class="name">{{ m.modelName }}</div>
              <div class="price">{{ m.sell || m.promo || '-' }} DA</div>
            </div>
          </div>
        </div>
      </section>

      <!-- STEP 3 : VARIANTS -->
      <section v-if="selectorModel" class="step">
        <h3>3 — Variante</h3>

        <div v-if="activeVariants.length" class="grid variants-grid">
          <div
            v-for="v in activeVariants"
            :key="v.id"
            :class="['variant-card', {selector: selectorVariant && selectorVariant.id === v.id}]"
            @click="pickVariant(v)"
          >
            <div class="colour" :style="{background: v.color}"></div>
            <div class="v-meta">
              <div class="v-name">{{ v.colorName || v.color }}</div>
              <div class="v-sub">{{ v.size || '-' }} • Stock: {{ v.qty }}</div>
            </div>
          </div>
        </div>

        <!-- si pas de variant disponible -->
        <div v-else class="no-variants">
          <p>Ce modèle n'a pas de variantes disponibles.</p>
          <button class="choose-btn" @click="confirmSelectionWithoutVariant">
            Choisir ce modèle
          </button>
        </div>
      </section>

    </div>

    <div class="selector-footer2">
      <div v-if="selectorProduct" class="qty-selector">
        <label>Quantité :</label>
        <input type="number" min="1" v-model.number="selectedQty" />
      </div>


    </div>

    <!-- FOOTER -->
    <div class="selector-footer">
      <div class="left-info">
        <div v-if="selectorProduct" class="summary">
          <img :src="selectorProduct.image" class="summary-img" />
          <div>
            <div class="summary-name">{{ selectorProduct.name }}</div>
            <div class="summary-sub">
              <span v-if="selectorModel">{{ selectorModel.modelName }}</span>
              <span v-if="selectorVariant"> · {{ selectorVariant.colorName || selectorVariant.color }} {{ selectorVariant.size ? '· ' + selectorVariant.size : '' }}</span>
            </div>
          </div>
        </div>
      </div>

      

      <div class="actions">
        <button class="btn cancel" @click="$emit('cancel')">Annuler</button>

        <button class="btn save" :disabled="!canSave" @click="save">
          Sauvegarder
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

/* props */
const props = defineProps({
  initialItem: { type: Object, default: null },
  allProducts: { type: Object, default: () => ({ data: [] }) } // IMPORTANT
})
const emit = defineEmits(['save','cancel'])

/* helpers to extract product list correctly */
const productList = computed(() => {
  return Array.isArray(props.allProducts?.data)
    ? props.allProducts.data
    : []
})

/* LOCAL STATE */
const selectorProduct = ref(null)
const selectorModel = ref(null)
const selectorVariant = ref(null)
const selectedQty = ref(props.initialItem?.qty || 1)


/* actives */
const activeProducts = computed(() =>
  productList.value.filter(p => p.prodActive == "1")
)

const activeModels = computed(() => {
  if (!selectorProduct.value) return []
  return (selectorProduct.value.models || []).filter(m => m.isActive == "1")
})

const activeVariants = computed(() => {
  if (!selectorModel.value) return []
  return (selectorModel.value.details || []).filter(d => Number(d.qty) > 0)
})

/* initialize */
watch(() => props.initialItem, (it) => {
  if (!it) {
    selectorProduct.value = null
    selectorModel.value = null
    selectorVariant.value = null
    return
  }

  // find product in productList.data
  const prod = productList.value.find(p => String(p.id) === String(it.id))
  if (!prod) return

  selectorProduct.value = prod

  // model
  if (it.modelName) {
    const m = prod.models?.find(x =>
      x.modelName === it.modelName ||
      String(x.id) === String(it.modelId) ||
      String(x.indx) === String(it.indx)
    )
    if (m) selectorModel.value = m
  }

  // variant
  if (it.items?.length && selectorModel.value) {
    const v0 = it.items[0]
    const variant = selectorModel.value.details?.find(d =>
      String(d.id) === String(v0.id) ||
      d.colorName === v0.color_name ||
      d.color === v0.color
    )
    if (variant) selectorVariant.value = variant
  }
}, { immediate: true })

/* functions */
const firstModelPrice = (p) => {
  const m = (p.models || [])[0]
  return m ? (m.sell || m.promo || '-') : '-'
}

const pickProduct = (p) => {
  selectorProduct.value = p
  selectorModel.value = null
  selectorVariant.value = null
}

const pickModel = (m) => {
  selectorModel.value = m
  selectorVariant.value = null
}

const pickVariant = (v) => {
  selectorVariant.value = v
}

const confirmSelectionWithoutVariant = () => {
  selectorVariant.value = null
  save()
}

const canSave = computed(() => {
  if (!selectorProduct.value) return false

  if (!selectorProduct.value.models?.length) return true
  if (!selectorModel.value) return false
  if (!selectorModel.value.details?.length) return true

  return !!selectorVariant.value
})

const save = () => {
  if (!selectorProduct.value) return

  const base = props.initialItem || {}

  const item = {
    id: String(selectorProduct.value.id),
    productName: selectorProduct.value.name,
    image: selectorProduct.value.image,
    price:
      (selectorModel.value && (selectorModel.value.sell || selectorModel.value.promo)) ||
      selectorProduct.value.sell ||
      selectorProduct.value.promo ||
      base.price ||
      '0',
    qty: base.qty || 1,
    items: []
  }

  item.qty = selectedQty.value || 1


  if (selectorVariant.value) {
    let prix = 0
    if(selectorModel.value.promo) {
      prix = selectorModel.value.promo
    } else {
      prix = selectorModel.value.sell
    }
    item.items = [{
      id: String(selectorVariant.value.id),
      color_name: selectorVariant.value.colorName,
      color: selectorVariant.value.color,
      size: selectorVariant.value.size,
      qty: selectedQty.value,
      promo: prix,
      total: prix * selectedQty.value
    }]
  } else if (selectorModel.value) {
    item.items = [{
      id: String(selectorModel.value.id),
      qty: selectedQty.value,
      promo: 0,
      total: item.price * selectedQty.value
    }]
  }


  emit('save', item)
}
</script>


<style scoped>
/* ------------------------ */
/* STRUCTURE GLOBALE        */
/* ------------------------ */
.product-selector-wrap {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding-bottom: 20px;
  background-color: var(--color-whitly);
}
.dark .product-selector-wrap {
  background-color: var(--color-darky);
}

/* ------------------------ */
/* GRIDS                    */
/* ------------------------ */
.grid {
  width: 100%;
  display: grid;
  gap: 5px;
}

.step {
  width: 100%;
  padding: 5px;
  margin-block: 10px;
  border-radius: 12px;
  background-color: var(--color-zioly1);
}
.dark .step {
  background-color: var(--color-zioly2);
}

/* Produits */
.products-grid { 
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); 
}

/* Modèles */
.models-grid { 
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); 
}

/* Variantes */
.variants-grid { 
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
}

/* ------------------------ */
/* CARDS GENERALES          */
/* ------------------------ */
.card {
  background: var(--color-whizy);
  border-radius: 12px;
  padding: 5px;
  display: flex;
  gap: 5px;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  box-shadow: 0 4px 16px rgba(0,0,0,0.05);
  cursor: pointer;
  transition: transform .12s ease, box-shadow .12s ease, border .12s ease;
  border: 1px solid #eee;
  min-width: 0; /* IMPORTANT: empêche overflow */
}
.dark .card {
  border: 1px solid #242424;
  background: var(--color-darkly);
}

.card:hover {
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.card.selector {
  background: var(--color-whiby);
  border: 2px solid var(--color-primary, #1976d2);
}
.dark .card.selector {
  background: var(--color-darkow);
  border: 2px solid var(--color-primary, #1976d2);
}

/* Photo */
.card-img {
  width: 62px;
  height: 62px;
  border-radius: 10px;
  object-fit: cover;
  flex-shrink: 0;
}

/* Texte card */
.card .meta {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.card .name {
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.card .price {
  font-weight: 700;
  font-size: 13px;
  color: var(--color-primary, #1976d2);
  margin-top: 6px;
}

/* Small card (models) */
.card.small .card-img {
  width: 54px;
  height: 54px;
}

/* ------------------------ */
/* VARIANTS CARDS           */
/* ------------------------ */
.variant-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 12px;
  background: var(--color-whizy);
  border: 1px solid #eaeaea;
  cursor: pointer;
  transition: transform .12s ease, border .12s ease;
  min-width: 0;
}
.dark .variant-card {
  border: 1px solid #242323;
  background: var(--color-darkly);
}

.variant-card.selector {
  background: var(--color-whiby);
  border: 2px solid var(--color-primary, #1976d2);
}
.dark .variant-card.selector {
  background: var(--color-darkow);
  border: 2px solid var(--color-primary, #1976d2);
}

.colour {
  min-width: 32px;
  min-height: 32px;
  max-width: 36px;
  max-height: 36px;
  border-radius: 50%;
  border: 2px solid #ddd;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  flex-shrink: 0;
}

.v-meta {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.v-name {
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.v-sub {
  font-size: 13px;
  color: #666;
  margin-top: 3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ------------------------ */
/* NO VARIANTS / WARN       */
/* ------------------------ */
.no-variants {
  display: flex;
  gap: 12px;
  padding: 12px;
  border-radius: 10px;
  background: #FFF6E5;
  border: 1px solid #FFDEB4;
}

.choose-btn {
  background:#ff8a00;
  color:#fff;
  border:none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor:pointer;
  font-weight: 600;
}

/* ------------------------ */
/* FOOTER & SUMMARY         */
/* ------------------------ */
.selector-footer {
  margin-top: 5px;
  padding-top: 5px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap; /* IMPORTANT MOBILE */
}
.selector-footer2 {
  margin-top: 16px;
  padding-top: 14px;
  border-top: 1px solid #f0f0f0;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap; /* IMPORTANT MOBILE */
}

.summary {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
  min-width: 0;
}

.summary-img {
  width:48px;
  height:48px;
  object-fit:cover;
  border-radius: 8px;
}

.summary-name {
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.summary-sub {
  font-size: 13px;
  color: #666;
}

/* Buttons */
.actions {
  display:flex;
  gap:10px;
}

.btn {
  padding: 8px 14px;
  border-radius: 8px;
  border:none;
  cursor:pointer;
  font-weight: 600;
  white-space: nowrap;
}

.btn.cancel { background:#f1f1f1; color:#333; }
.btn.save {
  background: linear-gradient(90deg,#2d8cf0,#1a73e8);
  color:white;
}
.btn.save:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

/* ------------------------ */
/* RESPONSIVE OPTIMIZED     */
/* ------------------------ */
@media (max-width: 720px) {
  .products-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
  .models-grid   { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); }
  .variants-grid { grid-template-columns: 1fr; }

  .card-img { width:54px; height:54px; }
}

/* Ultra small devices (350px - 420px) */
@media (max-width: 420px) {
  .products-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
  .name { font-size: 13px; }
}

/* Very tiny phones (<= 340px) */
@media (max-width: 340px) {
  .products-grid { grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); }
  .card-img { width:48px; height:48px; }
  .name { font-size: 12px; }
}

.qty-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.qty-selector input {
  width: 60px;
  padding: 4px 6px;
  border-radius: 6px;
  border: 1px solid #ddd;
}

</style>

