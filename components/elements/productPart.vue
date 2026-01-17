<template>

  <div class="spacer"></div>

    <div class="container">

      <h1 @click="prodActive = !prodActive">
          <span v-if="!productName">
              {{t('product name')}}
          </span>
          <span v-else>
              {{productName}}
          </span>
          

          <Radio :selected="prodActive"/>
      </h1>

      <div style="display: flex; align-items: center; justify-content: center; width: 100%;">
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
            <span v-if="!prodImage">{{ t('product image 1:1') }}</span>
            <img v-else-if="prodImage" :src="prodImage" alt="Preview" />
          </label>
        </button>
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
          <Inputer :placeHolder="t('product name')" :img="resizeSvg('package', 16, 16)" :required="true" v-model="productName" @blur:modelValue=""/>

          <Inputer :placeHolder="t('slug')" :holder="t('this input must be unique')" :img="resizeSvg('link', 16, 16)" :required="true" v-model="slug" @blur:modelValue=""/>

          <Inputer :placeHolder="t('badge')" :holder="t('ex: limited')" :img="resizeSvg('reference', 16, 16)" :required="false" v-model="badge" @blur:modelValue=""/>
        </div>
      </div>

      



      


      
    </div>
    <div class="container">

      <div class="formRow" @click="catalogActive = !catalogActive">
          <h2>
              {{ t('product catalog') }}

          </h2>

          <Radio :selected="catalogActive"/>
      </div>

      <Gbtn :text="t('add image')" @click="addCatalog" color="var(--color-zioly2)" :svg="icons['add']"/>


        <div class="folder-tree">
          
          <div v-if="isCatalog" v-for="(ref, index) in catalogImage" :key="index"
              >

              
            <button type="button" class="folder-item" @click="openExplorer2(index)">
              <!--h3 style="font-size: 14px;">id: {{ index + 1 }}</!h3-->
              <label class="inputImg2">
                <span v-if="!ref.previewImage">{{ t('optimal 1:1') }}</span>
                <img v-else-if="ref.previewImage" :src="ref.previewImage" alt="Preview" />
              </label>
            </button>

              <!-- Bouton de suppression -->
            <button class="removeImg" @click="clearCatalog(index)" type="button">
              <div v-html="resizeSvg('trashX', 24, 24)">

              </div>
            </button>

              
          </div>
          
          
        </div>
      </div>

      <div class="container">

        <div v-if="!newCat" class="formRow" @click="categoryActive = !categoryActive">
          <h2>
            {{ t('activate category') }}
          </h2>
              
          <Radio :selected="categoryActive"/>
        </div>

      <div v-if="!newCat">
        <div v-if="categories" class="formRow">
          <Selector :options="categories" @update:modelValue="setCat" color="var(--color-zioly2)" :placeHolder="t('categorie')" :modelValue="categoryName" v-model="categoryName"/>
          <Selector v-if="categoryName && subCategories" :options="subCategories" @update:modelValue="setSubCat" color="var(--color-zioly2)" :placeHolder="t('sub categorie')" :modelValue="categoryName2" v-model="categoryName2"/>
          <Selector v-if="categoryName2 && categoriesElements" :options="categoriesElements" @update:modelValue="setCatElements" color="var(--color-zioly2)" :placeHolder="t('categorie element')" :modelValue="categoryName3" v-model="categoryName3"/>
          
        </div>
        <p v-else >
          {{ t('no category yet') }}
        </p>
        

        <Gbtn :text="t('add new category')" @click="newCategory" color="var(--color-zioly2)" :svg="icons['add']"/>
          
      </div>
      </div>
      <EditCat v-if="newCat"
          :model-value="categoryName"
          @saved="updatedCategories"
          @cancel="newCat = false"
      /> 

    <div class="container">
      <div class="formRow" @click="activeDes = !activeDes">
        <h2>
          {{ t('activate description') }}
        </h2>
            
        <Radio :selected="activeDes"/>
      </div>
      
      <Editor :key="descriptionKey" v-model="description"  @update:modelValue="updateDescription" />


    </div>

    <div class="container">
      <div class="formRow" >
        <Inputer :placeHolder="t('youtube link')" :img="resizeSvg('youtube', 24, 24)" :required="false" v-model="youtubeLink" @onBlur="updateVideoId"/>

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

  <div class="spacer"></div>
</template>

<script setup>
import Radio from './bloc/radio.vue';
import Inputer from './bloc/input.vue';
import iconsFilled from '../../public/iconsFilled.json'
import icons from '../../public/icons.json'
import Gbtn from './bloc/gBtn.vue';
import Editor from '../editor.vue';
import EditCat from '../elements/editCategory.vue';
import Selector from '../elements/bloc/select.vue';

import { watch } from 'vue';

import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

const { t } = useLang()

const prodActive = ref(true)
const catalogActive = ref(true)
const catalogSelected = ref(-1)
const productName = ref('')
const slug = ref('')
const badge = ref('')
const catalogImage = ref([])
const isCatalog = ref(true)
const activeDes = ref(true)
const descriptionKey = ref(0)
const description = ref("")
const newCat = ref(false)
const categoryList = ref([])
const categories = ref([])
const subCategories = ref([])
const categoriesElements = ref([])
const categoryName = ref('')
const categoryName2 = ref('')
const categoryName3 = ref('')
const selectedCategory = ref('')
const catID = ref(0)
const categoryActive = ref(true)
const youtubeLink = ref('')
const videoId = ref(null)
const ytbActive = ref(true)



const emit = defineEmits(['openExplorProdImg', 'openExplorCataImg', 'message', 'isMessage'])

const props = defineProps({
    prodImage: {String, default: ''},
    imageRef: {String, default: ''}
})

watch(() => props.imageRef, v => {
    if(catalogImage.value[catalogSelected.value]) {
        catalogImage.value[catalogSelected.value].previewImage = v
    } else {
        catalogImage.value[catalogSelected.value].previewImage.push(v)
    }
})




var resizeSvg = (svg, width, height) => {

    if(iconsFilled[svg]) {
        return iconsFilled[svg]
        .replace(/width="[^"]+"/, `width="${width}"`)
        .replace(/height="[^"]+"/, `height="${height}"`)
    } else if(icons[svg]) {
        return icons[svg]
        .replace(/width="[^"]+"/, `width="${width}"`)
        .replace(/height="[^"]+"/, `height="${height}"`)
    } else {
        return icons['svg']
        .replace(/width="[^"]+"/, `width="${width}"`)
        .replace(/height="[^"]+"/, `height="${height}"`)
    }

}

const openExplorer = () => {
    emit('openExplorProdImg')
}

const openExplorer2 = (index) => {
  emit('openExplorCataImg')
  catalogSelected.value = index
}

function addCatalog() {
  catalogImage.value.push({ 
    previewImage: null ,
    imageBlob: null,
  });
  isCatalog.value = true;
}

function clearCatalog(index) {
  if (index >= 0 && index < catalogImage.value.length) {
    catalogImage.value.splice(index, 1);
    isCatalog.value = catalogImage.value.length > 0;
  } else {
    emit('isMessage', true)
    emit('message', t('invalid index'))
  }
}

const updateDescription = (value) => {
  description.value = value;
};

function setCat(cat) {
  categoryName.value = cat
}
function setSubCat(cat) {
  categoryName2.value = cat
}
function setCatElements(cat) {
  categoryName3.value = cat
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
      
      for(var i = 0; i < result.categories.length; i++) {
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

        if(catID.value === result.categories[i].id) {
          if(result.categories[i].level === 'meta') {
            categoryName.value = result.categories[i].name
          }
          if(result.categories[i].level === 'branch') {
            categoryName2.value = result.categories[i].name
            const parrentId = result.categories[i].parent_id
            for(var met of result.categories) {
              if(met.id === parrentId) {
                categoryName.value = met.name
                return
              }
            }
          }
          if(result.categories[i].level === 'leaf') {
            categoryName3.value = result.categories[i].name
            const parrentId = result.categories[i].parent_id
            for(var met of result.categories) {
              if(met.id === parrentId) {
                categoryName2.value = met.name
                const parrentId2 = met.parent_id
                for(var met2 of result.categories) {
                  if(met2.id === parrentId2) {
                    categoryName.value = met2.name
                    return
                  }
                }
              }
            }
          }
          
        }
      }

      
    } else {
      emit('isMessage', true)
      emit('message', result.message)
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


onMounted(async () => {


  await getCategory();
  // on n'appelle PAS directement setProducts ici !

  
});




</script>

<style scoped>

.spacer{
  height: 80px;
}

.container{
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 90%;
    background-color: var(--color-whitly);
    border-radius: 10px;
    box-shadow: 2px 2px 2px 1px rgba(0, 0, 0, 0.1);
    margin-block: 5px;
    padding-block: 5px;
}
.dark .container {
    background-color: var(--color-darkly);
    box-shadow: 2px 2px 2px 1px rgba(0, 0, 0, 0.8);


}

.container h1{
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    background-color: var(--color-whity);
    border-radius: 20px;
    padding-inline: 5px;
    margin-block: 5px;
}
.dark .container h1{
    background-color: var(--color-darkow);
}
.container h2{
    display: flex;
    justify-content: left;
    align-items: center;
    width: 100%;
    font-size: 18px;
}

.formRow{
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding-inline: 5px;
    margin-block: 5px;
    background-color: var(--color-whity);
    border-radius: 20px;
    cursor: pointer;
}
.dark .formRow{
    background-color: var(--color-darkow);
}

.imageUploadSection {
    width: 100%;
    max-width: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 5px;
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

.inputImg2 {
display: flex;
align-items: center;
justify-content: center;
width: 100px;
height: 100px;
background-color: var(--color-whizy);
border: 1px solod var(--color-rangy);
border-radius: 5px;
cursor: pointer; /* Indique que c'est cliquable */
transition: all 0.3s ease;
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


.removeImg {
  position: relative;
  top: -105px;
  left: 85px;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  border-radius: 50%;
  justify-content: center;
  cursor: pointer;
  z-index: 500;
  color: var(--color-rady);
  background-color: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}
.dark .removeImg {
  background-color: var(--color-darkow);
}
.removeImg svg {
  color: var(--color-rady);
}

.youtube {
  width: 100%; 
  height: 300px; 
  margin-top: 50px;
}




</style>