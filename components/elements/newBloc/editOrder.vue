<template>
  <div class="orderEdit">

    <!-- NAME -->
    <Inputer v-model="localName" placeHolder="name" :img="icons['user']" :required="true" />

    <!-- PHONE -->
    <Inputer v-model="localPhone" placeHolder="phone" :img="icons['phone']" :required="true" />



    <Selector :options="deliverySty" :showIt="showSty" :disabled="false" @close="showSty = false"
      @update:modelLabel="updateSty" :modelValue="deliveryMethod" placeHolder="delivery model" :img="icons['delivery']"
      :required="true" />

    <!-- WILAYA -->
    <Selector :options="wilayas" :showIt="showWilaya" :disabled="false" @close="showWilaya = false"
      @update:modelLabel="updateWilaya" :modelValue="localWilaya" placeHolder="wilaya" :img="icons['wilaya']"
      :required="true" />

    <!-- COMMUNE -->
    <Selector :options="communes" :showIt="showCommune" :disabled="false" @close="showCommune = false"
      @update:modelValue="updateCommune" :modelValue="localCommune" placeHolder="commune" :img="icons['commune']"
      :required="true" />

    <div class="boxColumn">
      <h3>{{ t('delivery type') }}</h3>

      <Switcher :label1="deskLabel" :label2="homeLabel" img1="location" img2="home" :position="localType"
        :has1="isDesk" :has2="true" @click:1="updateDeskFees" @click:2="updateHomeFees" />
    </div>

    <!-- ADDRESS -->
    <Inputer v-model="localAdresse" placeHolder="adresse" :img="icons['adresse']" />

    <Inputer v-model="localDiscount" placeHolder="discount" :img="icons['discount']" @onBlur="calculerPrix" />

    <span v-if="disLog">
      {{ disLog }}
    </span>

    <OrderProductsList :products="props.products" :allProducts="resultProduct" @update:products="updateProducts" />

    <!-- PRICE CORRECTOR -->
    <div class="price-corrector">

      <RectBtn v-if="negativity" style="width: 10%;" iconColor="#ff5555" svg="minus" @click:ok="negativity = false" />

      <RectBtn v-else style="width: 10%;" iconColor="var(--color-blumy)" svg="plus" @click:ok="negativity = true" />

      <Inputer v-model="priceCorrection" placeHolder="price correction" type="number" :img="icons['discount']" />


      <RectBtn style="width: 10%;" iconColor="var(--color-greeny)" svg="check" @click:ok="applyPriceCorrection" />
    </div>


 

    <!-- TOTAL DISPLAY -->
    <div class="boxColumn">
      <div class="total-box">
        <span>Total :</span>
        <strong>{{ prixTotal }} DA</strong>
      </div>
      <CallToAction v-if="!isUpdating" class="total-box" :text="t('save')" :svg="icons['check']"
        @clicked="updateOrder" />
      <span v-else>
        updating....
      </span>


    </div>




    <!--ProductSelector /-->

  </div>
</template>

<script setup>
import Inputer from '../bloc/input.vue'
import Selector from '../bloc/select.vue'
import icons from '../../../public/icons.json'
import Switcher from '../newBloc/switcher.vue'
//import ProductSelector from '../productSelector.vue'
import OrderProductsList from '../orderProductsList.vue'
import CallToAction from '../bloc/callToActionBtn.vue'
import RectBtn from '../newBloc/rectBtn.vue';

const { t } = useLang()

const deskLabel = computed(() => {
  const fee = props.selectedFees?.tarif_stopdesk

  
  return fee ? `${t('stop desk')} (${fee} DA)` : t('stop desk')
})

const homeLabel = computed(() => {
  const fee = props.selectedFees?.tarif
  
  return fee ? `${t('home')} (${fee} DA)` : t('home')
})

const props = defineProps({
  id: { type: Number, default: 0 },
  phone: { type: String, default: '' },
  name: { type: String, default: '' },

  wilaya: { type: String, default: '' },
  commune: { type: String, default: '' },

  wilayas: {
    type: Array,
    default: () => [
      { value: 'confirmed', label: 'Confirm', img: '' },
      { value: 'waiting', label: 'Wait', img: '' },
      { value: 'shipping', label: 'Deliver', img: '' },
      { value: 'unreaching', label: 'Unreachable', img: '' },
    ]
  },

  communes: {
    type: Array,
    default: () => [
      { value: 'confirmed', label: 'Confirm', img: '' },
      { value: 'waiting', label: 'Wait', img: '' },
      { value: 'shipping', label: 'Deliver', img: '' },
      { value: 'unreaching', label: 'Unreachable', img: '' },
    ]
  },

  adresse: { type: String, default: '' },
  discount: { type: String, default: '' },
  deliveryMethod: { type: String, default: '' },
  deliveryFees: { type: Number, default: 0 },
  deliveryType: { type: Number, default: 0 },
  products: { type: Array, default: () => [] },
  resultProduct: { type: Object, default: () => { } },
  deliverySty: { type: Array, default: () => [] },
  selectedFees: { type: Object, default: () => {} },
  total: { type: Number, default: 0 },
  isDesk: { type: Boolean, default: false },
})

const localFees = ref()
const localCommune = ref(props.commune)
const localWilaya = ref(props.wilaya)
const prixTotal = ref(props.total)
const initialFees = ref(props.deliveryFees)


watch(() => props.deliveryFees, v => {
  localFees.value = v
  calculerPrix()
})

watch(() => props.total, v => {
  prixTotal.value = Number(v) || 0
})



const localType = ref(props.deliveryType)

watch(() => props.deliveryType, v => {
  localType.value = Number(v)
})

onMounted(() => {
  localFees.value = props.deliveryFees
  prixTotal.value = props.total
  localCommune.value = props.commune
  localWilaya.value = props.wilaya


})



watch(() => props.commune, v => {
  if(v) {
    localCommune.value = v
  }
  calculerPrix()
})

watch(() => props.wilaya, v => {
  // Only emit if needed, or just sync local
  if(v) {
    localWilaya.value = v
  }
  calculerPrix()
})


const productList = ref([])

const updateProducts = (newList) => {
  productList.value = newList
  emit("update:products", newList)

  nextTick(() => calculerPrix())

}


const emit = defineEmits([
  'update:phone',
  'update:name',
  'update:wilaya',
  'update:commune',
  'update:adresse',
  'update:deskFees',
  'update:homeFees',
  'update:sty',
  'updated',
  'update:products'
])

/* LOCAL STATE (pour v-model interne) */
const localPhone = ref(props.phone)
const localName = ref(props.name)
const localAdresse = ref(props.adresse)
const localDiscount = ref(props.discount)


const isUpdating = ref(false)
const discount = ref()
const discountV = ref()
const isSuccess = ref(false)
const disLog = ref('')
const negativity = ref(false)



watch(localPhone, v => emit('update:phone', v))
watch(localName, v => emit('update:name', v))
watch(localAdresse, v => emit('update:adresse', v))




/* Selectors */
const showWilaya = ref(false)
const showCommune = ref(false)
const showSty = ref(false)

const updateWilaya = (vl) => {
  emit('update:wilaya', vl)
  localWilaya.value = vl
  calculerPrix()
}
const updateCommune = (vl) => {
  emit('update:commune', vl)
    calculerPrix()
}
const updateSty = (vl) => emit('update:sty', vl)
const updateDeskFees = () => {
  localType.value = 1

  localFees.value = props.selectedFees?.tarif_stopdesk

  emit('update:deskFees')
  calculerPrix()
}
const updateHomeFees = () => {
  localType.value = 0
  localFees.value = props.selectedFees?.tarif
  emit('update:homeFees')
  calculerPrix()
}

const priceCorrection = ref(0)

function applyPriceCorrection() {
  var correction = parseFloat(priceCorrection.value || 0)

  

  if (!isNaN(correction)) {
    if(negativity.value == true && correction > 0) {
      correction = -1 * correction
    }
    else if (negativity.value == false && correction < 0) {
      correction = -1 * correction
    }
    prixTotal.value = (prixTotal.value || 0) + correction
  }

  // reset or not ? (choisis)
  priceCorrection.value = 0
}


async function updateOrder() {
  // Préparer les données à envoyer
  isUpdating.value = true

  let formattedProducts = []

  const listSrc = productList.value.length > 0 ? productList.value : props.products

  if (!listSrc || listSrc.length === 0) {
    alert(t('please add at least one product'))
    isUpdating.value = false
    return
  }

  formattedProducts = listSrc.map(p => ({
    idP: parseInt(p.id),
    idM: parseInt(p.model_id ?? p.idM ?? p.id),
    name: p.productName || p.name,
    image: p.image,
    price: parseFloat(p.price) || 0,
    qty: parseInt(p.qty) || 0,
    ref: p.ref || "",
    total: parseFloat(p.total) || (parseFloat(p.price) * parseInt(p.qty) || 0), // fallback si NaN
    selected: (p.items || []).map(d => ({
      id: parseInt(d.id),
      color: d.color,
      colorName: d.color_name || d.colorName,
      size: d.size,
      qty: parseInt(d.qty) || 0,
      total: parseFloat(d.total) || 0,
      promo: parseFloat(d.promo) || 0,
      indx: parseInt(d.indx || 0)
    }))
  }));

  let finalCommune = localCommune.value
  if (typeof finalCommune === 'object' && finalCommune !== null) {
    finalCommune = finalCommune.nom || finalCommune.name || ""
  }

  // Ensure values are strings if undefined
  const finalWilaya = localWilaya.value || "";
  const finalName = localName.value || "";
  const finalPhone = localPhone.value || "";
  const finalAdresse = localAdresse.value || "";

  const payload = {
    orderId: props.id,
    name: finalName,
    phone: finalPhone,
    wilaya: finalWilaya,
    commune: finalCommune || "", // Ensure empty string if null
    adresse: finalAdresse,
    deliveryMethod: props.deliveryMethod,
    deliveryType: localType.value,
    deliveryFees: localFees.value,
    discountCode: localDiscount.value,
    discountValue: discount.value?.value || 0,
    total: prixTotal.value,
    products: formattedProducts
  };


  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=editOrder', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      console.error("Erreur réseau :", response.statusText);
      isUpdating.value = false
      return;
    }
    //const result = await response.text();

    const result = await response.json();

    if (result.success) {
      isUpdating.value = false
      emit('updated')
    } else {
      console.error("Erreur serveur :", result.message);
      isUpdating.value = false
    }

  } catch (err) {
    console.error("Erreur lors de l'update :", err);
    isUpdating.value = false
  }
}




async function calculerPrix() {

  isUpdating.value = true;

  prixTotal.value = 0

  let prixQty = 0;

  // Recalculate fees from selectedFees if available, otherwise trust localFees/props.deliveryFees
  // However, since parent manages fees via props.deliveryFees based on type, we should primarily trust that.
  // But if we want instant feedback on type switch before parent update?
  // Parent update is triggered via emit and prop update.
  // So relying on localFees (which is synced with props.deliveryFees) is safer.
  // We remove the conflicting logic.


  if (productList.value.length > 0) {

    for (const i of productList.value) {
      for (const ii of i.items) {
        const qty = parseFloat(ii.qty) || 0;

        if (ii.promo) {
          prixQty += parseFloat(ii.promo) * qty;
        } else {
          prixQty += parseFloat(ii.total) * qty;
        }
      }
    }

    prixTotal.value = prixQty + parseFloat(localFees.value)
  } else {
    prixQty = props.total - initialFees.value
    prixTotal.value = prixQty + parseFloat(localFees.value)
  }

  


  if (localDiscount.value) {
    const postBody = JSON.stringify({
      code: localDiscount.value,
      time: Math.floor(Date.now() / 1000),
      phone: localPhone.value
    });






    const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=testDiscount', {
      method: 'POST',
      body: postBody,
    });
    if (!response2.ok) {
      disLog.value = "error in response";
      isUpdating.value = false;
      return;
    }
    const textResponse = await response2.json();  // Récupérer la réponse en texte
    if (textResponse.success) {
      var type = '';
      if (textResponse.message === '1' && prixQty) {
        if (textResponse.data.type === 0) {
          type = '%';
          discount.value = { value: parseFloat(textResponse.data.value), type: textResponse.data.type };
          prixTotal.value -= (prixQty / 100) * parseFloat(discount.value.value);
        } else {
          type = 'DA';
          discount.value = { value: parseFloat(textResponse.data.value), type: textResponse.data.type };
          prixTotal.value -= parseFloat(discount.value.value);
        }
        discountV.value = `${textResponse.data.value} ${type}`;
        disLog.value = `CODE : - ${textResponse.data.value} ${type}`;
        isSuccess.value = 1
      } else {
        if (textResponse.message === '1') {
          if (textResponse.data.type === 0) {
            type = '%';
            discount.value = { value: parseFloat(textResponse.data.value), type: textResponse.data.type };
            prixTotal.value -= (prixQty / 100) * parseFloat(discount.value.value);
          } else {
            type = 'da';
            discount.value = { value: parseFloat(textResponse.data.value), type: textResponse.data.type };
            prixTotal.value -= parseFloat(discount.value.value);
          }
          discountV.value = `${textResponse.data.value} ${type}`;
          disLog.value = `code: - ${textResponse.data.value} ${type}`;
          isSuccess.value = 1;
        } else if (textResponse.message === '2') {
          disLog.value = 'the validity period has expired';
          isSuccess.value = 0;
        } else if (textResponse.message === '3') {
          disLog.value = 'you have already used the code with your phone';
          isSuccess.value = 0;
        } else if (textResponse.message === '4') {
          disLog.value = 'the promo code has expired';
          isSuccess.value = 0;
        } else if (textResponse.message === '5') {
          disLog.value = 'invalid code';
          isSuccess.value = 0;
        } else if (textResponse.message === '6') {
          disLog.value = 'code unavailable';
          isSuccess.value = 0;
        } else if (textResponse.message === '7') {
          disLog.value = 'phone number is required';
          isSuccess.value = 0;
        } else if (textResponse.message === '8') {
          disLog.value = 'sorry, no promo code available';
          isSuccess.value = 0;
        }
      }

    } else {
      if (textResponse.message === '2') {
        disLog.value = 'the validity period has expired';
        isSuccess.value = 0;
      } else if (textResponse.message === '3') {
        disLog.value = 'you have already used the code with your phone';
        isSuccess.value = 0;
      } else if (textResponse.message === '4') {
        disLog.value = 'the promo code has expired';
        isSuccess.value = 0;
      } else if (textResponse.message === '5') {
        disLog.value = 'invalid code';
        isSuccess.value = 0;
      } else if (textResponse.message === '6') {
        disLog.value = 'code unavailable';
        isSuccess.value = 0;
      } else if (textResponse.message === '7') {
        disLog.value = 'phone number is required';
        isSuccess.value = 0;
      } else if (textResponse.message === '8') {
        disLog.value = 'sorry, no promo code available';
        isSuccess.value = 0;
      }
    }

    isUpdating.value = false;
  } else {
    isSuccess.value = 0
    isUpdating.value = false;
  }



}


</script>


<style>
.orderEdit {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

.boxRow {
  display: flex;
  justify-content: center;
  align-items: center;
}

.boxColumn {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 100%;
  min-width: 100px;
  flex-direction: column;
}

.boxColumn h3 {
  font-size: 16px;
  font-weight: bold;
}

.price-corrector {
  width: 100%;
  margin-top: 15px;
  display: flex;
  gap: 5px;
  display: flex;
  justify-content: center;
  align-items: center;
}



.total-box {
  margin-top: 20px;
  padding: 14px 18px;
  width: 90%;
  border-radius: 16px;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
  text-align: center;
  display: flex;
  justify-content: space-between;
  font-size: 18px;
  font-weight: 600;
}

.total-box strong {
  font-size: 20px;
  color: var(--color-primary, #4facfe);
}
</style>
