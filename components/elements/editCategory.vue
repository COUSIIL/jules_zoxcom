<template>

  <Message :isVisible="isMessage" :message="message"  @ok="isMessage = false"/>
  <Explorer :show="isExplorer" @confirm="getExplorerImg" @cancel="isExplorer = false" />
  <div class="catBox">
    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">
      {{ isEdit ? t('edit category') : t('new category') }}
    </h2>
    <div class="formLine" @click="form.sustainable = !form.sustainable">
      {{ t('activate this categorie') }}
      <Radio :selected="form.sustainable"/>
    </div>

    <div class="productContent">

          <!-- 4. Image & Preview -->
      <div style="display: flex; justify-content: center; align-items: center; flex-direction: column; margin-block: 5px;">
        <div class="important">
          <DotLottieVue
            style="height: 24px; width: 24px"
            src="/animations/important.lottie"
            autoplay
            loop
          />
        </div>
        <button
          @click.prevent="openExplorer"
          type="button"
          class="imageBox"
        >
          <template v-if="!catImage">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ t('click to choose') }}</span>
          </template>
          <img
            v-else
            :src="catImage"
            alt="preview"
            class="object-cover w-full h-full rounded-lg"
          />
        </button>
      </div>

      <!-- 1. Name & SEO Slug -->
       <div class="productForm">
        <Inputer
          v-model="form.name"
          :img="icons['category']"
          :label="t('category name')"
          :placeHolder="t('category name')"
          :required="true"
          holder="Bras Mixeur"
          @blur:modelValue="generateSlug"
          color="var(--color-zioly2)"
          class="dark:bg-darkiw dark:text-whitly"
        />
        <Inputer
          v-model="form.slug"
          :img="icons['link']"
          :label="t('seo slug')"
          :placeHolder="t('slug')"
          :required="true"
          holder="bras-mixeur"
          color="var(--color-zioly2)"
          class="dark:bg-darkiw dark:text-whitly"
        />
       </div>
      

    </div>

    

    <!-- 2. Description (meta) -->
    <TextInputer
      v-model="form.description"
      :img="icons['edit']"
      :label="t('meta description')"
      type="textarea"
      :required="true"
      :placeHolder="t('description')"
        color="var(--color-zioly2)"
        class="dark:bg-darkiw dark:text-whitly"
    />

    <p>
      {{ t('chose categorie type') }}
    </p>
    <div style="display: flex; justify-content: center; align-items: center; gap: 5px; margin-block: 5px;">
      <div class="btnBox" :style="form.level === 'meta' ? {border: '2px solid var(--color-greny)'} : {}" @click="form.level = 'meta'">
        <div v-html="icons['categorie']" alt="meta"></div>
        <p>
          {{ t('categorie') }}
        </p>
      </div>
      <div class="btnBox" :style="form.level === 'branch' ? {border: '2px solid var(--color-greny)'} : {}" @click="form.level = 'branch'">
        <div v-html="icons['branch']" alt="meta"></div>
        <p>
          {{ t('subcategory') }}
        </p>
      </div>
      <div class="btnBox" :style="form.level === 'leaf' ? {border: '2px solid var(--color-greny)'} : {}" @click="form.level = 'leaf'">
        <div v-html="icons['leaf']" alt="meta"></div>
        <p>
          {{ t('elements') }}
        </p>
      </div>
    </div>
    


    <div class="productContent">
      <Selector v-if="form.level != 'meta'"
        v-model="form.parent_id"
        :img="icons['link']"
        :label="t('parent category')"
        :options="parentOptions"
        :disabled="form.level === 'meta'"
        :placeHolder="form.level === 'branch' ? t('category') : form.level === 'leaf' ? t('subcategorie') : t('parent')"
        :required="true"
        color="var(--color-zioly2)"
        class="dark:bg-darkiw dark:text-whitly"
      />



      <!-- 6. Faceted Search Attributes -->
      <Selector
        v-model="form.facets"
        :img="icons['filter']"
        :options="facetOptions"
        :label="t('filter attributes')"
        :placeHolder="t('attributes')"
        :required="true"
        color="var(--color-zioly2)"
        
      />

    </div>


    <!-- 7. Advanced SEO (optional) -->
    <Inputer
        v-model="form.meta_title"
        :img="icons['tag']"
        :label="t('meta title')"
        :placeHolder="t('seo title')"
        holder="descriptive and informative title"
        color="var(--color-zioly2)"
        
      />

    <!-- 8. Actions -->
    <div v-if="!isSaving" class="flex justify-end gap-4 pt-4">
      <Gbtn
        :text="t('cancel')"
        color="var(--color-rady)"
        @click="cancel"
        :svg="icons['x']"
        :disabled="isSaving"
      />
      <Gbtn
        :text="t(isEdit ? 'save category' : 'create category')"
        color="var(--color-greny)"
        @click="saveCategory"
        :svg="icons['check']"
        :loading="isSaving"
      />
    </div>

    <LoaderBlack v-else style="width: 100px;"/>


  </div>
</template>

<script setup>
import Gbtn from './bloc/gBtn.vue'
import Inputer from './bloc/input.vue'
import Message from './bloc/message.vue'
import TextInputer from './bloc/inputText.vue'
import Selector from './bloc/select.vue'
import icons from '~/public/icons.json'
import Explorer from '../components/elements/explorer.vue';
import Radio from '../components/elements/bloc/radio.vue';
import LoaderBlack from './animations/loaderBlack.vue';

import { ref, computed, onMounted } from 'vue'
import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

const { t } = useLang()
const emit = defineEmits(['saved', 'cancel'])
const props = defineProps({
  modelValue: { type: Object, default: null },
})

const categoryList = ref([])

const isEdit = ref(!!props.modelValue)
const isSaving = ref(false)
const message = ref('')
const isMessage = ref(false)

var result = ref()

const form = ref({
  id: null,
  name: '',
  slug: '',
  description: '',
  level: 'meta',
  parent_id: null,
  image: '',
  sustainable: true,
  facets: [],
  meta_title: '',
})

const facetOptions = [
  { label: t('color'), value: 'color' },
  { label: t('size'), value: 'size' },
  { label: t('brand'), value: 'brand' },
  { label: t('price range'), value: 'priceRange' },
  { label: t('discount'), value: 'discount' },
  { label: t('availability'), value: 'availability' }, // En stock / Rupture
  { label: t('rating'), value: 'rating' },
  { label: t('delivery option'), value: 'deliveryOption' }, // Livraison rapide, gratuite...
  { label: t('style'), value: 'style' },
  { label: t('material'), value: 'material' },
  { label: t('occasion'), value: 'occasion' }, // Mariage, Sport, Bureau...
  { label: t('season'), value: 'season' }, // Été, Hiver...
  { label: t('fit'), value: 'fit' }, // Slim, Regular, Oversize...
  { label: t('gender'), value: 'gender' },
  { label: t('age group'), value: 'ageGroup' },
  { label: t('pattern'), value: 'pattern' }, // Uni, Rayé, Fleuri...
  { label: t('feature'), value: 'feature' }, // Imperméable, Antidérapant, Anti-UV...
  { label: t('collection'), value: 'collection' },
  { label: t('origin'), value: 'origin' }, // Fabriqué en France, Italie...
  { label: t('eco responsibility'), value: 'ecoFriendly' },
  { label: t('bestseller'), value: 'bestSeller' },
  { label: t('new arrival'), value: 'newArrival' },
  { label: t('customizable'), value: 'customizable' },
  { label: t('usage'), value: 'usage' }, // Extérieur, intérieur
  { label: t('compatibility'), value: 'compatibility' }, // Pour accessoires tech (iPhone, Android...)
  { label: t('warranty'), value: 'warranty' },
  { label: t('stock level'), value: 'stockLevel' }
]


const catImage = ref('')
const isExplorer = ref(false)

const getExplorerImg = (value) => {
  
  catImage.value = value
  isExplorer.value = !isExplorer.value
}

const parentOptions = computed(() => {
  
  if (form.value.level == 'branch') {
    return categoryList.value
      .filter(c => c.level == 'meta')
      .map(c => ({ label: c.label, value: c.value }))
  }
  if (form.value.level == 'leaf') {
    return categoryList.value
      .filter(c => c.level !== 'leaf' && c.level !== 'meta')
      .map(c => ({ label: `${c.level}: ${c.label}`, value: c.value }))
  }
  return []
})
onMounted(() => {
  getCategory()
  if (props.modelValue) Object.assign(form.value, props.modelValue)

})

function generateSlug() {
  form.value.slug = form.value.name
    .toLowerCase()
    .replace(/[^\w\s-]/g, '')
    .trim()
    .replace(/\s+/g, '-')
}

function openExplorer() {
  isExplorer.value = true
}

async function saveCategory() {
  // 1. Validation locale
  if (!form.value.name) {
    
    message.value = t('please fill categorie name');
    isMessage.value = true
    return;
  }
  if (!form.value.level) {
    message.value = t('please fill categorie type');
    isMessage.value = true
    return;
  }
  if (!catImage.value) {
    message.value = t('please fill add image categorie');
    isMessage.value = true
    return;
  }
  if (!form.value.slug) {
    message.value = t('please fill categorie slug');
    isMessage.value = true
    return;
  }
  if (!form.value.parent_id && form.value.level != 'meta') {
    message.value = t('please fill link parrent categorie');
    isMessage.value = true
    return;
  }
  if (!form.value.description) {
    message.value = t('please fill categorie description');
    isMessage.value = true
    return;
  }
  if (!form.value.facets) {
    message.value = t('please fill categorie facets');
    isMessage.value = true
    return;
  }
  if (!form.value.meta_title) {
    message.value = t('please fill meta title categorie');
    isMessage.value = true
    return;
  }

  isSaving.value = true;
  message.value   = '';


  form.value.image = catImage.value;

  // 2. Prépare le payload
  const payload = { ...form.value };

  try {
    // 3. Envoi POST
    const res = await fetch('https://management.hoggari.com/backend/api.php?action=postCategory', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify(payload)
    });

    // 4. On parse toujours le JSON
    const data = await res.json();

    // 5. Gestion des erreurs HTTP
    if (!res.ok) {
      // Par exemple, 400, 500...
      message.value = data.message;
      isMessage.value = true
      return
    }

    // 6. Succès !
    // data contient { success, message, id }
    
    // On met à jour l'id dans le form s'il s'agit d'une création
    form.value.id = data.id ?? form.value.id;
    message.value = t(data.message);
    isMessage.value = true
    // On notifie le parent
    getCategory()
    emit('saved', result.value);
    

  } catch (err) {
    message.value = err.message || t('an error occurred');
    isMessage.value = true
  } finally {
    isSaving.value = false;
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
    result.value = await response.json();
    if (result.value.success) {
      for(var i =0; i < result.value.categories.length; i++) {
        categoryList.value.push({'label': result.value.categories[i].name, 'image': result.value.categories[i].image, 'value': parseInt(result.value.categories[i].id), 'level': result.value.categories[i].level})
      }
    } else {
      isMessage.value = true
      message.value = result.message
    }

}


function cancel() {
  emit('cancel')
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.catBox {
    width: calc(100% - 20px);
  max-width: 800px;
  top: 50px;
  margin: 10px;
  border-radius: 8px;
  padding: 5px;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
  background-color: var(--color-whitly);
  box-shadow: 0 4px 8px #3b3b3b20;
  text-align: center;
}
.dark .catBox {
  background-color: var(--color-darkly);
  box-shadow: 0 4px 8px #00000033;
}

.imageBox {
  width: 150px;
  height: 150px;
  border-radius: 24px;
  background-color: var(--color-whizy);
  cursor: pointer;
}
.imageBox:hover {
  background-color: var(--color-whiby);
}
.dark .imageBox {
  background-color: var(--color-darkiw);
}
.dark .imageBox:hover {
  background-color: var(--color-darkow);
}

.btnBox {
  max-width: 150px;
  max-height: 150px;
  min-width: 100px;
  min-height: 100px;
  border-radius: 24px;
  background-color: var(--color-whizy);
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}
.btnBox p {
  font-size: 14px;
}
.btnBox:hover {
  background-color: var(--color-whiby);
}
.dark .btnBox {
  background-color: var(--color-darkiw);
}
.dark .btnBox:hover {
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
  gap: 5px;
  justify-content: space-between;
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
</style>
