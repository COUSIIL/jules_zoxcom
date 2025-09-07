<template>
  <div class="flex flex-col items-center w-full max-w-3xl p-5 m-2.5 mx-auto rounded-md shadow-md bg-whitly dark:bg-darkly">
    <div class="flex items-center justify-center w-full gap-2.5">
      <p>{{ t('transfer') }}</p>
      <Radio :selected="is_transfer" @changed="is_transfer = !is_transfer"/>
    </div>

    <div class="flex flex-wrap items-center justify-center w-full gap-4">
      <!-- Sélecteur Banque -->
       <div v-if="!is_transfer" class="flex items-center justify-center w-full gap-2.5">
        <Selector 
            :options="props.options" 
            @update:modelValue="setBank" 
            color="var(--color-zioly2)" 
            :placeHolder="t('banks')" 
            :modelValue="form.account_id" 
            v-model="bank_name" 
            :img="icons['bank']"
            class="flex flex-col flex-1 gap-2.5 min-w-[100px]"
        />

        <button v-if="form.direction == 'out'" class="flex items-center justify-center w-10 h-10 rounded-full cursor-pointer min-w-10 min-h-10 bg-rady" @click="form.direction = 'in'">
            <div v-html="resizeSvg(icons['up'], 30, 30)"></div>
        </button>
        <button v-else class="flex items-center justify-center w-10 h-10 rounded-full cursor-pointer min-w-10 min-h-10 bg-greny" @click="form.direction = 'out'">
            <div v-html="resizeSvg(icons['down'], 30, 30)"></div>
        </button>
       </div>
       <div v-else class="flex items-center justify-center w-full gap-2.5">
        <Selector 
            :options="props.options" 
            @update:modelValue="setBankFrom" 
            color="var(--color-zioly2)" 
            :placeHolder="t('banks')" 
            :modelValue="form.from_account_id" 
            v-model="from_bank_name" 
            :img="icons['bank']"
            class="flex flex-col flex-1 gap-2.5 min-w-[100px]"
        />
        <p>{{ t('to') }}</p>
       <Selector 
            :options="props.options" 
            @update:modelValue="setBankTo" 
            color="var(--color-zioly2)" 
            :placeHolder="t('banks')" 
            :modelValue="form.to_account_id" 
            v-model="to_bank_name" 
            :img="icons['bank']"
            class="flex flex-col flex-1 gap-2.5 min-w-[100px]"
        />
       </div>
      
      <!-- Inputs -->
      <div class="flex flex-col flex-1 gap-2.5 min-w-[100px]">
        <Inputer 
          :placeHolder="t('description')" 
          v-model="form.description" 
          :required="false" 
          :holder="t('ex: transfer')"
        />
        <Inputer 
          :placeHolder="t('amount')" 
          v-model="form.amount" 
          type="number" 
          :holder="0"
          :required="true" 
        />
      </div>

      <!-- Boutons -->
      <div class="flex flex-wrap justify-center w-full gap-4 mt-2.5 md:flex-col">
        <CBtn :text="t('cancel')" :svg="icons['x']" @clicked="emit('x')" />
        <CallToAction :text="t('add')" :svg="icons['check']" @clicked="submitForm" />
      </div>
    </div>
  </div>
</template>

<script setup>
import Inputer from '../components/elements/bloc/input.vue'
import CallToAction from '../components/elements/bloc/callToActionBtn.vue'
import CBtn from '../components/elements/bloc/cancelBtn.vue'
import Selector from '../components/elements/bloc/select.vue'
import { ref } from 'vue'
import icons from '~/public/icons.json'
import Radio from '../components/elements/bloc/radio.vue';

const { t } = useLang()

var resizeSvg = (svg, width, height) => {
  return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
}

const bank_name = ref('')
const from_bank_name = ref('')
const to_bank_name = ref('')
const is_transfer = ref(false)

// Données du formulaire
const form = ref({
  account_id: 0,
  direction: 'in', // "in" ou "out"
  amount: '',
  description: '',
  reference: '',
  date: '',
  from_account_id: '',
  to_account_id: ''
})

const props = defineProps({
  banks: Array,
  options: { default: [], value: Array }
})

const emit = defineEmits(['saving', 'cancel', 'success', 'message', 'x'])

function setBank(bankID) {
  form.value.account_id = bankID
  for (var op of props.banks) {
    if (bankID === op.id) {
      bank_name.value = op.name
      break
    }
  }
}

function setBankFrom(bankID) {
  form.value.from_account_id = bankID
  for (var op of props.banks) {
    if (bankID === op.id) {
      from_bank_name.value = op.name
      break
    }
  }
}

function setBankTo(bankID) {
  form.value.to_account_id = bankID
  for (var op of props.banks) {
    if (bankID === op.id) {
      to_bank_name.value = op.name
      break
    }
  }
}

const submitForm = async () => {
  emit('saving', true)

  // Générer la date actuelle au format ISO
  form.value.date = new Date().toISOString().slice(0, 19).replace('T', ' ')

  if(is_transfer.value) {
    // Validation côté client
    if (!form.value.from_account_id) {
      emit('message', t('please select a bank from account'))
      emit('saving', false)
      return
    }

    if (!form.value.to_account_id) {
      emit('message', t('please select a bank to account'))
      emit('saving', false)
      return
    }

    if (!form.value.amount || isNaN(form.value.amount) || form.value.amount <= 0) {
      emit('message', t('please enter a valid amount'))
      emit('saving', false)
      return
    }


    try {
      const payload = {
        from_account_id: Number(form.value.from_account_id),
        to_account_id: Number(form.value.to_account_id),
        amount: Number(form.value.amount),
        description: form.value.description || t('transfert entre comptes'),
        reference: form.value.reference || 'TRANSFER',
        date: form.value.date || '' // optionnel
      }
      


      const response = await fetch(
        'https://management.hoggari.com/backend/finance.php?action=transfer',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        }
      )

      if (!response.ok) {
        throw new Error('Server error')
      }

      const result = await response.json()

      if (result.success) {
        emit('success', result)
        emit(
          'message',
          result.message || t('transfer created successfully')
        )
      } else {
        emit('message', result.message || t('failed to create transfer'))
        emit('cancel', true)
      }
    } catch (error) {
      emit('message', error.message || t('unexpected error'))
    } finally {
      emit('saving', false)
    }
  } else {
    // Validation côté client
    if (!form.value.account_id) {
      emit('message', t('please select a bank account'))
      emit('saving', false)
      return
    }

    if (!form.value.direction || !['in', 'out'].includes(form.value.direction)) {
      emit('message', t('please choose a valid transaction direction'))
      emit('saving', false)
      return
    }

    if (!form.value.amount || isNaN(form.value.amount) || form.value.amount <= 0) {
      emit('message', t('please enter a valid amount'))
      emit('saving', false)
      return
    }

    var valRef = 0
    if (form.value.direction === 'in') {
          for (var op of props.banks) {
              if (form.value.account_id == op.id) {
                  valRef = parseFloat(op.current_balance) + parseFloat(form.value.amount)
                  break
              }
          }
      } else {
          for (var op of props.banks) {
              if (form.value.account_id == op.id) {
                  valRef = parseFloat(op.current_balance) - parseFloat(form.value.amount)
                  break
              }
          }
      }

      form.value.reference = valRef


    try {
      const payload = { ...form.value }

      const response = await fetch(
        'https://management.hoggari.com/backend/finance.php?action=addTransaction',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        }
      )

      if (!response.ok) {
        throw new Error('Server error')
      }

      const result = await response.json()

      if (result.success) {
        emit('success', result)
        emit(
          'message',
          result.message || t('transaction created successfully')
        )
      } else {
        emit('message', result.message || t('failed to create transaction'))
        emit('cancel', true)
      }
    } catch (error) {
      emit('message', error.message || t('unexpected error'))
    } finally {
      emit('saving', false)
    }
  }

  
}
</script>
