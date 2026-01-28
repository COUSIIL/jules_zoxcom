<template>
  <div class="spacer"></div>

  <!-- Global Configuration (Colors & Sizes) -->
  <div class="container global-config">
      <div class="header-row">
          <h3>{{ t('global configuration') }}</h3>
      </div>
      <div class="formRow">
        <InputBtn :placeHolder="t('color')" type="color" color="var(--color-zioly2)" :img="resizeSvg('colorSvg', 16, 16)" :required="false" v-model="color"/>
        <InputBtn :placeHolder="t('color name')" type="text" :img="icons['colorName'] || icons['tag']" :required="false" v-model="colorName" :svg="resizeSvg('add', 20, 20)" color="var(--color-zioly2)" @clicked="addColor(color, colorName)"/>
        <Lister :options="modelValue.colors || []" color="var(--color-zioly2)" @update:options="removeColor"/>
      </div>

      <div class="formRow">
        <InputBtn :placeHolder="t('size')" type="text" :img="icons['size'] || icons['tag']" :required="false" v-model="size" :svg="resizeSvg('add', 20, 20)" color="var(--color-zioly2)" @clicked="addSize(size)"/>
        <Lister :options="modelValue.sizes || []" color="var(--color-zioly2)" @update:options="removeSize"/>
      </div>
  </div>

  <!-- Models Loop -->
  <div class="container model-card" v-for="(model, mIndex) in modelValue.models" :key="mIndex">
    <!-- Model Header -->
    <div class="model-header" :class="{ 'active': model.isActive }">
      <div class="model-title" @click="model.isActive = !model.isActive">
         <div class="status-indicator" :class="{ 'active': model.isActive }"></div>
         <h2>{{ t('model') }} {{ mIndex + 1 }}: {{ model.name }}</h2>
      </div>
      <div class="model-actions">
          <button :title="t('duplicate model')" @click.stop="duplicateModel(mIndex)" class="action-btn">
             <div v-html="resizeSvg('copy', 20, 20)"></div>
          </button>
          <button :title="t('remove model')" @click.stop="removeModel(mIndex)" class="action-btn delete">
             <div v-html="resizeSvg('trashX', 20, 20)"></div>
          </button>
          <Radio :selected="model.isActive" @click="model.isActive = !model.isActive"/>
      </div>
    </div>

    <div v-if="model.isActive" class="model-body">
       <!-- Basic Info & Image -->
       <div class="model-basic-info">
          <button type="button" class="imageUploadSection" @click="openExplorer(mIndex)">
             <label class="inputImg">
                <span v-if="!model.imageUrls">{{ t('model image 1:1') }}</span>
                <img v-else :src="model.imageUrls" alt="Preview" />
             </label>
          </button>

          <div class="info-inputs">
             <Inputer :placeHolder="t('model name')" :img="resizeSvg('package', 16, 16)" :required="true" v-model="model.name"/>
             <div class="row-inputs">
                 <Inputer :placeHolder="t('reference')" :img="resizeSvg('reference', 16, 16)" :required="false" v-model="model.ref"/>
                 <Inputer :placeHolder="t('sku')" :img="resizeSvg('barcode1', 16, 16)" :required="false" v-model="model.sku"/>
             </div>
             <Inputer :placeHolder="t('brand')" :img="resizeSvg('tag', 16, 16)" :required="false" v-model="model.brand"/>
          </div>
       </div>

       <div class="divider"></div>

       <!-- Price Configuration -->
       <div class="config-section">
           <div class="section-header" @click="{ model.isVariablePrice = !model.isVariablePrice; if(model.isVariablePrice && !model.activeColor && !model.activeSize) model.activeColor = true; }">
              <h3>{{ t('variable price per variant') }}</h3>
              <Radio :selected="model.isVariablePrice"/>
           </div>

           <div v-if="!model.isVariablePrice" class="simple-price-row">
              <Inputer type="number" :placeHolder="t('buying price')" :img="resizeSvg('invoice', 16, 16)" v-model="model.buy"/>
              <Inputer type="number" :placeHolder="t('selling price')" :img="resizeSvg('moneyTag', 16, 16)" :required="true" v-model="model.sell"/>
              <Inputer type="number" :placeHolder="t('promo price')" :img="resizeSvg('promotion', 16, 16)" v-model="model.promo"/>
              <div class="qty-group">
                 <Inputer v-if="!model.infinit_stock" type="number" :placeHolder="t('quantity')" :img="resizeSvg('box', 16, 16)" v-model="model.qty"/>
                 <div class="infinite-toggle" @click="model.infinit_stock = !model.infinit_stock">
                    <span>{{ t('infinite') }}</span>
                    <Radio :selected="model.infinit_stock"/>
                 </div>
              </div>
           </div>
       </div>

       <div class="divider"></div>

       <!-- Variants Configuration -->
       <div class="config-section">
          <h3>{{ t('variants configuration') }}</h3>
          <div class="variant-toggles">
               <div class="toggle-item" @click="{ model.activeColor = !model.activeColor; if(!model.activeColor && !model.activeSize) model.isVariablePrice = false; }">
                  <Radio :selected="model.activeColor"/>
                  <span>{{ t('Activate color') }}</span>
               </div>
               <div class="toggle-item" @click="{ model.activeSize = !model.activeSize; if(!model.activeColor && !model.activeSize) model.isVariablePrice = false; }">
                   <Radio :selected="model.activeSize"/>
                   <span>{{ t('Activate Size') }}</span>
               </div>
          </div>
       </div>

       <div v-if="model.activeColor || model.activeSize" class="variants-container">

          <div class="variants-actions">
              <Gbtn :text="t('add variant')" @click="addVariant(mIndex)" color="var(--color-zioly2)" :svg="icons['add']"/>
          </div>

          <!-- Variants Table -->
          <div class="table-wrapper">
              <table class="variants-table">
                  <thead>
                      <tr>
                          <th width="40"></th> <!-- Remove -->
                          <th width="60">{{ t('image') }}</th>
                          <th v-if="model.activeColor">{{ t('color') }}</th>
                          <th v-if="model.activeSize">{{ t('size') }}</th>
                          <th v-if="model.isVariablePrice">{{ t('pricing') }} ({{t('buy')}}/{{t('sell')}})</th>
                          <th v-if="model.isVariablePrice">{{ t('infos') }}</th>
                          <th>{{ t('qty') }}</th>
                          <th width="40"></th> <!-- Copy -->
                      </tr>
                  </thead>
                  <tbody>
                      <tr v-for="(detail, dIndex) in model.details" :key="dIndex">
                          <td>
                              <button class="icon-btn delete" @click="removeVariant(mIndex, dIndex)">
                                 <div v-html="resizeSvg('trashX', 18, 18)"></div>
                              </button>
                          </td>
                          <td>
                              <div class="mini-catalog-picker" @click="openCatalogPicker(mIndex, dIndex)">
                                  <img v-if="detail.catalog_image" :src="detail.catalog_image" />
                                  <div v-else class="placeholder" v-html="resizeSvg('image', 16, 16)"></div>
                              </div>
                          </td>
                          <td v-if="model.activeColor">
                             <div class="color-cell">
                                 <Selector
                                    :options="modelValue.colors || []"
                                    :placeHolder="t('color')"
                                    color="var(--color-zioly2)"
                                    :modelValue="detail.color"
                                    @update:modelValue="(val) => detail.color = val"
                                    @update:modelLabel="(label) => detail.colorName = label"
                                    style="width: 100%; min-width: 120px;"
                                 />
                                 <div class="color-dot" :style="{ backgroundColor: detail.color }"></div>
                             </div>
                          </td>
                          <td v-if="model.activeSize">
                             <Selector
                                :options="modelValue.sizes || []"
                                :placeHolder="t('size')"
                                color="var(--color-zioly2)"
                                :modelValue="detail.size"
                                @update:modelValue="(val) => detail.size = val"
                                style="width: 100%; min-width: 80px;"
                             />
                          </td>
                          <td v-if="model.isVariablePrice">
                              <div class="price-inputs">
                                  <input type="number" v-model="detail.buy" :placeholder="t('buy')" class="mini-input"/>
                                  <input type="number" v-model="detail.sell" :placeholder="t('sell')" class="mini-input"/>
                              </div>
                          </td>
                           <td v-if="model.isVariablePrice">
                              <div class="price-inputs">
                                  <input type="text" v-model="detail.ref" :placeholder="t('ref')" class="mini-input"/>
                                  <input type="text" v-model="detail.sku" :placeholder="t('sku')" class="mini-input"/>
                              </div>
                          </td>
                          <td>
                              <input type="number" v-model="detail.qty" class="mini-input qty-input"/>
                          </td>
                          <td>
                              <button class="icon-btn" @click="duplicateVariant(mIndex, dIndex)" :title="t('duplicate')">
                                 <div v-html="resizeSvg('copy', 18, 18)"></div>
                              </button>
                          </td>
                      </tr>
                  </tbody>
              </table>
          </div>
       </div>

    </div>
  </div>

  <div class="container add-model-container">
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
import InputBtn from './bloc/inputBtn.vue';
import Lister from './bloc/list.vue';
import Selector from './bloc/select.vue';
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

// Global Config State
const color = ref('')
const colorName = ref('')
const size = ref('')

const colorNames = {
  "#000000": "Black", "#FFFFFF": "White", "#FF0000": "Red", "#DC143C": "Crimson", "#B22222": "FireBrick", "#8B0000": "DarkRed",
  "#FFA07A": "LightSalmon", "#FA8072": "Salmon", "#E9967A": "DarkSalmon", "#F08080": "LightCoral", "#CD5C5C": "IndianRed", "#800000": "Maroon",
  "#FFFF00": "Yellow", "#FFD700": "Gold", "#FFA500": "Orange", "#FF8C00": "DarkOrange", "#FF6347": "Tomato", "#FF4500": "OrangeRed",
  "#00FF00": "Lime", "#7CFC00": "LawnGreen", "#7FFF00": "Chartreuse", "#ADFF2F": "GreenYellow", "#32CD32": "LimeGreen", "#98FB98": "PaleGreen",
  "#00FA9A": "MediumSpringGreen", "#00FF7F": "SpringGreen", "#3CB371": "MediumSeaGreen", "#2E8B57": "SeaGreen", "#008000": "Green", "#006400": "DarkGreen",
  "#0000FF": "Blue", "#1E90FF": "DodgerBlue", "#00BFFF": "DeepSkyBlue", "#87CEFA": "LightSkyBlue", "#ADD8E6": "LightBlue", "#4682B4": "SteelBlue",
  "#4169E1": "RoyalBlue", "#00008B": "DarkBlue", "#000080": "Navy", "#191970": "MidnightBlue",
  "#00FFFF": "Cyan", "#E0FFFF": "LightCyan", "#AFEEEE": "PaleTurquoise", "#40E0D0": "Turquoise", "#48D1CC": "MediumTurquoise", "#20B2AA": "LightSeaGreen",
  "#008B8B": "DarkCyan", "#008080": "Teal",
  "#FF00FF": "Magenta", "#DA70D6": "Orchid", "#BA55D3": "MediumOrchid", "#9370DB": "MediumPurple", "#8A2BE2": "BlueViolet", "#9400D3": "DarkViolet",
  "#9932CC": "DarkOrchid", "#800080": "Purple", "#4B0082": "Indigo", "#6A5ACD": "SlateBlue",
  "#C0C0C0": "Silver", "#D3D3D3": "LightGray", "#A9A9A9": "DarkGray", "#808080": "Gray", "#696969": "DimGray", "#2F4F4F": "DarkSlateGray", "#708090": "SlateGray", "#778899": "LightSlateGray"
}

// Helpers
var resizeSvg = (svg, width, height) => {
    let svgContent = '';
    if(iconsFilled[svg]) {
        svgContent = iconsFilled[svg];
    } else if(icons[svg]) {
        svgContent = icons[svg];
    } else if (svg === 'colorSvg') {
       return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="${width}" height="${height}" color="currentColor" fill="none"><path d="M13.435 7L7.15915 13.2759M7.15915 13.2759L4.82728 15.6077C3.92569 16.5093 3.47489 16.9601 3.23745 17.5334C3 18.1066 3 18.7441 3 20.0192V21H3.98082C5.25586 21 5.89338 21 6.46663 20.7626C7.03988 20.5251 7.49068 20.0743 8.39227 19.1727L14.2891 13.2759M7.15915 13.2759H14.2891M14.2891 13.2759L17 10.565" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19.2087 8.38869L20.82 10M19.2087 8.38869L20.0705 7.52682C20.363 7.23431 20.5093 7.08805 20.611 6.94529C21.1297 6.21676 21.1297 5.23953 20.611 4.511C20.5093 4.36824 20.363 4.22198 20.0705 3.92947C19.778 3.63697 19.6318 3.4907 19.489 3.38905C18.7605 2.87032 17.7832 2.87032 17.0547 3.38905C16.912 3.4907 16.7657 3.63695 16.4732 3.92947L15.6113 4.79133M19.2087 8.38869L15.6113 4.79133M14 3.18002L15.6113 4.79133" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>`;
    } else {
        svgContent = icons['svg'] || '';
    }
    return svgContent.replace(/width="[^"]+"/, `width="${width}"`).replace(/height="[^"]+"/, `height="${height}"`);
}

function hexToRgb(hex) {
  const bigint = parseInt(hex.slice(1), 16)
  return { r: (bigint >> 16) & 255, g: (bigint >> 8) & 255, b: bigint & 255 }
}

function colorDistance(c1, c2) {
  return Math.sqrt(Math.pow(c1.r - c2.r, 2) + Math.pow(c1.g - c2.g, 2) + Math.pow(c1.b - c2.b, 2))
}

function getClosestColorName(hex) {
  if(!hex) return 'Unknown'
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

watch(color, (newColor) => {
  colorName.value = getClosestColorName(newColor)
})

const addColor = (c, n) => {
  if(!props.modelValue.colors) props.modelValue.colors = []
  const exists = props.modelValue.colors.some(col => col.value === c)
  if (!exists && n && c) {
    props.modelValue.colors.push({ value: c, label: n })
  } else {
    emit('isMessage', true)
    emit('message', t('you cannot have the same color twice'))
  }
}

const addSize = (n) => {
  if(!props.modelValue.sizes) props.modelValue.sizes = []
  const exists = props.modelValue.sizes.some(s => s.value === n)
  if (!exists && n) {
    props.modelValue.sizes.push({ value: n, label: n })
  } else {
    emit('isMessage', true)
    emit('message', t('you cannot have the same size twice'))
  }
}

const removeColor = (newValues) => {
  props.modelValue.colors = newValues.map(c => ({ value: c.value, label: c.label }))
}
const removeSize = (newValues) => {
  props.modelValue.sizes = newValues.map(c => ({ value: c.value, label: c.label }))
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
        brand: '',
        buy: 0,
        sell: 0,
        promo: 0,
        qty: 0,
        infinit_stock: false,
        isVariablePrice: false,
        activeColor: false,
        activeSize: false,
        details: []
    })
}

function removeModel(index) {
    if(confirm(t('Are you sure you want to remove this model?'))) {
        props.modelValue.models.splice(index, 1)
    }
}

function duplicateModel(index) {
    if(!props.modelValue.models) return;
    const modelToCopy = props.modelValue.models[index];
    const newModel = JSON.parse(JSON.stringify(modelToCopy));
    newModel.name = newModel.name + ' (Copy)';
    props.modelValue.models.push(newModel);
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
        promo: 0,
        ref: '',
        sku: '',
        catalog_image: '',
        catalog_index: 0
    })
}

function duplicateVariant(mIndex, dIndex) {
    if(!props.modelValue.models[mIndex].details) return;
    const variantToCopy = props.modelValue.models[mIndex].details[dIndex];
    const newVariant = JSON.parse(JSON.stringify(variantToCopy));
    props.modelValue.models[mIndex].details.push(newVariant);
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
    flex-wrap: wrap;
    gap: 10px;
}
.dark .formRow { background-color: var(--color-darkow); }

.imageUploadSection {
    width: 150px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 5px;
    border: none;
    background: none;
    cursor: pointer;
}
.inputImg {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 120px;
    height: 120px;
    background-color: var(--color-whizy);
    border-radius: 10px;
    padding: 5px;
    overflow: hidden;
    border: 2px dashed var(--color-rangy);
}
.inputImg img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.dark .inputImg { background-color: var(--color-darky); }

/* Model Card Styles */
.model-card {
    padding: 0;
    overflow: hidden;
    transition: all 0.3s ease;
}
.model-header {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: var(--color-whity);
    cursor: pointer;
    border-bottom: 1px solid transparent;
}
.dark .model-header { background: var(--color-darkow); }
.model-header.active {
    border-bottom: 1px solid var(--color-rangy);
}

.model-title {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
}
.status-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: var(--color-rady);
}
.status-indicator.active {
    background-color: var(--color-zioly2);
}
.model-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}
.action-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    color: var(--color-zioly2);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    border-radius: 5px;
    transition: background 0.2s;
}
.action-btn:hover {
    background: rgba(0,0,0,0.05);
}
.action-btn.delete { color: var(--color-rady); }

.model-body {
    width: 100%;
    padding: 15px;
    animation: fadeIn 0.3s ease;
}

.model-basic-info {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    align-items: flex-start;
}
.info-inputs {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 300px;
}
.row-inputs {
    display: flex;
    gap: 10px;
}

.divider {
    width: 100%;
    height: 1px;
    background: var(--color-rangy);
    margin: 20px 0;
    opacity: 0.3;
}

.config-section {
    width: 100%;
    margin-bottom: 15px;
}
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    background: var(--color-whity);
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 10px;
}
.dark .section-header { background: var(--color-darkow); }

.simple-price-row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
    padding: 10px;
    justify-content: center;
}
.qty-group {
    display: flex;
    align-items: center;
    gap: 15px;
}
.infinite-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.variant-toggles {
    display: flex;
    gap: 20px;
    margin-top: 10px;
}
.toggle-item {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 5px;
    background: var(--color-whity);
    border: 1px solid var(--color-rangy);
}
.dark .toggle-item { background: var(--color-darkow); }

/* Variants Table Styling */
.variants-container {
    margin-top: 20px;
}
.variants-actions {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}
.table-wrapper {
    overflow-x: auto;
    width: 100%;
    border-radius: 8px;
    border: 1px solid var(--color-rangy);
}
.variants-table {
    width: 100%;
    border-collapse: collapse;
    background: var(--color-whitly);
    min-width: 800px; /* Ensure it scrolls on small screens */
}
.dark .variants-table { background: var(--color-darkly); }

.variants-table th {
    background: var(--color-whity);
    padding: 10px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid var(--color-rangy);
}
.dark .variants-table th { background: var(--color-darkow); }

.variants-table td {
    padding: 8px;
    border-bottom: 1px solid var(--color-rangy);
    vertical-align: middle;
}
.variants-table tr:last-child td { border-bottom: none; }

.icon-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    color: var(--color-zioly2);
    opacity: 0.7;
    transition: all 0.2s;
}
.icon-btn:hover { opacity: 1; background: rgba(0,0,0,0.05); }
.icon-btn.delete { color: var(--color-rady); }

.mini-catalog-picker {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    overflow: hidden;
    background: var(--color-whizy);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--color-rangy);
}
.dark .mini-catalog-picker { background: var(--color-darky); }
.mini-catalog-picker img { width: 100%; height: 100%; object-fit: cover; }
.mini-catalog-picker .placeholder { color: var(--color-zioly2); opacity: 0.5; }

.color-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}
.color-dot {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 1px solid rgba(0,0,0,0.1);
    flex-shrink: 0;
}

.price-inputs {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.mini-input {
    width: 100%;
    padding: 5px;
    border-radius: 4px;
    border: 1px solid var(--color-rangy);
    background: var(--color-whity);
    color: inherit;
    font-size: 0.9em;
}
.dark .mini-input { background: var(--color-darkow); border-color: rgba(255,255,255,0.1); }
.qty-input {
    width: 80px;
    text-align: center;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Modal */
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