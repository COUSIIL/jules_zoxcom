import { ref } from 'vue';

// --- Le Composable ---
const loading = ref(false)

const dataRemindCreated = ref()
const dataReminds = ref()
const dataRemind = ref()
const dataRemindUpdate = ref()
const dataRemindRemouve = ref()
const log = ref('')
const dataLoad = ref()
const orderIdRemindRemouved  = ref(-1)

export const useReminder = () => {
    const createRemind = async (note, reminder_date, user_id) => {
        loading.value = true;
        const remindRay = JSON.stringify({
                note: note,
                reminder_date: reminder_date,
                work: 1,
                user_id: user_id
        });

        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=createReminder', {
        method: 'POST',
        body: remindRay,
        });
        if(!response2.ok){
            log.value = "error in response";
            loading.value = false;
            return;
        }

        const textResponse = await response2.json();  // Récupérer la réponse en texte
        
        if (textResponse.success) {
            log.value = textResponse.message;
            dataLoad.value = remindRay;
            dataRemindCreated.value = textResponse.data;
            loading.value = false;
        } else {
            log.value = textResponse.message;
            loading.value = false;
        }
        loading.value = false;
    }

    const getReminds = async () => {
        loading.value = true;

        
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getReminder');

        if(!response2.ok){
            log.value = "error in response";
            loading.value = false;
            return;
        }

        const textResponse = await response2.json();  // Récupérer la réponse en texte
        
        if (textResponse.success) {
            log.value = textResponse.message;
            dataReminds.value = textResponse.data;
            loading.value = false;
        } else {
            log.value = textResponse.message;
            loading.value = false;
        }
        loading.value = false;
    }

    const getRemind = async (id) => {
        loading.value = true;
        const remindRay = JSON.stringify({
                id: id,
        });

        
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=getReminder', {
        method: 'POST',
        body: remindRay,
        });
        if(!response2.ok){
            log.value = "error in response";
            loading.value = false;
            return;
        }

        const textResponse = await response2.json();  // Récupérer la réponse en texte
        
        if (textResponse.success) {
            log.value = textResponse.message;
            dataRemind.value = textResponse.data;
            loading.value = false;
        } else {
            log.value = textResponse.message;
            loading.value = false;
        }
        loading.value = false;
    }

    const updateRemind = async (id, note, reminder_date, work) => {
        loading.value = true;
        const remindRay = JSON.stringify({
            id: id,
            note: note,
            reminder_date: reminder_date,
            work: work,
        });

        
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=editReminder', {
        method: 'POST',
        body: remindRay,
        });
        if(!response2.ok){
            log.value = "error in response";
            loading.value = false;
            return;
        }

        const textResponse = await response2.json();  // Récupérer la réponse en texte
        
        if (textResponse.success) {
            log.value = textResponse.message;
            dataRemindUpdate.value = textResponse.data;
            loading.value = false;
        } else {
            log.value = textResponse.message;
            loading.value = false;
        }
        loading.value = false;
    }

    const remouveRemind = async (id, orderId) => {
        loading.value = true;
        const remindRay = JSON.stringify({
                id: id,
                orderId: orderId,
        });

        
        const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=remouveReminder', {
        method: 'POST',
        body: remindRay,
        });
        if(!response2.ok){
            log.value = "error in response";
            loading.value = false;
            return;
        }

        const textResponse = await response2.json();  // Récupérer la réponse en texte
        
        if (textResponse.success) {
            log.value = textResponse.message;
            orderIdRemindRemouved.value = orderId;
            dataRemindRemouve.value = textResponse.data;
            loading.value = false;
            //console.log('orderIdRemindRemouved.value: ', orderIdRemindRemouved.value)
        } else {
            log.value = textResponse.message;
            loading.value = false;
        }
        loading.value = false;
    }

    return {
        dataRemindCreated,
        dataRemind,
        dataReminds,
        dataRemindUpdate,
        dataRemindRemouve,
        log,
        loading,
        createRemind,
        getReminds,
        getRemind,
        updateRemind,
        remouveRemind,
        dataLoad,
        orderIdRemindRemouved
    }
}

