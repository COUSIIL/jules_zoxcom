<template>
  <div :style="{display: 'flex', flexDirection: 'column', justifyContent: 'left', alignItems: 'center'}" v-if="isMounted">

    <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
    <Explorer v-if="isExplorer" :show="isExplorer" @confirm="getExplorerImg" @cancel="isExplorer = false" />
    <Explorer v-if="isExplorer2" :show="isExplorer2" @confirm="getExplorerCatalog" @cancel="isExplorer2 = false" />

    <ProductNavBar @getClick="changePage"/>

    <div style="width: 100%; max-width: 1000px; padding-bottom: 80px;">
        <ProductPart v-if="currentPage === 1"
            :modelValue="productData"
            @openExplorProdImg="isExplorer = true"
            @openExplorCataImg="(idx) => { isExplorer2 = true; imageRefIndex = idx }"
            :prodImage="img"
            :imageRef="imageRef"
            @isMessage="openMessage"
            @message="ridMessage"
        />

        <ProductModels v-if="currentPage === 2"
            :modelValue="productData"
            @openExplorer="(idx) => { isExplorer = true; modelImageIndex = idx }"
            :imageRef="img"
            @isMessage="openMessage"
            @message="ridMessage"
        />

        <ProductParameter v-if="currentPage === 3" :modelValue="productData" />
        <ProductTransaction v-if="currentPage === 4" :modelValue="productData" />
        <ProductViewer v-if="currentPage === 5" :modelValue="productData" />
        <ProductBusiness v-if="currentPage === 6" :modelValue="productData" />
    </div>

    <!-- Save Button Floating or Fixed at bottom -->
    <div style="position: fixed; bottom: 20px; display: flex; justify-content: center; align-items: center; width: 80%; max-width: 800px; z-index: 1500;" v-if="!uploading">
      <CancelBtn style="width: 20%; min-width: 100px;" :text="t('cancel')" @clicked="navigateTo('/products')" :svg="icons['x']" />
      <CallToAction style="width: 80%" :text="t('save')" @clicked="saveProduct" :svg="icons['check']" />
    </div>
    <LoaderBlack v-else width="100px" style="position: fixed; bottom: 20px;" />

  </div>

  <LoaderBlack v-else width="100px" />
</template>

<script setup>
import { ref, onMounted, reactive, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router'
import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
import ProductNavBar from '../../components/elements/productNavBar.vue';
import ProductPart from '../../components/elements/productPart.vue';
import ProductModels from '../../components/elements/productModels.vue';
import ProductParameter from '../../components/elements/productParameter.vue';
import ProductTransaction from '../../components/elements/productTransaction.vue';
import ProductViewer from '../../components/elements/productViewer.vue';
import ProductBusiness from '../../components/elements/productBusiness.vue';
import Explorer from '../../components/elements/explorer.vue';
import Message from '../components/elements/bloc/message.vue';
import CallToAction from '../components/elements/bloc/callToActionBtn.vue';
import CancelBtn from '../components/elements/bloc/cancelBtn.vue';

import icons from '~/public/icons.json'

const { t } = useLang()
const route = useRoute()
const router = useRouter()

// Use useOrders (plural) to access getProduct
const { getProduct, resultProduct } = useOrders()

const productID = ref(-1)
const isMounted = ref(false)
const isMessage = ref(false)
const message = ref('')
const uploading = ref(false)

// Explorer State
const isExplorer = ref(false)
const isExplorer2 = ref(false)
const img = ref('') // Shared ref for explorer return
const imageRef = ref('') // For catalog explorer return
const imageRefIndex = ref(-1) // To know which catalog item to update
const modelImageIndex = ref(-1) // To know which model to update

const currentPage = ref(1)

// Main Data State
const productData = reactive({
    id: -1,
    name: '',
    image: '',
    slug: '',
    label: '', // badge
    prodActive: true,
    cataActive: true,
    catalog: [], // Array of objects { previewImage: '' }
    category: '', // ID
    isDescription: true,
    description: '',
    youtubeUrl: '',
    ytbActive: true,
    models: [],
    
    // New fields
    ratingActive: false,
    liveChatActive: false,
    countdownActive: false,
    gamblingActive: false,
    aiHelperActive: false,
    aspaActive: false,
    salesCanalActive: false,

    factoryName: '',
    productionTime: '',
    targetAudience: '',
    genre: '',
    internalInfo: ''
})

onMounted(async () => {
  if (!process.client) return;

  const storedId = route.params.id;
  productID.value = storedId && storedId !== 'new' ? parseInt(storedId) : -1;
  productData.id = productID.value;

  if (productID.value > 0) {
      await getProduct();
  }

  isMounted.value = true;
});

// Watch for fetched data and populate state
watch(resultProduct, (newResult) => {
    if (newResult && newResult.data && productID.value > 0) {
        // Find the product matching the ID
        const newData = newResult.data.find(p => p.id == productID.value);
        
        if (newData) {
            Object.assign(productData, {
                ...newData,
                prodActive: newData.prodActive == 1 || newData.prodActive == true,
                catalog: newData.catalog ? newData.catalog.map(c => ({ previewImage: c.image })) : [],
                models: newData.models ? newData.models.map(m => ({
                    ...m,
                    isActive: m.isActive == 1 || m.isActive == true,
                    infinit_stock: m.infinit_stock == 1 || m.infinit_stock == true,
                    imageUrls: m.imageUrls || '',
                    details: m.details || [],
                    isVariablePrice: m.details && m.details.some(d => Number(d.buy) > 0 || Number(d.sell) > 0)
                })) : []
            });
            
            // Handle booleans that might come as 0/1 from DB
            const boolFields = ['isDescription', 'ytbActive', 'cataActive', 'ratingActive', 'liveChatActive', 'countdownActive', 'gamblingActive', 'aiHelperActive', 'aspaActive', 'salesCanalActive'];
            boolFields.forEach(field => {
                if(newData[field] !== undefined) productData[field] = (newData[field] == 1 || newData[field] == true);
            });

            // Ensure category is ID
            if(newData.category) productData.category = parseInt(newData.category);
            
            // Handle JSON fields (colors, sizes) if they come as strings
            if (typeof productData.colors === 'string') {
                 try { productData.colors = JSON.parse(productData.colors); } catch(e) { productData.colors = []; }
            }
            if (typeof productData.sizes === 'string') {
                 try { productData.sizes = JSON.parse(productData.sizes); } catch(e) { productData.sizes = []; }
            }
        }
    }
});

const getExplorerImg = (value) => {
  img.value = value;
  isExplorer.value = false;

  // If we were selecting for main product
  if(currentPage.value === 1) {
      productData.image = value;
  } else if (currentPage.value === 2 && modelImageIndex.value !== -1) {
      if (productData.models[modelImageIndex.value]) {
          productData.models[modelImageIndex.value].imageUrls = value;
      }
  }
}

const getExplorerCatalog = (value) => {
  imageRef.value = value;
  isExplorer2.value = false;
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

const saveProduct = async () => {
    uploading.value = true;
    
    // Transform data for backend
    const payload = {
        ...productData,
        // Map catalog objects to URL strings
        catalogUrl: productData.catalog.map(c => c.previewImage),
        // Map booleans to 0/1
        prodActive: productData.prodActive ? 1 : 0,
        cataActive: productData.cataActive ? 1 : 0,
        ytbActive: productData.ytbActive ? 1 : 0,
        isDescription: productData.isDescription ? 1 : 0,
        ratingActive: productData.ratingActive ? 1 : 0,
        liveChatActive: productData.liveChatActive ? 1 : 0,
        countdownActive: productData.countdownActive ? 1 : 0,
        gamblingActive: productData.gamblingActive ? 1 : 0,
        aiHelperActive: productData.aiHelperActive ? 1 : 0,
        aspaActive: productData.aspaActive ? 1 : 0,
        salesCanalActive: productData.salesCanalActive ? 1 : 0,
        
        // Ensure models are formatted correctly (backend handles details inside models)
        models: productData.models.map(m => ({
            ...m,
            isActive: m.isActive ? 1 : 0,
            infinit_stock: m.infinit_stock ? 1 : 0,
            imageUrls: m.imageUrls // Rename back if backend expects imageUrls or image? Backend uses imageUrls in insertModels
        }))
    };

    try {
        const response = await fetch('https://management.hoggari.com/backend/api.php?action=postProducts', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const result = await response.json();

        if (result.success) {
            isMessage.value = true;
            message.value = result.message;
            if (productID.value === -1) {
                 // If created new, redirect to edit page or list
                 // router.push('/products');
            }
        } else {
            isMessage.value = true;
            message.value = result.message || 'Error saving product';
        }
    } catch (e) {
        console.error(e);
        isMessage.value = true;
        message.value = 'Network error saving product';
    } finally {
        uploading.value = false;
    }
}

</script>

<style scoped>
/* Scoped styles if needed */
</style>
