<template>
    <div v-if="showDropdown" class="backClaque" @click="showDropdown = false, emit('close')"></div>

    <div class="floating-input3" :class="props.class">
      <div class="dropdown1" :style="{ backgroundColor: color }">
        <div v-if="!disabled" class="selected1" @click="toggleDropdown" :class="{ disabled: props.disabled }">
          <img v-if="!selectedImage && props.modelImage" :src="`/${props.modelImage}`" alt="icon" />
          <div
            v-else-if="selectedImage && isSvgString(selectedImage)" 
            v-html="selectedImage">
          </div> 
          <img v-else-if="selectedImage" :src="`/${selectedImage}`" alt="icon" />
          <p v-if="!selectedLabel && props.modelValue">{{ modelValue || t('select') }}</p>
          <p v-else-if="selectedLabel">{{ selectedLabel || t('select') }}</p>
          <p style="width: 20px;">

          </p>
        </div>

        <span v-if="!disabled" class="lock-icon" @click="emit('toggleLock')">
          <div v-if="props.disabled" v-html="resizeSvg(icons['lock'], 18, 18)">

          </div>
          <div v-else v-html="resizeSvg(icons['unLock'], 18, 18)">

          </div>
        </span>

        <ul v-if="showDropdown" class="dropdown-list1">
          <li
            v-for="option in inputOptions"
            class="dropdown-item1"
            @click="() => selectOption(option)"
          >
            <span class="option-label">

              <img v-if="option.img && !isSvgString(option.img)" class="img-circle" :src="`/${option.img}`" alt="icon" />
              
              <div v-else-if="option.img" class="img-circle" v-html="option.img">

              </div>

              <div
                v-if="typeof option.value === 'string' && option.value.startsWith('#')"
                class="color-circle"
                :style="{ backgroundColor: option.value }"
              ></div>

              <div v-if="typeof option.value === 'number'">
                {{ option.value }}
              </div>

              {{ option.label }}
            </span>
          </li>
        </ul>
      </div>

      <label v-if="!disabled" class="floated">
        <div class="iconSelect" v-html="resizedImg"></div>
        {{ t(placeHolder) }}
        <div v-if="required" style="margin-inline: 2px;">
          <DotLottieVue
            style="height: 16px; width: 16px"
            src="/animations/important.lottie"
            autoplay
            loop
          />
        </div>
      </label>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { DotLottieVue } from '@lottiefiles/dotlottie-vue'
import icons from '~/public/icons.json'

const { t } = useLang()

const props = defineProps({
  modelValue: [String, Number],
  modelImage: String,
  options: {default: [], type: Array},
  placeHolder: String,
  img: String,
  required: Boolean,
  color: String,
  label: String,
  disabled: {default: false, type: Boolean},
  class: String,
  showIt: {default: false, type: Boolean},
})

  const resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

const emit = defineEmits(['update:modelValue', 'update:modelLabel', 'update:modelImage', 'toggleLock', 'close'])

const showDropdown = ref(false)
const inputValue = ref(props.modelValue || '')
const inputOptions = ref(props.options || [])

watch(() => props.modelValue, newVal => {
  inputValue.value = newVal ?? ''
})

watch(() => props.showIt, newVal => {
  showDropdown.value = newVal
})

watch(() => props.options, newVal => {
  inputOptions.value = newVal
})

function isSvgString(content) {
  return typeof content === 'string' && content.trim().startsWith('<svg');
}


const selectedLabel = computed(() =>
  props.options.find(opt => opt.value === inputValue.value)?.label || ''
)

const selectedImage = computed(() =>
  props.options.find(opt => opt.value === inputValue.value)?.img || ''
)

const newWidth = 20
const newHeight = 20

const resizedImg = computed(() => {
  if (!props.img) return ''
  return props.img
    .replace(/width="[^"]+"/, `width="${newWidth}"`)
    .replace(/height="[^"]+"/, `height="${newHeight}"`)
})

function toggleDropdown() {
  if (!props.disabled) showDropdown.value = !showDropdown.value
}

function selectOption(option) {

  inputValue.value = option['value']

  
  emit('update:modelValue', inputValue.value)
  emit('update:modelLabel', option['label'])
  emit('update:modelImage', option['img'])
  emit('close')
  showDropdown.value = false
  //props.showIt = false
  //props.options = []
}
</script>

<style scoped>

.option-label {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.img-circle {
  width: 30px;
  min-width: 30px;
  max-width: 30px;
  height: 30px;
  min-height: 30px;
  max-height: 30px;
  border-radius: 50%;
  object-fit: cover;
  display: block; /* ou inline-block selon ton contexte */
  background-color: var(--color-whiby);
  display: flex;
  justify-content: center;
  align-items: center;
}
.dark .img-circle {
  background-color: var(--color-darkow);
}

.color-circle {
  width: 30px; height: 30px; border-radius: 50%;
}


.iconSelect {
  margin-inline: 2px;
}

.backClaque {
  position: fixed;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(2px);         /* flou principal */
  -webkit-backdrop-filter: blur(2px); /* compatibilité Safari */
  z-index: 1000;
}


.dropdown1 {
  position: relative;
  border-radius: 22px;
  cursor: pointer;
  width: 100%;
  min-width: 100px;
  max-width: 250px;
}

.selected1 {
  width: 100%;
  font-size: 16px;
  height: 40px;
  border-radius: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  outline: none;
  background: linear-gradient(to right, var(--color-whity), var(--color-whiby));
  border: 2px solid var(--color-zioly2);
  font-size: 14px;
  align-content: center;
  padding-inline: 5px;
}
.selected1.disabled {
  background-color: #f3f3f3;
  cursor: not-allowed;
  color: #666;
}
.dark .selected1 {
  background: linear-gradient(to right, var(--color-darky), var(--color-darkiw));
  border-radius: 24px;
}

.selected1 img {
  width: 20px;
  height: 20px;
}

.dropdown-list1 {
  position:fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* Centrage parfait */
  background: var(--color-whitly);
  list-style: none;
  margin: 0;
  padding: 4px;
  border-radius: 22px;
  box-shadow: 0 0 6px rgba(0,0,0,0.2);
  width: 90%;          /* ou max-width pour adaptatif */
  max-width: 400px;    /* empêche que ce soit trop large */
  z-index: 1100;
  max-height: 200px;   /* limite la hauteur */
  overflow-y: auto;    /* scroll si besoin */
}

.dark .dropdown-list1 {
  background: var(--color-darky);
  box-shadow: 0 0 6px rgba(0,0,0,0.8);
}

.dropdown-item1 {
  display: flex;
  justify-content: space-between;
  padding: 4px 8px;
  align-items: center;
}

.dropdown-item1:hover {
  background: var(--color-whizy);
  border-radius: 22px;
}
.dark .dropdown-item1:hover {
  background: var(--color-zioly1);
  border-radius: 22px;
}

.floating-input3 {
  position: relative;
  width: 100%;
  margin-inline: 5px;
  margin-block: 10px;
  min-width: 100px;
  max-width: 250px;
}


.floating-input3 label {
  position: absolute;
  height: 18px;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  background: var(--color-zioly2);
  padding: 0 4px;
  color: var(--color-whitly);
  font-size: 14px;
  pointer-events: none;
  transition: all 0.2s ease;
  border-radius: 12px;
}


.floating-input3 label.floated {
  top: 0;
  left: 10px;
  font-size: 12px;
  color: var(--color-whity);
  transform: translateY(-50%);
  display: flex;
  justify-content: center;
  align-items: center;
}

.floating-input3 input.locked {
  background-color: #f3f3f3;
  cursor: not-allowed;
  color: #666;
}

.floating-input3 .lock-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  
  color: var(--color-zioly2);
  cursor: pointer;
  z-index: 2;
}



/* Réutilise le style du label flottant et du conteneur .gBtn */
</style>
