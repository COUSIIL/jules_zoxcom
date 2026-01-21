<template>
  <Loader v-if="loading" width="80px" />

  <Filter v-if="isFilter" :wilayas="[]" @close="isFilter = false" @selected="filterSelected" />

  <Confirm
    :isVisible="isConfirm"
    :message="t('Are you sure you want to delete this archived order?')"
    @confirm="deleteOrderConfirmed"
    @cancel="isConfirm = false"
  />

  <EditArchiveModal
    :isOpen="isEditOpen"
    :order="currentOrder"
    @close="isEditOpen = false"
    @save="saveOrder"
  />

  <div class="containerOrder">
    <div class="boxRow">
      <Search style="width: 70%;" v-model:searcher="searchValue" @search-submitted="onSearch" />
      <div style="display: flex; gap: 5px; width: 30%; justify-content: flex-end;">
          <RectBtn style="width: 40px;" svg="filter" @click:ok="isFilter = true" />
          <RectBtn :text="t('Export CSV')" svg="csv" :isSimple="true" @click="exportAllCSV" />
      </div>
    </div>

    <div v-if="orderData" class="boxColumn">
      <div v-if="orderData[0]" class="boxColumn">
        <div class="boxRow">
          <div v-html="resizeSvg(iconsFilled['order'], 18, 18)"></div>
          <div>{{ t('number of archived orders: ') }}</div>
          <div style="font-weight: bold; color: var(--color-zioly4)">{{ ordersCount }}</div>
        </div>
      </div>
      <div class="line"></div>
    </div>

    <div v-if="limitedDt && limitedDt.length" class="uler">
      <div v-for="(dts, index) in limitedDt" :key="index" class="ulerli">
        <div style="width: 100%;">

          <div style="display: flex; justify-content: space-between; align-items: center">
            <div class="actionBar">
              <!-- Status Badge -->
              <RectBtn :text="dts.status" :iconColor="returnColor(dts.status)" :svg="returnSVG(dts.status)" :isSimple="true" />

              <!-- Actions -->
              <div class="action-buttons">
                  <!--RectBtn svg="edit" :isSimple="true" @click="openEdit(dts)" /-->
                  <RectBtn svg="trashX" :isSimple="true" iconColor="var(--color-rady)" @click="confirmDelete(dts.id)" />
                  <RectBtn svg="reciept" :isSimple="true" @click="exportPDF(dts)" />
              </div>
            </div>
          </div>

          <div class="box1">
            <div class="order-item" role="listitem" aria-label="order" :class="[{ active: dts.isMore }]">
              <button type="button" :class="['title1', `status-${dts.status ? dts.status.toLowerCase() : ''}`, { active: dts.isMore }]" @click="doMore(index)">
                {{ dts.id }}
              </button>

              <div class="box2" @click="doMore(index)">
                <div class="boxGroup">
                  <div class="boxItem">
                    <div v-html="resizeSvg(iconsFilled['order'], 18, 18)"></div>
                    <p class="text">order-{{ dts.id }}</p>
                  </div>
                  <div class="boxItem">
                    <div v-html="resizeSvg(iconsFilled['phone'], 18, 18)"></div>
                    <p class="text">{{ dts.phone }}</p>
                  </div>
                </div>

                <div class="boxGroup">
                  <div class="boxItem">
                    <div v-html="resizeSvg(iconsFilled['alarm'], 18, 18)"></div>
                    <p class="text">{{ dts.create }}</p>
                  </div>
                  <div class="boxItem">
                    <div v-html="resizeSvg(iconsFilled['location'], 18, 18)"></div>
                    <p class="text">{{ dts.deliveryZone }} - {{ dts.sZone }}</p>
                  </div>
                </div>
              </div>

            </div>

            <div v-if="dts.isMore" class="order-details">
                <!-- Details View -->
                <div class="grid2">
                  <div class="copyCard">
                    <div class="rowFlex"><h4>{{ t('localisation') }}</h4></div>
                    <p><b>Wilaya:</b> {{ dts.deliveryZone }}</p>
                    <p><b>Commune:</b> {{ dts.sZone }}</p>
                    <p><b>Adresse:</b> {{ dts.mZone }}</p>
                  </div>

                  <div class="copyCard infos">
                    <div class="rowFlex"><h4>{{ t('customer information') }}</h4></div>
                    <p><b>Nom:</b> {{ dts.name }}</p>
                    <p><b>Téléphone:</b> {{ dts.phone }}</p>
                    <p><b>Date:</b> {{ dts.create }}</p>
                  </div>

                  <div class="copyCard infos">
                    <div class="rowFlex"><h4>{{ t('delivery') }}</h4></div>
                    <p><b>{{ t('deliver name') }}:</b> {{ dts.method }}</p>
                    <p v-if="dts.type == 0"><b>{{ t('delivery type') }}:</b> {{ t('home') }}</p>
                    <p v-else><b>{{ t('delivery type') }}:</b> {{ t('stop desk') }}</p>
                    <p><b>{{ t('fees') }}:</b> {{ dts.deliveryValue }} DA</p>
                    <p><b>Tracking:</b> {{ dts.tracking }}</p>
                  </div>

                  <!-- Produits -->
                  <div class="products">
                    <ul>
                      <div class="copyCard">
                        <div class="rowFlex"><h4>{{ t('products') }}</h4></div>
                        <li v-for="(item, idx) in dts.items" :key="idx" class="product">
                          <div class="product-info">
                            <div>
                              <div class="product-img-wrapper">
                                <img :src="item.image" alt="product" class="product-img" />
                              </div>
                              <p class="product-name">{{ item.productName }}</p>
                            </div>
                            <div class="columnFlex">
                              <div v-if="item.items.length > 0" v-for="(sub, i2) in item.items" :key="i2" class="sub-item">
                                <div class="tags">
                                  <span class="tag color">
                                    <span class="color-dot" :style="{ background: sub.color }"></span>
                                    {{ sub.color_name }}
                                  </span>
                                  <span class="tag size">Taille: {{ sub.size }}</span>
                                  <span class="tag qty">x{{ sub.qty }}</span>
                                </div>
                                <div class="price">
                                   <span class="new">{{ sub.total }} DA</span>
                                </div>
                              </div>
                              <div v-else class="sub-item">
                                <div class="tags">
                                  <span class="tag size">{{ item.productName }}</span>
                                  <span class="tag qty">x{{ item.qty }}</span>
                                </div>
                                <div class="price">
                                  <span class="new">{{ item.price }} DA</span>
                                </div>
                              </div>
                            </div>
                          </div>
                        </li>
                      </div>
                    </ul>
                  </div>
                </div>

                <div class="copyCard total">
                   <div class="rowFlex">
                      <h3>Total: {{ dts.total }} DA</h3>
                   </div>
                   <!-- Notes -->
                   <div v-if="dts.note && dts.note.length" style="margin-top: 10px;">
                      <h4>Notes:</h4>
                      <p v-for="(n, ni) in dts.note" :key="ni" style="color: #666; font-size: 0.9em;">
                          - {{ n.text }}
                      </p>
                   </div>
                </div>
            </div>

          </div>
        </div>
      </div>
    </div>
    <RectBtn style="margin-bottom: 50px;" text="more" @click="limitNewDt()" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import '../orders/main.css'

import { useArchivedOrders } from '../../composables/getArchivedOrders';
import { useExporter } from '../../composables/useExporter';
import { useLang } from '../../composables/useLang';

import Loader from '../../components/elements/animations/loaderBlack.vue';
import RectBtn from '../../components/elements/newBloc/rectBtn.vue';
import Search from '../../components/search.vue';
import Filter from '../../components/filter.vue';
import Confirm from '../../components/elements/bloc/confirm.vue';
import EditArchiveModal from '../../components/archives/EditArchiveModal.vue';

import iconsFilled from '../../public/iconsFilled.json'
import icons from '../../public/icons.json'

const { t } = useLang()
const { data, loading, getArchivedOrders, search, filterBy, deleteArchivedOrder, updateArchivedOrder } = useArchivedOrders();
const { exportToThermalPDF } = useExporter();

const searchValue = ref("")
const isFilter = ref(false)
const ordersCount = ref(0)
const orderData = ref([])
const limit = ref(20);
const limitedDt = ref([]);
const dt = ref([]);

// Confirm Modal
const isConfirm = ref(false);
const orderToDelete = ref(null);

// Edit Modal
const isEditOpen = ref(false);
const currentOrder = ref(null);

const statusInfo = ref([
  { name: 'canceled', color: 'var(--color-rady)', svg: 'x' },
  { name: 'waiting', color: 'var(--color-rangy)', svg: 'alarm' },
  { name: 'pending', color: 'var(--color-rangy)', svg: 'alarm' },
  { name: 'confirmed', color: 'var(--color-blumy)', svg: 'thumb-up' },
  { name: 'completed', color: 'var(--color-greeny)', svg: 'check' },
  { name: 'shipping', color: 'var(--color-yelly)', svg: 'truck' },
  { name: 'unreaching', color: 'var(--color-gorry)', svg: 'phone' },
  { name: 'returned', color: 'var(--color-ioly)', svg: 'back' }
])

const resizeSvg = (svg, width, height) => {
  if(!svg) return '';
  return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
}

const returnColor = (vl) => {
  var myColor
  for (let color of statusInfo.value) {
    if (color.name === vl) {
      myColor = color.color
      break
    }
  }
  return myColor || 'var(--color-darky)'
};

const returnSVG = (vl) => {
  var mySvg
  for (let svg of statusInfo.value) {
    if (svg.name === vl) {
      mySvg = svg.svg
      break
    }
  }
  return mySvg || 'box'
};

onMounted(() => {
  getArchivedOrders()
});

watch(data, (newData) => {
  if (!newData) return;
  ordersCount.value = data.value.length
  orderData.value = data.value

  dt.value = newData.map(item => ({
    ...item,
    isMore: false,
  }))

  limitedDt.value = dt.value.slice(0, limit.value);
});

const onSearch = (val) => {
  search(val)
}

const filterSelected = (vl) => {
  if (typeof vl === 'object') {
      filterBy(vl)
  } else if (vl === 'all') {
    filterBy('all')
  } else {
    filterBy('status', vl)
  }
}

const doMore = (index) => {
  limitedDt.value[index].isMore = !limitedDt.value[index].isMore
}

const limitNewDt = () => {
  limit.value += 20;
  limitedDt.value = dt.value.slice(0, limit.value);
};

// --- Actions ---

const confirmDelete = (id) => {
    orderToDelete.value = id;
    isConfirm.value = true;
};

const deleteOrderConfirmed = async () => {
    if (orderToDelete.value) {
        await deleteArchivedOrder(orderToDelete.value);
    }
    isConfirm.value = false;
    orderToDelete.value = null;
};

const openEdit = (order) => {
    currentOrder.value = order;
    isEditOpen.value = true;
};

const saveOrder = async (updatedOrder) => {
    const success = await updateArchivedOrder(updatedOrder);
    if (success) {
        isEditOpen.value = false;
        currentOrder.value = null;
    }
};

const exportPDF = (order) => {
    exportToThermalPDF(order);
};

const exportAllCSV = () => {
    // Basic CSV Export for all displayed data
    if (!dt.value || dt.value.length === 0) return;

    const headers = ['ID', 'Date', 'Name', 'Phone', 'Wilaya', 'Commune', 'Address', 'Status', 'Total'];
    const rows = dt.value.map(o => [
        o.id,
        o.create,
        `"${o.name}"`,
        o.phone,
        `"${o.deliveryZone}"`,
        `"${o.sZone}"`,
        `"${o.mZone}"`,
        o.status,
        o.total
    ]);

    let csvContent = "data:text/csv;charset=utf-8,"
        + headers.join(",") + "\n"
        + rows.map(e => e.join(",")).join("\n");

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "archived_orders.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

</script>

<style scoped>

.line {
    width: 100%;
    height: 1px;
    background-color: var(--color-gray);
    margin: 10px 0;
}
.uler {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.actionBar {
    display: flex;
    justify-content: space-between;
    width: 100%;
    align-items: center;
}
.action-buttons {
    display: flex;
    gap: 10px;
}
</style>
