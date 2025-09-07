<template>
  <LoaderBlack v-if="isSaving" width="80px" />

  <Message 
    :isVisible="isMessage"
    :message="message"
    @ok="isMessage = false"
  />
  <div style="width: 100%; display: flex; flex-direction: column; align-items: center;">
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
  

  <div style="height: 80px;">

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

<style>
.boxContainerTrans {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  background-color: var(--color-whitly);
  border-radius: 6px;
  transition: all 0.3s ease;
  padding-block: 10px;
  margin-block: 5px;
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
}
.dark .boxContainerTrans {
  background-color: var(--color-darkly);
}

.boxContainer2 {
  display: flex;
  flex-direction: column;
  width: 100%;
  max-width: 800px;
  background-color: var(--color-whitly);
  border-radius: 6px;
  transition: all 0.3s ease;
  padding: 10px;
  margin-block: 10px;
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
  cursor: pointer;
}
.dark .boxContainer2 {
  background-color: var(--color-darkly);
}

.center_column_graph {
  width: 90%;
  display: flex;
  flex-wrap: wrap; /* ✅ pour mobile */
  justify-content: center;
  align-items: center;
  margin-inline: 10px;
}

.center_column2 {
  display: flex;
  flex-wrap: wrap; /* ✅ Permet de passer en colonne sur petit écran */
  justify-content: flex-start;
  align-items: center;
  padding-inline: 2px;
  gap: 5px;
}

.insider3 {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  min-width: 0; /* ✅ laisse respirer */
}

.mainTitle,
.mainTitle2,
.mainTitle3 {
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;
  white-space: nowrap;
  text-overflow: ellipsis;
  gap: 5px;
}

.mainTitle4 {
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    gap: 5px;
    font-size: 14px;
    color: var(--color-garry);
  }
  .dark .mainTitle4 {
    font-size: 14px;
    color: var(--color-gorry);
  }

.svg-up svg {
  color: var(--color-greny);
}
.svg-down svg {
  color: var(--color-rady);
}
.svg-stable svg {
  color: var(--color-gorry);
}
.dark .svg-stable svg {
  color: var(--color-garry);
}


/* ✅ Ajustement responsive */
@media (max-width: 768px) {

  .mainTitle,
  .mainTitle2,
  .mainTitle3 {
    font-size: 12px;
  }
  .mainTitle4 {
    font-size: 10px;
    color: var(--color-garry);
  }
  .dark .mainTitle4 {
    font-size: 10px;
    color: var(--color-gorry);
  }
}

@media (max-width: 480px) {

  .mainTitle {
    font-size: 12px;
  }

  .mainTitle2 {
    font-size: 12px;
  }

  .mainTitle3 {
    font-size: 14px;
  }

  .mainTitle4 {
    font-size: 10px;
    color: var(--color-garry);
  }
  .dark .mainTitle4 {
    font-size: 10px;
    color: var(--color-gorry);
  }

  .svg-up svg,
  .svg-down svg,
  .svg-stable svg {
    width: 16px; /* ✅ Icônes réduites sur petit écran */
  }
}



</style>
