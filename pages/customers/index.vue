<template>
  <div v-if="isMounted" class="customer-wrapper">

    <div v-if="isUpdating">
      <LoaderBlack width="80px" />
    </div>

    <div v-if="customerList" class="box">
      <div v-html="resizeSvg(icons['user'], 20, 20)">

      </div>
      <label>
          {{ customerList.length }}
      </label>
            
    </div>

    <div
      v-for="(customer, index) in visibleCustomers"
      :key="index"
      
    >
    <div class="customer-card">
      <div>
        <div class="box">
          <label>
            ID: {{ customer.id }}
          </label>
        </div>
        
        <div class="box">
            <div v-html="resizeSvg(icons['user'], 20, 20)">

            </div>
            <label>
                {{ customer.name }}
            </label>
            
        </div>
        <div class="box">
            <div v-html="resizeSvg(icons['phone'], 20, 20)">

            </div>
            <label>
                {{ customer.phone }}
            </label>
            
        </div>

        <div v-if="customer.items.length > 0" class="box">
            <div v-html="resizeSvg(icons['time'], 20, 20)">

            </div>
            <label>
                {{ customer.items[0].created_at }}
            </label>
            
        </div>
      </div>

      <div style="display: flex; justify-content: center; align-items: center; flex-direction: column">
        <div class="box">
          <div v-html="resizeSvg(icons['fire'], 20, 20)">

          </div>
          <label>
              {{ customer.power }}
          </label>
          
        </div>

        <button class="x" type="button" @click="deleteCustomer(customer.id, index)">
            <div v-html="resizeSvg(icons['deleteImg'], 20, 20)">

          </div>
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
      console.error('error in getting view page response');
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
      console.error('error in getting view page response');
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
  gap: 5px;
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
  box-shadow: 0 2px 8px rgba(255, 255, 255, 0.05);
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
  gap: 5px;
  max-width: 100%; /* sécurité */
  overflow: hidden;
  font-size: 14px;
}

.box label {
  max-width: 150px;
  min-width: 80px;
  white-space: nowrap;       /* reste sur une seule ligne */
  overflow: hidden;          /* masque ce qui dépasse */
  text-overflow: ellipsis;   /* ajoute "…" si le texte est trop long */
  display: inline-block;
}

.x {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background-color: var(--color-rady);
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  color: var(--color-whity)
}

</style>
