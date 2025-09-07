<template>

  <LoaderBlack v-if="isUpdating" width="80px"/>



  <div v-if="isMounted" class="flex flex-col items-center max-w-full">

    <div class="flex items-center justify-between w-full max-w-3xl min-w-[300px] p-2.5 my-2.5 transition-all duration-300 ease-in-out rounded-md shadow-md bg-whitly dark:bg-darkly">
      
      <div class="flex items-center justify-center mx-2.5">
        <svg class="mx-1.25" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="currentColor" fill="none">
          <path d="M12 22C11.1818 22 10.4002 21.6698 8.83693 21.0095C4.94564 19.3657 3 18.5438 3 17.1613C3 16.7742 3 10.0645 3 7M12 22C12.8182 22 13.5998 21.6698 15.1631 21.0095C19.0544 19.3657 21 18.5438 21 17.1613V7M12 22L12 11.3548" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M8.32592 9.69138L5.40472 8.27785C3.80157 7.5021 3 7.11423 3 6.5C3 5.88577 3.80157 5.4979 5.40472 4.72215L8.32592 3.30862C10.1288 2.43621 11.0303 2 12 2C12.9697 2 13.8712 2.4362 15.6741 3.30862L18.5953 4.72215C20.1984 5.4979 21 5.88577 21 6.5C21 7.11423 20.1984 7.5021 18.5953 8.27785L15.6741 9.69138C13.8712 10.5638 12.9697 11 12 11C11.0303 11 10.1288 10.5638 8.32592 9.69138Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M6 12L8 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
          <path d="M17 4L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        </svg>
        <h1>
          {{ productsLength }} {{ t('products') }}
        </h1>
        
      </div>
      <Linker :link="'/products/0'" :text="t('add product')" :svg="icons['addPackage']" />
    </div>

      <div v-if="!isUpdating" class="flex flex-wrap items-center justify-center gap-5">
        <Confirm :isVisible="showConfirm"
          @confirm="confirmation(true)"
          @cancel="confirmation(false)"
        />

      <div 
        v-for="(product, productIndex) in productList" 
        :key="productIndex"
        class="product-card"
      >
        <div class="product-image-wrapper" @click="selectProduct(product.id)">
          <img
            :src="product.image.startsWith('https') ? product.image : 'https://management.hoggari.com' + product.image"
            :alt="product.name"
            class="product-image"
          />
          <!-- Bouton Delete flottant -->
          <button class="delete-button-floating" @click.stop="productID = product.id; showConfirm = true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" color="currentColor" fill="none">
                <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                <path d="M9.5 16.5L9.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                <path d="M14.5 16.5L14.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
            </svg>
          </button>

          <div class="absolute top-1.5 left-1.5 flex items-center justify-center h-6 max-w-24 px-1.5 bg-white/80 rounded-full shadow-md transition-all duration-200" v-if="product.prodActive == 1">
            <div class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] bg-greny mx-1.25 rounded-full">
            </div>
            <p class="m-1.25 text-xs font-bold text-darkow">
              {{ t('active') }}
            </p>
          </div>
          <div class="absolute top-1.5 left-1.5 flex items-center justify-center h-6 max-w-24 px-1.5 bg-white/80 rounded-full shadow-md transition-all duration-200" v-else>
            <div class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] bg-rady mx-1.25 rounded-full">
            </div>
            <p class="m-1.25 text-xs font-bold text-darkow">
              {{ t('disabled') }}
            </p>
          </div>
          
        </div>
        <div class="w-full mt-2.5 text-center">
          <h2 class="text-base font-semibold my-1.25">{{ product.name }}</h2>
          <label v-if="product.models[0]" class="inline-block px-2 py-1 mt-1 text-3xl font-bold text-green-600 bg-green-100 rounded-md">
            {{ product.models[0].sell }}
          </label>
          <div v-for="(view, viewIndex) in viewPageList" 
          :key="viewIndex">
            <div class="flex items-center justify-between w-full" v-if="view.product_id === product.id">
              
              <h3>
                {{ view.view }}
              </h3>
              <div v-html="resizeSvg(icons['view'], 20, 20)">

              </div>
              
            </div>

          </div>
          <div v-for="(click, clickIndex) in productClickList" 
          :key="clickIndex">
            <div class="flex items-center justify-between w-full" v-if="click.product_id === product.id">
              
              <h3>
                {{ click.click }}
              </h3>
              <div v-html="resizeSvg(icons['purshase'], 20, 20)">

              </div>
              
            </div>

          </div>
        </div>
      </div>


    </div>
    <LoaderBlack v-else width="80px;"/>
      

  </div>
</template>
  
<script setup>
  import { ref, onMounted } from 'vue';
  import { useRouter } from 'nuxt/app';

  import LoaderBlack from '../components/elements/animations/loaderBlack.vue';
  import Confirm from '../components/elements/bloc/confirm.vue';
  import Linker from '../components/elements/bloc/classBtn.vue';

  import icons from '~/public/icons.json'

  const { t } = useLang()

  
  
  const productList = ref(null); // Déclarer productList comme réactive
  const isMounted = ref(false); // Déclarer isMounted comme réactive
  const isUpdating = ref(false);
  const showConfirm = ref(false);
  const productID = ref(-1);
  const showEdit = ref(false);
  const router = useRouter();
  const productsLength = ref(0);
  const productClickList = ref();
  const viewPageList = ref();

  var resizeSvg = (svg, width, height) => {
    return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
  }

  const getViewPage = async () => {
  isUpdating.value = true;
  try {
    const response = await fetch('https://management.hoggari.com/backend/api.php?action=getViewPage', {
      method: 'GET',
    });

    if (!response.ok) {
      console.error('error in getting view page response');
      isMounted.value = true;
      isUpdating.value = false;
      return;
    }

    const result = await response.json();
    if(result.success) {
      viewPageList.value = result.data

    }else {
      console.error(result.message);
    }
    
    isUpdating.value = false;
  } catch (error) {
    console.error(error);
    isUpdating.value = false;
  } finally {
    isMounted.value = true;
    isUpdating.value = false;
  }
};

  const getProductClick = async () => {
    isUpdating.value = true;
    try {
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=getProductClick', {
        method: 'GET',
      });

      if (!response.ok) {
        console.error('error in getting product click response');
        isMounted.value = true;
        isUpdating.value = false;
        return;
      }

      const result = await response.json();
      if(result.success) {
        productClickList.value = result.data
      }else {
        console.error(result.message);
      }

      isUpdating.value = false;
    } catch (error) {
      console.error(error);
      isUpdating.value = false;
    } finally {
      isMounted.value = true;
      isUpdating.value = false;
    }
  };

  const getProducts = async () => {
    isUpdating.value = true;
    try {
      const response = await fetch('https://management.hoggari.com/backend/api.php?action=getProducts', {
        method: 'GET',
      });

      if (!response.ok) {
        console.error(t('error fetching products:'), response.statusText);
        isMounted.value = true;
        isUpdating.value = false;
        return;
      }

      const result = await response.json();
      productList.value = result.data;
      productsLength.value = productList.value.length;
      isUpdating.value = false;
    } catch (error) {
      console.error(t('an error occurred:'), error);
      isUpdating.value = false;
    } finally {
      isMounted.value = true;
      isUpdating.value = false;
    }
  };

  const confirmation = (value) => {
    if(value) {
      showConfirm.value = false;
      deleteProduct(productID.value);
      
    }else {
      showConfirm.value = false;
    }
  };

  const deleteProduct = async (id) => {

  isUpdating.value = true;
  const updateOrder = JSON.stringify({
      id: id,
      });
      const response2 = await fetch('https://management.hoggari.com/backend/api.php?action=deleteProduct', {
      method: 'POST',
      body: updateOrder,
      });
      if(!response2.ok){
          console.error(t("error in response"));
          isUpdating.value = false;
          return;
      }
      const textResponse = await response2.json();  // Récupérer la réponse en texte
      if (textResponse.success) {
        productList.value = null;
        await getProducts();

          isUpdating.value = false;
      } else {
          console.error(textResponse.message);
          isUpdating.value = false;
      }


      isUpdating.value = false;

  };
  const selectProduct = (id) => {
    productID.value = id;
    localStorage.setItem('productID', id);
    showEdit.value = true;

    // Sauvegarder dans le localStorage
    router.push(`/products/${id}`);
    
  };


  onMounted(() => {
    getProducts();
    getViewPage();
    getProductClick();
  });
  
  </script>

  