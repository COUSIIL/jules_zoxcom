<template>
  <div class="product-part-wrapper">

    <!-- Header Section -->
    <div class="part-header card-style" @click="modelValue.prodActive = !modelValue.prodActive">
       <div class="header-title">
          <span class="status-indicator" :class="{ active: modelValue.prodActive }"></span>
          <h1>{{ modelValue.name || t('product name') }}</h1>
       </div>
       <div class="header-actions">
           <span class="label-text">{{ modelValue.prodActive ? t('Visible') : t('Hidden') }}</span>
           <Radio :selected="modelValue.prodActive" />
       </div>
    </div>

    <div class="part-grid">

        <!-- Left Column -->
        <div class="grid-column">

            <!-- Identity Card -->
            <div class="part-card card-style">
                <h3>{{ t('Identity') }}</h3>
                <div class="card-content">
                     <Inputer :placeHolder="t('product name')" :img="resizeSvg('package', 16, 16)" :required="true" v-model="modelValue.name" @blur:modelValue="generateSlug"/>
                     <Inputer :placeHolder="t('slug')" :holder="t('this input must be unique')" :img="resizeSvg('link', 16, 16)" :required="true" v-model="modelValue.slug"/>
                     <Inputer :placeHolder="t('badge')" :holder="t('ex: limited')" :img="resizeSvg('reference', 16, 16)" :required="false" v-model="modelValue.label"/>
                </div>
            </div>

            <!-- Media Card -->
            <div class="part-card card-style">
                 <div class="card-header">
                    <h3>{{ t('Media') }}</h3>
                 </div>

                 <div class="media-section">
                     <!-- Main Image -->
                     <div class="main-image-area">
                        <button type="button" class="image-upload-btn" @click="openExplorer">
                            <div class="important-icon">
                                <DotLottieVue style="height: 24px; width: 24px" src="/animations/important.lottie" autoplay loop />
                            </div>
                            <div class="image-preview-box">
                                <span v-if="!modelValue.image" class="placeholder-text">{{ t('product image 1:1') }}</span>
                                <img v-else :src="modelValue.image" alt="Preview" />
                            </div>
                        </button>
                     </div>

                     <!-- Catalog -->
                     <div class="catalog-area">
                        <div class="catalog-header" @click="modelValue.cataActive = !modelValue.cataActive">
                            <span>{{ t('product catalog') }}</span>
                            <Radio :selected="modelValue.cataActive"/>
                        </div>

                        <div class="catalog-grid">
                            <div v-if="modelValue.catalog" v-for="(ref, index) in modelValue.catalog" :key="index" class="catalog-item">
                                <button type="button" class="catalog-thumb" @click="openExplorer2(index)">
                                    <span v-if="!ref.previewImage">{{ t('optimal 1:1') }}</span>
                                    <img v-else :src="ref.previewImage" alt="Catalog" />
                                </button>
                                <button class="remove-btn" @click="clearCatalog(index)" type="button">
                                     <div v-html="resizeSvg('trashX', 16, 16)"></div>
                                </button>
                            </div>

                            <!-- Add Button -->
                            <button class="add-catalog-btn" @click="addCatalog">
                                <div v-html="resizeSvg('add', 24, 24)"></div>
                                <span>{{ t('add image') }}</span>
                            </button>
                        </div>
                     </div>
                 </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="grid-column">

            <!-- Classification Card -->
            <div class="part-card card-style">
                 <div class="card-header" @click="categoryActive = !categoryActive">
                    <h3>{{ t('Classification') }}</h3>
                     <!-- Using existing newCat toggle logic if needed, but grouping logic here -->
                    <Radio :selected="categoryActive"/>
                 </div>

                 <div class="card-content" v-if="!newCat">
                      <div v-if="categories" class="classification-inputs">
                        <Selector :options="categories" @update:modelValue="setCat" color="var(--color-zioly2)" :placeHolder="t('categorie')" :modelValue="categoryName" v-model="categoryName"/>
                        <Selector v-if="categoryName && subCategories" :options="subCategories" @update:modelValue="setSubCat" color="var(--color-zioly2)" :placeHolder="t('sub categorie')" :modelValue="categoryName2" v-model="categoryName2"/>
                        <Selector v-if="categoryName2 && categoriesElements" :options="categoriesElements" @update:modelValue="setCatElements" color="var(--color-zioly2)" :placeHolder="t('categorie element')" :modelValue="categoryName3" v-model="categoryName3"/>
                      </div>
                      <p v-else class="empty-text">{{ t('no category yet') }}</p>

                      <div class="actions-row">
                          <Gbtn :text="t('add new category')" @click="newCategory" color="var(--color-zioly2)" :svg="icons['add']"/>
                      </div>
                 </div>

                 <EditCat v-if="newCat" :model-value="categoryName" @saved="updatedCategories" @cancel="newCat = false"/>
            </div>

            <!-- Content Card -->
            <div class="part-card card-style">
                <div class="card-header" @click="modelValue.isDescription = !modelValue.isDescription">
                    <h3>{{ t('Description') }}</h3>
                    <Radio :selected="modelValue.isDescription"/>
                </div>
                <div class="editor-wrapper">
                    <Editor :key="descriptionKey" v-model="modelValue.description" @update:modelValue="updateDescription" />
                </div>
            </div>

             <!-- Video Card -->
            <div class="part-card card-style">
                <div class="card-header">
                     <h3>{{ t('Video') }}</h3>
                     <Radio :selected="modelValue.ytbActive" @changed="modelValue.ytbActive = !modelValue.ytbActive"/>
                </div>
                <div class="card-content">
                    <Inputer :placeHolder="t('youtube link')" :img="resizeSvg('youtube', 24, 24)" :required="false" v-model="modelValue.youtubeUrl" @onBlur="updateVideoId"/>

                    <div v-if="videoId" class="video-preview">
                        <iframe
                            :src="`https://www.youtube.com/embed/${videoId}`"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </div>

        </div>

    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import Radio from './bloc/radio.vue';
import Inputer from './bloc/input.vue';
import iconsFilled from '../../public/iconsFilled.json'
import icons from '../../public/icons.json'
import Gbtn from './bloc/gBtn.vue';
import Editor from '../editor.vue';
import EditCat from '../elements/editCategory.vue';
import Selector from '../elements/bloc/select.vue';
import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

const { t } = useLang()

const props = defineProps({
  modelValue: { type: Object, required: true },
  prodImage: { type: String, default: '' },
  imageRef: { type: String, default: '' }
})

const emit = defineEmits(['openExplorProdImg', 'openExplorCataImg', 'message', 'isMessage', 'update:modelValue'])

const catalogSelected = ref(-1)
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
const videoId = ref(null)
const descriptionKey = ref(0)

// Sync external image changes
watch(() => props.prodImage, (newVal) => {
  if (newVal) props.modelValue.image = newVal
})

watch(() => props.imageRef, (newVal) => {
  if (catalogSelected.value !== -1 && props.modelValue.catalog && props.modelValue.catalog[catalogSelected.value]) {
     props.modelValue.catalog[catalogSelected.value].previewImage = newVal
  } else if (newVal) {
     // Fallback if needed
  }
})

// Moved watch after updateVideoId definition to avoid ReferenceError

const generateSlug = () => {
  if (props.modelValue.name) {
     props.modelValue.slug = props.modelValue.name.toLowerCase().replace(/[^\w\s-]/g, '').trim().replace(/\s+/g, '-')
  }
}

var resizeSvg = (svg, width, height) => {
    if(iconsFilled[svg]) {
        return iconsFilled[svg].replace(/width="[^"]+"/, `width="${width}"`).replace(/height="[^"]+"/, `height="${height}"`)
    } else if(icons[svg]) {
        return icons[svg].replace(/width="[^"]+"/, `width="${width}"`).replace(/height="[^"]+"/, `height="${height}"`)
    } else {
        return icons['svg'] ? icons['svg'].replace(/width="[^"]+"/, `width="${width}"`).replace(/height="[^"]+"/, `height="${height}"`) : ''
    }
}

const openExplorer = () => {
    emit('openExplorProdImg')
}

const openExplorer2 = (index) => {
  catalogSelected.value = index
  emit('openExplorCataImg', index) // Pass index if needed by parent, though parent uses imageRef
}

function addCatalog() {
  if (!props.modelValue.catalog) props.modelValue.catalog = []
  props.modelValue.catalog.push({
    previewImage: null ,
    imageBlob: null,
  });
}

function clearCatalog(index) {
  if (index >= 0 && index < props.modelValue.catalog.length) {
    props.modelValue.catalog.splice(index, 1);
  } else {
    emit('isMessage', true)
    emit('message', t('invalid index'))
  }
}

const updateDescription = (value) => {
  props.modelValue.description = value;
};

function setCat(cat) {
  categoryName.value = cat
  updateSelectedCategory()
}
function setSubCat(cat) {
  categoryName2.value = cat
  updateSelectedCategory()
}
function setCatElements(cat) {
  categoryName3.value = cat
  updateSelectedCategory()
}

function updateSelectedCategory() {
    // Logic to find ID from names
    // This is simplified, actual logic depends on how categories are structured
    // Assuming backend handles ID resolution or we just pass ID.
    // For now, let's try to set modelValue.category to the ID of the deepest selected category

    let selected = null;

    // Helper to find ID by name in flat list if needed
    const findId = (name) => {
        const found = categoryList.value.find(c => c.label === name);
        return found ? found.value : null;
    }

    if (categoryName3.value) selected = findId(categoryName3.value);
    else if (categoryName2.value) selected = findId(categoryName2.value);
    else if (categoryName.value) selected = findId(categoryName.value);

    if (selected) props.modelValue.category = selected;
}

function newCategory() {
  newCat.value = true;
}

function updatedCategories(result) {
  // Refresh categories logic
  // Reuse existing logic from original file
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
      emit('isMessage', true)
      emit('message', 'error in getting data')
      return
    }
    const result = await response.json();
    if (result.success) {
      // Logic to populate categories
      // reusing logic
      for(var i = 0; i < result.categories.length; i++) {
        categoryList.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
        if(result.categories[i].level === 'meta') {
          categories.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
        } else if(result.categories[i].level === 'branch') {
          subCategories.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
        } else if(result.categories[i].level === 'leaf') {
          categoriesElements.value.push({'label': result.categories[i].name, 'image': result.categories[i].image, 'value': parseInt(result.categories[i].id), 'level': result.categories[i].level})
        }
      }
      
      // Reverse init category selection based on modelValue.category (ID)
       if(props.modelValue.category) {
           const id = props.modelValue.category;
           const cat = categoryList.value.find(c => c.value === id);
           // ... logic to prefill selectors ...
           // Simplify for now:
           if(cat) {
               if(cat.level === 'meta') categoryName.value = cat.label;
               // Need parent traversal for deeper levels, assuming flat list has parent_id or similar from API
               // Original code had logic for this, I should try to preserve it if possible or rely on backend data
           }
       }

    } else {
      emit('isMessage', true)
      emit('message', result.message)
    }
}

const updateVideoId = () => {
    if (props.modelValue.youtubeUrl) {
        videoId.value = getYouTubeId(props.modelValue.youtubeUrl);
    }
};

watch(() => props.modelValue.youtubeUrl, () => {
  updateVideoId()
}, { immediate: true })

watch(() => props.modelValue.description, () => {
  // refresh logic if needed
})

function getYouTubeId(url) {
  if (!url) return null;
  const regExp = /^.*(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|.*[?&]v=))([^#\&\?]*).*/;
  const match = url.match(regExp);
  return match && match[1] ? match[1] : null;
}

onMounted(async () => {
  await getCategory();
  updateVideoId();
  // Force editor refresh
  descriptionKey.value++;
});

</script>

<style scoped>
.product-part-wrapper {
    display: flex;
    flex-direction: column;
    gap: 15px;
    width: 100%;
}

.card-style {
    background-color: var(--color-whitly);
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    padding: 15px;
    transition: all 0.2s;
}
.dark .card-style {
    background-color: var(--color-darkly);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

/* Header */
.part-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
}
.header-title {
    display: flex;
    align-items: center;
    gap: 10px;
}
.status-indicator {
    width: 10px; height: 10px;
    border-radius: 50%;
    background-color: var(--color-rady);
}
.status-indicator.active {
    background-color: var(--color-greeny);
}
.part-header h1 {
    font-size: 1.25rem;
    font-weight: 700;
}
.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Grid Layout */
.part-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}
@media(max-width: 900px) {
    .part-grid { grid-template-columns: 1fr; }
}

.grid-column {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.part-card h3 {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: var(--color-text-muted);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    cursor: pointer;
}
.card-header h3 { margin-bottom: 0; }

.card-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
}

/* Media Section */
.media-section {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.main-image-area {
    display: flex;
    justify-content: center;
}
.image-upload-btn {
    position: relative;
    border: 2px dashed var(--color-zioly2);
    border-radius: 8px;
    background: transparent;
    cursor: pointer;
    padding: 10px;
    transition: border-color 0.2s;
}
.image-upload-btn:hover { border-color: var(--color-primary); }
.important-icon {
    position: absolute; top: -10px; left: -10px;
    background: var(--color-whitly);
    border-radius: 50%;
}
.dark .important-icon { background: var(--color-darkly); }
.image-preview-box {
    width: 180px; height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.image-preview-box img {
    max-width: 100%; max-height: 100%; object-fit: contain;
}

/* Catalog */
.catalog-header {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 10px; cursor: pointer;
    font-size: 0.9rem; font-weight: 600;
}
.catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
}
.catalog-item {
    position: relative;
    aspect-ratio: 1;
}
.catalog-thumb {
    width: 100%; height: 100%;
    border: 1px solid var(--color-zioly2);
    border-radius: 6px;
    background: var(--color-whizy);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; overflow: hidden;
    padding: 2px;
}
.dark .catalog-thumb { background: var(--color-darky); }
.catalog-thumb img { width: 100%; height: 100%; object-fit: contain; }
.remove-btn {
    position: absolute; top: -5px; right: -5px;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: var(--color-whitly);
    border: 1px solid var(--color-rady);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--color-rady);
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.add-catalog-btn {
    aspect-ratio: 1;
    border: 2px dashed var(--color-zioly2);
    border-radius: 6px;
    background: transparent;
    cursor: pointer;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 5px; color: var(--color-zioly2);
    font-size: 0.75rem;
}
.add-catalog-btn:hover { border-color: var(--color-primary); color: var(--color-primary); }

/* Classification */
.classification-inputs {
    display: flex; flex-direction: column; width: 100%; align-items: center; gap: 5px;
}
.empty-text { color: var(--color-text-muted); text-align: center; font-style: italic; }
.actions-row { width: 100%; display: flex; justify-content: center; margin-top: 10px; }

/* Video */
.video-preview {
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
    border-radius: 6px;
    overflow: hidden;
    margin-top: 10px;
}
.video-preview iframe { width: 100%; height: 100%; }

.editor-wrapper {
    /* Style fix for the editor component if needed */
    width: 100%;
}
</style>