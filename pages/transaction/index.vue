<template>
  <LoaderBlack v-if="isSaving" width="80px" />

  <Message 
    :isVisible="isMessage"
    :message="message"
    @ok="isMessage = false"
  />
  <div class="flex flex-col items-center w-full">
    <div class="center_column_graph">
      <GraphLine :transactions="transactions"/>

      <div v-if="!isActive" class="boxContainerTrans">
        <div class="mainTitle">
          <div v-html="resizeSvg(icons['transfer'], 20, 20)"></div>
          <h1>{{ transactionsLength }} {{ t('transactions') }}</h1>
        </div>

        <CallToAction 
          :text="t('add transaction')" 
          :svg="icons['add']" 
          @clicked="activate" 
        />
      </div>

      

      <Addtransaction 
        v-if="isActive"
        @saving="saving"
        @success="success"
        @cancel="cancel"
        @message="messager"
        @x="isActive = false"
        :options="option"
        :banks="banks"
      />

      <div v-if="transactions && transactions.length" class="center_column_graph">
        <div 
          v-for="(box, index) in transactions" 
          :key="index" 
          class="boxContainerTrans transaction-item"
        >
          <!-- Ligne 1 -->
          <div class="center_column2">
            <div 
              v-html="resizeSvg(icons['transfer'], 30, 30)" 
              :class="box.kind === 'out' ? 'svg-down' : 'svg-up'"
            ></div>

            <div class="insider3">
              <p class="mainTitle">{{ box.created_at }}</p>
              <p class="mainTitle4">{{ t('id') }} {{ box.id }}</p>
            </div>
          </div>

          <!-- Ligne 2 -->
          <div class="center_column2">
            <div v-html="resizeSvg(icons['bank'], 20, 20)"></div>
            <div class="insider3">
              <p v-if="getBankById(box.account_id)" class="mainTitle">
                {{ getBankById(box.account_id).name }}
              </p>
              <p class="mainTitle4">
                {{ box.reference }} 
                {{ getBankById(box.account_id)?.currency || '' }}
              </p>
            </div>
          </div>

          <!-- Ligne 3 -->
          <div class="center_column2">
            <p class="mainTitle2">
              {{ box.amount }} {{ banks[getBankById(box.account_id) - 1]?.currency || '' }}
            </p>

            <div 
              v-html="resizeSvg(icons[box.kind === 'out' ? 'down' : 'up'], 20, 20)" 
              :class="box.kind === 'out' ? 'svg-down' : 'svg-up'"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  

  <div class="h-20">

  </div>
</template>


<script setup>

  import { ref, onMounted } from 'vue';
  import { useRouter } from 'nuxt/app';

  import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
  import Addtransaction from '../components/elements/addTransaction.vue';
  import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
  import Message from '../components/elements/bloc/message.vue';
  import GraphLine from '~/components/elements/graphLine.vue'

  

  import icons from '~/public/icons.json'

  const router = useRouter();

  var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

  const { t } = useLang()

  const transactionsLength = ref(0)
  const transactions = ref()
  const banks = ref()
  const successData = ref()
  const isSaving = ref(false)
  const isMessage = ref(false)
  const message = ref('')
  const isActive = ref(false)
  const option = ref([])

  onMounted(() => {
    getTransaction()
    getBanks()
  })

  const activate = () => {
    isActive.value = true
  }

  const success = (value) => {
    successData.value = value
    getTransaction()
  }
  const cancel = () => {
    isActive.value = false
  }
  const saving = (value) => {
    isSaving.value = value
  }
  const messager = (value) => {
    isMessage.value = true
    message.value = value
  }

  function getBankById(id) {
    if (!banks.value || !Array.isArray(banks.value)) return null
    return banks.value.find(bank => String(bank.id) === String(id)) || null
  }


  const getTransaction = async () => {
    try {
        isSaving.value = true
        const res = await fetch('https://management.hoggari.com/backend/finance.php?action=listTransactions', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
        })

        const data = await res.json()
        if (data.success) {
          transactions.value = data.data.transactions
          transactionsLength.value = transactions.value.length
        } else {
        messager(data.message || t('failed to load transactions'))
        }
    } catch (err) {
        console.error(err)
        messager(t('error while fetching transactions'))
    } finally {
        isSaving.value = false
    }
  }

  const getBanks = async () => {
    try {
        isSaving.value = true
        const res = await fetch('https://management.hoggari.com/backend/api.php?action=getBanks', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
        })

        const data = await res.json()
        if (data.success) {
          banks.value = data.data
          
          option.value = data.data.map(bank => ({
              value: bank.id,
              label: bank.name,
              img: ''
          }))
        } else {
        messager(data.message || t('failed to load banks'))
        }
    } catch (err) {
        console.error(err)
        messager(t('error while fetching banks'))
    } finally {
        isSaving.value = false
    }
  }

</script>

