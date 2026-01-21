<template>
  <div class="spacer"></div>

  <div class="container" v-for="(model, mIndex) in modelValue.models" :key="mIndex">
    <div class="formRow" @click="model.isActive = !model.isActive">
      <h2>{{ t('model') }} {{ mIndex + 1 }}: {{ model.name }}</h2>
      <Radio :selected="model.isActive"/>
    </div>

    <div v-if="model.isActive">
       <!-- Basic Info -->
       <div style="display: flex; align-items: center; justify-content: center; width: 100%;">
          <button type="button" class="imageUploadSection" @click="openExplorer(mIndex)">
             <label class="inputImg">
                <span v-if="!model.imageUrls">{{ t('model image 1:1') }}</span>
                <img v-else :src="model.imageUrls" alt="Preview" />
             </label>
          </button>

          <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
             <Inputer :placeHolder="t('model name')" :img="resizeSvg('package', 16, 16)" :required="true" v-model="model.name"/>
             <Inputer :placeHolder="t('reference')" :img="resizeSvg('reference', 16, 16)" :required="false" v-model="model.ref"/>
             <Inputer :placeHolder="t('sku')" :img="resizeSvg('barcode1', 16, 16)" :required="false" v-model="model.sku"/>
             <Inputer :placeHolder="t('brand')" :img="resizeSvg('tag', 16, 16)" :required="false" v-model="model.brand"/>
          </div>
       </div>

       <!-- Price Configuration -->
       <div class="formRow" @click="{ model.isVariablePrice = !model.isVariablePrice; if(model.isVariablePrice) model.activeColor = true; }">
          <h3>{{ t('variable price per variant') }}</h3>
          <Radio :selected="model.isVariablePrice"/>
       </div>

       <div v-if="!model.isVariablePrice" class="sub-section">
          <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 10px;">
             <Inputer type="number" :placeHolder="t('buying price')" :img="resizeSvg('invoice', 16, 16)" v-model="model.buy"/>
             <Inputer type="number" :placeHolder="t('selling price')" :img="resizeSvg('moneyTag', 16, 16)" :required="true" v-model="model.sell"/>
             <Inputer type="number" :placeHolder="t('promo price')" :img="resizeSvg('promotion', 16, 16)" v-model="model.promo"/>
          </div>

          <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
             <Inputer v-if="!model.infinit_stock" type="number" :placeHolder="t('quantity')" :img="resizeSvg('box', 16, 16)" v-model="model.qty"/>
             <div class="formRow" style="width: auto;" @click="model.infinit_stock = !model.infinit_stock">
                <span>{{ t('infinite stock') }}</span>
                <Radio :selected="model.infinit_stock"/>
             </div>
          </div>
       </div>

       <!-- Variants Configuration -->
       <div class="formRow" @click="{ model.activeColor = !model.activeColor; if(!model.activeColor) model.isVariablePrice = false; }">
          <h3>{{ t('activate variants (color/size)') }}</h3>
          <Radio :selected="model.activeColor"/>
       </div>

       <div v-if="model.activeColor" class="sub-section">
          <Gbtn :text="t('add variant')" @click="addVariant(mIndex)" color="var(--color-zioly2)" :svg="icons['add']"/>

          <div v-for="(detail, dIndex) in model.details" :key="dIndex" class="variant-item">
              <div style="display: flex; justify-content: space-between; width: 100%;">
                  <h4>{{ t('variant') }} {{ dIndex + 1 }}</h4>
                  <button class="removeImg" style="top:0; left:0; position:relative;" @click="removeVariant(mIndex, dIndex)">
                     <div v-html="resizeSvg('trashX', 20, 20)"></div>
                  </button>
              </div>

              <!-- Variant Image from Catalog -->
              <div class="catalog-selector">
                  <span v-if="!detail.catalog_image">{{ t('select image') }}</span>
                  <img v-else :src="detail.catalog_image" class="mini-preview" />
                  <Gbtn :text="t('pick')" @click="openCatalogPicker(mIndex, dIndex)" :svg="icons['image']" />
              </div>

              <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                 <Inputer :placeHolder="t('color name')" v-model="detail.colorName" />
                 <Inputer :placeHolder="t('size')" v-model="detail.size" />
                 <input type="color" v-model="detail.color" style="height: 40px; border: none; background: none;" />
              </div>

              <div v-if="model.isVariablePrice">
                  <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                     <Inputer type="number" :placeHolder="t('buy')" v-model="detail.buy" style="max-width: 80px;" />
                     <Inputer type="number" :placeHolder="t('sell')" v-model="detail.sell" style="max-width: 80px;" />
                     <Inputer type="number" :placeHolder="t('qty')" v-model="detail.qty" style="max-width: 80px;" />
                  </div>
              </div>
              <div v-else>
                  <Inputer type="number" :placeHolder="t('qty')" v-model="detail.qty" />
              </div>
          </div>
       </div>

       <Gbtn :text="t('remove model')" @click="removeModel(mIndex)" color="var(--color-rady)" :svg="icons['trashX']"/>
    </div>
  </div>

  <div class="container">
      <Gbtn :text="t('add new model')" @click="addModel" color="var(--color-zioly2)" :svg="icons['add']"/>
  </div>

  <div class="spacer"></div>

  <!-- Catalog Picker Modal -->
  <div v-if="showCatalogPicker" class="modal-overlay">
      <div class="modal-content">
          <h3>{{ t('select from catalog') }}</h3>
          <div class="catalog-grid">
              <div v-for="(img, idx) in modelValue.catalog" :key="idx" class="catalog-item" @click="selectCatalogImage(img)">
                  <img :src="img.previewImage" />
              </div>
          </div>
          <Gbtn :text="t('close')" @click="showCatalogPicker = false" color="var(--color-rady)" />
      </div>
  </div>

</template>

<script setup>
import { ref, watch } from 'vue';
import Radio from './bloc/radio.vue';
import Inputer from './bloc/input.vue';
import Gbtn from './bloc/gBtn.vue';
import iconsFilled from '../../public/iconsFilled.json'
import icons from '../../public/icons.json'

const { t } = useLang()

const props = defineProps({
  modelValue: { type: Object, required: true },
  imageRef: { type: String, default: '' } // For model image explorer return
})

const emit = defineEmits(['openExplorProdImg', 'message', 'isMessage', 'openExplorer'])

const activeModelIndex = ref(-1)
const showCatalogPicker = ref(false)
const pickingFor = ref({ mIndex: -1, dIndex: -1 })

var resizeSvg = (svg, width, height) => {
    if(iconsFilled[svg]) {
        return iconsFilled[svg].replace(/width="[^"]+"/, `width="${width}"`).replace(/height="[^"]+"/, `height="${height}"`)
    } else if(icons[svg]) {
        return icons[svg].replace(/width="[^"]+"/, `width="${width}"`).replace(/height="[^"]+"/, `height="${height}"`)
    } else {
        return icons['svg'] ? icons['svg'].replace(/width="[^"]+"/, `width="${width}"`).replace(/height="[^"]+"/, `height="${height}"`) : ''
    }
}

const openExplorer = (index) => {
    activeModelIndex.value = index
    emit('openExplorer', index) // Parent should handle explorer and return image via prop
}

watch(() => props.imageRef, (newVal) => {
    if (activeModelIndex.value !== -1 && newVal) {
        if(props.modelValue.models[activeModelIndex.value]) {
            props.modelValue.models[activeModelIndex.value].imageUrls = newVal
        }
    }
})

function addModel() {
    if(!props.modelValue.models) props.modelValue.models = []
    props.modelValue.models.push({
        name: 'New Model',
        isActive: true,
        imageUrls: '',
        ref: '',
        sku: '',
        buy: 0,
        sell: 0,
        promo: 0,
        qty: 0,
        infinit_stock: false,
        isVariablePrice: false,
        activeColor: false,
        details: []
    })
}

function removeModel(index) {
    props.modelValue.models.splice(index, 1)
}

function addVariant(mIndex) {
    if(!props.modelValue.models[mIndex].details) props.modelValue.models[mIndex].details = []
    props.modelValue.models[mIndex].details.push({
        colorName: '',
        color: '#000000',
        size: '',
        qty: 0,
        buy: 0,
        sell: 0,
        catalog_image: ''
    })
}

function removeVariant(mIndex, dIndex) {
    props.modelValue.models[mIndex].details.splice(dIndex, 1)
}

function openCatalogPicker(mIndex, dIndex) {
    pickingFor.value = { mIndex, dIndex }
    showCatalogPicker.value = true
}

function selectCatalogImage(imgObj) {
    const { mIndex, dIndex } = pickingFor.value
    if (mIndex !== -1 && dIndex !== -1) {
        props.modelValue.models[mIndex].details[dIndex].catalog_image = imgObj.previewImage
    }
    showCatalogPicker.value = false
}

</script>

<style scoped>
.spacer{ height: 80px; }
.container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 90%;
    background-color: var(--color-whitly);
    border-radius: 10px;
    box-shadow: 2px 2px 2px 1px rgba(0, 0, 0, 0.1);
    margin-block: 5px;
    padding: 10px;
}
.dark .container {
    background-color: var(--color-darkly);
    box-shadow: 2px 2px 2px 1px rgba(0, 0, 0, 0.8);
}
.formRow {
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
.dark .formRow { background-color: var(--color-darkow); }

.imageUploadSection {
    width: 100%;
    max-width: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 5px;
    background: none;
    border: none;
    cursor: pointer;
}
.inputImg {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 150px;
    height: 150px;
    background-color: var(--color-whizy);
    border-radius: 5px;
    padding: 5px;
    overflow: hidden;
}
.inputImg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.dark .inputImg { background-color: var(--color-darky); }

.sub-section {
    width: 100%;
    padding: 10px;
    background-color: rgba(0,0,0,0.02);
    border-radius: 10px;
    margin-top: 5px;
}
.variant-item {
    background-color: var(--color-whity);
    padding: 10px;
    margin-block: 5px;
    border-radius: 8px;
    border: 1px solid var(--color-rangy);
}
.dark .variant-item { background-color: var(--color-darkow); }

.catalog-selector {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
}
.mini-preview {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    object-fit: cover;
}
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
}
.modal-content {
    background: var(--color-whitly);
    padding: 20px;
    border-radius: 10px;
    max-width: 80%;
    max-height: 80%;
    overflow-y: auto;
}
.dark .modal-content { background: var(--color-darkly); }
.catalog-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 10px;
    margin: 20px 0;
}
.catalog-item {
    cursor: pointer;
    border: 2px solid transparent;
}
.catalog-item:hover {
    border-color: var(--color-zioly2);
}
.catalog-item img {
    width: 100%;
    height: 80px;
    object-fit: cover;
}
</style>