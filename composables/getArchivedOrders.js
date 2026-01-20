import { ref } from 'vue';

export const useArchivedOrders = () => {
    const data = ref([])
    const loading = ref(true)
    const log = ref('')

    const getArchivedOrders = async (queryParams = {}) => {
        loading.value = true;
        const params = new URLSearchParams();
        for (const [key, value] of Object.entries(queryParams)) {
            if (value !== null && value !== undefined && value !== '') {
                params.append(key, value);
            }
        }

        const queryString = params.toString();
        const url = `https://management.hoggari.com/backend/api.php?action=getArchivedOrders${queryString ? '&' + queryString : ''}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                log.value = 'Network error';
                return;
            }
            const result = await response.json();
            if (result.success) {
                data.value = result.data ? result.data.slice() : []; // No need to reverse if sorted in SQL
            } else {
                log.value = result.message;
                data.value = [];
            }
        } catch (e) {
            log.value = e.message;
            data.value = [];
        } finally {
            loading.value = false;
        }
    }

    const search = async (value) => {
        await getArchivedOrders({ search: value });
    }

    const filterBy = async (by, value) => {
        if (typeof by === 'object') {
             await getArchivedOrders(by);
        } else if (by === 'status') {
            // Archives might not need status filter if they are all 'completed' or 'archived',
            // but we keep it just in case we archived canceled ones too.
             await getArchivedOrders({ status: value });
        } else if (by === 'all') {
             await getArchivedOrders();
        }
    }

    return {
        data,
        loading,
        log,
        getArchivedOrders,
        search,
        filterBy
    };
};
