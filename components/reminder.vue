<template>
  <Message :isVisible="showMessage" :message="loger" @ok="showMessage = false"/>

  <div class="reminder-wrapper">
    <div class="reminderBox">

        <div class="rawBox">
            <h1>{{ t('create reminder') }}</h1>
            <Btn :svg="'x'" iconColor="#ff5555" @click:ok="emit('close')"/>
        </div>
      

      <div>
        <VueDatePicker 
          v-model="reminder_date" 
          inline
          format="dd/MM/yyyy HH:mm" 
          :placeholder="t('chose reminder date')"
          :enable-time-picker="true"
          :auto-apply="true"
          class="date-range"
        />
      </div>


      <PostIt 
        :modelValue="note" 
        :size="200" 
        :rotate="0" 
        :auth="auth"
        @update:modelValue="(value) => setNote(value)" 
      />

      
        
        <CallToAction 
            v-if="!loading" 
            :text="t('create new remind')" 
            :svg="resizeSvg(iconsFilled['calendar'], 25, 25)" 
            @clicked="reminder"
        />
        <CallToAction 
            v-else 
            :text="t('creating...')" 
            :svg="resizeSvg(iconsFilled['calendar'], 25, 25)"
        />
      
      

    </div>
  </div>
</template>


<script setup>
    import PostIt from './elements/newBloc/postIt.vue'
    import Btn from './elements/newBloc/rectBtn.vue'
    import CallToAction from './elements/bloc/callToActionBtn.vue';
    import Message from './elements/bloc/message.vue';
    import { ref } from 'vue'
    //import icons from '~/public/icons.json'
    import iconsFilled from '~/public/iconsFilled.json'

    import VueDatePicker from "@vuepic/vue-datepicker"
    import "@vuepic/vue-datepicker/dist/main.css"

    import { useLang } from '~/composables/useLang';
    import { useReminder } from '../composables/reminder';

    const { createRemind, dataRemindCreated, loading } = useReminder();

    const { t } = useLang()

    const props = defineProps({
        auth: { type: Array, default: {} }

    })

    const emit = defineEmits(['update:modelValue', 'close']);

    const resizeSvg = (svg, width, height) => {
        return svg
            .replace(/width="[^"]+"/, `width="${width}"`)
            .replace(/height="[^"]+"/, `height="${height}"`);
    };

    const reminder_date = ref(null)
    const note = ref([])
    const loger = ref('')
    const showMessage = ref(false)

    // 💡 à ajouter dans ton script
    function formatDateToSQL(date) {
        if (!date) return null
        const d = new Date(date)
        const pad = (n) => (n < 10 ? '0' + n : n)
        return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`
    }


    const setNote = (vl) => {
        note.value = vl
    }

    const reminder = async () => {
        if (!reminder_date.value) {
            loger.value = 'Please select a reminder date'
            showMessage.value = true
            return
        }
        if (note.value.length < 1) {
            loger.value = 'Please make a note'
            showMessage.value = true
            return
        }

        // 🕓 Convertir la date avant l'envoi
        const formattedDate = formatDateToSQL(reminder_date.value)

        await createRemind(note.value, formattedDate)

        if (dataRemindCreated.value) {
            emit('update:modelValue', dataRemindCreated.value)
        }
    }


    


</script>

<style>

    /* ✅ Ajout d’un wrapper flottant centré */
    .reminder-wrapper {
        position: fixed; /* reste centré même en scrollant */
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        overflow-y: auto; /* scroll si trop de contenu */
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: rgba(20, 20, 20, 0.3); /* léger fond semi-transparent */
        backdrop-filter: blur(2px);
        z-index: 1000; /* au-dessus du reste */
        padding: 20px;
    }

    /* ✅ Box principale */
    .reminderBox {
        width: min(90%, 340px);
        max-height: 95vh;
        overflow-y: auto; /* scroll interne si contenu dépasse */
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        background-color: var(--color-whitly);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        gap: 15px;
        margin-top: 100px;
    }

    /* ✅ Mode sombre */
    .dark .reminderBox {
        background: var(--color-darkly);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.8);
    }

    .reminderBox h1 {
        font-size: 1.4rem;
        font-weight: bold;
        text-align: center;
    }
    .rawBox{
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

</style>