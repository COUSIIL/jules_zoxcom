<template>
  <Loader v-if="loading" width="80px" />
  <Confirm :isVisible="showConfirm" @confirm="deleteOrder(idToEdit, indexToEdit), showConfirm = false"
    @cancel="cancelConfirmDelete" />

  <Confirm :isVisible="isUpdatingTrcking" @confirm="updateOrderValue(orderTracking.id, 'tracking_code', orderTracking.tracking), isUpdatingTrcking = false"
    @cancel="cancelConfirmDelete" />

  <Message :isVisible="showOrLog" :message="orLog" @ok="orLog = '', showOrLog = false" />

  <Reminder v-if="isReminder" :auth="auth" @update:modelValue="(vl) => saveReminder(vl, idToRemind)"
    @close="isReminder = false" />



  <Filter v-if="isFilter" :wilayas="wilayas" :products="resultProduct?.data || []" @close="isFilter = false" @selected="filterSelected" />

  <Selector :options="statusOptions" :showIt="showStatus" :disabled="true" @close="showStatus = false"
    @update:modelValue="editStatus" />

  <Selector :options="moreOptions" :showIt="showMoreOption" :disabled="true" @close="showMoreOption = false"
    @update:modelValue="moreOption" />

  <Selector v-if="delegateOption" :options="delegateOption" :showIt="showDelegate" :disabled="true" @close="showDelegate = false"
    @update:modelValue="delegate" />

  <Selector :options="exportOptions" :showIt="showExport" :disabled="true" @close="showExport = false"
    @update:modelValue="exportation" />

  <!-- PIN MODAL -->
  <div v-if="showPinModal" class="overlay-modal">
    <div class="modal-box">
      <h3>{{ t('Pin Order') }} #{{ selectDelegate?.id }}</h3>
      <p>{{ t('Enter a reason for pinning this order:') }}</p>
      <textarea v-model="pinReason" class="pin-input" :placeholder="t('Reason...')"></textarea>

      <div class="modal-actions">
        <RectBtn :text="t('Cancel')" iconColor="#ff5555" svg="x" @click:ok="showPinModal = false" :isSimple="true"/>
        <RectBtn v-if="selectDelegate?.is_pinned" :text="t('Unpin')" iconColor="#ff5555" svg="unpin" @click:ok="confirmUnpin" :isSimple="true"/>
        <RectBtn :text="t('Save Pin')" iconColor="#2ecc71" svg="pin" @click:ok="confirmPin" :isSimple="true"/>
      </div>
    </div>
  </div>


  <Selector :options="multyOption" :showIt="showMultyMoreOption" :disabled="true" @close="showMultyMoreOption = false"
    @update:modelValue="selectingAllOrders" />

  <List :options="selectedOrderMapList" :showIt="showOrderMapList" :disabled="true" @close="showOrderMapList = false"
    @update:value="unselectOrder" @update:options="updateOrderMapList" />

  <Action :options="selectedOrder" :showIt="showAction" @close="showAction = false" @editStat="editStats"/>

  <HistoryModal :isVisible="showHistoryModal" :orderId="historyOrderId" @close="showHistoryModal = false" />

  <Confirm :isVisible="showConfirmArchive" :message="t('Archive selected orders?')" @confirm="archiveOrders" @cancel="showConfirmArchive = false" />
  <Confirm :isVisible="showConfirmReset" :message="t('All orders archived. Reset Order IDs to 1?')" @confirm="resetIds" @cancel="showConfirmReset = false" />

  <nav v-if="showDeliver" class="overlay">
    <Deliver v-if="!isShipping" :isVisible="showDeliver" :_name="nameDeliver" :_phone1="phoneDeliver"
      :_total="totalDeliver" :_indexing="indexDeliver" @confirm="shipping" @cancel="cancelShipping" />
    <div v-else-if="isShipping">
      <Loader width="80px" />
    </div>
  </nav>

  <div class="containerOrder">
    <div class="boxRow">
      <Search style="width: 90%;" v-model:searcher="searchValue" @search-submitted="onSearch" />
      <RectBtn style="width: 10%;" svg="filter" @click:ok="isFilter = true" />
    </div>

    <div v-if="orderData" class="boxColumn">
      <div v-if="orderData[0]" class="boxColumn">
        <div class="boxRow">
          <div v-html="resizeSvg(iconsFilled['order'], 18, 18)">

          </div>
          <div>
            {{ t('number of orders: ') }}
          </div>
          <div style="font-weight: bold; color: var(--color-zioly4)">
            {{ ordersCount }}
          </div>

        </div>
        <div class="boxRow">
          <div v-html="resizeSvg(iconsFilled['calendar'], 18, 18)">

          </div>
          <div>
            {{ t('first order at: ') }}
          </div>
          <div style="font-weight: bold; color: var(--color-zioly4)">
            {{ orderData[0].create }}
          </div>


        </div>

      </div>

      <div class="line">

      </div>

    </div>

    <!--Switcher /-->

    

    <div class="actionBar">
      <!--div class="cutDiver">

      </!--div-->
      <div class="floatingBtn2">

        <RectBtn :text="t('action')"
          @click:ok="showAction = true" svg="play" :isSimple="true" />

        <RectBtn v-if="selectedOrder.length > 0" :text="t('archive')"
          @click:ok="confirmArchive" svg="package" :isSimple="true" iconColor="#f39c12" />

        <RectBtn :text="selectedOrder.length + ' ' + t('select')"
          @click:ok="showOrderMapList = true" svg="finger" :isSimple="true" />

        <RectBtn svg="dots"
          @click:ok="showMultyMoreOption = true" :isSimple="true" />
        

      </div>
    </div>

    <div v-if="limitedDt && limitedDt.length" class="uler">
      <div v-for="(dts, index) in limitedDt" :key="index" class="ulerli">
        <div @contextmenu.prevent="onRightClick(dts, $event)" style="width: 100%;">

          <div style="display: flex; justify-content: space-between; align-items: center">
            <Bubble v-if="dts?.reminder_id" text="" img="alarm"/>
            <div class="actionBar">
              <!-- PIN INDICATOR -->
              <div v-if="dts.is_pinned" class="pinned-indicator" :title="dts.pin_reason">
                 <div v-html="resizeSvg(icons['pin'], 24, 24)"></div>

                 
              </div>
            <!--div class="cutDiver">

            </!--div-->
              <div class="floatingBtn">


                <RectBtn iconColor="#ff5555" svg="trashX" @click:ok="showConfirmDelete(dts.id, index)" :isSimple="true" />


                  
                <RectBtn v-if="isWorkingDelegate && dts.delegated == 1"
                  iconColor="var(--color-greeny)"
                  svg="share"
                  @click:ok="openLink"
                  :isSimple="true"
                />



                

                <RectBtn :text="dts.status" :iconColor="returnColor(dts.status)" :svg="returnSVG(dts.status)"
                  @click:ok="returnStatusList(dts.status, dts.id, index)" :isSimple="true" />

                <RectBtn svg="dots"
                  @click:ok="selectDelegate = dts, showMoreOption = true" :isSimple="true" />

                <button class="radioBtn" @click="selectOrder(dts)">
                  <Radio :selected="dts.isSelected" />
                </button>

              </div>
            </div>
          </div>
          
          

        <div class="box1">

            <div v-if="dts.ownerStateParsed" class="owner_state" @click="openHistory(dts)" style="cursor: pointer">
               <img v-if="dts.ownerStateParsed.image" :src="dts.ownerStateParsed.image.startsWith('http') ? dts.ownerStateParsed.image : webLink + dts.ownerStateParsed.image" :alt="dts.ownerStateParsed.name">
               <img v-else-if="dts.ownerStateParsed.name === 'Bot' || dts.ownerStateParsed.type === 'bot'" src="https://cdn-icons-png.flaticon.com/512/4712/4712027.png" alt="Bot" style="width: 32px; height: 32px; border-radius: 50%;">

               <h1>{{ dts.ownerStateParsed.name }} :</h1>
               <p>{{ dts.ownerStateParsed.action }}</p>
            </div>
            <div v-else-if="dts.owner" class="owner_state" @click="openHistory(dts)" style="cursor: pointer">
               <img v-if="newMembers[dts.owner]?.profile_image" :src="webLink + newMembers[dts.owner].profile_image" :alt="dts.owner">
               <h1>{{ formatOwner(dts.owner) }} :</h1>
               <p v-if="dts.owner_conf_date">{{ t('confirmed at') }} {{ dts.owner_conf_date }}</p>
               <p v-else>{{ t('confirmed') }}</p>
            </div>

          <div class="order-item" role="listitem" aria-label="order" :class="[

            { active: dts.isMore }
          ]">
            <button type="button" :class="[
              'title1',
              `status-${dts.status.toLowerCase()}`,
              { active: dts.isMore }
            ]" @click="doMore(index)">

              {{ index + 1 }}

            </button>
            <!-- HTML (version nettoyée) -->
            <div class="box2" @click="doMore(index)">
              <!-- groupe 1 -->
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

              <!-- groupe 2 -->
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







            <SquareBtn icon="phone" width="24" height="24" @click:ok="hrefLink(dts.phone)" />

          </div>

          <div
            v-if="Array.isArray(dts.note) && dts.note.length > 0 && dts.note.some(n => n?.text?.trim()) && !dts.isMore"
            :class="['noteBox', dts.status ? `status-${dts.status.toLowerCase()}` : '']">



            <Note :speed="80" :gap="48">
              <div v-for="noti in dts.note">
                <div v-if="noti?.text?.trim()" class="rowFlex2">
                  <img class="circleImg"
                    :src="noti?.profile_image ? `https://management.hoggari.com/uploads/profile/${noti.profile_image}` : 'https://management.hoggari.com/uploads/profile/default.png'"
                    :alt="noti?.profile_image || t('user profile')" />
                  <span v-if="noti.user" class="note-user"> {{ noti.user }} : </span>
                  <span class="note-text">{{ noti.text }}</span>


                </div>

              </div>

            </Note>
          </div>

          <div v-if="dts.isMore" class="order-details">

            <button class="copyCard" @click="copyIp(dts.ip, index)" title="Copy">
              <h4>
                <p>
                  <b>IP: </b>
                  {{ dts.ip }}
                </p>

                <div v-if="dts.copiedId === false" v-html="resizeSvg(icons['copy'], 18, 18)"></div>
                <div v-else v-html="resizeSvg(iconsFilled['copyCheck'], 18, 18)"></div>

              </h4>

            </button>

            <div :class="[
              'editableSection',
              { click: editOrderChanger, exit: !editOrderChanger }
            ]">
              <div v-if="!dts.isEditing">
                <div class="actionBar2">
                  <!--div class="cutDiver">

                  </!--div-->
                  <RectBtn svg="edit" @click:ok="editOrder(index, true)" :isSimple="true" />
                </div>
                <!-- Livraison & Infos -->
                <div class="grid2">
                  <div class="copyCard">
                    <div class="rowFlex">
                      <h4>{{ t('localisation') }}</h4>
                    </div>

                    <p><b>Wilaya:</b> {{ dts.deliveryZone }}</p>
                    <p><b>Commune:</b> {{ dts.sZone }}</p>
                    <p><b>Adresse:</b> {{ dts.mZone }}</p>

                  </div>

                  <div class="copyCard infos">

                    <div class="rowFlex">
                      <h4>{{ t('customer information') }}</h4>
                    </div>
                    <p><b>Nom:</b> {{ dts.name }}</p>
                    <p><b>Téléphone:</b> {{ dts.phone }}</p>
                    <p><b>Date:</b> {{ dts.create }}</p>
                  </div>

                  <div class="copyCard infos">

                    <div class="rowFlex">
                      <h4>{{ t('delivery') }}</h4>
                    </div>
                    <p><b>{{ t('deliver name') }}:</b> {{ dts.method }}</p>
                    <p v-if="dts.type == 0"><b>{{ t('delivery type') }}:</b> {{ t('home') }}</p>
                    <p v-else><b>{{ t('delivery type') }}:</b> {{ t('stop desk') }}</p>
                    <p><b>{{ t('fees') }}:</b> {{ dts.deliveryValue }} DA</p>
                    <Inputer v-model="dts.tracking" :placeHolder="t('tracking code')" :placeholder="dts.tracking" :img="icons['adresse']" @onBlur="orderTracking = dts, isUpdatingTrcking = true" />
                    <p><b>{{ t('activity') }}:</b>
                    <div class="activityText" v-if="dts.activity">{{ dts.activity }}</div>
                    </p>



                  </div>

                  <!-- Produits -->
                  <div class="products">
                    <ul>
                      <div class="copyCard">
                        <div class="rowFlex">
                          <h4>{{ t('products') }}</h4>
                        </div>
                        <li v-for="(item, idx) in dts.items" :key="idx" class="product">

                          <div class="product-info">
                            <div>
                              <div class="product-img-wrapper">
                                <img :src="item.image" alt="product" class="product-img" />
                              </div>
                              <p class="product-name">{{ item.productName }}</p>
                            </div>

                            <div class="columnFlex">
                              <div v-if="item.items.length > 0" v-for="(sub, i2) in item.items" :key="i2"
                                class="sub-item">
                                <div class="tags">

                                  <span class="tag color">
                                    <span class="color-dot" :style="{ background: sub.color }"></span>
                                    {{ sub.color_name }}
                                  </span>
                                  <span class="tag size">Taille: {{ sub.size }}</span>
                                  <span class="tag qty">x{{ sub.qty }}</span>
                                </div>

                                <div class="price">
                                  <span v-if="sub.promo && sub.promo !== '0.00'" class="promo">
                                    <span class="old">{{ sub.total }} DA</span>
                                    <span class="new">{{ sub.promo }} DA</span>
                                  </span>
                                  <span v-else class="new">{{ sub.total }} DA</span>
                                </div>


                              </div>
                              <div v-else class="sub-item">
                                <div class="tags">
                                  <span class="tag size">{{ item.productName }}</span>
                                  <span class="tag qty">x{{ item.qty }}</span>
                                </div>

                                <div class="price">
                                  <span v-if="item.promo && item.promo !== '0.00'" class="promo">
                                    <span class="old">{{ item.price }} DA</span>
                                    <span class="new">{{ item.promo }} DA</span>
                                  </span>
                                  <span v-else class="new">{{ item.price }} DA</span>
                                </div>
                              </div>
                            </div>

                          </div>
                        </li>
                      </div>

                    </ul>
                  </div>
                </div>


                <!-- Total -->
                <div class="copyCard total">
                  <h3>Total: {{ dts.total }} DA</h3>
                </div>
              </div>

              <div v-else>
                <div class="actionBar2">
                  <!--div class="cutDiver">

                  </!--div-->
                  <RectBtn svg="x" @click:ok="editOrder(index, false)" :isSimple="true" />
                </div>
                <EditOrder v-if="dts?.commune" :wilayas="dts.wilayas" :communes="dts.commune" :name="dts.name"
                  :phone="dts.phone" :wilaya="dts.deliveryZone" :adresse="dts.mZone" :commune="dts.selectedCommune"
                  :deliveryType="parseFloat(dts.deliveryType)" :deliveryFees="parseFloat(dts.deliveryValue)" :products="dts.items"
                  :resultProduct="resultProduct" :deliveryMethod="dts.method" :deliverySty="dts.deliverySty"
                  :total="parseInt(dts.total)" :id="parseInt(dts.id)" :selectedFees="dts.selectedFees"
                  :isDesk="dts.has_desk" @update:wilaya="vl => updateCommune(index, vl)"
                    @update:deskFees="updateFees('desk', index)"
                    @update:homeFees="updateFees('home', index)"
                  @updated="editOrder(index, false), getOrders()" @update:products="vl => updateProducts(index, vl)"
                  @update:commune="vl => updateSelectedFees(index, vl)" />
              </div>

            </div>






            <PostIt :modelValue="dts.note" :color="currentColor" :size="180" :rotate="0" :auth="auth"
              @update:modelValue="(notesArray) => onPostItUpdate(notesArray, index)" />

            <div v-if="dts?.reminder_id">
              <Reminder2 v-if="dts.reminder" :reminder="dts.reminder" :orderId="parseInt(dts.id)" @deleted="actualizeRemindRemouve"  />
            </div>


            <CallToAction v-if="!dts?.reminder_id || !dts?.reminder" :text="t('add remind')" :svg="iconsFilled['calendar']"
              @clicked="idToRemind = dts.id, isReminder = true, statusIndex = index" />

          </div>
        </div>
        </div>

      </div>
    </div>
    <RectBtn style="margin-bottom: 50px;" text="more" @click="limitNewDt()" />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import './main.css'

import { useOrders } from '../../composables/getOrders';
import { useDelivery } from '../../composables/getDelivery';
//import Switcher from '../../components/elements/newBloc/switcher.vue';
import Loader from '../../components/elements/animations/loaderBlack.vue';
import RectBtn from '../../components/elements/newBloc/rectBtn.vue';
import SquareBtn from '../../components/elements/newBloc/squareConBtn.vue';
import Note from '../../components/elements/newBloc/noteTicker.vue';
import PostIt from '../../components/elements/newBloc/postIt.vue';
import Selector from '../../components/elements/bloc/select.vue';
import Inputer from '../../components/elements/bloc/input.vue';
import List from '../../components/elements/bloc/list.vue';
import Radio from '../../components/elements/bloc/radio.vue';
import Confirm from '../../components/elements/bloc/confirm.vue';
import Message from '../../components/elements/bloc/message.vue';
import Deliver from '../../components/deliver.vue';
import Search from '../../components/search.vue';
import CallToAction from '../../components/elements/bloc/callToActionBtn.vue'
import Reminder from '../../components/reminder.vue'
import Filter from '../../components/filter.vue'
import Reminder2 from '../../components/elements/newBloc/reminder.vue'
import EditOrder from '../../components/elements/newBloc/editOrder.vue'
import Bubble from '../../components/elements/newBloc/bubble.vue'
import Action from '../../components/elements/newBloc/action.vue'
import HistoryModal from '../../components/elements/newBloc/historyModal.vue'
import { useAuth } from '../../composables/useAuth';

import { useReminder } from '../../composables/reminder';
import { useOrderDz } from '../../composables/useOrderDz';
import { useExporter } from '../../composables/useExporter';

const webLink = 'https://management.hoggari.com/uploads/profile/'



const { getRemind, dataRemind } = useReminder();
const { delegateOrder, isWorkingDelegate, isDelegated } = useOrderDz();
const { exportToCSV, exportToThermalPDF } = useExporter();

const { auth, getauth } = useAuth();

import iconsFilled from '../../public/iconsFilled.json'
import icons from '../../public/icons.json'

const { t } = useLang()


const { data, loading, getOrders, deleteOrder, updateOrderValue, deliverOrder, viewTracking, search, filterBy, resultProduct, getProduct, updated, orLog } = useOrders();
const { getDelivery, isUpdatingWilaya, wilayas, deliveryFees, municipalitys, getCommune, setDelivery, deliverySty, selectedFees, setCommune, isDesk } = useDelivery();
const idToEdit = ref(0)
const statusID = ref(0)
const statusIndex = ref(0)
const indexToEdit = ref(0)
const showConfirm = ref(false)
const currentColor = ref('#ffef6c')
const idToRemind = ref(0)
const currentIndex = ref(0)
const searchValue = ref("")
const lastQueryParams = ref({})
let evtSource = null;


const showDeliver = ref(false);
const isShipping = ref(false);
const nameDeliver = ref([]);
const phoneDeliver = ref([]);
const totalDeliver = ref([]);
const indexDeliver = ref([]);
const selectedOrder = ref([]);
const selectedOrderMapList = ref([]);

const isReminder = ref(false)
const isFilter = ref(false)
const editOrderChanger = ref(false)

const productsList = ref([])
const ordersCount = ref(0)
const orderData = ref([])
const showDelegate = ref(false)
const showOrderMapList = ref(false)
const selectDelegate = ref()

const membersLength = ref(0)
const members = ref([])
const newMembers = ref({})
const showOrLog = ref(false)
const showAction = ref(false)
const isUpdatingTrcking = ref(false)
const orderTrcking = ref()

const showHistoryModal = ref(false)
const historyOrderId = ref(0)


const isArchiving = ref(false)
const showConfirmArchive = ref(false)
const showConfirmReset = ref(false)

const confirmArchive = () => {
  if (selectedOrder.value.length === 0) return
  showConfirmArchive.value = true
}

const archiveOrders = async () => {
  showConfirmArchive.value = false
  isArchiving.value = true
  const ids = selectedOrder.value.map(o => o.id)

  try {
    const res = await fetch('https://management.hoggari.com/backend/api.php?action=archiveOrders', {
      method: 'POST',
      body: JSON.stringify({ order_ids: ids })
    })
    const result = await res.json()

    if (result.success) {
      // Clear selection
      selectedOrder.value = []
      selectedOrderMapList.value = []
      for(let i in dt.value) {
        dt.value[i].isSelected = false
      }

      // Refresh
      getOrders()

      if (result.is_empty) {
        showConfirmReset.value = true
      }
    } else {
      alert(result.message || 'Error archiving')
    }
  } catch (e) {
    console.error(e)
  } finally {
    isArchiving.value = false
  }
}

const resetIds = async () => {
  showConfirmReset.value = false
  try {
    const res = await fetch('https://management.hoggari.com/backend/api.php?action=resetOrderIds')
    const result = await res.json()
    alert(result.message)
    getOrders()
  } catch (e) {
    console.error(e)
  }
}



var showConfirmDelete = (id, index) => {
  idToEdit.value = id
  indexToEdit.value = index
  showConfirm.value = true
}

var cancelConfirmDelete = () => {
  idToEdit.value = 0
  indexToEdit.value = 0
  showConfirm.value = false
}

var resizeSvg = (svg, width, height) => {
  return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
}

const showStatus = ref(false)
const showMoreOption = ref(false)
const showMultyMoreOption = ref(false)
const showExport = ref(false)
const showPinModal = ref(false)
const pinReason = ref('')

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

const statusOptions = ref([{ value: 'test', label: 'image', img: '' }])
const multyOption = ref([
  { value: 'all', label: 'Select all orders', img: '' },
  { value: 'visible', label: 'Select visible orders', img: '' },
  { value: 'cancel', label: 'Cancel selected orders', img: '' }
])
const delegateOption = ref([{ value: 'delegate', label: 'Delegate to OrderDz', img: 'orderDz.png' }])
const moreOptions = ref([
  { value: 'delegate', label: 'automatic order tracking', img: resizeSvg(icons['external_off'], 25, 25) },
  { value: 'export', label: 'export', img: resizeSvg(icons['export'], 25, 25) },
  { value: 'pin', label: 'Pin Order', img: resizeSvg(icons['pin'], 25, 25) }
])

const exportOptions = ref([
  { value: 'CSV', label: 'export as CSV', img: resizeSvg(icons['csv'], 25, 25) },
  { value: 'PDF', label: 'export as PDF ', img: resizeSvg(icons['pdf'], 25, 25) }
])

var returnStatusList = (val, id, index) => {
  statusID.value = id
  statusIndex.value = index
  if (val === 'canceled') {
    statusOptions.value = [
      { value: 'confirmed', label: 'Confirm', img: resizeSvg(iconsFilled['thumb-up'], 25, 25) },
      { value: 'waiting', label: 'Wait', img: resizeSvg(iconsFilled['alarm'], 25, 25) },
      { value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25) },
      { value: 'unreaching', label: 'Unreachable', img: resizeSvg(iconsFilled['phone'], 25, 25) }
    ]
  } else if (val === 'waiting' || val === 'pending') {
    statusOptions.value = [
      { value: 'confirmed', label: 'Confirm', img: resizeSvg(iconsFilled['thumb-up'], 25, 25) },
      { value: 'unreaching', label: 'Unreachable', img: resizeSvg(iconsFilled['phone'], 25, 25) },
      { value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25) },
      { value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25) }
    ]
  } else if (val === 'confirmed') {
    statusOptions.value = [
      { value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25) },
      { value: 'unreaching', label: 'Unreachable', img: resizeSvg(iconsFilled['phone'], 25, 25) },
      { value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25) },
      { value: 'waiting', label: 'Wait', img: resizeSvg(iconsFilled['alarm'], 25, 25) }
    ]
  } else if (val === 'completed') {
    statusOptions.value = [
      { value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25) }
    ]
  } else if (val === 'shipping') {
    statusOptions.value = [
      { value: 'returned', label: 'Return', img: resizeSvg(iconsFilled['back'], 25, 25) },
      { value: 'completed', label: 'Completed', img: resizeSvg(iconsFilled['check'], 25, 25) }
    ]
  } else if (val === 'unreaching') {
    statusOptions.value = [
      { value: 'confirmed', label: 'Confirm', img: resizeSvg(iconsFilled['thumb-up'], 25, 25) },
      { value: 'waiting', label: 'Wait', img: resizeSvg(iconsFilled['alarm'], 25, 25) },
      { value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25) },
      { value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25) }
    ]
  } else if (val === 'returned') {
    statusOptions.value = [
      { value: 'confirmed', label: 'Confirm', img: resizeSvg(iconsFilled['thumb-up'], 25, 25) },
      { value: 'unreaching', label: 'Unreachable', img: resizeSvg(iconsFilled['phone'], 25, 25) },
      { value: 'canceled', label: 'Cancel', img: resizeSvg(iconsFilled['x'], 25, 25) },
      { value: 'shipping', label: 'Deliver', img: resizeSvg(iconsFilled['truck'], 25, 25) }
    ]
  }

  showStatus.value = true
}



var returnColor = (vl) => {

  var myColor
  for (let color of statusInfo.value) {

    if (color.name === vl) {
      myColor = color.color
      break
    }
  }

  return myColor

};

var returnSVG = (vl) => {

  var mySvg
  for (let svg of statusInfo.value) {

    if (svg.name === vl) {
      mySvg = svg.svg
      break
    }
  }

  return mySvg

};


const dt = ref([]);
const limit = ref(20);
const limitedDt = ref([]);

watch(data, (newData) => {
  ordersCount.value = data.value.length
  orderData.value = data.value
  if (!newData || newData.length === 0) return;
  reverseOrders(newData);
});





// Texte brut affiché dans le PostIt
const postItText = ref('');

// Initialiser postItText depuis limitedDt
watch(
  () => limitedDt.value[0]?.note,
  (newNotes) => {
    if (newNotes && Array.isArray(newNotes)) {
      postItText.value = newNotes.map(n => n.text).join('\n');
    }
  },
  { immediate: true }
);

onMounted(() => {
  getOrders()
  getauth()
  getDelivery()
  getUsers()
  
  getProduct().then(() => {
    if (resultProduct.value && resultProduct.value.data) {
      productsList.value = resultProduct.value.data
    }
  })

  // SSE Setup
  evtSource = new EventSource('/backend/api.php?action=sseOrders');

  const eventQueue = [];
  let isProcessingQueue = false;

  const processQueue = async () => {
    if (isProcessingQueue) return;
    isProcessingQueue = true;

    // Debounce to batch rapid events
    await new Promise(resolve => setTimeout(resolve, 500));

    if (eventQueue.length > 0) {
      // Process batch - currently we refresh all, but we have the data to be specific
      const batch = [...eventQueue];
      eventQueue.length = 0;

      console.log('Processing SSE batch:', batch);
      await getOrders(lastQueryParams.value, true);
    }

    isProcessingQueue = false;
    // Check if more events arrived
    if (eventQueue.length > 0) {
      processQueue();
    }
  };

  const handleEvent = (event) => {
    try {
      const data = JSON.parse(event.data);
      eventQueue.push(data);
      processQueue();
    } catch (e) {
      console.error("SSE Parse Error", e);
    }
  };

  evtSource.onmessage = handleEvent;
  evtSource.addEventListener('order_update', handleEvent);
  evtSource.addEventListener('ping', () => {}); // Ignore pings

  //getUserData();
});

onUnmounted(() => {
  if (evtSource) {
    evtSource.close();
  }
});


const getUsers = async () => {

  const response = await fetch('https://management.hoggari.com/backend/api.php?action=getUsers', {
    method: 'GET',
  })

  if(!response.ok) {
    console.error(t('error'))
    return
  }

  const result = await response.json()
  membersLength.value = result.data.length
  members.value = result.data
  //console.log('members', members.value)
  for (const member of members.value) {
    newMembers.value[member.username] = {
      username: member.username,
      profile_image: member.profile_image
    };
  }
  

}

const openLink = () => {
  window.open('https://orderdz.com', '_blank')
}

const updateOrderMapList = (newOption) => {
  selectedOrderMapList.value = newOption
}

const unselectOrder = (id) => {

  for(let i in dt.value) {
    if(dt.value[i].id == id) {
      dt.value[i].isSelected = false
      break
      //newOptions.splice(index, 1)
    }
  }
  for(let ii in selectedOrder.value) {
    if(selectedOrder.value[ii].id == id) {
      //dt.value[i].isSelected = false
      selectedOrder.value.splice(ii, 1)
      break
      
    }
  }
}

const selectOrder = (order) => {
  
  for(let i in dt.value) {
    if(order.id == dt.value[i].id) {
      if(dt.value[i].isSelected) {
        for(let ii in selectedOrder.value) {

          selectedOrder.value.splice(ii, 1);
          selectedOrderMapList.value.splice(ii, 1);

          break
        }
        
        dt.value[i].isSelected = false
      } else {
        selectedOrder.value.push(dt.value[i])
        selectedOrderMapList.value.push({value: dt.value[i].id, label: `order ${dt.value[i].id}`})
        dt.value[i].isSelected = true
      }
      break
    }
  }
  
}


const selectingAllOrders = (type) => {

  if(type == 'all') {

    if(selectedOrder.value.length > 0) {

      selectedOrder.value = []
      selectedOrderMapList.value = []
      for(let i in dt.value) {
        dt.value[i].isSelected = false
      }

    }

    for(let i in dt.value) {
      
      selectedOrder.value.push(dt.value[i])
      selectedOrderMapList.value.push({value: dt.value[i].id, label: `order ${dt.value[i].id}`})
      dt.value[i].isSelected = true

    }

  } else if(type == 'visible') {
    if(selectedOrder.value.length > 0) {

      selectedOrder.value = []
      selectedOrderMapList.value = []
      for(let i in limitedDt.value) {
        limitedDt.value[i].isSelected = false
      }

    }

    for(let i in limitedDt.value) {

      
      selectedOrder.value.push(limitedDt.value[i])
      selectedOrderMapList.value.push({value: limitedDt.value[i].id, label: `order ${limitedDt.value[i].id}`})
      limitedDt.value[i].isSelected = true

    }

    

  } else if(type == 'cancel') {
    if(selectedOrder.value.length > 0) {

      selectedOrder.value = []
      selectedOrderMapList.value = []
      for(let i in dt.value) {
        dt.value[i].isSelected = false
      }

    }

  }
}

const onRightClick = (item, event) => {

  // Empêcher menu clic droit du navigateur (si .prevent non utilisé)
  event.preventDefault()

  // Exemple : ouvrir ton menu contextuel
  selectDelegate.value = item
  showMoreOption.value = true
}


// appel — passer le tableau pur
const filterSelected = (vl) => {
  if (typeof vl === 'object') {
      lastQueryParams.value = vl
      filterBy(vl)
  } else if (vl === 'all') {
    lastQueryParams.value = {}
    filterBy('all')
  } else {
    lastQueryParams.value = { status: vl }
    filterBy('status', vl)
  }
}

const onSearch = (val) => {
  lastQueryParams.value = { search: val }
  search(val)
}

const editStatus = async (vl, index, id) => {
  if(index || index == 0) {
    statusIndex.value = index
  }
  if(id) {
    statusID.value = id
  }

  if (vl === 'shipping') {
    const order = dt.value[statusIndex.value];
    nameDeliver.value = [order.name];
    phoneDeliver.value = [order.phone];
    totalDeliver.value = [order.total];
    indexDeliver.value = [statusIndex.value];
    await shipping(statusIndex.value)
    dt.value[statusIndex.value].status = vl
    

  } else if (vl === 'confirmed') {
    const mainStatus = dt.value[statusIndex.value].status
    dt.value[statusIndex.value].status = vl
    
    await updateOrderValue(statusID.value, 'status', vl, auth.value.username);
    dt.value[statusIndex.value].owner = auth.value.username
    if(updated.value == -1) {
      dt.value[statusIndex.value].owner = null
      dt.value[statusIndex.value].status = mainStatus
      showOrLog.value = true
    }

  } else {
    const mainStatus = dt.value[statusIndex.value].status
    dt.value[statusIndex.value].status = vl
    await updateOrderValue(statusID.value, 'status', vl, auth.value.username);
    if(updated.value === -1) {
      dt.value[statusIndex.value].status = mainStatus
      showOrLog.value = true
    }
  }
};



const shipping = async (index) => {

  if(index || index == 0) {
    statusIndex.value = index
  }
  

  const order = dt.value[statusIndex.value];
  var ttc = 0
  for (let i of order.items) {
    for (let itm of i.items) {
      if (itm.promo) {
        ttc += parseFloat(itm.promo)
      } else {
        ttc += parseFloat(itm.total)
      }
    }
  }

  const isHome = Number(order.type) === 0; // ✅ 0 = Home, 1 = Stop Desk
  var newMethod
  for(let wil in wilayas.value) {
    if(wilayas.value[wil].wilaya_name == order.deliveryZone) {
      if(isHome) {
        newMethod = wilayas.value[wil].home_method
      } else {
        newMethod = wilayas.value[wil].desk_method 
      }
      break
    }
  }

  if (totalDeliver.value !== parseFloat(order.total)) {
    await deliverOrder(
      order,
      order.items,
      isHome ? 'Home' : 'Stop Desk',
      newMethod,
      totalDeliver.value,
      0,
      order.deliveryZone
    );
  } else {
    await deliverOrder(
      order,
      order.items,
      isHome ? 'Home' : 'Stop Desk',
      order.method,
      ttc,
      order.deliveryValue,
      order.deliveryZone
    );
  }

};

const cancelShipping = () => {
  showDeliver.value = false;
};

const editNote = async (id, note) => {
  let noteJson;
  const authData = JSON.parse(localStorage.getItem('auth'));

  try {
    if (Array.isArray(note)) {
      // ✅ Si c’est un tableau de note
      noteJson = JSON.stringify(
        note.map(n => ({
          text: n.text ?? '',
          user: n.user ?? null,
          // 🧠 Si user existe => image du user connecté, sinon vide
          profile_image: n.user ? authData?.profile_image || '' : '',
          isClientNote: !!n.isClientNote,
          color: n.color ?? '#fff'
        }))
      );
    } else {
      // ⚠ Note simple (client)
      noteJson = JSON.stringify([
        {
          text: typeof note === 'string' ? note : JSON.stringify(note),
          user: null,
          isClientNote: true,
          profile_image: '', // ❌ pas d’image pour le client
          color: '#fff'
        }
      ]);
    }
    const mainStatus = limitedDt.value[statusIndex.value].note
    limitedDt.value[statusIndex.value].note = note
    await updateOrderValue(id, 'note', noteJson, auth.value.username);
    if(updated.value === -1) {
      limitedDt.value[statusIndex.value].note = mainStatus
      showOrLog.value = true
    }
    
  } catch (error) {
    console.error('Erreur lors de la mise à jour des note :', error);
  }
};




const onPostItUpdate = async (notesArray, orderIndex) => {
  // Mettre à jour seulement la commande ciblée
  const order = limitedDt.value[orderIndex];
  const isMoreState = order.isMore; // garder l'état isMore
  order.note = notesArray;

  // Envoyer la mise à jour à l'API
  await editNote(order.id, notesArray);

  // Réassigner pour forcer la réactivité si nécessaire
  limitedDt.value[orderIndex] = { ...order, isMore: isMoreState };
};

const editStats = async (vl) => {
  loading.value = true;
  // Utiliser un Set pour stocker les IDs déjà traités afin d'éviter les doublons
  const processedIds = new Set();
  for (const item of vl) {
    if (!processedIds.has(item.id)) {
      const index = dt.value.findIndex(order => order.id === item.id);
      if (index !== -1) { // S'assurer que la commande existe
        await editStatus(item.toStatus, index, item.id);
        processedIds.add(item.id); // Ajouter l'ID à l'ensemble des IDs traités
      }
    }
  }

  loading.value = false
  
}


const saveReminder = async (remind, order_id) => {
  // 1️⃣ Met à jour en base

  const mainStatus = limitedDt.value[statusIndex.value].reminder
  limitedDt.value[statusIndex.value].reminder = remind.id
  await updateOrderValue(order_id, 'reminder_id', remind.id, auth.value.username)
  if(updated.value === -1) {
    limitedDt.value[statusIndex.value].reminder = mainStatus
    showOrLog.value = true
    
  }
  

  // 2️⃣ Récupère les infos complètes du rappel
  await getRemind(remind.id)

  // 3️⃣ Met à jour localement la ligne courante sans perdre les autres champs
  const currentOrder = limitedDt.value[statusIndex.value]
  if (!currentOrder) return

  limitedDt.value[statusIndex.value] = {
    ...currentOrder,
    reminder_id: remind.id,
    reminder: dataRemind.value
  }

  isReminder.value = false
}

const actualizeRemindRemouve = (id) => {

  const newIndex = limitedDt.value.findIndex(item => parseInt(item.id) === id);
  console.log('id: ', id)
  console.log('newIndex: ', newIndex)
  if (newIndex !== -1) {

    // 3️⃣ Met à jour localement la ligne courante sans perdre les autres champs
    const currentOrder = limitedDt.value[newIndex]
    if (!currentOrder) return

    limitedDt.value[newIndex] = {
      ...currentOrder,
      reminder_id: null,
      reminder: null
    }

    isReminder.value = false
  }
}

const copyIp = async (ip, index) => {

  await navigator.clipboard.writeText(ip)
  limitedDt.value[index].copiedId = true
  setTimeout(() => {
    limitedDt.value[index].copiedId = false
  }, 1000) // 1000 ms = 1 seconde

}

const reverseOrders = async (vl) => {
  if (Array.isArray(vl)) {
    // 1. Snapshot current state map
    const currentStateMap = new Map();
    if (dt.value && dt.value.length > 0) {
      dt.value.forEach(order => {
        currentStateMap.set(order.id, {
          isMore: order.isMore,
          isEditing: order.isEditing,
          isSelected: order.isSelected,
          copiedId: order.copiedId,
          activity: order.activity,
          wilayas: order.wilayas,
          commune: order.commune,
          selectedCommune: order.selectedCommune,
          deliveryType: order.deliveryType,
          has_desk: order.has_desk,
          selectedFees: order.selectedFees,
          deliverySty: order.deliverySty
        });
      });
    }

    dt.value = vl
      .map(item => {
        let note = [];

        try {
          // ✅ Si item.note est une chaîne JSON valide
          if (typeof item.note === 'string') {
            const parsed = JSON.parse(item.note);

            if (Array.isArray(parsed)) {
              note = parsed.map(note => ({
                text: note.text ?? '',
                user: note.user ?? null,
                isClientNote: !!note.isClientNote,
                profile_image: note.profile_image,
                color: note.color ?? '#d6e7ff'
              }));
            } else if (parsed && typeof parsed === 'object') {
              // ✅ Si c’est un seul objet
              note = [{
                text: parsed.text ?? '',
                user: parsed.user ?? null,
                isClientNote: !!parsed.isClientNote,
                profile_image: parsed.profile_image,
                color: parsed.color ?? '#d6e7ff'
              }];
            } else if (parsed) {
              // ✅ Si c’est autre chose (string, nombre, etc.)
              note = [{
                text: String(parsed),
                user: null,
                isClientNote: true,
                profile_image: '',
                color: '#d6e7ff'
              }];
            }
          } else if (Array.isArray(item.note)) {
            // ✅ Déjà un tableau
            note = item.note.map(note => ({
              text: note.text ?? '',
              user: note.user ?? null,
              isClientNote: !!note.isClientNote,
              profile_image: note.profile_image,
              color: note.color ?? '#d6e7ff'
            }));
          } else if (item.note) {
            // ✅ Chaîne brute ou autre
            note = [{
              text: String(item.note),
              user: null,
              isClientNote: true,
              profile_image: '',
              color: '#d6e7ff'
            }];
          }
        } catch (e) {
          // ⚠ JSON invalide → note client simple
          note = [{
            text: String(item.note ?? ''),
            user: null,
            isClientNote: true,
            profile_image: '',
            color: '#d6e7ff'
          }];
        }

        let ownerStateParsed = null;
        try {
            if (item.owner_state) {
                ownerStateParsed = typeof item.owner_state === 'string' ? JSON.parse(item.owner_state) : item.owner_state;
            }
        } catch (e) {
            console.error('Error parsing owner_state', e);
        }

        const existingState = currentStateMap.get(item.id);

        if (existingState) {
          return {
            ...item,
            note,
            ownerStateParsed,
            isMore: existingState.isMore,
            isEditing: existingState.isEditing,
            isSelected: existingState.isSelected,
            copiedId: existingState.copiedId,
            activity: existingState.activity || '',
            wilayas: existingState.wilayas || [],
            commune: existingState.commune || [],
            selectedCommune: existingState.selectedCommune,
            deliveryType: existingState.deliveryType || item.type,
            has_desk: existingState.has_desk,
            selectedFees: existingState.selectedFees,
            deliverySty: existingState.deliverySty
          };
        }

        return {
          ...item,
          ownerStateParsed,
          isMore: false,
          isEditing: false,
          isSelected: false,
          copiedId: false,
          activity: '',
          wilayas: [],
          commune: [],
          note
        };
      })
      .reverse();

    limitedDt.value = dt.value.slice(0, limit.value);
    
  }

  
  
};

const openHistory = (order) => {
  historyOrderId.value = parseInt(order.id)
  showHistoryModal.value = true
}

const moreOption = (vl) => {
  if (vl == 'delegate') {
    showDelegate.value = true
  }
  if (vl == 'export') {
    showExport.value = true
  }
  if (vl == 'pin') {
    pinReason.value = selectDelegate.value.pin_reason || ''
    showPinModal.value = true
  }
}

const confirmPin = async () => {
  if (!selectDelegate.value) return

  const payload = {
    order_id: selectDelegate.value.id,
    reason: pinReason.value
  }

  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=pinOrder', {
      method: 'POST',
      body: JSON.stringify(payload)
    })
    const res = await response.json()
    if (res.success) {
      // Update local state
      const idx = dt.value.findIndex(o => o.id === selectDelegate.value.id)
      if (idx !== -1) {
        dt.value[idx].is_pinned = true
        dt.value[idx].pin_reason = pinReason.value
      }
      showPinModal.value = false
    } else {
      alert(res.message)
    }
  } catch (e) {
    console.error(e)
  }
}

const confirmUnpin = async () => {
    if (!selectDelegate.value) return
    if (!confirm('Unpin this order?')) return

    try {
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=unpinOrder', {
            method: 'POST',
            body: JSON.stringify({ order_id: selectDelegate.value.id })
        })
        const res = await response.json()
        if (res.success) {
            const idx = dt.value.findIndex(o => o.id === selectDelegate.value.id)
            if (idx !== -1) {
                dt.value[idx].is_pinned = false
                dt.value[idx].pin_reason = null
            }
            showPinModal.value = false
        }
    } catch (e) {
        console.error(e)
    }
}

const exportation = (vl) => {

  const order = selectDelegate.value
  if(!order) return

  if(vl == 'CSV') {
    exportToCSV(order)
  } else if(vl == 'PDF') {
    exportToThermalPDF(order)
  }
  
}



const delegate = async (vl) => {

  var newProd = []
  if(dt.value) {
    await getProduct()

    for(let prod of limitedDt.value[0].items) {
      for(let i of resultProduct.value.data) {
        if(i.id == prod.id) {
          newProd.push(i)
        }
      }
      
    }

  }

  for(let com of wilayas.value) {
    if(selectDelegate.value.deliveryZone == com.wilaya_name) {
      await getCommune(com)
      delegateOrder([selectDelegate.value], newProd, com, municipalitys.value);
      const index = dt.value.findIndex(item => item.id === selectDelegate.value.id)
      dt.value[index].delegate = true
      

      const mainStatus = limitedDt.value[statusIndex.value].delegated
      limitedDt.value[statusIndex.value].delegated = 1
      await updateOrderValue(selectDelegate.value.id, 'delegated', 1, auth.value.username);
      if(updated.value === -1) {
        limitedDt.value[statusIndex.value].delegated = mainStatus
        showOrLog.value = true
      }
      

    }
  }
    
  
  

}


const doMore = async (val) => {
  currentIndex.value = val
  limitedDt.value[val].isMore = !limitedDt.value[val].isMore

  if (limitedDt.value[val].isMore === true && limitedDt.value[val].tracking) {
    limitedDt.value[val].activity = await viewTracking(limitedDt.value[val].tracking)
  }

  if (limitedDt.value[val]?.reminder_id) {
    // 2️⃣ Récupère les infos complètes du rappel
    await getRemind(limitedDt.value[val].reminder_id)

    // 3️⃣ Met à jour localement la ligne courante sans perdre les autres champs
    const currentOrder = limitedDt.value[currentIndex.value]
    if (!currentOrder) return

    limitedDt.value[currentIndex.value] = {
      ...currentOrder,
      reminder_id: limitedDt.value[val].reminder_id,
      reminder: dataRemind.value
    }
  }

}


const limitNewDt = () => {
  limit.value += 20;
  limitedDt.value = dt.value.slice(0, limit.value);
};

const hrefLink = (link) => {
  // Si c'est un numéro => tel:...
  if (/^\+?\d+$/.test(link)) {
    window.location.href = `tel:${link}`
  } else {
    // Sinon on considère que c'est un lien web
    window.open(link, "_blank")
  }
}

const editOrder = async (index, val) => {
  editOrderChanger.value = true

  // durée visible de l'animation "in"
  setTimeout(() => {
    const el = document.querySelector('.editableSection.click')
    if (el) {
      el.classList.add('exit')
      // laisse le temps à l'animation "exit" de jouer
      setTimeout(() => {
        editOrderChanger.value = false
        el.classList.remove('exit')
      }, 400) // même durée que gradientOut
    } else {
      editOrderChanger.value = false
    }
  }, 300)

  // activer le mode édition pour la commande sélectionnée
  if (!val) {
    limitedDt.value[index].isEditing = false
    limitedDt.value[index].wilayas = []
    limitedDt.value[index].selectedCommune = null
    limitedDt.value[index].deliveryType = limitedDt.value[index].type
    editOrderChanger.value = false
    
    return
  }

  
  limitedDt.value[index].deliverySty = deliverySty.value
  //console.log('limitedDt.value[index].deliverySty: ', limitedDt.value[index].deliverySty)

  // s'assurer que wilayas est bien un tableau
  if (!Array.isArray(wilayas.value)) {
    console.warn('wilayas n’est pas un tableau :', wilayas.value)
    return
  }

  limitedDt.value[index].wilayas = []
  var currentWilaya


  // remplir la liste des wilayas
  for (const i of wilayas.value) {
    if (limitedDt.value[index].deliveryZone == i.wilaya_name) {
      currentWilaya = i
    }

    limitedDt.value[index].wilayas.push({
      label: i.wilaya_name,
      value: i.wilaya_id,
      img: ''
    })
  }

  
  
  
  await updateCommune(index, currentWilaya)

  limitedDt.value[index].isEditing = true

}

const updateSelectedFees = async (index, vl) => {
  //console.log('vl: ', vl)
  limitedDt.value[index].selectedFees = []
  limitedDt.value[index].deleveryValue = null
  limitedDt.value[index].has_desk = null
  if(vl?.has_stop_desk == 1) {
    limitedDt.value[index].has_desk = true
    isDesk.value = true
  } else {
    limitedDt.value[index].has_desk = false
    isDesk.value = false
  }
  var newMunic
  if(municipalitys.value?.data) {
    newMunic = municipalitys.value.data
  } else {
    newMunic = municipalitys.value
  }
  for (const i of newMunic) {

    if ((i.nom == vl.name) || (i.name == vl.name)) {
      await setCommune(i)
      break

    }
    limitedDt.value[index].selectedCommune = vl.name
  }
  

  if(!limitedDt.value[index]?.deliveryType) {
    limitedDt.value[index].deliveryType = limitedDt.value[index].type
  }

  if (limitedDt.value[index].deliveryType === '1') {
    
    updateFees('desk', index)
  } else {

    updateFees('home', index)
  }

}

const updateCommune = async (index, wilaya) => {

  //console.log('wilaya: ', wilaya)
  
  

  if (!Array.isArray(wilaya)) {
    for (const wil of wilayas.value) {
      if (wil.wilaya_name == wilaya) {
        wilaya = wil
      }
    }
  }

  var newMunic

  if(!limitedDt.value[index].selectedCommune) {
    limitedDt.value[index].selectedCommune = limitedDt.value[index].sZone
    await getCommune(wilaya, limitedDt.value[index].selectedCommune)

    

    if(municipalitys.value?.data) {
      newMunic = municipalitys.value.data
    } else {
      newMunic = municipalitys.value
    }
  } else {
    await getCommune(wilaya)

    if(municipalitys.value?.data) {
      newMunic = municipalitys.value.data
    } else {
      newMunic = municipalitys.value
    }
    if(newMunic[0]?.nom) {
      limitedDt.value[index].selectedCommune = newMunic[0].nom
    } else {
      limitedDt.value[index].selectedCommune = newMunic[0].name
    }
    
  }


  if (!Array.isArray(limitedDt.value[index].commune)) {
    limitedDt.value[index].commune = []
  }

  if (limitedDt.value[index].commune.length > 0) {
    limitedDt.value[index].commune = []
  }

  var selectedCommuneObj = null
  for (const i of newMunic) {
    
    var newName = ''
    if(i.nom) {
      newName = i.nom
    } else {
      newName = i.name
    }

    if (newName == limitedDt.value[index].selectedCommune) {
      selectedCommuneObj = i
    }

    if (i.has_stop_desk == 0) {
      
      limitedDt.value[index].commune.push({
        label: newName,
        value: i,
        img: ''
      })
    } else {
      limitedDt.value[index].commune.push({
        label: newName + ' (Desk)',
        value: i,
        img: ''
      })
    }

  }

  limitedDt.value[index].has_desk = null

  if (selectedCommuneObj) {
    await setCommune(selectedCommuneObj)
    if(selectedCommuneObj.has_stop_desk == 1) {
      limitedDt.value[index].has_desk = true
      isDesk.value = true
    } else {
      limitedDt.value[index].has_desk = false
      isDesk.value = false
    }
  }

  if(selectedFees.value) {
    limitedDt.value[index].selectedFees = selectedFees.value
    
  }

  if(!limitedDt.value[index]?.deliveryType) {
    limitedDt.value[index].deliveryType = limitedDt.value[index].type
  }
  
  
  if (limitedDt.value[index].deliveryType === '1') {
    updateFees('desk', index)
  } else {
    updateFees('home', index)
  }

}

function updateProducts(index, lv) {
  limitedDt.value[index].products = lv
  limitedDt.value[index].wilayas = []
}

const updateFees = (type, index) => {
  // S'il n'y a pas de point relais, on retombe toujours sur le tarif "home"
  const useDeskFees = type === 'desk' && isDesk.value

  limitedDt.value[index].deliveryType = useDeskFees ? '1' : '0'
  setDelivery(useDeskFees ? '1' : '0')

  const newFees = selectedFees.value
  limitedDt.value[index].selectedFees = newFees

  limitedDt.value[index].deliveryValue =
    useDeskFees ? newFees.tarif_stopdesk : newFees.tarif
}

const formatOwner = (owner) => {
  // Ensure we display only the username (or appropriate identifier)
  return owner;
}


</script>

<style scoped>
.overlay-modal {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.5);
  display: flex; justify-content: center; align-items: center;
  z-index: 2000;
}
.modal-box {
  background: var(--color-whity);
  padding: 20px;
  border-radius: 10px;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.dark .modal-box {
  background: var(--color-darkly);
}
.pin-input {
  width: 100%;
  height: 80px;
  margin: 15px 0;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
  background: var(--color-whizy);
  color: var(--color-darky);
}
.dark .pin-input {
    background: var(--color-darkow);
    border: 1px solid #444;
    color: var(--color-whity);
}
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}
.pinned-indicator {
    font-size: 1.2rem;
    margin-right: 10px;
}
</style>
