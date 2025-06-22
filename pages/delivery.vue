<template>

    <div style="width: 100%; display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <div class="boxDelivery" v-if="isCreating === false">
            <p>
                {{ t('delivery') }}
            </p>
            <CallToAction :text="t('add delivery')" :svg="icons['add']" @clicked="isCreating = !isCreating"/>
        </div>

        <AddDelivery v-else @cancel="isCreating = false" @save="isCreating = false"/>
        
    </div>


</template>

<script setup>

import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
import Editor from '../components/editor.vue';
import Radio from '../components/elements/bloc/radio.vue';
import Inputer from '../components/elements/bloc/input.vue';
import InputBtn from '../components/elements/bloc/inputBtn.vue';
import Lister from '../components/elements/bloc/list.vue';
import Selector from '../components/elements/bloc/select.vue';
import Gbtn from '../components/elements/bloc/gBtn.vue';
import Message from '../components/elements/bloc/message.vue';
import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
import CancelBtn from '../components/elements/bloc/cancelBtn.vue';
import Explorer from '../components/elements/explorer.vue';
import EditCat from '../components/elements/editCategory.vue';
import AddDelivery from '../components/elements/addDelivery.vue';

import icons from '~/public/icons.json'

import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

const { t } = useLang()

var isCreating = ref(false)


</script>


<style>
.boxDelivery {
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
.dark .boxDelivery{
    background-color: var(--color-darkly);
}

.boxDelivery p {
    margin-inline: 10px;
}

</style>