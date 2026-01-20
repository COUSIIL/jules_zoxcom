<template>
  <div class="module-content">
    <div class="module-header">
      <div class="logo-container">
        <img src="https://management.hoggari.com/orderDz.png" alt="OrderDz logo" class="logo-img" />
      </div>
      <h3 class="title">{{ t('orderDz api key') }}</h3>
      <Toggle v-if="!loading" :toggle="work" @toggle="activator('orderdz_module')"/>
    </div>

    <div class="input-group">
      <h3>{{ t('key :') }}</h3>
      <input class="input" v-model="orderDzKey" type="text" :placeholder="t('Enter OrderDz API Key')">
    </div>

    <button v-if="!saving" class="btn2 save-btn" @click="applyOrderDz" type="button">
      {{ t('save') }}
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
        <path d="M2.5 12C2.5 7.52166 2.5 5.28249 3.89124 3.89124C5.28249 2.5 7.52166 2.5 12 2.5C16.4783 2.5 18.7175 2.5 20.1088 3.89124C21.5 5.28249 21.5 7.52166 21.5 12C21.5 16.4783 21.5 18.7175 20.1088 20.1088C18.7175 21.5 16.4783 21.5 12 21.5C7.52166 21.5 5.28249 21.5 3.89124 20.1088C2.5 18.7175 2.5 16.4783 2.5 12Z" stroke="currentColor" stroke-width="1.5" />
        <path d="M6 13.5L7.5 9L9.375 13.5M6 13.5L5.5 15M6 13.5H9.375M9.375 13.5L10 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M12.5 12V9.7C12.5 9.51387 12.5 9.42081 12.5245 9.34549C12.5739 9.19327 12.6933 9.07393 12.8455 9.02447C12.9208 9 13.0139 9 13.2 9H14.5C15.3284 9 16 9.67157 16 10.5C16 11.3284 15.3284 12 14.5 12H12.5ZM12.5 12V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        <path d="M18.5 9V15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
    </button>

    <div v-else class="loader-container">
      <Loader :style="{width: '40px', height: '40px'}"/>
    </div>

    <p class="status-msg" v-if="disLog">
      {{ disLog }}
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useLang } from '~/composables/useLang';
import { useAuth } from '~/composables/useAuth';
import Loader from '../../components/loader.vue';
import Toggle from '../components/toggle.vue';

const { t } = useLang();
const { getauth } = useAuth();

// ----------------------
// DATA
// ----------------------
const disLog = ref('');
const orderDzKey = ref('');
const name = ref('orderDz');
const work = ref(false);
const saving = ref(false);
const loading = ref(true);

// ----------------------
// FUNCTIONS
// ----------------------
const getOrderDz = async () => {
  loading.value = true;
  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=testOrderDz', {
      method: 'GET'
    });

    if (!response.ok) throw new Error('Network error');

    const textResponse = await response.json();

    if (textResponse.success) {
      orderDzKey.value = textResponse.data.key;
      work.value = textResponse.data.work == 1;
    } else {
      console.error('API Error: ', textResponse.message);
    }
  } catch (err) {
    console.error('Fetch Error: ', err);
  } finally {
    loading.value = false;
  }
};

const activator = async (table) => {
  const newValue = !work.value;
  work.value = newValue;

  const auth = getauth();
  const token = auth ? auth.token : null;

  const order = JSON.stringify({
    token: token,
    table: table,
    value: newValue ? 1 : 0
  });

  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=updateActivator', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: order
    });

    if (!response.ok) throw new Error('Network error');
    const textResponse = await response.json();

    if (!textResponse.success) {
      console.error('Activation failed: ', textResponse.message);
      work.value = !newValue;
      disLog.value = textResponse.message;
    }
  } catch (err) {
    console.error('Activation Error: ', err);
    work.value = !newValue;
    disLog.value = t("connection error");
  }
};

const applyOrderDz = async () => {
  saving.value = true;
  disLog.value = "";

  const auth = getauth();
  const token = auth ? auth.token : null;

  const orderDzModule = JSON.stringify({
    token: token,
    name: name.value,
    key: orderDzKey.value || '',
    work: work.value ? 1 : 0
  });

  disLog.value = t("waiting response...");

  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=orderDzModule', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: orderDzModule
    });

    if (!response.ok) throw new Error('Network error');

    const textResponse = await response.json();

    if (textResponse.success) {
      disLog.value = textResponse.message;
    } else {
      disLog.value = textResponse.message + (textResponse.data ? ` : ${textResponse.data}` : '');
    }
  } catch (error) {
    disLog.value = `${t("request failed: ")}${error.message}`;
  } finally {
    saving.value = false;
  }
};

// ----------------------
// MOUNTED
// ----------------------
onMounted(() => {
  getOrderDz();
});
</script>

<style scoped>
.module-content {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

.module-header {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.logo-container {
  width: 40px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #f5f5f5;
  padding: 5px;
}

.logo-img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.title {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
  flex-grow: 1;
  text-align: left;
  padding-left: 15px;
}

.input-group {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 5px;
}

.input-group h3 {
  font-size: 0.9rem;
  opacity: 0.8;
  margin: 0;
}

.input {
  width: 100%;
  padding: 10px;
  border-radius: 6px;
  border: 1px solid #ddd;
  background-color: var(--bg-input);
  color: var(--color-text);
}

.save-btn {
  width: 100%;
  margin-top: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 8px;
}

.loader-container {
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-msg {
  font-size: 0.85rem;
  color: var(--color-text);
  opacity: 0.7;
  margin-top: 5px;
}
</style>
