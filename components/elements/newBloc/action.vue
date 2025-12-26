<template>
    <div v-if="showDropdown" class="backClaque" @click="showDropdown = false, emit('close')"></div>

    <div v-if="showDropdown" class="dropdown-list1" :style="{ backgroundColor: color }">

        <div class="centerBox">
            <div class="flexBox">
                <Selector :options="statusList" :showIt="showStatus" :disabled="false" @close="showStatus = false"
                @update:modelValue="showOrder" :placeHolder="t('status to edit')" />




                <Selector v-if="changeStatusList?.length > 0" :options="changeStatusList" :showIt="showStatusToChange" :disabled="false" @close="showStatusToChange = false"
                    @update:modelValue="v => editStat(orderList, v)" :placeHolder="t('change status to')" />
            </div>
        </div>
        
        



        <ul class="rowBox">
            <li class="box2" v-for="(order, index) in orderList" :key="index">

                <div class="flexBox2">
                    <div :class="[
                        'statusBox',
                        `status-${order.status.toLowerCase()}`
                    ]">

                    </div>
                    <p>
                        id: {{ order.id }}
                    </p>
                    <p>
                        {{ order.phone }}
                    </p>
                    <p>
                        {{ order.shippingyZone }} - {{ order.sZone }}
                    </p>
                </div>

            </li>
        </ul>


    </div>
</template>

<script setup>

import Selector from '../bloc/select.vue';

const { t } = useLang()

const props = defineProps({
    modelValue: String,
    options: { default: [], type: Array },
    placeHolder: String,
    color: String,
    showIt: { default: false, type: Boolean },
})

onMounted(() => {

})

const emit = defineEmits(['close', 'editStat'])
const statusList = ref([])

const showDropdown = ref(false)
const changeStatusList = ref([])

const showStatus = ref(false)
const showStatusToChange = ref(false)

watch(() => props.showIt, newVal => {
    statusList.value = []
    if (!showDropdown.value && props.options) {
        var newOptions = props.options
        const filteredStatusConf = newOptions.filter(i => i.status === 'confirmed');
        const filteredStatusCanc = newOptions.filter(i => i.status === 'canceled');
        const filteredStatusShip = newOptions.filter(i => i.status === 'shipping');
        const filteredStatusRetu = newOptions.filter(i => i.status === 'returned');
        const filteredStatusUnre = newOptions.filter(i => i.status === 'unreaching');
        const filteredStatusPend = newOptions.filter(i => i.status === 'waiting');
        const filteredStatusComp = newOptions.filter(i => i.status === 'completed');
        
        if(filteredStatusConf.length > 0) {
            statusList.value.push({ label: `${t('confirmed')} ${filteredStatusConf.length}`, value: filteredStatusConf, selected: false })
        }
        
        if(filteredStatusCanc.length > 0) {
            statusList.value.push({ label: `${t('canceled')} ${filteredStatusCanc.length}`, value: filteredStatusCanc, selected: false })
        }

        if(filteredStatusShip.length > 0) {
            statusList.value.push({ label: `${t('shipping')} ${filteredStatusShip.length}`, value: filteredStatusShip, selected: false })
        }

        if(filteredStatusRetu.length > 0) {
            statusList.value.push({ label: `${t('returned')} ${filteredStatusRetu.length}`, value: filteredStatusRetu, selected: false })
        }

        if(filteredStatusUnre.length > 0) {
            statusList.value.push({ label: `${t('unreaching')} ${filteredStatusUnre.length}`, value: filteredStatusUnre, selected: false })
        }

        if(filteredStatusPend.length > 0) {
            statusList.value.push({ label: `${t('waiting')} ${filteredStatusPend.length}`, value: filteredStatusPend, selected: false })
        }

        if(filteredStatusComp.length > 0) {
            statusList.value.push({ label: `${t('completed')} ${filteredStatusComp.length}`, value: filteredStatusComp, selected: false })
        }



    }

    showDropdown.value = newVal

})
const orderList = ref([])
const showOrder = (order) => {
    orderList.value = order
    const statOrder = order[0].status

    if (statOrder === 'confirmed') {
        changeStatusList.value = [
            { label: 'shipping', value: 'shipping' },
            { label: 'cancel', value: 'canceled' },
            { label: 'unreachable', value: 'unreaching' },
            { label: 'await', value: 'waiting' }
        ]
    } else if (statOrder === 'shipping') {
        changeStatusList.value = [
            { label: 'return', value: 'returned' },
            { label: 'complet', value: 'completed' }
        ]
    } else if (statOrder === 'returned') {
        changeStatusList.value = [
            { label: 'shipping', value: 'shipping' },
            { label: 'cancel', value: 'canceled' },
            { label: 'unreachable', value: 'unreaching' },
            { label: 'confirm', value: 'confirmed' }
        ]
    } else if (statOrder === 'canceled') {
        changeStatusList.value = [
            { label: 'shipping', value: 'shipping' },
            { label: 'confirm', value: 'confirmed' },
            { label: 'unreachable', value: 'unreaching' },
            { label: 'await', value: 'waiting' }
        ]
    } else if (statOrder === 'unreaching') {
        changeStatusList.value = [
            { label: 'shipping', value: 'shipping' },
            { label: 'confirm', value: 'confirmed' },
            { label: 'cancel', value: 'canceled' },
            { label: 'await', value: 'waiting' }
        ]
    } else if (statOrder === 'waiting') {
        changeStatusList.value = [
            { label: 'shipping', value: 'shipping' },
            { label: 'confirm', value: 'confirmed' },
            { label: 'cancel', value: 'canceled' },
            { label: 'unreachable', value: 'unreaching' }
        ]
    } else if (statOrder === 'completed') {
        changeStatusList.value = [
            { label: 'cancel', value: 'canceled' },

        ]
    }

    showStatus.value = false
}

const editStat = (ord, status) => {
    var mapValue = []

    for (let i in ord) {

        mapValue.push({ id: ord[i].id, toStatus: status })
        orderList.value[i].status = status

    }

    emit('editStat', mapValue)

    showStatusToChange.value = false



}

</script>

<style scoped>
.imageCircle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    /* ou inline-block selon ton contexte */
}


.dropdown-list1 {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    /* Centrage parfait */
    background: var(--color-whitly);
    list-style: none;
    margin: 0;
    padding: 4px;
    border-radius: 22px;
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.2);
    width: 90%;
    /* ou max-width pour adaptatif */
    max-width: 800px;
    /* empêche que ce soit trop large */
    z-index: 1100;
    height: 300px;
    max-height: 400px;
    /* limite la hauteur */
    overflow-y: auto;
    /* scroll si besoin */
}

.dark .dropdown-list1 {
    background: var(--color-darky);
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.8);
}


.backClaque {
    position: fixed;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.15);
    backdrop-filter: blur(2px);
    /* flou principal */
    -webkit-backdrop-filter: blur(2px);
    /* compatibilité Safari */
    z-index: 1000;
}

/* Réutilise le style du label flottant et du conteneur .gBtn */

.flexBox {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 5px;
    margin-block: 10px;
}

.flexBox2 {
    display: flex;
    gap: 5px;
    margin-block: 10px;
    width: 100%;
}

@media (max-width: 400px) {
    .flexBox {
        grid-template-columns: 1fr;
    }
}

.rowBox {
    display: flex;
    flex-direction: column;
    justify-content: left;
    align-items: left;
    gap: 5px;
}

.centerBox{
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
}

.box {
    border-radius: 18px;
    display: flex;
    justify-content: center;
    align-items: center;
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.2);
    padding: 5px;
    cursor: pointer;
    background-color: var(--color-whiby);
}

.box2 {
    display: flex;
    justify-content: left;
    align-items: center;
    padding-inline: 5px;
    cursor: pointer;
    margin-block: 2px;
    width: calc(100% - 10px);
}

.statusBox {
    min-width: 10px;
    max-width: 10px;
    background-color: var(--color-garry);
    margin-right: 5px;
}


.status-confirmed {
    background-color: var(--color-blumy);
}

.status-shipping {
    background-color: var(--color-yelly);
}

.status-waiting {
    background-color: var(--color-rangy);
}

.status-pending {
    background-color: var(--color-rangy);
}

.status-unreaching {
    background-color: var(--color-gorry);
}

.status-returned {
    background-color: var(--color-ioly);
}

.status-canceled {
    background-color: var(--color-rady);
}

.status-completed {
    background-color: var(--color-greeny);
}
</style>