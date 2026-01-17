<template>

  <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
  <Explorer v-if="isExplorer" :show="isExplorer" @confirm="getExplorerImg" @cancel="isExplorer = false" />
  <Explorer v-if="isExplorer2" :show="isExplorer2" @confirm="getExplorerCatalog" @cancel="isExplorer2 = false" />
  
  <div :style="{display: 'flex', flexDirection: 'column', justifyContent: 'left', alignItems: 'center'}" v-if="isMounted">
    
    <ProductNavBar @getClick="changePage"/>


    <ProductPart v-if="currentPage === 1" @openExplorProdImg="getExplorerImg" @openExplorCataImg="getExplorerCatalog" :prodImage="img" @isMessage="openMessage" @message="ridMessage" :imageRef="imageRef" />



  </div>

  <LoaderBlack v-else width="100px" />
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router'
import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
import Editor from '../../components/editor.vue';
import ProductNavBar from '../../components/elements/productNavBar.vue';
import ProductPart from '../../components/elements/productPart.vue';
import Inputer from '../components/elements/bloc/input.vue';
import InputBtn from '../components/elements/bloc/inputBtn.vue';
import Lister from '../components/elements/bloc/list.vue';
import Selector from '../components/elements/bloc/select.vue';
import Gbtn from '../components/elements/bloc/gBtn.vue';
import Message from '../components/elements/bloc/message.vue';
import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
import CancelBtn from '../components/elements/bloc/cancelBtn.vue';
import Explorer from '../../components/elements/explorer.vue';
import EditCat from '../components/elements/editCategory.vue';

import icons from '~/public/icons.json'

import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

const { getProduct, resultProduct } = useOrders()


const { t } = useLang()

const route = useRoute()

const productID = ref(-1)
const isMounted = ref(false)
const isMessage = ref(false)
const message = ref('')
const img = ref('')
const isExplorer = ref(false)
const isExplorer2 = ref(false)
const imageRef = ref('')
const currentPage = ref(1)



onMounted(async () => {
  if (!process.client) return;

  const storedId = route.params.id;
  productID.value = storedId.toString ? storedId : null;

  await getProduct()

  console.log('resultProduct: ', resultProduct.value)

  // on n'appelle PAS directement setProducts ici !

  isMounted.value = true
  
});


const getExplorerImg = (value) => {
  img.value = value
  isExplorer.value = !isExplorer.value
}

const getExplorerCatalog = (value) => {
  imageRef.value = value
  isExplorer2.value = !isExplorer2.value
}


const openMessage = (value) => {
  isMessage.value = value
}

const ridMessage = (value) => {
  message.value = value
}

const changePage = (value) => {
  currentPage.value = value
}




</script>

<style scoped>


</style>
