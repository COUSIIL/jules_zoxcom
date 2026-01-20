<template>
  <div class="orderView">
     <!-- Info Client -->
     <div class="info-section">
        <h3>{{ t('customer information') }}</h3>
        <p><strong>{{ t('name') }}:</strong> {{ name }}</p>
        <p><strong>{{ t('phone') }}:</strong> {{ phone }}</p>
        <p><strong>{{ t('address') }}:</strong> {{ adresse }}</p>
        <p><strong>{{ t('wilaya') }}:</strong> {{ wilaya }}</p>
        <p><strong>{{ t('commune') }}:</strong> {{ commune }}</p>
     </div>

     <!-- Delivery -->
     <div class="info-section">
        <h3>{{ t('delivery') }}</h3>
        <p><strong>{{ t('type') }}:</strong> {{ deliveryType === 1 ? t('stop desk') : t('home') }}</p>
        <p><strong>{{ t('method') }}:</strong> {{ deliveryMethod }}</p>
        <p><strong>{{ t('fees') }}:</strong> {{ deliveryFees }} DA</p>
     </div>

     <!-- Products -->
     <div class="info-section">
        <h3>{{ t('products') }}</h3>
        <div class="order-product-list">
          <div
            v-for="(item, index) in products"
            :key="index"
            class="product-lister"
          >
            <div class="left">
              <img :src="item.image" alt="" class="p-image" />

              <div class="infos">
                <h4 :title="item.productName">{{ item.productName }}</h4>

                <div v-if="item.items?.length" class="variants">
                  <p v-for="v in item.items" :key="v.id" class="variant-line">
                    <span v-if="v.promo && v.promo != '0.00'" class="price">{{ v.promo }} DA</span>
                    <span v-else-if="v.price" class="price">{{ v.price }} DA</span>
                    <span v-else-if="v.total" class="price">{{ v.total }} DA</span>
                    <span >Qty : <strong>{{ item.qty }}</strong></span>
                    <span v-if="v.color_name">Color : <strong>{{ v.color_name }}</strong></span>
                    <span v-if="v.size"> Size : <strong>{{ v.size }}</strong></span>
                  </p>
                </div>
                <div v-else>
                    <span v-if="item.price" class="price">{{ item.price }} DA</span>
                    <span >Qty : <strong>{{ item.qty }}</strong></span>
                </div>
              </div>
            </div>
          </div>
        </div>
     </div>

     <!-- Total -->
     <div class="total-box">
        <span>Total :</span>
        <strong>{{ total }} DA</strong>
     </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { useLang } from '~/composables/useLang'

const { t } = useLang()

const props = defineProps({
  name: { type: String, default: '' },
  phone: { type: String, default: '' },
  adresse: { type: String, default: '' },
  wilaya: { type: String, default: '' },
  commune: { type: String, default: '' },
  deliveryType: { type: Number, default: 0 },
  deliveryMethod: { type: String, default: '' },
  deliveryFees: { type: Number, default: 0 },
  products: { type: Array, default: () => [] },
  total: { type: Number, default: 0 }
})
</script>

<style scoped>
.orderView {
  display: flex;
  flex-direction: column;
  gap: 15px;
  width: 100%;
  max-width: 500px;
  padding: 10px;
}

.info-section {
    background: rgba(255,255,255,0.5);
    padding: 10px;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.dark .info-section {
    background: rgba(0,0,0,0.2);
}

.info-section h3 {
    font-size: 1.1rem;
    font-weight: bold;
    margin-bottom: 5px;
    border-bottom: 1px solid #ccc;
    padding-bottom: 5px;
}

.total-box {
  margin-top: 20px;
  padding: 14px 18px;
  width: 100%;
  border-radius: 16px;
  background: var(--color-primary);
  color: white;
  text-align: center;
  display: flex;
  justify-content: space-between;
  font-size: 18px;
  font-weight: 600;
}

.order-product-list {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
  max-height: 320px;
  overflow-y: auto;
  padding-right: 6px;
}
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

.left { display:flex; align-items:center; gap:12px; width: 100%; }
.p-image {
  min-width: 60px; max-width: 60px; height: 60px; border-radius: 10px; object-fit: cover;
}

.infos { display:flex; flex-direction:column; overflow:hidden; min-width: 0; width: 100%; }
.infos h4 {
  font-size: 15px; font-weight:600; margin:0; text-overflow: ellipsis; overflow:hidden; white-space:nowrap;
}
.price { color: var(--color-primary, #1976d2); font-weight:700; margin-right: 10px; }
.variants { width: 100%; margin-top:6px; }
.variant-line { width: 100%; margin:0; font-size:13px; display: flex; flex-direction: column; }
</style>
