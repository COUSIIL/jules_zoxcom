import { ref } from 'vue';
import { useAuth } from './useAuth';

export const useArchivedOrders = () => {
    const { getauth } = useAuth();
    const data = ref([])
    const loading = ref(true)
    const log = ref('')

    const parseNote = (note) => {
        if (!note) return [];
        try {
            const parsed = JSON.parse(note);
            if (Array.isArray(parsed)) return parsed;
            return [];
        } catch (e) {
            return [{ text: note, user: '', color: '', isClientNote: false }];
        }
    };

    const getArchivedOrders = async (queryParams = {}) => {
        loading.value = true;
        const params = new URLSearchParams();
        for (const [key, value] of Object.entries(queryParams)) {
            if (value !== null && value !== undefined && value !== '') {
                params.append(key, value);
            }
        }

        const queryString = params.toString();
        const url = `/backend/api.php?action=getArchivedOrders${queryString ? '&' + queryString : ''}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                log.value = 'Network error';
                return;
            }
            const result = await response.json();
            if (result.success) {
                data.value = (result.data ? result.data : []).map(order => ({
                    ...order,
                    note: parseNote(order.note)
                }));
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
             await getArchivedOrders({ status: value });
        } else if (by === 'all') {
             await getArchivedOrders();
        }
    }

    const deleteArchivedOrder = async (id) => {
        const user = getauth();
        const token = user ? user.token : '';
        const url = `/backend/api.php?action=deleteArchivedOrder`;
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: JSON.stringify({ order_id: id, token }),
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                data.value = data.value.filter(o => o.id !== id);
                return true;
            } else {
                alert('Error: ' + result.message);
                return false;
            }
        } catch (e) {
            alert('Error: ' + e.message);
            return false;
        }
    };

    const updateArchivedOrder = async (updatedOrder) => {
        const user = getauth();
        const token = user ? user.token : '';
        const url = `/backend/api.php?action=editArchivedOrder`;
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: JSON.stringify({
                    order_id: updatedOrder.id,
                    token,
                    ...updatedOrder
                }),
                headers: { 'Content-Type': 'application/json' }
            });
            const result = await response.json();
            if (result.success) {
                // Update local state
                const index = data.value.findIndex(o => o.id === updatedOrder.id);
                if (index !== -1) {
                    data.value[index] = { ...data.value[index], ...updatedOrder };
                }
                return true;
            } else {
                alert('Error: ' + result.message);
                return false;
            }
        } catch (e) {
            alert('Error: ' + e.message);
            return false;
        }
    };

    return {
        data,
        loading,
        log,
        getArchivedOrders,
        search,
        filterBy,
        deleteArchivedOrder,
        updateArchivedOrder
    };
};
