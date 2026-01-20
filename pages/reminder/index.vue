<template>
  <div class="container">
    <!-- ✅ Fenêtre flottante pour créer/éditer un reminder -->
    <Reminder
      v-if="isReminder"
      :auth="auth"
      :id="selectedReminder?.id"
      :initialDate="selectedReminder?.reminder_date"
      :initialNote="parseNote(selectedReminder?.note)"
      @update:modelValue="refreshReminds"
      @close="closeReminder"
    />

    <!-- ✅ Modal View Order -->
    <div v-if="showOrderView" class="overlay-modal" @click.self="showOrderView = false">
        <div class="modal-box">
             <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                 <h2>{{ t('Order Details') }} #{{ selectedOrder?.id }}</h2>
                 <Btn svg="x" iconColor="#ff5555" @click:ok="showOrderView = false" :isSimple="true"/>
             </div>
             <ViewOrder
               v-if="selectedOrder"
               :name="selectedOrder.name"
               :phone="selectedOrder.phone"
               :adresse="selectedOrder.mZone"
               :wilaya="selectedOrder.deliveryZone"
               :commune="selectedOrder.sZone"
               :deliveryType="parseInt(selectedOrder.type)"
               :deliveryMethod="selectedOrder.method"
               :deliveryFees="parseInt(selectedOrder.deliveryValue)"
               :products="selectedOrder.items"
               :total="parseInt(selectedOrder.total)"
             />
        </div>
    </div>

    <div class="reminder-page">
      <!-- 🧭 En-tête -->
      <div class="boxContainer1 header">
        <div class="title">
          <div v-html="resizeSvg(icons['calendar'], 20, 20)"></div>
          <h1 v-if="dataReminds">{{ dataReminds.length }} {{ t('reminds') }}</h1>
        </div>

        <CallToAction
          :text="t('add remind')"
          :svg="iconsFilled['calendar']"
          @clicked="openCreateReminder"
        />
      </div>

      <!-- 🧩 Liste des reminders -->
      <div
        v-for="(remind, index) in dataReminds"
        :key="index"
        class="reminder-item"
      >
        <div class="reminder-header">
          <div class="reminder-info">
            <div style="display: flex; gap: 10px; align-items: center;">
              <span class="reminder-id">#{{ remind.id }}</span>
              <span class="reminder-date">{{ remind.reminder_date }}</span>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; margin-top: 5px;">
              <div v-if="remind.username" style="display: flex; align-items: center; gap: 5px; font-size: 0.85rem; color: #555;">
                <img v-if="remind.user_image" :src="`https://management.hoggari.com/uploads/profile/${remind.user_image}`" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;" />
                <span class="dark:text-white">{{ remind.username }}</span>
              </div>

              <div
                v-if="remind.order_id"
                class="order-badge"
                @click="viewOrderDetails(remind.order_id)"
              >
                Order #{{ remind.order_id }}
              </div>
            </div>
          </div>

          <div class="reminder-actions">
            <button @click="updateRemindeWorker(remind)">
                <Radio :selected="remind.work"/>
            </button>
            <Btn svg="edit" iconColor="var(--color-primary)" @click:ok="editReminder(remind)" />
            <Btn svg="trashX" iconColor="#ff5555" @click:ok="remouveReminder(remind.id, remind.order_id)" />
          </div>
        </div>

        <!-- ✅ Notes affichées proprement -->
        <div class="reminder-notes">
          <div
            v-for="(n, i) in parseNote(remind.note)"
            :key="i"
            class="note"
            :style="{ backgroundColor: n.color || '#fff8a3' }"
          >
            <img
              v-if="n.profile_image"
              class="note-avatar"
              :src="`https://management.hoggari.com/uploads/profile/${n.profile_image}`"
              alt="profile"
            />
            <div class="note-content">
              <p class="note-text">{{ n.text }}</p>
              <small class="note-user">@{{ n.user }}</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import Reminder from '../components/reminder.vue'
import ViewOrder from '../components/elements/newBloc/viewOrder.vue'
import { useAuth } from '../../composables/useAuth'
import { useReminder } from '../../composables/reminder'
import { useOrders } from '../../composables/getOrders'
import icons from '~/public/icons.json'
import iconsFilled from '~/public/iconsFilled.json'
import { useLang } from '~/composables/useLang'
import Radio from '../../components/elements/bloc/radio.vue'
import Btn from '../../components/elements/newBloc/rectBtn.vue'
import CallToAction from '../../components/elements/bloc/callToActionBtn.vue'

const { t } = useLang()
const { auth, getauth } = useAuth()
const { getReminds, updateRemind, remouveRemind, dataReminds } = useReminder()
const { getOrder } = useOrders()

const isReminder = ref(false)
const selectedReminder = ref(null)
const showOrderView = ref(false)
const selectedOrder = ref(null)

const resizeSvg = (svg, width, height) => {
  return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
}

// ✅ Parse proprement la note reçue du backend
const parseNote = (noteStr) => {
  try {
      if(Array.isArray(noteStr)) return noteStr;
      if (typeof noteStr !== 'string') return [];
    const parsed = JSON.parse(noteStr)
    return Array.isArray(parsed) ? parsed : []
  } catch {
    return []
  }
}

const updateRemindeWorker = async (remind) => {
    remind.work = !remind.work
    // Note: passing array directly, make sure backend handles it (it should as we fixed it)
    await updateRemind(remind.id, remind.note, remind.reminder_date, remind.work)
    refreshReminds()
}

const remouveReminder = async (id, orderId) => {
    if(!confirm(t('Are you sure you want to delete this reminder?'))) return;
    await remouveRemind(id, orderId)
    refreshReminds()
}

const refreshReminds = async () => {
  isReminder.value = false
  selectedReminder.value = null
  await getReminds()
}

const openCreateReminder = () => {
    selectedReminder.value = null
    isReminder.value = true
}

const editReminder = (remind) => {
    selectedReminder.value = remind
    isReminder.value = true
}

const closeReminder = () => {
    isReminder.value = false
    selectedReminder.value = null
}

const viewOrderDetails = async (id) => {
  const order = await getOrder(id)
  if (order) {
    selectedOrder.value = order
    showOrderView.value = true
  }
}

onMounted(() => {
  getauth()
  getReminds()
})
</script>


<style>

.boxContainer1 {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    max-width: 800px;
    min-width: 300px;
    background-color: var(--color-whitly);
    border-radius: 6px;
    transition: all 0.3s ease;
    padding-block: 10px;
    margin-block: 10px;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.15);
  }
  .dark .boxContainer1{
    background-color: var(--color-darkly);
  }

.reminder-page {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
  width: 100%;
  max-width: 900px;
  margin: auto;
  padding-bottom: 50px;
}

/* En-tête */
.boxContainer1.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.title {
  display: flex;
  align-items: center;
  gap: 8px;
}

.reminder-actions {
    width: 140px; /* Increased width to accommodate extra button */
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 5px;
}

/* Bloc reminder */
.reminder-item {
  width: 100%;
  max-width: 800px;
  background-color: var(--color-whitly);
  border-radius: 8px;
  padding: 12px 16px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.reminder-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
}

.dark .reminder-item {
  background-color: var(--color-darkly);
}

/* Header de chaque reminder */
.reminder-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.reminder-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.reminder-id {
  font-weight: 600;
  color: var(--color-primary);
}

.reminder-date {
  font-size: 0.9rem;
  opacity: 0.8;
}

.order-badge {
    font-size: 0.8rem;
    background: var(--color-yelly);
    color: #000;
    padding: 2px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.2s;
}
.order-badge:hover {
    opacity: 0.8;
    text-decoration: underline;
}

/* Notes */
.reminder-notes {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.note {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  border-radius: 8px;
  padding: 8px 10px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.note-avatar {
  width: 35px;
  height: 35px;
  border-radius: 50%;
  object-fit: cover;
  flex-shrink: 0;
}

.note-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.note-text {
  font-weight: 500;
  margin: 0;
}

.note-user {
  font-size: 0.8rem;
  color: #555;
}

/* Modal styles */
.overlay-modal {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.5);
  display: flex; justify-content: center; align-items: center;
  z-index: 2000;
  backdrop-filter: blur(2px);
}
.modal-box {
  background: var(--color-whity);
  padding: 20px;
  border-radius: 10px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.dark .modal-box {
  background: var(--color-darkly);
}

</style>
