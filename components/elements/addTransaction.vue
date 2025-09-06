<template>
  <div class="transactionContainer">
    <div class="formContainer2">
      <p>
        {{ t('transfer') }}
      </p>
      <Radio :selected="is_transfer" @changed="is_transfer = !is_transfer"/>
    </div>

    <div class="formContainer">

      <!-- Sélecteur Banque -->
       <div v-if="!is_transfer" class="formContainer2">
        <Selector 
            :options="props.options" 
            @update:modelValue="setBank" 
            color="var(--color-zioly2)" 
            :placeHolder="t('banks')" 
            :modelValue="form.account_id" 
            v-model="bank_name" 
            :img="icons['bank']"
            class="formItem"
        />

        <button v-if="form.direction == 'out'" class="btnTransactionOut" @click="form.direction = 'in'">
            <div v-html="resizeSvg(icons['up'], 30, 30)">

            </div>
        </button>
        <button v-else class="btnTransactionIn" @click="form.direction = 'out'">
            <div v-html="resizeSvg(icons['down'], 30, 30)">

            </div>
        </button>
       </div>
       <div v-else class="formContainer2">
        <Selector 
            :options="props.options" 
            @update:modelValue="setBankFrom" 
            color="var(--color-zioly2)" 
            :placeHolder="t('banks')" 
            :modelValue="form.from_account_id" 
            v-model="from_bank_name" 
            :img="icons['bank']"
            class="formItem"
        />
        <p>
        {{ t('to') }}
       </p>
       <Selector 
            :options="props.options" 
            @update:modelValue="setBankTo" 
            color="var(--color-zioly2)" 
            :placeHolder="t('banks')" 
            :modelValue="form.to_account_id" 
            v-model="to_bank_name" 
            :img="icons['bank']"
            class="formItem"
        />
       </div>
       
      

      <!-- Inputs -->
      <div class="formItem">
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
      <div class="actions">
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
      emit('message', t('Please select a bank from account'))
      emit('saving', false)
      return
    }

    if (!form.value.to_account_id) {
      emit('message', t('Please select a bank to account'))
      emit('saving', false)
      return
    }

    if (!form.value.amount || isNaN(form.value.amount) || form.value.amount <= 0) {
      emit('message', t('Please enter a valid amount'))
      emit('saving', false)
      return
    }


    try {
      const payload = {
        from_account_id: Number(form.value.from_account_id),
        to_account_id: Number(form.value.to_account_id),
        amount: Number(form.value.amount),
        description: form.value.description || 'Transfert entre comptes',
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
        emit('message', result.message || t('Failed to create transfer'))
        emit('cancel', true)
      }
    } catch (error) {
      emit('message', error.message || 'Unexpected error')
    } finally {
      emit('saving', false)
    }
  } else {
    // Validation côté client
    if (!form.value.account_id) {
      emit('message', t('Please select a bank account'))
      emit('saving', false)
      return
    }

    if (!form.value.direction || !['in', 'out'].includes(form.value.direction)) {
      emit('message', t('Please choose a valid transaction direction'))
      emit('saving', false)
      return
    }

    if (!form.value.amount || isNaN(form.value.amount) || form.value.amount <= 0) {
      emit('message', t('Please enter a valid amount'))
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
          result.message || t('Transaction created successfully')
        )
      } else {
        emit('message', result.message || t('Failed to create transaction'))
        emit('cancel', true)
      }
    } catch (error) {
      emit('message', error.message || 'Unexpected error')
    } finally {
      emit('saving', false)
    }
  }

  
}
</script>


<style scoped>
.transactionContainer {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  max-width: 800px;
  background-color: var(--color-whitly);
  border-radius: 6px;
  padding: 20px;
  margin: 10px auto;
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .transactionContainer {
  background-color: var(--color-darkly);
}

.formContainer {
  display: flex;
  flex-wrap: wrap; /* permet le passage à la ligne */
  gap: 15px;
  width: 100%;
  justify-content: center;
}

.formContainer2 {
  display: flex;
  justify-content: center; /* permet le passage à la ligne */
  gap: 10px;
  width: 100%;
  align-items: center;
}

.formItem {
  flex: 1 1 100px; /* chaque bloc prend min 300px et s'adapte */
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.actions {
  display: flex;
  justify-content: center;
  gap: 15px;
  width: 100%;
  margin-top: 10px;
  flex-wrap: wrap; /* empile les boutons si pas de place */
}

@media (max-width: 600px) {
  .formContainer {
    flex-direction: column;
    align-items: stretch;
  }
  .actions {
    flex-direction: column;
  }
}

.btnTransactionIn {
    background-color: var(--color-greny);
    width: 40px;
    height: 40px;
    min-width: 40px;
    min-height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
}

.btnTransactionOut {
    background-color: var(--color-rady);
    width: 40px;
    height: 40px;
    min-width: 40px;
    min-height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>


