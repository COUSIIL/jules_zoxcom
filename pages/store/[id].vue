<template>
  <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
    <!--Page ID : {{ route.params.id }}-->

    <EmailEdit />

    <SetDelivery :name="route.params.id"/>
    
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

import Loader from '../components/loader.vue';
import Search from '../components/search.vue';
import Confirm from '../components/confirm.vue';
import Deliver from '../components/deliver.vue';

import EmailEdit from '~/components/elements/emailEdit.vue';
import SetDelivery from '~/components/elements/deliveryMethod.vue';


const route = useRoute()
</script>
