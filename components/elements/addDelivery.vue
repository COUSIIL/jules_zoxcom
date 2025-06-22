<template>

  <LoaderBlack v-if="isSaving" width="100px" />
  <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>

    <div class="boxAddDelivery">
        <div class="contentBox">
          <div class="contentForm">
            <Selector :options="deliveryList" :placeHolder="t('method')" :img="icons['delivery']" @update:modelLabel="selectedDelivery" :required="true"/>
            <Inputer :placeHolder="t('delivery name')" type="text" :img="icons['label']" v-model="deliveryName" :required="true" style="max-width: 250px;" />
            <Selector :options="wilaya" :placeHolder="t('drop-off area')" :img="icons['drop']" @update:modelLabel="selectedDropArea" :required="true" />
          </div>
          <div class="contentForm">
            <div class="contentLine" @click="returnFree = !returnFree">
              <div v-html="icons['deliveryReturn']">

              </div>
              {{ t('free return') }}
              <Radio :selected="returnFree"/>
            </div>
            <div class="contentLine" @click="includeFees = !includeFees">
              <div v-html="icons['invoice']">

              </div>
              {{ t('include shipping fees in delivery form') }}
              <Radio :selected="includeFees"/>
            </div>
            <div style="width: 100%; 
              display: flex; 
              justify-content:center; 
              align-items: center;">
              <Gbtn :text="t('cancel')" @click="cancel" color="var(--color-rady)" :svg="icons['x']"/>
              <Gbtn :text="t('save')" @click="save" color="var(--color-greny)" :svg="icons['check']"/>
              
            </div>
            
            
          </div>

          
          
        </div>


        

        

        
    </div>
</template>

<script setup>

import LoaderBlack from '../elements/animations/loaderBlack.vue';
import Editor from '../editor.vue';
import Radio from '../elements/bloc/radio.vue';
import Inputer from '../elements/bloc/input.vue';
import InputBtn from '../elements/bloc/inputBtn.vue';
import Lister from '../elements/bloc/list.vue';
import Selector from '../elements/bloc/select.vue';
import Gbtn from '../elements/bloc/gBtn.vue';
import Message from '../elements/bloc/message.vue';
import CallToAction from '../elements/bloc/callToActionBtn.vue';
import CancelBtn from '../elements/bloc/cancelBtn.vue';
import Explorer from '../elements/explorer.vue';
import EditCat from '../elements/editCategory.vue';
import AddDelivery from '../elements/addDelivery.vue';

import icons from '~/public/icons.json'

import { DotLottieVue } from '@lottiefiles/dotlottie-vue'
import { onMounted } from 'vue';

const { t } = useLang()

const deliveryListed = ref([{label: 'ups', value: 'testUps', img: 'ups.svg'}, {label: 'anderson', value: 'testAnderson', img: 'anderson.png'}, {label: 'yalidine', value: 'testYalidine', img: 'yalidine.png'}, {label: 'guepex', value: 'testGuepex', img: 'guepex.png'}])

var deliveryList = ref([])

var deliveryName = ref('')
var dropArea = ref('')

var selectedDeliveryId = ref()

var returnFree = ref(false)
var includeFees = ref(false)

var isSaving = ref(false)
var isMessage = ref(false)
var message = ref('')

const wilaya = ref([
            {value: 1, label: 'Adrar', homePrice: 1600, deskPrice: 800},
            {value: 2, label: 'Chlef', homePrice: 900, deskPrice: 450},
            {value: 3, label: 'Laghouat', homePrice: 1200, deskPrice: 600},
            {value: 4, label: 'Oum El Bouaghi', homePrice: 900, deskPrice: 350},
            {value: 5, label: 'Batna', homePrice: 850, deskPrice: 400},
            {value: 6, label: 'Béjaïa', homePrice: 850, deskPrice: 400},
            {value: 7, label: 'Biskra', homePrice: 850, deskPrice: 350},
            {value: 8, label: 'Béchar', homePrice: 1400, deskPrice: 800},
            {value: 9, label: 'Blida', homePrice: 800, deskPrice: 350},
            {value: 10, label: 'Bouira', homePrice: 850, deskPrice: 400},
            {value: 11, label: 'Tamanrasset', homePrice: 1600, deskPrice: 1000},
            {value: 12, label: 'Tébessa', homePrice: 800, deskPrice: 600},
            {value: 13, label: 'Tlemcen', homePrice: 900, deskPrice: 350},
            {value: 14, label: 'Tiaret', homePrice: 950, deskPrice: 400},
            {value: 15, label: 'Tizi Ouzou', homePrice: 850, deskPrice: 400},
            {value: 16, label: 'Alger', homePrice: 750, deskPrice: 350},
            {value: 17, label: 'Djelfa', homePrice: 1200, deskPrice: 600},
            {value: 18, label: 'Jijel', homePrice: 850, deskPrice: 400},
            {value: 19, label: 'Sétif', homePrice: 850, deskPrice: 350},
            {value: 20, label: 'Saïda', homePrice: 1000, deskPrice: 400},
            {value: 21, label: 'Skikda', homePrice: 850, deskPrice: 400},
            {value: 22, label: 'Sidi Bel Abbès', homePrice: 900, deskPrice: 400},
            {value: 23, label: 'Annaba', homePrice: 850, deskPrice: 350},
            {value: 24, label: 'Guelma', homePrice: 850, deskPrice: 400},
            {value: 25, label: 'Constantine', homePrice: 850, deskPrice: 400},
            {value: 26, label: 'Médéa', homePrice: 850, deskPrice: 400},
            {value: 27, label: 'Mostaganem', homePrice: 900, deskPrice: 400},
            {value: 28, label: "M'Sila", homePrice: 900, deskPrice: 350},
            {value: 29, label: 'Mascara', homePrice: 950, deskPrice: 400},
            {value: 30, label: 'Ouargla', homePrice: 1200, deskPrice: 600},
            {value: 31, label: 'Oran', homePrice: 900, deskPrice: 350},
            {value: 32, label: 'El Bayadh', homePrice: 1400, deskPrice: 600},
            {value: 33, label: 'Illizi', homePrice: 1400, deskPrice: 600},
            {value: 34, label: 'Bordj Bou Arreridj', homePrice: 850, deskPrice: 400},
            {value: 35, label: 'Boumerdès', homePrice: 650, deskPrice: 400},
            {value: 36, label: 'El Tarf', homePrice: 850, deskPrice: 400},
            {value: 37, label: 'Tindouf', homePrice: 1200, deskPrice: 800},
            {value: 38, label: 'Tissemsilt', homePrice: 950, deskPrice: 400},
            {value: 39, label: 'El Oued', homePrice: 1000, deskPrice: 600},
            {value: 40, label: 'Khenchela', homePrice: 800, deskPrice: 350},
            {value: 41, label: 'Souk Ahras', homePrice: 850, deskPrice: 400},
            {value: 42, label: 'Tipaza', homePrice: 850, deskPrice: 350},
            {value: 43, label: 'Mila', homePrice: 800, deskPrice: 350},
            {value: 44, label: 'Aïn Defla', homePrice: 900, deskPrice: 400},
            {value: 45, label: 'Naâma', homePrice: 1400, deskPrice: 800},
            {value: 46, label: 'Aïn Témouchent', homePrice: 950, deskPrice: 400},
            {value: 47, label: 'Ghardaïa', homePrice: 1200, deskPrice: 600},
            {value: 48, label: 'Relizane', homePrice: 950, deskPrice: 400},
            {value: 49, label: 'Timimoun', homePrice: 1200, deskPrice: 800},
            {value: 50, label: 'Bordj Badji Mokhtar', homePrice: null, deskPrice: null},
            {value: 51, label: 'Ouled Djellal', homePrice: 1200, deskPrice: 800},
            {value: 52, label: 'Béni Abbès', homePrice: 1200, deskPrice: 800},
            {value: 53, label: "In Salah", homePrice: 1200, deskPrice: 800},
            {value: 54, label: "In Guezzam", homePrice: null, deskPrice: null},
            {value: 55, label: 'Touggourt', homePrice: 1200, deskPrice: 800},
            {value: 56, label: 'Djanet', homePrice: 1200, deskPrice: 800},
            {value: 57, label: "El M'Ghair", homePrice: 1200, deskPrice: 800},
            {value: 58, label: 'El Meniaa', homePrice: 1200, deskPrice: 800},
        ])

const emit = defineEmits(['cancel', 'save'])
    

function cancel() {

  emit('cancel')
}

const save = async () => {
  isSaving.value = true
  if(!selectedDeliveryId.value) {
    message.value = "pleas chose method"
    isMessage.value = true
    isSaving.value = false
    return
  }
  if(!deliveryName.value) {
    message.value = "pleas enter delivery name"
    isMessage.value = true
    isSaving.value = false
    return
  }
  if(!dropArea.value) {
    message.value = "pleas chose wilaya drop area"
    isMessage.value = true
    isSaving.value = false
    return
  }
  const payload = {
    method: deliveryList.value.find(d => d.label === deliveryName.value)?.value || 'custom',
    delivery_name: deliveryName.value,
    drop_area_name: dropArea.value,
    drop_area_id: wilaya.value.find(w => w.label === dropArea.value)?.value || null,
    return_free: returnFree.value,
    include_fees: includeFees.value,
  };

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=addDeliveryMethod', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  });

  if(!response.ok) {
    message.value = "error in response"
    isMessage.value = true
    isSaving.value = false
  }

  const result = await response.json();
  if (result.success) {
    
    message.value = result.message
    isMessage.value = true
    emit('cancel')
    isSaving.value = false
  } else {
    message.value = result.message
    isMessage.value = true
    isSaving.value = false
  }
};


const testDelivery = async(method) => {
  try {
    var response
      response = await fetch(`https://management.hoggari.com/backend/api.php?action=${method}`, {
      method: 'GET',
    });
    

    if (!response.ok) {
      //isMessage.value = true
      //message.value = t('error in request get products')
      isMounted.value = true
      return false
    }

    const result = await response.json();

    if (result.success && result.data.work == 1) {
      return true
    } else {
      return false
    }
  } catch (e) {
    //isMessage.value = true
    //message.value = t(e)
    return false
  }
}

const selectedDelivery = (value) => {
  selectedDeliveryId.value = value
  deliveryName.value = value
}

const selectedDropArea = (value) => {
  dropArea.value = value
}

onMounted(async () => {
  const results = await Promise.all(
    deliveryListed.value.map(async (method) => {
      const isValid = await testDelivery(method['value']);
      return isValid
        ? {
            label: method['label'],
            value: method['value'],
            img: method['img'],
          }
        : null;
    })
  );
  

  deliveryList.value = results.filter(Boolean); // Retire les `null`
  deliveryList.value.push({label: 'custom', value: 'custom', img: 'z.svg'})
});





</script>

<style>

.boxAddDelivery {
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-direction: column;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .boxAddDelivery{
    background-color: var(--color-darkly);
}

.boxAddDelivery p {
    margin-inline: 10px;
}

.contentBox {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap; /* Clé pour mobile */
    gap: 10px;
}

.contentForm {
  flex: 1;
  max-width: 250px;
  display: flex;
  justify-content: center;
  flex-direction: column;
  align-items: stretch;
  gap: 10px;
}

.contentRow {
  width: calc(100% - 20px);
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
  align-items: center;
  margin: 5px;
  padding: 10px;
  background-color: var(--color-whity);
  border-radius: 12px;
  max-width: 650px;
}
.dark .contentRow {
  background-color: var(--color-darkow);
}

.contentMiniBox {
  width: 100px;
  height: 100px;
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 5px;
  padding: 5px;
  background-color: var(--color-whity);
  border-radius: 12px;
  cursor: pointer;
}
.contentMiniBox:hover {
  background-color: var(--color-whiby);
}
.dark .contentMiniBox {
  background-color: var(--color-darkow);
}
.dark .contentMiniBox:hover {
  background-color: var(--color-whizy);
}

.contentLine {
  width: calc(100% - 20px);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: var(--color-whity);
  border-radius: 12px;
  margin: 5px;
  padding: 10px;
  cursor: pointer;
}
.dark .contentLine {
  background-color: var(--color-darkow);
}

</style>