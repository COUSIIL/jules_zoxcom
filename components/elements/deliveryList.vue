<template>
    <LoaderBlack v-if="isSaving" width="100px" />
    <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
    <Confirm :isVisible="toConfirm" @confirm="deleteDeliveryMethod(parseInt(deliverId))" @cancel="toConfirm = false" />
    <Confirm :isVisible="toSave" @confirm="save(saveId)" @cancel="toSave = false" />
    

    <div v-if="isMounted" style="width: 100%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <div class="boxDelivery" v-for="(delivery, index) in deliveryList" :key="index">
            <div class="boxDeliveryTitle">
                <div class="bigBox">
                    <div class="directBox">
                        {{ delivery.delivery_name }}
                        <Radio :selected="parseInt(delivery.active)" @changed="updateActiveDelivery(parseInt(delivery.id),delivery.active, index)" />
                    </div>
                    <div class="miniBox">
                        {{ delivery.created_at }}
                    </div>
                    
                </div>
                
                
                
                <div class="bigBox">
                    <div class="directBox">
                        {{ t('drop area') }}
                    </div>
                    <div class="miniBox">
                        {{ delivery.drop_area_id }} {{ delivery.drop_area_name }}
                    </div>
                </div>

                <div class="rowBox">
                    
                    <button class="directBtn" @click="edit(index)">
                        <div v-html="resizeSvg(icons['edit'], 20, 20)" >

                        </div>
                        {{ t('edit') }}
                    </button>

                    <button class="directBtn" style="background-color: var(--color-greny)" type="button" @click="toSaving(index)">
                        <div v-html="resizeSvg(icons['check'], 20, 20)" >

                        </div>
                    </button>

                    <button class="directBtn" type="button" @click="toConfirm = true, deliverId = parseInt(delivery.id)">
                        <div v-html="resizedImgDelete" >

                        </div>
                    </button>

                    
                </div>
            </div>

            <div v-if="delivery.isMore && delivery.wilaya" class="contentDelivery">
                <div style="width: 100%;" v-for="(wilaya, index2) in delivery.delivery_info" :key="index2">
                    <div class="wilayaBox" @click="wilaya.wilaya_active = !wilaya.wilaya_active" style="cursor: pointer;">
                        <p
                        :title="`${wilaya.wilaya_id} ${wilaya.wilaya_name}`"
                        style="max-width: 200px; min-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 2px;"
                        >
                            {{ wilaya.wilaya_id }} {{ wilaya.wilaya_name }}
                        </p>

                        <Radio :selected="wilaya.wilaya_active" @changed="wilaya.wilaya_active = !wilaya.wilaya_active" />
                    </div>
                    <div class="wilayaBox">
                        <div style="width: 50%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <Inputer style="max-width: 200px;" type="number" :lock="false" :placeHolder="t('house')" :holder="t('price')" v-model="wilaya.home_price"/>
                                <Radio :selected="wilaya.home_active" @changed="wilaya.home_active = !wilaya.home_active" />
                            </div>
                            <Selector :options="deliveryListed" :placeHolder="t('delivery')" :img="icons['delivery']" :modelValue="wilaya.delivery_home" :modelImage="resolvedImage(index, index2, 'home')" :disabled="false" @update:modelLabel="val => wilaya.delivery_home = val"/>
                        </div>
                        
                        <div style="width: 50%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                            <div style="display: flex; justify-content: center; align-items: center;">
                                <Inputer style="max-width: 200px;" type="number" :lock="false" :placeHolder="t('desk')" :holder="t('price')" v-model="wilaya.desk_price"/>
                                <Radio :selected="wilaya.desk_active" @changed="wilaya.desk_active = !wilaya.desk_active" />
                            </div>
                            <Selector :options="deliveryListed" :placeHolder="t('delivery')" :img="icons['delivery']" :modelValue="wilaya.delivery_desk" :modelImage="resolvedImage(index, index2, 'desk')" :disabled="false" @update:modelLabel="val => wilaya.delivery_desk = val" @update:modelImage="val => delivery.delivery_content.img = val"/>
                        </div>
                        
                        

                        
                    </div>

                        
                        
                    
                    
                </div>
                

            </div>
            

            
        </div>
        
    </div>



</template>

<script setup>

import LoaderBlack from '../elements/animations/loaderBlack.vue';
import Radio from './bloc/radio.vue';
import Inputer from './bloc/input.vue';
import icons from '~/public/icons.json'
import Selector from './bloc/select.vue';
import Message from '../elements/bloc/message.vue';
import Confirm from '../elements/bloc/confirm.vue';

import { computed, ref, watch } from 'vue'

const { t } = useLang()

var deliveryList = ref([])
const toConfirm = ref(false)
const toSave = ref(false)
const isMounted = ref(false)
const isDeliting = ref(false)
const isMessage = ref(false)
const isSaving = ref(false)
const message = ref('')
const saveId = ref()

const deliverId = ref()

const props = defineProps({
  update: {type: Boolean, default: false},

})

watch(() => props.update, newVal => {
  getDelivery()
})

const iconMap = {
  ups: 'ups.svg',
  anderson: 'anderson.png',
  yalidine: 'yalidine.png',
  guepex: 'guepex.png',
  custom: 'z.svg'
}



var resolvedImage = (id, index, type) => {
    if(type === 'home') {
        if (deliveryList.value[id].delivery_info[index].delivery_home === 'ups') {
            return iconMap['ups']
        } else if (deliveryList.value[id].delivery_info[index].delivery_home === 'anderson') {
            return iconMap['anderson']
        } else if (deliveryList.value[id].delivery_info[index].delivery_home === 'yalidine') {
            return iconMap['yalidine']
        } else if (deliveryList.value[id].delivery_info[index].delivery_home === 'guepex') {
            return iconMap['guepex']
        } else if (deliveryList.value[id].delivery_info[index].delivery_home === 'custom') {
            return iconMap['custom']
        }
    } else if (type === 'desk') {
        if (deliveryList.value[id].delivery_info[index].delivery_desk === 'ups') {
            return iconMap['ups']
        } else if (deliveryList.value[id].delivery_info[index].delivery_desk === 'anderson') {
            return iconMap['anderson']
        } else if (deliveryList.value[id].delivery_info[index].delivery_desk === 'yalidine') {
            return iconMap['yalidine']
        } else if (deliveryList.value[id].delivery_info[index].delivery_desk === 'guepex') {
            return iconMap['guepex']
        } else if (deliveryList.value[id].delivery_info[index].delivery_desk === 'custom') {
            return iconMap['custom']
        }
    }
    
    return svg
}


const deliveryListed = ref([{label: 'ups', value: 'testUps', img: 'ups.svg'}, {label: 'anderson', value: 'testAnderson', img: 'anderson.png'}, {label: 'yalidine', value: 'testYalidine', img: 'yalidine.png'}, {label: 'guepex', value: 'testGuepex', img: 'guepex.png'}])

const emit = defineEmits(['update:modelValue'])

const resizedImgDelete = computed(() => {
  if (!icons['deleteImg']) return ''
  return icons['deleteImg']
    .replace(/width="[^"]+"/, `width="${20}"`)
    .replace(/height="[^"]+"/, `height="${20}"`)
    .replace(/color="[^"]+"/, `color="${'var(--color-rady)'}"`)
})

var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
}





const edit = async (index) => {
    isSaving.value = true
    if (deliveryList.value[index].isMore){
        deliveryList.value[index].isMore = !deliveryList.value[index].isMore
    } else {
        deliveryList.value[index].isMore = true;
    }

    deliveryList.value[index].wilaya = await getWilaya(deliveryList.value[index].delivery_name)
    for (let i = 0; i < deliveryList.value[index].wilaya.length; i++) {
        deliveryList.value[index].wilaya[i] = {
            ...deliveryList.value[index].wilaya[i], // garder les données existantes
            home_price: null,
            desk_price: null,
            desk_active: true,
            home_active: true,
            wilaya_active: true
        }
    }
    if(deliveryList.value[index].delivery_name === 'ups') {
        deliveryList.value[index].delivery_content = {label: 'ups', value: 'testUps', img: 'ups.svg'}
    } else if(deliveryList.value[index].delivery_name === 'anderson') {
        deliveryList.value[index].delivery_content = {label: 'anderson', value: 'testAnderson', img: 'anderson.png'}
    } else if(deliveryList.value[index].delivery_name === 'yalidine') {
        deliveryList.value[index].delivery_content = {label: 'yalidine', value: 'testYalidine', img: 'yalidine.png'}
    } else if(deliveryList.value[index].delivery_name === 'guepex') {
        deliveryList.value[index].delivery_content = {label: 'guepex', value: 'testGuepex', img: 'guepex.png'}
    } else if(deliveryList.value[index].delivery_name === 'custom') {
        deliveryList.value[index].delivery_content = {label: 'custom', value: 'testCustom', img: 'z.svg'}
    }


}

const toSaving = (id) => {
    saveId.value = id
    toSave.value = true
}

const save = async (index) => {
  toSave.value = false
  isSaving.value = true

  const delivery = deliveryList.value[index]

  /* 1. Validations
  if (!selectedDeliveryId.value) {
    message.value = "please choose method"
    isMessage.value = true
    isSaving.value = false
    return
  }
  if (!deliveryName.value) {
    message.value = "please enter delivery name"
    isMessage.value = true
    isSaving.value = false
    return
  }
  if (!dropArea.value) {
    message.value = "please choose wilaya drop area"
    isMessage.value = true
    isSaving.value = false
    return
  }
  */

  // 3. Construit le payload avec les bonnes sources
  const pureWilayas = JSON.stringify(delivery.delivery_info)
  const pureContent = JSON.stringify(delivery.delivery_content)

  const payload = {
    method:           delivery.method,
    delivery_name:    delivery.delivery_name,
    drop_area_name:   delivery.drop_area_name,
    // Assure-toi d'utiliser la bonne liste statique ici (wilayaOptions ou wilaya)
    drop_area_id:     parseInt(delivery.drop_area_id),
    return_free:      parseInt(delivery.return_free) ? 1 : 0,
    include_fees:     parseInt(delivery.include_fees) ? 1 : 0,
    delivery_info:    pureWilayas,    // doit être un Array d'objets JSON-serializables
    delivery_content: pureContent
  }

  console.log('payload: ', payload)


  // 4. Envoi
  try {
    const response = await fetch(
    'https://management.hoggari.com/backend/api.php?action=addDeliveryMethod',
      {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify(payload)
      }
    )

    if (!response.ok) {
      message.value   = "error in response"
      isMessage.value = true
      isSaving.value  = false
      return   // <<-- Interrompt ici pour ne pas continuer
    }

    // 5. Traitement de la réponse
    const result = await response.json()
    message.value   = result.message
    isMessage.value = true
    isSaving.value  = false

    if (result.success) {
      message.value   = result.message
      isMessage.value = true
      isSaving.value  = false
      //emit('save')
    }
  } catch (e) {
    message.value   = e
    isMessage.value = true
    isSaving.value  = false
  }

}

const getWilaya = async(method) => {
    if(method == 'ups') {
        method = 'Ups'
    }else if(method == 'anderson') {
        method = 'Anderson'
    }
    else if(method == 'yalidine') {
        method = 'Yalidine'
    }
    else if(method == 'geupex') {
        method = 'Geupex'
    }
    else if(method == 'custom') {
        method = 'Custom'
    }
    try {
        var response
        response = await fetch(`https://management.hoggari.com/backend/api.php?action=get${method}Wilaya`, {
        method: 'GET',
        });
        

        if (!response.ok) {
        isMessage.value = true
        message.value = t('error in request get wilaya')
        isSaving.value = false
        return false
        }

        const result = await response.json();
        isSaving.value = false
        return result
        
    }catch (e) {
        isMessage.value = true
        message.value = t(e)
        isSaving.value = false
    }
}

const deleteDeliveryMethod = async(id) => {

    isDeliting.value = true
    toConfirm.value = false

    const payload = {
        id: [id],
    }
    try{
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=deleteDeliveryMethod', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        if (!response.ok) {
        isMessage.value = true
        message.value = t('error in request get products')
        isDeliting.value = false
        return
        }

        const result = await response.json();

        
        if(result.success) {
            isMessage.value = true
            message.value = t(result.message)
            isDeliting.value = false
            getDelivery()
        } else {
            isMessage.value = true
            message.value = t(result.message)
            isDeliting.value = false
        }


    }catch (e) {
        isMessage.value = true
        message.value = t(e)   
    }
}


const updateActiveDelivery = async(id, value, index) => {

    if (deliveryList.value[index].active === '1') {
        deliveryList.value[index].active = true
    } else if (deliveryList.value[index].active === '0') {
        deliveryList.value[index].active = false
    }
    deliveryList.value[index].active = !deliveryList.value[index].active

    const payload = {
        id: id,
        status: 'active',
        value: deliveryList.value[index].active
    }

    console.log('payload: ', payload)

    try{
        const response = await fetch(`https://management.hoggari.com/backend/api.php?action=updateActiveDelivery`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        if (!response.ok) {
        isMessage.value = true
        message.value = t('error in update activator')
        return
        }

        const result = await response.json();

        
        if(!result.success) {
            deliveryList.value[index].active = !deliveryList.value[index].active
        }
        isMessage.value = true
        message.value = t(result.message)

    }catch (e) {
        isMessage.value = true
        message.value = t(e)
        deliveryList.value[index].active = !deliveryList.value[index].active
    }

    if (deliveryList.value[index].active || deliveryList.value[index].active === '1') {
        deliveryList.value[index].active = '1'
    } else if (!deliveryList.value[index].active || deliveryList.value[index].active === '0') {
        deliveryList.value[index].active = '0'
    }
}


const getDelivery = async() => {
  try {
    var response
      response = await fetch(`https://management.hoggari.com/backend/api.php?action=getDeliveryMethod`, {
      method: 'GET',
    });
    

    if (!response.ok) {
      //isMessage.value = true
      //message.value = t('error in request get products')
      return false
    }

    const result = await response.json();

    if (result.success) {
    deliveryList.value = result.data

    deliveryList.value.forEach(item => {
        try {
        item.delivery_info = JSON.parse(item.delivery_info)
        } catch (e) {
        console.error('Erreur de parsing pour delivery_info:', e.message, item)
        item.delivery_info = [] // ou null selon ton besoin
        }
    })

    console.log('result: ', deliveryList.value)
    emit('deliveryList', deliveryList.value)
    } else {
    console.log('result: ', result.message)
    }
  } catch (e) {
    //isMessage.value = true
    //message.value = t(e)
  }
}

const testDelivery = async(method) => {
  try {
    var response
      response = await fetch(`https://management.hoggari.com/backend/api.php?action=${method}`, {
      method: 'GET',
    });
    

    if (!response.ok) {
      isMessage.value = true
      message.value = t('error in request get test dilivery')
      return false
    }

    const result = await response.json();

    if (result.success && result.data.work == 1) {
      return true
    } else {
      return false
    }
  } catch (e) {
    isMessage.value = true
    message.value = t(e)
    return false
  }
}

onMounted (async() => {
    getDelivery()
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

    deliveryListed.value = results.filter(Boolean) // Retire les `null`
    deliveryListed.value.push({label: 'custom', value: 'testCustom', img: 'z.svg'})
    isMounted.value = true
})


</script>

<style>

.bigBox {
    padding: 5px;
    align-items: start;
}

.directBtn {
    
    font-size: 16px;
    margin: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px;
    border-radius: 8px;
    background-color: var(--color-whizy);
    gap: 2px;
    cursor: pointer;

}
.dark .directBtn {
    background-color: var(--color-darkow);
}

.directBox {
    font-size: 16px;
    font-weight: bold;
    display: flex;
    justify-content: space-between;
    align-items: center;

}

.rowBox {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wilayaBox {
    width: calc(100% - 10px);
    margin: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px;
    padding: 2px;
    background-color: var(--color-whizy);
}
.dark .wilayaBox{
    background-color: var(--color-darkow);
}

.miniBox {
    font-size: 14px;
    margin: 5px;
    color: var(--color-zioly1);
    text-align: start;
}
.dark .miniBox {
    color: var(--color-garry);
}

.contentDelivery {
    width: 100%; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    flex-direction: column;
    padding-block: 5px;
    margin-block: 5px;
}


.boxDelivery {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-direction: column;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    border-radius: 6px;
    transition: all 0.3s ease;
    margin-block: 5px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}

.boxDeliveryTitle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin-block: 5px;
}

</style>