<template>
  
  <div :style="{display: 'flex', flexDirection: 'column', justifyContent: 'center', alignItems: 'center'}" v-if="isMounted">
    <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
    <Explorer :show="isExplorer" @confirm="getExplorerImg" @cancel="isExplorer = false" />
    <div class="boxProduct">
      <h1 style="font-size: 18px; font-weight: bold;">
        {{ t('product informations') }}
      </h1>
      
      <div class="productContent">
        <button type="button" class="imageUploadSection" @click="openExplorer">
          <div class="important">
                <DotLottieVue
                  style="height: 24px; width: 24px"
                  src="/animations/important.lottie"
                  autoplay
                  loop
                />
          </div>
          <label class="inputImg">
            <span v-if="!previewImage && !productImg">optimale 1:1</span>
            <img v-else-if="productImg" :src="productImg" alt="Preview" />
            <img v-else-if="previewImage" :src="previewImage" alt="Preview" />
          </label>
        </button>

        <div class="productForm">
          <div class="formLine" @click="prodActive = !prodActive">
            {{ t('publish this product') }}
            <Radio :selected="prodActive"/>
          </div>

          <Inputer :placeHolder="'product name'" :img="productSvg" :required="true" v-model="productName" @blur:modelValue="generateSlug"/>
          <Inputer :placeHolder="'slug'" :img="icons['link']" :required="true" v-model="slug"/>
          <Inputer :placeHolder="'label'" :img="labelSvg" :required="false" v-model="label"/>

          
        </div>
        <div class="productForm">
          <div class="formRow">
            <InputBtn :placeHolder="'color'" type="color" color="var(--color-zioly2)" :img="colorSvg" :required="false" v-model="color"/>
            <InputBtn :placeHolder="'color name'" type="text" :img="icons['colorName']" :required="false" v-model="colorName" :svg="addSvg" color="var(--color-zioly2)" @clicked="addColor(color, colorName)"/>
            <Lister :options="colors" color="var(--color-zioly2)" @update:options="removeColor"/>
          </div>

          <div class="formRow">
            <InputBtn :placeHolder="'size'" type="text" :img="icons['size']" :required="false" v-model="size" :svg="addSvg" color="var(--color-zioly2)" @clicked="addSize(size)"/>
            <Lister :options="sizes" color="var(--color-zioly2)" @update:options="removeSize"/>
          </div>
        </div>
      </div>
        
    </div>

    <div class="boxProduct">
      <div :style="{ display: 'flex', justifyContent: 'center', alignItems: 'center', width: '80%', maxWidth: '800px', minWidth: '200px' }">
        <Inputer :placeHolder="'youtube link'" :img="ytbSvg" :required="false" v-model="youtubeLink" @blur:modelValue="updateVideoId"/>

        <Radio :selected="ytbActive" @changed="ytbActive = !ytbActive"/>

      </div>
      
      <div :style="{ marginBlock: '5px', width: '80%', maxWidth: '800px', minWidth: '200px' }">
        <iframe 
          v-if="videoId"
          :src="`https://www.youtube.com/embed/${videoId}`"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowfullscreen
          class="youtube"
        ></iframe>
      </div>
      
    </div>


    
    <div v-if="!newCat" class="boxProduct">
      <div v-if="categories" class="formRow">
        <Selector :options="categories" @update:modelValue="setCat" color="var(--color-zioly2)" placeHolder="categorie" :modelValue="categoryName" v-model="categoryName"/>
        <Selector v-if="categoryName && subCategories" :options="subCategories" @update:modelValue="setSubCat" color="var(--color-zioly2)" placeHolder="sub categorie" :modelValue="categoryName2" v-model="categoryName2"/>
        <Selector v-if="categoryName2 && categoriesElements" :options="categoriesElements" @update:modelValue="setCatElements" color="var(--color-zioly2)" placeHolder="categorie element" :modelValue="categoryName3" v-model="categoryName3"/>
        
      </div>
      <p v-else >
        {{ t('no categorie yet') }}
      </p>
      

      <Gbtn :text="t('add new category')" @click="newCategory" color="var(--color-zioly2)" :svg="icons['add']"/>
         
    </div>
    <EditCat v-if="newCat"
        :model-value="categoryName"
        @saved="updatedCategories"
        @cancel="newCat = false"
    /> 


    <Explorer :show="isExplorer3" @confirm="getExplorerCatalog" @cancel="isExplorer3 = false" />
    <div class="boxProduct">

      <div class="formLine" @click="cataActive = !cataActive">
        {{ t('activate catalogue') }}
        <Radio :selected="cataActive"/>
      </div>

      <Gbtn :text="t('add image')" @click="addCatalog" color="var(--color-zioly2)" :svg="icons['add']"/>


      <div class="folder-tree">
        
        <div v-if="isCatalog" v-for="(ref, index) in catalogImage" :key="index"
            >

            
            <button type="button" class="folder-item" @click="openExplorer3(index)">
              <h3 style="font-size: 14px;">id: {{ index + 1 }}</h3>
              <label class="inputImg2">
                <span v-if="!ref.previewImage">1:1</span>
                <img v-else-if="ref.previewImage" :src="ref.previewImage" alt="Preview" />
              </label>
            </button>

            <!-- Bouton de suppression -->
            <button class="removeImg" @click="clearCatalog(index)" type="button">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none">
                <path d="M19.5 5.5L18.8803 15.5251C18.7219 18.0864 18.6428 19.3671 18.0008 20.2879C17.6833 20.7431 17.2747 21.1273 16.8007 21.416C15.8421 22 14.559 22 11.9927 22C9.42312 22 8.1383 22 7.17905 21.4149C6.7048 21.1257 6.296 20.7408 5.97868 20.2848C5.33688 19.3626 5.25945 18.0801 5.10461 15.5152L4.5 5.5"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M3 5.5H21M16.0557 5.5L15.3731 4.09173C14.9196 3.15626 14.6928 2.68852 14.3017 2.39681C14.215 2.3321 14.1231 2.27454 14.027 2.2247C13.5939 2 13.0741 2 12.0345 2C10.9688 2 10.436 2 9.99568 2.23412C9.8981 2.28601 9.80498 2.3459 9.71729 2.41317C9.32164 2.7167 9.10063 3.20155 8.65861 4.17126L8.05292 5.5"
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M9.5 16.5L9.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                <path d="M14.5 16.5L14.5 10.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
              </svg>
            </button>

            
        </div>
        
        
      </div>

    </div>



    <Explorer :show="isExplorer4" @confirm="getExplorerModel" @cancel="isExplorer4 = false" />
    <div class="boxProduct">
        
        
      <div v-if="isModal" v-for="(ref, index) in modal" :key="index" 
      :style="{
        width: '80%',
        marginBlock: '20px', 
        display: 'flex', 
        flexDirection: 'column', 
        justifyContent: 'center', 
        alignItems: 'center',
        
        }">
        <div class="formLine" @click="ref.isActive = !ref.isActive">
          {{ t('acivate modal') }} {{index + 1}}
          <Radio :selected="ref.isActive"/>
        </div>
        <div class="productContent">
          <button type="button" class="imageUploadSection" @click="openExplorer4(index)">
            <div class="important">
                <DotLottieVue
                  style="height: 24px; width: 24px"
                  src="/animations/important.lottie"
                  autoplay
                  loop
                />
            </div>
            <label class="inputImg">
              
              <span v-if="!ref.previewImage">optimale 1:1</span>
              <img v-else-if="ref.previewImage" :src="ref.previewImage" alt="Preview" />
            </label>
          </button>



          <div class="productForm">
            

            <Inputer :placeHolder="'model name'" :img="productSvg" :required="true" v-model="ref.name"/>
            <Inputer :placeHolder="'buy price'" type="number" :img="icons['invoice']" :required="false" @input="validatePrice(index)" v-model="ref.buyPrice"/>
            <Inputer :placeHolder="'sell price'" type="number" :img="icons['moneyTag']" :required="true" v-model="ref.sellPrice"/>
            <Inputer :placeHolder="'promo price'" type="number" :img="icons['promotion']" :required="false" v-model="ref.promo"/>
            
            
          </div>

          
          <Inputer :placeHolder="'model reference'" :img="icons['reference']" :required="false" v-model="ref.reference"/>
          <Inputer :placeHolder="'model sku'" :img="icons['barcode1']" :required="false" v-model="ref.sku"/>
          
          <div class="formRow">
            <div type="button" @click="activeStock(index)" style="width: 100%; height: 50px; margin: 5px; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
              {{t('Infinite stock')}}
              <Radio :selected="ref.infinit_stock"/>

            </div>
          </div>

          <div class="formRow">

            <div type="button" @click="activeColor(index)" style="width: 100%; height: 50px; margin: 5px; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
              {{t('Activate color')}}
              <Radio :selected="ref.activeColor"/>

            </div>
          </div>
          <div class="formRow">
            <div type="button" @click="activeSize(index)" style="width: 100%; height: 50px; margin: 5px; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
              {{t('Activate Size')}}
              <Radio :selected="ref.activeSize"/>

            </div>
          </div>
          
        </div>

        <!--Add Details-->
        <div v-if="ref.activeColor || ref.activeSize" class="formRow">
          <Selector v-if="ref.activeColor" :options="colors" color="var(--color-zioly2)" placeHolder="color" @update:modelValue="getColor" />
          <Selector v-if="ref.activeSize" :options="sizes" color="var(--color-zioly2)" placeHolder="size" @update:modelValue="getSize" />
          <Inputer v-if="!ref.infinit_stock" style="max-width: 100px;" type="number" :placeHolder="'qty'" :img="labelSvg" :required="true" v-model="ref.quantity"/>
          <Gbtn :text="t('add detail')" @click="addDetail(ref.quantity, index)" color="var(--color-zioly2)" :svg="icons['add']"/>
        </div>

        <div class="folder-tree">
          <div v-for="(ref2, indexD) in ref.details" :key="indexD" >
            <div class="folder-item">
              <div :style="{
                    minWidth: '50px', 
                    maxWidth: '50px', 
                    minHeight: '50px', 
                    maxHeight: '50px', 
                    margin: '5px',
                    borderRadius: '50%',
                    backgroundColor: ref2.color, }" 
                  </div>
              
              <h3 v-if="ref.activeColor">{{ ref2.colorName }}</h3>
              <h3 v-if="ref.activeSize"> {{ ref2.size }}</h3>
              <h3 v-if="ref.infinit_stock">{{ ref2.qty }}</h3>
              <div class="removeImg2">
                <Gbtn :text="t('')" @click="remouveDetails(index, indexD)" color="#ff5555" :svg="icons['x']"/>
              </div>
              
            </div>

          </div>

          
        </div>

        <div class="formRow">
          <p style="font-size: 3vh; font-weight: bold;">
            {{ t('delevery packaging details') }}
          </p>
          <div class="formRow">
            <div type="button" @click="ref.breakable = !ref.breakable" style="width: 100%; height: 50px; margin: 5px; display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
              {{t('product breakable')}}
              <Radio :selected="ref.breakable"/>

            </div>
          </div>
          <Inputer style="max-width: 200px;" type="number" :placeHolder="'weight'" :img="icons['weight']" v-model="ref.weight" holder="KG"/>
          <Inputer style="max-width: 200px;" type="number" :placeHolder="'volume'" :img="icons['volume']" v-model="ref.volume" holder="m2"/>
        </div>

        <Gbtn :text="t('remove')" @click="clearRef(index)" color="#ff5555" :svg="icons['x']"/>
        </div>


        <Gbtn :text="t('add model')" @click="addRef" color="var(--color-zioly2)" :svg="icons['add']"/>

        
    </div>

    <div class="boxProduct">
      <div class="formLine" @click="activeDes = !activeDes">
            {{ t('activate discription') }}
            <Radio :selected="activeDes"/>
      </div>
      
      <Editor :key="descriptionKey" v-model="description"  @update:modelValue="updateDescription" />
    </div>

    

    <div style="position: sticky; bottom: 20px; display: flex; justify-content: center; align-items: center; width: 80%; margin: 30px; z-index: 1500;" v-if="!uploading">
      <CancelBtn style="width: 20%; min-width: 100px;" :text="t('clear')" @clicked="resetForm" :svg="icons['clear']" />
      <CallToAction style="width: 80%" :text="t('save')" @clicked="uploadProductImage" :svg="icons['check']" />
      
    </div>
    <LoaderBlack v-else width="100px" />

  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
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

import icons from '~/public/icons.json'

import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

const { t } = useLang()

const productSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" color="currentColor" fill="none">
    <path d="M10.5 14.5H7.5C6.55719 14.5 6.08579 14.5 5.79289 14.7929C5.5 15.0858 5.5 15.5572 5.5 16.5V16.5C5.5 17.4428 5.5 17.9142 5.79289 18.2071C6.08579 18.5 6.55719 18.5 7.5 18.5H10.5C11.4428 18.5 11.9142 18.5 12.2071 18.2071C12.5 17.9142 12.5 17.4428 12.5 16.5V16.5C12.5 15.5572 12.5 15.0858 12.2071 14.7929C11.9142 14.5 11.4428 14.5 10.5 14.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M21.5 13.5V8.5C21.5 7.50878 21.5 7.01317 21.3461 6.55132C21.1921 6.08947 20.8947 5.69298 20.3 4.9C19.4167 3.7223 18.9751 3.13344 18.3416 2.81672C17.7082 2.5 16.9721 2.5 15.5 2.5H8.5C7.02786 2.5 6.2918 2.5 5.65836 2.81672C5.02492 3.13344 4.58328 3.72229 3.7 4.9C3.10527 5.69298 2.8079 6.08947 2.65395 6.55132C2.5 7.01317 2.5 7.50878 2.5 8.5V13.5C2.5 17.2712 2.5 19.1569 3.67157 20.3284C4.84315 21.5 6.72876 21.5 10.5 21.5H13.5C17.2712 21.5 19.1569 21.5 20.3284 20.3284C21.5 19.1569 21.5 17.2712 21.5 13.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M3 6.5H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M14.5 6.5H9.5L10.5 2.5H13.5L14.5 6.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M14.5 6.5V8.5C14.5 9.44281 14.5 9.91421 14.2071 10.2071C13.9142 10.5 13.4428 10.5 12.5 10.5H11.5C10.5572 10.5 10.0858 10.5 9.79289 10.2071C9.5 9.91421 9.5 9.44281 9.5 8.5V6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>`)

const labelSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" color="currentColor" fill="none">
    <circle cx="1.5" cy="1.5" r="1.5" transform="matrix(1 0 0 -1 16 8.00024)" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    <path d="M2.77423 11.1439C1.77108 12.2643 1.7495 13.9546 2.67016 15.1437C4.49711 17.5033 6.49674 19.5029 8.85633 21.3298C10.0454 22.2505 11.7357 22.2289 12.8561 21.2258C15.8979 18.5022 18.6835 15.6559 21.3719 12.5279C21.6377 12.2187 21.8039 11.8397 21.8412 11.4336C22.0062 9.63798 22.3452 4.46467 20.9403 3.05974C19.5353 1.65481 14.362 1.99377 12.5664 2.15876C12.1603 2.19608 11.7813 2.36233 11.472 2.62811C8.34412 5.31646 5.49781 8.10211 2.77423 11.1439Z" stroke="currentColor" stroke-width="1.5" />
    <path d="M7.00002 14.0002L10 17.0002" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
</svg>`)

const colorSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" color="currentColor" fill="none">
    <path d="M13.435 7L7.15915 13.2759M7.15915 13.2759L4.82728 15.6077C3.92569 16.5093 3.47489 16.9601 3.23745 17.5334C3 18.1066 3 18.7441 3 20.0192V21H3.98082C5.25586 21 5.89338 21 6.46663 20.7626C7.03988 20.5251 7.49068 20.0743 8.39227 19.1727L14.2891 13.2759M7.15915 13.2759H14.2891M14.2891 13.2759L17 10.565" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
    <path d="M19.2087 8.38869L20.82 10M19.2087 8.38869L20.0705 7.52682C20.363 7.23431 20.5093 7.08805 20.611 6.94529C21.1297 6.21676 21.1297 5.23953 20.611 4.511C20.5093 4.36824 20.363 4.22198 20.0705 3.92947C19.778 3.63697 19.6318 3.4907 19.489 3.38905C18.7605 2.87032 17.7832 2.87032 17.0547 3.38905C16.912 3.4907 16.7657 3.63695 16.4732 3.92947L15.6113 4.79133M19.2087 8.38869L15.6113 4.79133M14 3.18002L15.6113 4.79133" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>`)

const addSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
    <path d="M2 12C2 8.22876 2 6.34315 3.17157 5.17157C4.34315 4 6.22876 4 10 4H13C14.9628 4 15.9443 4 16.7889 4.42229C17.6334 4.84458 18.2223 5.62972 19.4 7.2C21.1333 9.51111 22 10.6667 22 12C22 13.3333 21.1333 14.4889 19.4 16.8C18.2223 18.3703 17.6334 19.1554 16.7889 19.5777C15.9443 20 14.9628 20 13 20H10C6.22876 20 4.34315 20 3.17157 18.8284C2 17.6569 2 15.7712 2 12Z" stroke="currentColor" stroke-width="1.5"></path>
    <path d="M11 8V16M15 12L7 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>`)

const ytbSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" color="currentColor" fill="none">
    <path d="M12 20.5C13.8097 20.5 15.5451 20.3212 17.1534 19.9934C19.1623 19.5839 20.1668 19.3791 21.0834 18.2006C22 17.0221 22 15.6693 22 12.9635V11.0365C22 8.33073 22 6.97787 21.0834 5.79937C20.1668 4.62088 19.1623 4.41613 17.1534 4.00662C15.5451 3.67877 13.8097 3.5 12 3.5C10.1903 3.5 8.45489 3.67877 6.84656 4.00662C4.83766 4.41613 3.83321 4.62088 2.9166 5.79937C2 6.97787 2 8.33073 2 11.0365V12.9635C2 15.6693 2 17.0221 2.9166 18.2006C3.83321 19.3791 4.83766 19.5839 6.84656 19.9934C8.45489 20.3212 10.1903 20.5 12 20.5Z" stroke="currentColor" stroke-width="1.5" />
    <path d="M15.9621 12.3129C15.8137 12.9187 15.0241 13.3538 13.4449 14.2241C11.7272 15.1705 10.8684 15.6438 10.1728 15.4615C9.9372 15.3997 9.7202 15.2911 9.53799 15.1438C9 14.7089 9 13.8059 9 12C9 10.1941 9 9.29112 9.53799 8.85618C9.7202 8.70886 9.9372 8.60029 10.1728 8.53854C10.8684 8.35621 11.7272 8.82945 13.4449 9.77593C15.0241 10.6462 15.8137 11.0813 15.9621 11.6871C16.0126 11.8933 16.0126 12.1067 15.9621 12.3129Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" />
</svg>`)

const cancelSvg = ref(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="currentColor" fill="none">
    <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
</svg>`)



const isMounted = ref(false);
const previewImage = ref(null);
const categoryImage = ref(null);
const modal = ref([]);
const catalogImage = ref([]);
const prodActive = ref(true);
const cataActive = ref(true);
const ytbActive = ref(true);
const desImage = ref([]);
const isModal = ref(false);
const isCatalog = ref(false);
const isDescription = ref(false);
const description = ref(" ");
const activeDes = ref(true);
const selectedCategory = ref('');
const newCat = ref(false);
const catLog = ref('');
const categoryName = ref('');
const categoryName2 = ref('');
const categoryName3 = ref('');
const youtubeLink = ref('');
const videoId = ref(null);
const categoryList = ref([]);
const categories = ref([]);
const subCategories = ref([]);
const categoriesElements = ref([]);
const label = ref('');
const productName = ref('');
const logSave = ref('');
const productID = ref(-1);
const uploading = ref(false);
const descriptionKey = ref(0);
const imageRef = ref(0);
const imageModel = ref(0);
const message = ref('');
const isMessage = ref(false);
const slug = ref('')

// Valeur du color picker et du nom
const color = ref('')
const colorName = ref('')
const size = ref('')
const isExplorer = ref(false)
const isExplorer3 = ref(false)
const isExplorer4 = ref(false)

const productImg = ref('')


// Dictionnaire de noms de couleurs HTML/CSS standards
const colorNames = {
  "#000000": "Black",
  "#FFFFFF": "White",
  "#FF0000": "Red",
  "#DC143C": "Crimson",
  "#B22222": "FireBrick",
  "#8B0000": "DarkRed",
  "#FFA07A": "LightSalmon",
  "#FA8072": "Salmon",
  "#E9967A": "DarkSalmon",
  "#F08080": "LightCoral",
  "#CD5C5C": "IndianRed",
  "#800000": "Maroon",

  "#FFFF00": "Yellow",
  "#FFD700": "Gold",
  "#FFA500": "Orange",
  "#FF8C00": "DarkOrange",
  "#FF6347": "Tomato",
  "#FF4500": "OrangeRed",

  "#00FF00": "Lime",
  "#7CFC00": "LawnGreen",
  "#7FFF00": "Chartreuse",
  "#ADFF2F": "GreenYellow",
  "#32CD32": "LimeGreen",
  "#98FB98": "PaleGreen",
  "#00FA9A": "MediumSpringGreen",
  "#00FF7F": "SpringGreen",
  "#3CB371": "MediumSeaGreen",
  "#2E8B57": "SeaGreen",
  "#008000": "Green",
  "#006400": "DarkGreen",

  "#0000FF": "Blue",
  "#1E90FF": "DodgerBlue",
  "#00BFFF": "DeepSkyBlue",
  "#87CEFA": "LightSkyBlue",
  "#ADD8E6": "LightBlue",
  "#4682B4": "SteelBlue",
  "#4169E1": "RoyalBlue",
  "#00008B": "DarkBlue",
  "#000080": "Navy",
  "#191970": "MidnightBlue",

  "#00FFFF": "Cyan",
  "#E0FFFF": "LightCyan",
  "#AFEEEE": "PaleTurquoise",
  "#40E0D0": "Turquoise",
  "#48D1CC": "MediumTurquoise",
  "#20B2AA": "LightSeaGreen",
  "#008B8B": "DarkCyan",
  "#008080": "Teal",

  "#FF00FF": "Magenta",
  "#DA70D6": "Orchid",
  "#BA55D3": "MediumOrchid",
  "#9370DB": "MediumPurple",
  "#8A2BE2": "BlueViolet",
  "#9400D3": "DarkViolet",
  "#9932CC": "DarkOrchid",
  "#800080": "Purple",
  "#4B0082": "Indigo",
  "#6A5ACD": "SlateBlue",

  "#C0C0C0": "Silver",
  "#D3D3D3": "LightGray",
  "#A9A9A9": "DarkGray",
  "#808080": "Gray",
  "#696969": "DimGray",
  "#2F4F4F": "DarkSlateGray",
  "#708090": "SlateGray",
  "#778899": "LightSlateGray"
}

const colors = ref([])
const sizes = ref([])

const addColor = (color, name) => {
  const exists = colors.value.some(c => c.value === color)
  if (!exists && name && color) {
    colors.value.push({ value: color, label: name })
  } else {
    isMessage.value = true
    message.value = 'you can not have the same color twice'
  }
}

const getColor = (value) => {
  color.value = value
  colorName.value = value.label
}
const getSize = (value) => {
  size.value = value
}

const validateForm = () => {

  // Vérifier l'image principale
  if (!productImg.value && !previewImage.value) {
    isMessage.value = true;
    message.value = "image principale manquante"
    return false;
  }

  // Vérifier le nom du produit
  if (!productName.value || productName.value.trim() === "") {
    isMessage.value = true;
    message.value = "nom du produit requis"
    return false;
  }

  if (!slug.value || slug.value.trim() === "") {
    isMessage.value = true;
    message.value = "slug du produit requis"
    return false;
  }

  // Si catégorie personnalisée activée
  /*if (newCat) {
    if (!categoryName.value || categoryName.value.trim() === "") {
      isMessage.value = true;
      message.value = "Nom de la catégorie requis"
      return false;
    }
    if (!categoryImage.value) {
      isMessage.value = true;
      message.value = "Image de la catégorie manquante"
      return false;
    }
  }*/

  // Vérifier au moins un modèle
  if (!modal.value.length) {
    isMessage.value = true;
    message.value = "Au moins un modèle est requis"
    return false;
  }

  // Vérifier chaque modèle
  modal.value.forEach((ref, index) => {
    if (!ref.previewImage) {
      isMessage.value = true;
      message.value = `Image manquante pour le modèle ${index + 1}`
      return false;
    }
    if (!ref.name || ref.name.trim() === "") {
      isMessage.value = true;
      message.value = `Nom requis pour le modèle ${index + 1}`
      return false;
    }
    if (!ref.sellPrice || isNaN(parseFloat(ref.sellPrice))) {
      isMessage.value = true;
      message.value = `Prix de vente invalide pour le modèle ${index + 1}`
      return false;

    }

    // Vérifier les détails si taille/couleur activées
    if ((ref.activeColor || ref.activeSize) && (!ref.details || !ref.details.length)) {
      isMessage.value = true;
      message.value = `Aucun détail ajouté pour le modèle ${index + 1} avec tailles/couleurs activées`
      return false;
    }

    // Si stock infini actif, la quantité est requise dans les détails
    if ((ref.activeColor || ref.activeSize) && ref.infinit_stock) {
      ref.details.forEach((detail, dIdx) => {
        if (!detail.qty || isNaN(parseInt(detail.qty))) {
          isMessage.value = true;
          message.value = `Quantité manquante dans le détail ${dIdx + 1} du modèle ${index + 1}.`
          return false;
        }
      });
    }
  });

  // Vérifier que toutes les images de catalogue sont présentes
  /*if (cataActive.value) {
    if (!catalogImage.length || catalogImage.some(ref => !ref.previewImage)) {
      isMessage.value = true;
      message.value = "une ou plusieurs images du catalogue sont manquantes"
      return false;
    }
  }*/
  return true;
  
};


const addDetail = (qty, index) => {
  var selectedDetail = modal.value[index].details
  
  if(selectedDetail) {
    const exists = selectedDetail.some(c => c.color === color.value && c.size === size.value)

    if(!exists) {
      selectedDetail = {color: color.value, size: size.value, qty: qty, index: index, colorName: colorName.value}
      modal.value[index].details.push(selectedDetail);
    } else {
      isMessage.value = true
      message.value = 'you can not add the same detail twice'
    }
    
  } else {
      selectedDetail = {color: color.value, size: size.value, qty: qty, index: index, colorName: colorName.value}

      modal.value[index].details.push(selectedDetail);
  }

}

const activeColor = (index) => {
  modal.value[index].activeColor = !modal.value[index].activeColor
  if(!modal.value[index].activeSize && !modal.value[index].activeColor) {
    modal.value[index].details = [];
  }
}
const activeSize = (index) => {
  modal.value[index].activeSize = !modal.value[index].activeSize
  if(!modal.value[index].activeSize && !modal.value[index].activeColor) {
    modal.value[index].details = [];
  }
}
const activeStock = (index) => {
  modal.value[index].infinit_stock = !modal.value[index].infinit_stock
}

const addSize = (name) => {
  const exists = sizes.value.some(s => s.value === name)
  if (!exists && name) {
    sizes.value.push({ value: name, label: name })
  } else {
    isMessage.value = true
    message.value = 'you can not have the same size twice'
  }
}

const removeColor = (newValues) => {
  colors.value = newValues.map(c => ({ value: c.value, label: c.label }))
}
const removeSize = (newValues) => {
  sizes.value = newValues.map(c => ({ value: c.value, label: c.label }))
}


const openExplorer = () => {
  isExplorer.value = !isExplorer.value
}

const openExplorer3 = (index) => {
  isExplorer3.value = !isExplorer3.value
  imageRef.value = index
}

const openExplorer4 = (index) => {
  isExplorer4.value = !isExplorer4.value
  imageModel.value = index
}

const getExplorerImg = (value) => {
  
  productImg.value = value
  isExplorer.value = !isExplorer.value
}


const getExplorerCatalog = (value) => {
  if(catalogImage.value[imageRef.value]) {
    catalogImage.value[imageRef.value].previewImage = value
  } else {
    catalogImage.value[imageRef.value].previewImage.push(value)
  }
  
  isExplorer3.value = !isExplorer3.value
}

const getExplorerModel = (value) => {
  if(modal.value[imageModel.value]) {
    modal.value[imageModel.value].previewImage = value
  } else {
    modal.value[imageModel.value].previewImage.push(value)
  }
  
  isExplorer4.value = !isExplorer4.value
}

// Convertir hex en RGB
function hexToRgb(hex) {
  const bigint = parseInt(hex.slice(1), 16)
  return {
    r: (bigint >> 16) & 255,
    g: (bigint >> 8) & 255,
    b: bigint & 255
  }
}

// Calculer la distance entre deux couleurs
function colorDistance(c1, c2) {
  return Math.sqrt(
    Math.pow(c1.r - c2.r, 2) +
    Math.pow(c1.g - c2.g, 2) +
    Math.pow(c1.b - c2.b, 2)
  )
}

// Trouver le nom de couleur le plus proche
function getClosestColorName(hex) {
  const target = hexToRgb(hex)
  let closestName = 'Unknown'
  let minDistance = Infinity

  for (const [key, name] of Object.entries(colorNames)) {
    const current = hexToRgb(key)
    const dist = colorDistance(target, current)
    if (dist < minDistance) {
      minDistance = dist
      closestName = name
    }
  }

  return closestName
}



// Mettre à jour automatiquement le nom quand la couleur change
watch(color, (newColor) => {
  colorName.value = getClosestColorName(newColor)
})







function remouveDetails(index, indexD) {
  if (index >= 0 && index < modal.value.length) {
    modal.value[index].details.splice(indexD, 1);
  } else {
    isMessage.value = true
    message.value = t('invalid index')
  }
}







const updateVideoId = () => {
  videoId.value = getYouTubeId(youtubeLink.value);
};

function getYouTubeId(url) {
  const regExp = /^.*(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|.*[?&]v=))([^#\&\?]*).*/;
  const match = url.match(regExp);
  return match && match[1] ? match[1] : null;
}




async function setProducts () {

  if(productID.value) {

      const response = await fetch('https://management.hoggari.com/backend/api.php?action=getProducts', {
        method: 'GET',
      });

      if (!response.ok) {
        isMessage.value = true
        message.value = t('error in request get products')
        isMounted.value = true;
        return;
      }

      const result = await response.json();
      var webUrl = '';

      if (result.success) {
        
        
        for (let ii = 0; ii < result.data.length; ii++) {
          if (result.data[ii].id === productID.value) {
            console.log(result.data[ii])
            for (let category of categoryList.value) {
              
              if (category['level']) {
                let parrent = parseInt(category['value'])
                if (category['level'] === 'leaf') {
                  categoryName3.value = category['value']

                  // Trouver le niveau "branch"
                  const branch = categoryList.value.find(c => c.id === parrent)
                  if (branch) {
                    categoryName2.value = branch['value']
                    parrent = parseInt(branch['value'])

                    // Trouver le niveau "meta"
                    const meta = categoryList.value.find(c => c.id === parrent)
                    if (meta) {
                      categoryName.value = meta['value']
                      
                    }

                  }
                  return

                } else if (category['level'] === 'branch') {
                  categoryName2.value = category['value']

                  const meta = categoryList.value.find(c => c.id === parrent)
                  if (meta) {
                    categoryName.value = meta['value']
                    return
                  }

                } else {
                  // Si c'est "meta"
                  categoryName.value = category['value']
                  return
                }

              } else {
                // Pas de parent_id
                categoryName.value = category['value']
                return
              }

              
              
            }

            if (!result.data[ii].image.startsWith('https')) {
              webUrl = 'https://management.hoggari.com'
            }

            // Conversion de l'image principale en Blob
            previewImage.value = webUrl + result.data[ii].image;
            youtubeLink.value = result.data[ii].youtubeUrl;
            ytbActive.value = result.data[ii].ytb_active;
            slug.value = result.data[ii].slug;

            updateVideoId();

            const rawColors = result.data[ii].colors;

            if (rawColors) {
              try {
                colors.value = JSON.parse(rawColors); // transforme la chaîne JSON en tableau utilisable
              } catch (e) {
                colors.value = [];
              }
            }

            const rawSizes= result.data[ii].sizes;

            if (rawSizes) {
              try {
                sizes.value = JSON.parse(rawSizes); // transforme la chaîne JSON en tableau utilisable
              } catch (e) {
                sizes.value = [];
              }
            }

            

            if(result.data[ii].prodActive == 0) {
              prodActive.value = false;
            } else {
              prodActive.value = true;
            }

            
            for (let i = 0; i < result.data[ii].models.length; i++) {
              var modelActive = false;
              var stockActive = false;
              var colorActive = false;
              var sizeActive = false;
              if(result.data[ii].models[i].modelActive == 0) {
                modelActive = false;
              } else {
                modelActive = true;
              }

              if(result.data[ii].models[i].infinit_stock == 0) {
                stockActive = false;
              } else {
                stockActive = true;
              }

              if(result.data[ii].models[i].aColor == 0) {
                colorActive = false;
              } else {
                colorActive = true;
              }

              if(result.data[ii].models[i].aSize == 0) {
                sizeActive = false;
              } else {
                sizeActive = true;
              }
              modal.value.push({ 
                name: result.data[ii].models[i].modelName, 
                reference: result.data[ii].models[i].ref, 
                quantity: result.data[ii].models[i].qty, 
                buyPrice: result.data[ii].models[i].buy, 
                sellPrice: result.data[ii].models[i].sell, 
                sku: result.data[ii].models[i].sku,
                isActive: modelActive,
                infinit_stock: stockActive,
                details: result.data[ii].models[i].details,
                activeColor: colorActive,
                activeSize: sizeActive,
                previewImage: webUrl + result.data[ii].models[i].image, 
                imageBlob: '', 
                breakable: result.data[ii].models[i].breakable,
                weight: result.data[ii].models[i].weight,
                volume: result.data[ii].models[i].volume,
                promo: result.data[ii].models[i].promo
              });
            }
            
            for (let i = 0; i < result.data[ii].catalog.length; i++) {
              if(result.data[ii].catalog[i]) {
                catalogImage.value.push({ 
                previewImage: webUrl + result.data[ii].catalog[i].image,
                imageBlob: '',
              });
              }
              
            }


            for (let i = 0; i < result.data[ii].descriptionImage.length; i++) {
              
              if(result.data[ii].descriptionImage[i]) {
                desImage.value.push({ 
                previewImage: webUrl + result.data[ii].descriptionImage[i],
                imageBlob: '',
              });
              }
              
            }
          

            if(result.data[ii].aDescription == 0) {
              activeDes.value = false;
            } else {
              activeDes.value = true;
            }
          
            isModal.value = true;
            isCatalog.value = true;

            // Remplace tous les src qui commencent par "/"
            description.value = result.data[ii].description.replace(
              /<img\s+[^>]*src=["'](\/[^"']+)["']/g,
              (match, p1) => {
                return match.replace(p1, webUrl + p1);
              }
            );

            nextTick(() => {
              // 🔥 Change la clé pour forcer le rechargement de l'éditeur
              descriptionKey.value++;
            });

            
            
            selectedCategory.value = '';
            catLog.value = '';
            label.value = result.data[ii].label;
            productName.value = result.data[ii].name;
            
          }
        }
      } else {
        isMessage.value = true
        message.value = result.message
      }

  } else {
    isMessage.value = true
    message.value = t('no product found')
  }
};



const updateDescription = (value) => {
  description.value = value;
};

function generateSlug() {
  slug.value = productName.value
    .toLowerCase()
    .replace(/[^\w\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
}

async function saveProduct(models, desUrls, catalogUrls, productUrl) {
  logSave.value = "saving data..."
  try {
    const postProduct = JSON.stringify({
      name: productName.value,
      image: productUrl,
      label: label.value,
      category: selectedCategory.value,
      catalogUrl: catalogUrls,
      prodActive: prodActive.value,
      models: models,
      descriptionUrl: desUrls,
      isDescription: isDescription.value,
      description: description.value,
      youtubeUrl: youtubeLink.value,
      colors: colors.value,
      sizes: sizes.value,
      ytbActive: ytbActive.value,
      cataActive: cataActive.value,
      slug: slug.value
    });

    const response = await fetch('https://management.hoggari.com/backend/api.php?action=postProducts', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'  // <- Obligatoire pour indiquer du JSON
        },
        body: postProduct,
      });

      if (!response.ok) {
        logSave.value = "Error saving product in response";
        isMessage.value = true
        message.value = t('error saving product in response')
        uploading.value = false;
        return null;
    } 
    const result = await response.json();
    if(result.success) {
      
      uploading.value = false;
      isMessage.value = true;
      message.value = result.message
    } else {
      uploading.value = false;
      isMessage.value = true;
      message.value = result.message
    }

      
  } catch (e) {
    isMessage.value = true;
    message.value = `error in saving product: ${e}`
    uploading.value = false;
  }


      

}

async function uploadProductImage() {

  const isTrue = validateForm()

  if(isTrue) {

  uploading.value = true;
  logSave.value = "uploading image...";
  if(modal.value) {

    var saveModels = [];
    var saveDesImage = [];
    var saveCatalogImage = [];
    var saveProductImage;
    if(productImg.value) {
      saveProductImage = productImg.value
    }else if(previewImage.value) {
      saveProductImage = previewImage.value
    }
    
    

    for(var i = 0; i < modal.value.length; i++) {
      const item = modal.value[i]
      saveModels.push(
        { 'imageUrls': item.previewImage,
          'name': item.name,
          'ref': item.reference,
          'buy': parseFloat(item.buyPrice),
          'sell': parseFloat(item.sellPrice),
          'qty': 1,
          'activeColor': item.activeColor,
          'activeSize': item.activeSize,
          'sku': item.sku,
          'details': item.details,
          'infinit_stock': item.infinit_stock,
          'isActive': item.isActive,
          'breakable': item.breakable,
          'weight': item.weight,
          'volume': item.volume,
          'promo': item.promo
        }
        
      ); // Return saved product image
      
    };
    
    if(desImage.value) {
      for(var i = 0; i < desImage.value.length; i++) {
        saveDesImage.push(desImage.value[i].previewImage); // Return saved product image

      };
    }

    
    if(catalogImage.value) {
      for(var i = 0; i < catalogImage.value.length; i++) {
        
        saveCatalogImage.push(catalogImage.value[i].previewImage); // Return saved product image
          
      };
    }
    
    saveProduct(saveModels, saveDesImage, saveCatalogImage, saveProductImage);
  }
  }

  
  
}



async function getCategory() {
  const response = await fetch('https://management.hoggari.com/backend/api.php?action=getCategory', {
      method: 'GET',
    });
    if (!response.ok) {
      isMessage.value = true
      message.value = 'error in getting data'
      return
    }
    const result = await response.json();
    if (result.success) {
      console.log('result.data: ', result)
      for(var i =0; i < result.categories.length; i++) {
        categoryList.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
        if(result.categories[i].level === 'meta') {
          categories.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
          selectedCategory.value = parseInt(result.categories[i].id)
        } else if(result.categories[i].level === 'branch') {
          subCategories.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
          selectedCategory.value = parseInt(result.categories[i].id)
        } else if(result.categories[i].level === 'leaf') {
          categoriesElements.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
          selectedCategory.value = parseInt(result.categories[i].id)
        }
      }
    } else {
      isMessage.value = true
      message.value = result.message
    }

}

function validatePrice(index) {
  let value1 = String(modal.value[index].buyPrice); // Assurez-vous que value est une chaîne
  let value2 = String(modal.value[index].sellPrice); // Assurez-vous que value est une chaîne
  // Permet uniquement les chiffres
  modal.value[index].buyPrice = value1.replace(/\D/g, '');
  modal.value[index].sellPrice = value2.replace(/\D/g, '');
}


function addRef() {
  modal.value.push({ 
    name: "", 
    reference: "",
    quantity: 1, // Default quantity
    color: "", 
    size: "", 
    sku: '',
    infinit_stock: false,
    isActive: true,
    details: [],
    buyPrice: 0,
    sellPrice: 0,
    activeColor: false,
    activeSize: false,
    previewImage: null,
    imageBlob: null,
    breakable: 0,
    weight: 1,
    volume: 1,
    promo: 0
  });
  isModal.value = true;
}

function addCatalog() {
  catalogImage.value.push({ 
    previewImage: null ,
    imageBlob: null,
  });
  isCatalog.value = true;
}


/*function addDescription() {
  desImage.value.push({ 
    previewImage: null,
    imageBlob: null,
  });
  isDescription.value = true;
}*/

/*function clearDescription(index) {
  if (index >= 0 && index < desImage.value.length) {
    desImage.value.splice(index, 1);
    isDescription.value = desImage.value.length > 0;
  } else {
    console.error('Invalid index:', index);
  }
}*/
function clearRef(index) {
  if (index >= 1 && index < modal.value.length) {
    modal.value.splice(index, 1);
    isModal.value = modal.value.length > 0;
  } else {
    isMessage.value = true
    message.value = 'you can not remove this'
  }
}

function clearCatalog(index) {
  if (index >= 0 && index < catalogImage.value.length) {
    catalogImage.value.splice(index, 1);
    isCatalog.value = catalogImage.value.length > 0;
  } else {
    isMessage.value = true
    message.value = t('invalid index')
  }
}

function newCategory() {
  newCat.value = true;
}

function updatedCategories(result) {
  categoryList.value = []
  categories.value = []
  subCategories.value = []
  categoriesElements.value = []
  for(var i =0; i < result.categories.length; i++) {
    categoryList.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
    if(result.categories[i].level === 'meta') {
      categories.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
    } else if(result.categories[i].level === 'branch') {
      subCategories.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
    } else if(result.categories[i].level === 'leaf') {
      categoriesElements.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
    }
  }
}

function setCat(cat) {
  categoryName.value = cat
}
function setSubCat(cat) {
  categoryName2.value = cat
}
function setCatElements(cat) {
  categoryName3.value = cat
}

onMounted(() => {
  if (!process.client) return;

  const storedId = localStorage.getItem('productID');
  productID.value = storedId.toString ? storedId : null;

  console.log('productID.value', productID.value)
  setProducts();
  getCategory();
  // on n'appelle PAS directement setProducts ici !

  isMounted.value = true
  
});



function resetForm() {
  newCat.value = false;
  categoryName.value = '';
  categoryImage.value = null;
  logSave.value = '';
  previewImage.value = null;
  modal.value = [];
  catalogImage.value = [];
  desImage.value = [];
  isModal.value = false;
  isCatalog.value = false;
  isDescription.value = false;
  description.value = "";
  activeDes.value = true;
  selectedCategory.value = '';
  catLog.value = '';
  categoryList.value = [];
  label.value = '';
  productName.value = '';
  youtubeLink.value = '';
  videoId.value = null;
  color.value = ''
  colorName.value = ''
  size.value = ''
  isExplorer.value = false
  isExplorer3.value = false
  isExplorer4.value = false
  colors.value = []
  sizes.value = []

  productImg.value = ''
}
</script>

<style scoped>
.inputYoutube {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  width: 100%;
  max-width: 800px;
}

.youtube {
  width: 100%; 
  height: 300px; 
  margin-top: 50px;
}

.inputImg {
display: flex;
align-items: center;
justify-content: center;
width: 200px;
height: 200px;
background-color: var(--color-whizy);
border: 1px solod var(--color-rangy);
border-radius: 5px;
padding: 5px;
cursor: pointer; /* Indique que c'est cliquable */
transition: all 0.3s ease;
margin: 5px;
text-align: center;
color: var(--color-rangy);

}

.inputImg img {
max-width: 200px;
max-height: 200px;
object-fit: contain;
}

.dark .inputImg{
background-color: var(--color-darky);
}
.inputImg button{
background-color: var(--color-rangy);
cursor: pointer;
border: none;
cursor: pointer;
padding: 10px;
border-radius: 50%;
display: flex;
align-items: center;
justify-content: center;
transition: background 0.3s ease;
}
.inputImg button svg{
color: var(--color-whity);
}

.boxProduct {
    width: 90%;
    margin: 10px;
    border-radius: 8px;
    padding-block: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    background-color: var(--color-whitly);
    box-shadow: 0 4px 8px #3b3b3b20;
    text-align: center;
  }
  .dark .boxProduct {
    background-color: var(--color-darkly);
  }


  .productContent {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    flex-wrap: wrap; /* Clé pour mobile */
    gap: 10px;
}


.imageUploadSection {
  width: 100%;
  max-width: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin: 5px;
}

.productForm {
  flex: 1;
  min-width: 250px;
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 10px;
}

.formRow {
  width: calc(100% - 20px);
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  justify-content: center;
  align-items: center;
  margin: 5px;
  padding: 10px;
  background-color: var(--color-whity);
  border-radius: 12px;
  max-width: 650px;
}
.dark .formRow {
  background-color: var(--color-darkow);
}

.formLine {
  width: calc(100% - 20px);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: var(--color-whity);
  border-radius: 12px;
  margin: 10px;
  padding-inline: 5px;
  cursor: pointer;
}
.dark .formLine {
  background-color: var(--color-darkow);
}

/* Responsive pour écrans <= 768px */
@media screen and (max-width: 768px) {
  .productContent {
    flex-direction: column;
    align-items: center;
  }

  .formRow {
    display: flex;
    justify-content: center;
    flex-direction: column;
    align-items: center;
  }

  .productForm {
    width: 100%;
  }
}
.formRow2 {
  display: flex;
  justify-content: center;
  align-items: center;
}
.formRow2 p {
  max-width: 100px;
  min-width: 50px;
  background-color: var(--color-whity);
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 5px;
}
.dark .formRow2 p {
  background-color: var(--color-darkow);
}


.inputImg2 {
display: flex;
align-items: center;
justify-content: center;
width: 100px;
height: 100px;
background-color: var(--color-whizy);
border: 1px solod var(--color-rangy);
border-radius: 5px;
padding: 5px;
cursor: pointer; /* Indique que c'est cliquable */
transition: all 0.3s ease;
margin: 5px;
text-align: center;
color: var(--color-rangy);
}
.dark .inputImg2{
background-color: var(--color-darky);
/*box-shadow: 0px 0px 8px var(--color-rangy);*/
}
.inputImg2 img {
max-width: 100px;
max-height: 100px;
object-fit: contain;
}

.inputImg3 {
display: flex;
align-items: center;
justify-content: center;
flex-direction: column;
width: 100px;
height: 100px;
background-color: var(--color-whity);
border: 1px dashed var(--color-rangy);
border-radius: 50%;
cursor: pointer; /* Indique que c'est cliquable */
transition: all 0.3s ease;
text-align: center;
color: var(--color-rangy);
box-shadow: 0px 2px 8px var(--color-rangy);
}
.dark .inputImg3{
background-color: var(--color-darky);
box-shadow: 0px 0px 8px var(--color-rangy);
}

.removeImg {
position: relative;
top: -140px;
left: 95px;
width: 20px;
height: 20px;
display: flex;
align-items: center;
border-radius: 50%;
justify-content: center;
cursor: pointer;
z-index: 500;
}
.removeImg svg {
color: var(--color-rady);
}

.removeImg2 {
position: relative;
top: 0;
left: 40px;
width: 20px;
height: 20px;
display: flex;
align-items: center;
border-radius: 50%;
justify-content: center;
cursor: pointer;
z-index: 500;
}
.removeImg2 svg {
color: var(--color-rady);
}


.important {
position: relative;
bottom: 0;
left: 0px;
width: 20px;
height: 20px;
display: flex;
align-items: center;
border-radius: 50%;
justify-content: center;
cursor: pointer;
z-index: 500;
}


.boxList2{
max-width: calc(100% - 20px);
width: 100%;
border-radius: 6px;
margin: 10px;
display: flex;
justify-content: center;
flex-direction: column;
align-items: center;
background-color: var(--color-whity);
}
.dark .boxList2{
background-color: var(--color-darkiw);
}

.folder-tree {
  width: 100%;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 2fr));
  gap: 12px;
  padding: 12px;
  max-height: 400px;
  overflow-y: auto;

  /* Scrollbar styling */
  scrollbar-width: thin;              /* Firefox */
  scrollbar-color: #888 transparent; /* Firefox */
}

/* Chrome, Edge, Safari */
.folder-tree::-webkit-scrollbar {
  width: 8px;
}

.folder-tree::-webkit-scrollbar-track {
  background: transparent;
}

.folder-tree::-webkit-scrollbar-thumb {
  background-color: rgba(125, 105, 142, 0.6); /* couleur mauve translucide */
  border-radius: 10px;
  border: 2px solid transparent; /* espace autour */
  background-clip: content-box;
  transition: background-color 0.3s ease;
}

.folder-tree::-webkit-scrollbar-thumb:hover {
  background-color: rgba(125, 105, 142, 0.9);
}

.folder-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  user-select: none;
  padding: 8px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
  background-color: var(--color-whity);
}
.dark .folder-item {
  background-color: var(--color-darkow);
}

.folder-item:hover {
  background-color: #1400003f;
}
.dark .folder-item:hover {
  background-color: #e0e0e03f;
}

</style>
