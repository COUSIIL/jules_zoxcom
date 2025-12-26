<template>
  <div style="width: 90%; display: flex; justify-content: space-between; align-items: center;">
    <li>
        <div style="width: 30px; height: 30px; border-radius: 8px; display: flex; justify-content: center; align-items: center;">
            <img src="https://management.hoggari.com/orderDz.png" alt="">
        </div>
      
        <h3 class="title">{{ t('orderDz api key') }}</h3>
    </li>
    
    <Toggle v-if="!loading" :toggle="work" @toggle="activator('orderdz_module')"/>

</div>

<div style="width: 300px; min-width: 5px; display: flex; justify-content: center; align-items: center;">
  <h3>
      {{ t('key :') }}
  </h3>
  
</div>
    

    <input class="input" v-model="orderDzKey" type="text">

    <button v-if="!saving" class="btn2" style="width: 50%;" @click="applyorderDz" type="button">
      {{ t('save') }}
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
        <path d="M2.5 12C2.5 7.52166 2.5 5.28249 3.89124 3.89124C5.28249 2.5 7.52166 2.5 12 2.5C16.4783 2.5 18.7175 2.5 20.1088 3.89124C21.5 5.28249 21.5 7.52166 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1088C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1088C2.5 18.7175 2.5 16.4783 2.5 12Z" stroke="currentColor" stroke-width="1.5" />
        <path d="M6 13.5L7.5 9L9.375 13.5M6 13.5L5.5 15M6 13.5H9.375M9.375 13.5L10 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M12.5 12V9.7C12.5 9.51387 12.5 9.42081 12.5245 9.34549C12.5739 9.19327 12.6933 9.07393 12.8455 9.02447C12.9208 9 13.0139 9 13.2 9H14.5C15.3284 9 16 9.67157 16 10.5C16 11.3284 15.3284 12 14.5 12H12.5ZM12.5 12V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M18.5 9V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    </button>
    <div v-else>
      <Loader :style="{width: '80px', height: '80px'}"/>
    </div>
    <h3>
      {{ disLog }}
    </h3>
  </template>
  
<script setup>
  import { ref, onMounted } from 'vue'
  import { useLang } from '~/composables/useLang'
  import Loader from '../../components/loader.vue'
  import Toggle from '../components/toggle.vue'

  const { t } = useLang()

  // ----------------------
  // DATA
  // ----------------------
  const isMounted = ref(false)
  const disLog = ref('')
  const orderDzKey = ref('')
  const name = ref('orderDz')
  const work = ref(false)
  const saving = ref(false)
  const loading = ref(true)

  // ----------------------
  // FUNCTIONS
  // ----------------------
  const getorderDz = async () => {
    loading.value = true

    const response = await fetch('https://management.hoggari.com/backend/api.php?action=testOrderDz', {
      method: 'GET'
    })

    if (!response.ok) {
      console.log('error in response')
      loading.value = false
      return
    }

    const textResponse = await response.json()

    if (textResponse.success) {
      orderDzKey.value = textResponse.data.key
      work.value = textResponse.data.work == 1
      loading.value = false
    } else {
      console.log('textResponse: ', textResponse.message)
      loading.value = false
    }
  }

  const activator = async (table) => {
    work.value = !work.value

    const order = JSON.stringify({
      table: table,
      value: work.value
    })

    const response = await fetch('https://management.hoggari.com/backend/api.php?action=updateActivator', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: order
    })

    if (!response.ok) return

    const textResponse = await response.json()
    if (textResponse.success) {
      console.log('textResponse: ', textResponse.message)
    } else {
      console.error('textResponse: ', textResponse.data)
    }
  }

  const applyorderDz = async () => {
    saving.value = true
    const job = work.value ? 1 : 0

    const orderDzModule = JSON.stringify({
      name: name.value,
      key: orderDzKey.value || null,
      work: job
    })

    disLog.value = t("waiting response...")

    try {
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=orderDzModule', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: orderDzModule
      })

      if (!response.ok) {
        disLog.value = t("error in response")
        saving.value = false
        return
      }

      disLog.value = t("waiting data...")
      const textResponse = await response.json()

      if (textResponse.success) {
        disLog.value = textResponse.message
      } else {
        disLog.value = textResponse.message + (textResponse.data ? ` : ${textResponse.data}` : '')
      }

      console.log('textResponse: ', textResponse)
    } catch (error) {
      disLog.value = `${t("request failed: ")}${error.message}`
    } finally {
      saving.value = false
    }
  }

  // ----------------------
  // MOUNTED
  // ----------------------
  onMounted(() => {
    isMounted.value = true
    getorderDz()
  })
</script>


  <style>
  li {
    padding: 5px;
    margin: 2px;
    display: flex;
    justify-content: space-around;
    align-items: center;
    gap: 10px;
  }
  </style>
  