<template>
    <div v-if="showDropdown" class="fixed top-0 right-0 w-full h-full bg-black bg-opacity-15 backdrop-blur-sm z-1000" @click="showDropdown = false"></div>

    <div class="relative w-full max-w-xs min-w-[100px] my-2.5 mx-1.25" :class="props.class">
      <div class="relative w-full max-w-xs min-w-[100px] p-0.5 rounded-3xl cursor-pointer" :style="{ backgroundColor: color }">
        <div class="flex items-center justify-between w-full h-10 p-1 text-sm rounded-3xl outline-none bg-gradient-to-r from-whity to-whiby dark:from-darky dark:to-darkiw border-2 border-zioly2" @click="toggleDropdown" :class="{ 'bg-gray-200 cursor-not-allowed text-gray-500': props.disabled }">
          <img v-if="!selectedImage && props.modelImage" :src="`/${props.modelImage}`" alt="icon" class="w-5 h-5" />
          <div v-else-if="selectedImage && isSvgString(selectedImage)" v-html="selectedImage"></div>
          <img v-else-if="selectedImage" :src="`/${selectedImage}`" alt="icon" class="w-5 h-5" />
          <p v-if="!selectedLabel && props.modelValue" class="w-5">{{ modelValue || t('select') }}</p>
          <p v-else-if="selectedLabel" class="w-5">{{ selectedLabel || t('select') }}</p>
          <p class="w-5"></p>
        </div>

        <span class="absolute z-10 -translate-y-1/2 cursor-pointer top-1/2 right-2.5 text-zioly2" @click="emit('toggleLock')">
          <div v-if="props.disabled" v-html="resizeSvg(icons['lock'], 18, 18)"></div>
          <div v-else v-html="resizeSvg(icons['unLock'], 18, 18)"></div>
        </span>

        <ul v-if="showDropdown" class="absolute top-1/2 left-1/2 z-1100 w-11/12 max-w-md max-h-48 p-1 m-0 -translate-x-1/2 -translate-y-1/2 overflow-y-auto list-none bg-white rounded-3xl shadow-lg dark:bg-darky dark:shadow-2xl">
          <li
            v-for="option in props.options"
            class="flex items-center justify-between px-2 py-1 hover:bg-whizy dark:hover:bg-zioly1 rounded-3xl"
            @click="() => selectOption(option)"
          >
            <span class="flex items-center justify-center gap-2.5">
              <img v-if="option.img && !isSvgString(option.img)" :src="`/${option.img}`" alt="icon" />
              <div v-else-if="option.img" class="flex items-center justify-center w-8 h-8 rounded-full bg-whiby dark:bg-darkow" v-html="option.img"></div>
              <div
                v-if="typeof option.value === 'string' && option.value.startsWith('#')"
                class="w-8 h-8 rounded-full"
                :style="{ backgroundColor: option.value }"
              ></div>
              <div v-if="typeof option.value === 'number'">{{ option.value }}</div>
              {{ option.label }}
            </span>
          </li>
        </ul>
      </div>

      <label class="absolute top-0 left-2.5 flex items-center justify-center h-4 px-1 text-xs -translate-y-1/2 rounded-full pointer-events-none transition-all duration-200 ease-in-out text-whity bg-zioly2">
        <div class="mx-0.5" v-html="resizedImg"></div>
        {{ t(placeHolder) }}
        <div v-if="required" class="mx-0.5">
          <DotLottieVue
            class="w-4 h-4"
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
  options: {default: [], value: Array},
  placeHolder: String,
  img: String,
  required: Boolean,
  color: String,
  label: String,
  disabled: Boolean,
  class: String
})

  const resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

const emit = defineEmits(['update:modelValue', 'update:modelLabel', 'toggleLock'])

const showDropdown = ref(false)
const inputValue = ref(props.modelValue || '')

watch(() => props.modelValue, newVal => {
  inputValue.value = newVal ?? ''
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
  showDropdown.value = false
  props.options = []
}
</script>
