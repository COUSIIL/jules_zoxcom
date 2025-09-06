<template>
  <div class="boxContainer2">
    <div class="centerFlex">

      <div class="centerColumn">
        <Inputer :placeHolder="t('bank name')" v-model="form.bank_name" :required="true" :holder="t('ex: products')"/>
        <Selector 
            :options="currency" 
            @update:modelValue="setCurrency" 
            color="var(--color-zioly2)" 
            :placeHolder="t('currency')" 
            :modelValue="ids" 
            v-model="form.currency" 
            :img="icons['bank']"
            class="formItem"
        />
        <Inputer :placeHolder="t('initial balance')" v-model="form.balance" type="number" :holder="0"/>
      </div>

      

      <div class="actions">
        <CBtn :text="t('cancel')" :svg="icons['x']" @clicked="emit('x')"/>
        <CallToAction :text="t('add')" :svg="icons['check']" @clicked="submitForm"/>
      </div>
    </div>
  </div>
</template>

<script setup>
import Inputer from '../components/elements/bloc/input.vue'
import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
import CBtn from '../components/elements/bloc/cancelBtn.vue'
import Selector from '../components/elements/bloc/select.vue'
import { ref } from 'vue'
import icons from '~/public/icons.json'

const { t } = useLang()



const currency = ref([{value: 0, label: 'DZD', img: icons['dark']}, {value: 1, label: 'EUR', img: icons['EUR']}, {value: 2, label: 'USD', img: icons['USD']}])

// Données du formulaire banque
const form = ref({
  bank_name: '',
  balance: '',
  currency: 'EUR'
})

const ids = ref(0)

const emit = defineEmits(['saving', 'cancel', 'success', 'message', 'x'])

function setCurrency(id) {
  
  ids.value = id
  form.value.currency = currency.value[id].label
  console.log('form.value.currency: ', form.value.currency)

}


const submitForm = async () => {
  emit('saving', true)

  // Validation côté client
  if (!form.value.bank_name) {
    emit('message', t('enter a valid bank name'))
    emit('saving', false)
    return
  }

  if (!form.value.balance) {
    form.value.balance = 0
  }

  form.value.currency = currency.value[ids.value].label

  try {
    const payload = {
      name: form.value.bank_name,
      type: "banks",
      currency: form.value.currency,
      opening_balance: form.value.balance || 0,
    }

    const response = await fetch('https://management.hoggari.com/backend/finance.php?action=createAccount', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    })

    if (!response.ok) {
      throw new Error('Server error')
    }

    const result = await response.json()

    if (result.success) {
      emit('success', result)
      emit('message', result.message || t('Bank account created successfully'))
    } else {
      emit('message', result.message || t('Failed to create bank account'))
      emit('cancel', true)
    }
  } catch (error) {
    emit('message', error.message || 'Unexpected error')
  } finally {
    emit('saving', false)
  }
}
</script>

<style scoped>
.profile-image-upload {
  margin-top: 1rem;
  margin: 10px;
}

.upload-label {
  max-width: 150px;
  min-width: 150px;
  max-height: 150px;
  min-height: 150px;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  border: 2px dashed #ccc;
  padding: 1rem;
  justify-content: center;
  align-items: center;
  text-align: center;
  border-radius: 50%;
}

.image-preview img {
  max-width: 150px;
  max-height: 150px;
  border-radius: 50%;
}

.image-placeholder {
  color: #999;
  font-size: 14px;
}

.actions {
  display: flex;
  justify-content: center;
  gap: 15px;
  width: 100%;
  margin-top: 10px;
  flex-wrap: wrap; /* empile les boutons si pas de place */
}

.centerFlex {
  display: flex;
  flex-wrap: wrap; /* permet le passage à la ligne */
  gap: 15px;
  width: 100%;
  justify-content: center;
}

@media (max-width: 600px) {
  .centerFlex {
    flex-direction: column;
    align-items: stretch;
  }
  .actions {
    flex-direction: column;
  }
}

.centerColumn {
  width: 100%;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  align-items: center;
}

.boxContainer2 {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  max-width: 800px;
  min-width: 300px;
  background-color: var(--color-whitly);
  border-radius: 6px;
  transition: all 0.3s ease;
  padding-block: 10px;
  margin: 10px;
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .boxContainer2{
  background-color: var(--color-darkly);
}
</style>
