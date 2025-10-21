import { ref, computed} from 'vue';

// --- Le Composable ---

export const useOrder = () => {
    const data = ref();
    const log = ref('');
    const existe = ref(false)


    const getOrder = async (order_id) => {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=getOrder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id }),
        });
        if (!response.ok) {
            log.value = 'error in getting response category';
            return;
        }
        const result = await response.json();
        if (result.success) {
            if (!result.data) {
                log.value = 'No recent orders for now.';
            } else {
                
                log.value = result.message;
            
                data.value = result.data[0];
                
                
            }
            existe.value = true;
            
                
        } else {
            log.value = result.message;
            existe.value = false;
        }

    }



    return {
        data: computed(() => data.value),
        log: computed(() => log.value),
        existe: computed(() => existe.value),

        getOrder,
        

    };
};