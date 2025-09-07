<template>
  <div v-if="isMounted" class="max-w-3xl p-5 mx-auto">
    <div v-if="isUpdating">
      <LoaderBlack width="80px" />
    </div>

    <div v-if="customerList" class="flex items-center justify-start gap-1.25 p-1.25 text-sm overflow-hidden rounded-lg max-w-full">
      <div v-html="resizeSvg(icons['user'], 20, 20)"></div>
      <label>{{ customerList.length }}</label>
    </div>

    <div
      v-for="(customer, index) in visibleCustomers"
      :key="index"
    >
    <div class="flex items-center justify-between gap-1.25 p-4 mb-3 rounded-lg shadow-md bg-whitly dark:bg-darkly dark:shadow-2xl text-darky dark:text-whizy">
      <div>
        <div class="flex items-center justify-start gap-1.25 p-1.25 text-sm overflow-hidden rounded-lg max-w-full">
          <label class="inline-block min-w-[80px] max-w-[150px] overflow-hidden text-ellipsis whitespace-nowrap">
            ID: {{ customer.id }}
          </label>
        </div>
        
        <div class="flex items-center justify-start gap-1.25 p-1.25 text-sm overflow-hidden rounded-lg max-w-full">
            <div v-html="resizeSvg(icons['user'], 20, 20)"></div>
            <label class="inline-block min-w-[80px] max-w-[150px] overflow-hidden text-ellipsis whitespace-nowrap">
                {{ customer.name }}
            </label>
        </div>
        <div class="flex items-center justify-start gap-1.25 p-1.25 text-sm overflow-hidden rounded-lg max-w-full">
            <div v-html="resizeSvg(icons['phone'], 20, 20)"></div>
            <label class="inline-block min-w-[80px] max-w-[150px] overflow-hidden text-ellipsis whitespace-nowrap">
                {{ customer.phone }}
            </label>
        </div>

        <div v-if="customer.items.length > 0" class="flex items-center justify-start gap-1.25 p-1.25 text-sm overflow-hidden rounded-lg max-w-full">
            <div v-html="resizeSvg(icons['time'], 20, 20)"></div>
            <label class="inline-block min-w-[80px] max-w-[150px] overflow-hidden text-ellipsis whitespace-nowrap">
                {{ customer.items[0].created_at }}
            </label>
        </div>
      </div>

      <div class="flex flex-col items-center justify-center">
        <div class="flex items-center justify-start gap-1.25 p-1.25 text-sm overflow-hidden rounded-lg max-w-full">
          <div v-html="resizeSvg(icons['fire'], 20, 20)"></div>
          <label class="inline-block min-w-[80px] max-w-[150px] overflow-hidden text-ellipsis whitespace-nowrap">
              {{ customer.power }}
          </label>
        </div>

        <button class="flex items-center justify-center w-6 h-6 rounded-full cursor-pointer bg-rady text-whity" type="button" @click="deleteCustomer(customer.id, index)">
            <div v-html="resizeSvg(icons['deleteImg'], 20, 20)"></div>
        </button>
      </div>
    </div>
    </div>

    <button
      v-if="visibleCount < customerList.length"
      @click="loadMore"
      class="block px-5 py-2.5 mx-auto mt-5 text-base text-white bg-purple-600 border-none rounded-lg cursor-pointer transition-colors hover:bg-purple-700 dark:bg-purple-500 dark:hover:bg-purple-600"
    >
        <label>{{t('show more')}}</label>
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

import LoaderBlack from '../components/elements/animations/loaderBlack.vue';

const isMounted = ref(false);
const isUpdating = ref(true);
const updatingIndex = ref(0);
const customerList = ref([]);
const visibleCount = ref(100);

const visibleCustomers = computed(() => customerList.value.slice(0, visibleCount.value));

import icons from '~/public/icons.json'

const { t } = useLang()

onMounted(() => {
  getCustomers();
});

var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
}

function loadMore() {
  visibleCount.value += 100;
}

async function getCustomers() {
  isUpdating.value = true;
  customerList.value = []
  try {
    const response = await fetch(
      'https://management.hoggari.com/backend/api.php?action=getAllCustomers',
      { method: 'GET' }
    );

    if (!response.ok) {
      console.error(t('error in getting view page response'));
      return;
    }

    const result = await response.json();
    if (result.success) {
      customerList.value = result.data;
      isUpdating.value = false;
    } else {
      console.error(result.message);
      isUpdating.value = false;
    }
  } catch (error) {
    console.error(error);
    isUpdating.value = false;
  } finally {
    isUpdating.value = false;
    isMounted.value = true;
  }
}

async function deleteCustomer(id, index) {
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
      isUpdating.value = false;
      return;
    }
    const result = await response.json();
    
    if (result.success) {
      const i1 = customerList.value.findIndex(c => c.id === parseInt(id));
      if (i1 !== -1) customerList.value.splice(i1, 1);
      visibleCustomers.value.splice(index, 1)
      
      isUpdating.value = false;
    } else {

      isUpdating.value = false;
    }
  } catch (error) {
    console.error(error);
    isUpdating.value = false;
  } finally {
    isUpdating.value = false;
    isMounted.value = true;
  }
}


</script>
