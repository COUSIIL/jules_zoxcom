<template>
  <div v-if="isMounted" class="customer-wrapper">

    <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; align-items: center;">
        <Search style="flex: 1;" v-model:searcher="searchValue" @search-submitted="getCustomers(searchValue)" />
        <!--RectBtn text="Export CSV" svg="download" @click:ok="exportData" :isSimple="true" /-->
    </div>

    <div v-if="isUpdating">
      <LoaderBlack width="80px" />
    </div>

    <div v-if="customerList && !isUpdating" class="box" style="margin-bottom: 10px;">
      <div v-html="resizeSvg(icons['user'], 20, 20)"></div>
      <label>
          {{ customerList.length }} {{ t('Customers') }}
      </label>
    </div>

    <div
      v-for="(customer, index) in visibleCustomers"
      :key="index"
    >
    <div class="customer-card">
      <div style="flex: 1;">
        <div class="box">
          <label style="font-weight: bold;">ID: {{ customer.id }}</label>
        </div>
        
        <div class="box">
            <div v-html="resizeSvg(icons['user'], 20, 20)"></div>
            <label>{{ customer.name }}</label>
        </div>
        <div class="box">
            <div v-html="resizeSvg(icons['phone'], 20, 20)"></div>
            <label>{{ customer.phone }}</label>
        </div>
        <div v-if="customer.email" class="box">
            <div v-html="resizeSvg(icons['chat'], 20, 20)"></div> <!-- Placeholder for email icon -->
            <label>{{ customer.email }}</label>
        </div>
        <div v-if="customer.wilaya" class="box">
            <div v-html="resizeSvg(icons['location'], 20, 20)"></div>
            <label>{{ customer.wilaya }} - {{ customer.commune }}</label>
        </div>

        <div v-if="customer.items.length > 0" class="box">
            <div v-html="resizeSvg(icons['time'], 20, 20)"></div>
            <label>Last Order: {{ customer.items[0].created_at }}</label>
        </div>
      </div>

      <div class="floatingAction">
        <div class="box power-box">
          <div v-html="resizeSvg(icons['fire'], 20, 20)"></div>
          <label style="font-weight: bold; color: #ff5555;">{{ customer.power }}</label>
        </div>

        <button class="x" type="button" @click="deleteCustomer(customer.id, index)">
            <div v-html="resizeSvg(icons['deleteImg'], 20, 20)"></div>
        </button>

      </div>
    </div>
    
    
    </div>

    <button
      v-if="visibleCount < customerList.length"
      @click="loadMore"
      class="load-more-btn"
        >
        <label>
            {{t('show more')}}    
        </label>
      
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useLang } from '../../composables/useLang';

import LoaderBlack from '../../components/elements/animations/loaderBlack.vue';
import Search from '../../components/search.vue';
import RectBtn from '../../components/elements/newBloc/rectBtn.vue';

import icons from '~/public/icons.json';

const { t } = useLang();

const isMounted = ref(false);
const isUpdating = ref(true);
const updatingIndex = ref(0);
const customerList = ref([]);
const visibleCount = ref(100);
const searchValue = ref("");

const visibleCustomers = computed(() => customerList.value.slice(0, visibleCount.value));

onMounted(() => {
  getCustomers();
});

var resizeSvg = (svg, width, height) => {
    if (!svg) return '';
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
}

function loadMore() {
  visibleCount.value += 100;
}

function exportData() {
    window.open('https://management.hoggari.com/backend/api.php?action=exportCustomers', '_blank');
}

async function getCustomers(search = "") {
  isUpdating.value = true;
  customerList.value = []
  try {
    const url = search
        ? `https://management.hoggari.com/backend/api.php?action=getAllCustomers&search=${encodeURIComponent(search)}`
        : 'https://management.hoggari.com/backend/api.php?action=getAllCustomers';

    const response = await fetch(url, { method: 'GET' });

    if (!response.ok) {
      console.error(t('error in getting view page response'));
      return;
    }

    const result = await response.json();
    if (result.success) {
      customerList.value = result.data;
    } else {
      console.error(result.message);
    }
  } catch (error) {
    console.error(error);
  } finally {
    isUpdating.value = false;
    isMounted.value = true;
  }
}

async function deleteCustomer(id, index) {
  if (!confirm(t('Are you sure?'))) return;

  isUpdating.value = true;
  updatingIndex.value = index
  const data = JSON.stringify({
    id: parseInt(id),
  });

  try {
    const response = await fetch(
      'https://management.hoggari.com/backend/api.php?action=deleteCustomer',
      { method: 'POST',
        body: data
       }
    );
    
    if (!response.ok) {
      console.error(t('error in getting view page response'));
      return;
    }
    const result = await response.json();
    
    if (result.success) {
      const i1 = customerList.value.findIndex(c => c.id === parseInt(id));
      if (i1 !== -1) customerList.value.splice(i1, 1);
      
    }
  } catch (error) {
    console.error(error);
  } finally {
    isUpdating.value = false;
  }
}

watch(searchValue, (newVal) => {
    // Optional: Debounce here if needed, or rely on Enter/Click in Search component
    if (newVal === "") {
        getCustomers();
    }
});
</script>

<style scoped>
.customer-wrapper {
  padding: 20px;
  max-width: 800px;
  margin: auto;
}

.customer-card {
  background-color: var(--color-whitly);
  color: var(--color-darky);
  padding: 16px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.load-more-btn {
  display: block;
  margin: 20px auto 0;
  padding: 10px 20px;
  background-color: #7f5af0;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.load-more-btn:hover {
  background-color: #6e4ae0;
}

/* Mode sombre */
.dark .customer-card {
  background-color: var(--color-darkly);
  color: var(--color-whizy);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 1);
}

.dark .load-more-btn {
  background-color: #a084ff;
  color: #fff;
}

.dark .load-more-btn:hover {
  background-color: #8c6fff;
}

.box {
  padding: 5px;
  border-radius: 8px;
  display: flex;
  justify-content: left;
  align-items: center;
  gap: 8px;
  max-width: 100%;
  overflow: hidden;
  font-size: 14px;
}

.box label {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
  max-width: 300px;
}

.power-box {
    background: rgba(255, 85, 85, 0.1);
    padding: 5px 10px;
    border-radius: 15px;
}

.x {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background-color: var(--color-rady);
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  color: var(--color-whity);
  border: none;
  transition: transform 0.2s;
}

.x:hover {
    transform: scale(1.1);
}

.floatingAction {
  position: relative;
  right: 40px;
  bottom: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  gap: 10px;
}

@media (max-width: 350px) {
  .floatingAction {
    right: 140px;
    transform: translateX(50%); /* pour bien centrer */
  }
}

</style>
