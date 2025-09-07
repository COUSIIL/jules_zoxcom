<template>
  <div class="flex flex-col items-center justify-between w-full max-w-3xl min-w-[300px] p-2.5 m-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
    <div class="flex flex-wrap items-center justify-center w-full gap-4 md:flex-nowrap">

      <div class="flex flex-col items-center justify-between w-full">
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

      <div class="flex flex-wrap justify-center w-full gap-4 mt-2.5 md:flex-col">
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
      throw new Error(t('server error'))
    }

    const result = await response.json()

    if (result.success) {
      emit('success', result)
      emit('message', result.message || t('bank account created successfully'))
    } else {
      emit('message', result.message || t('failed to create bank account'))
      emit('cancel', true)
    }
  } catch (error) {
    emit('message', error.message || t('unexpected error'))
  } finally {
    emit('saving', false)
  }
}
</script>
