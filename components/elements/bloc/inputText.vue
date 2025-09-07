<template>
  <div class="relative w-full max-w-md my-2.5 mx-1.25">
    <input
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur:modelValue', $event.target.value)"
      :required="required"
      class="w-full h-10 px-3 py-3 text-sm border-2 rounded-lg outline-none bg-gradient-to-r from-whity to-whiby border-zioly2 dark:from-darky dark:to-darkiw"
    />
    <label class="absolute top-0 left-2.5 flex items-center justify-center h-4 px-1 text-xs -translate-y-1/2 rounded-full pointer-events-none transition-all duration-200 ease-in-out text-whity bg-zioly2">
      <div class="w-4 h-4 mx-0.5" v-if="img" v-html="resizedImg"></div>
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
import { DotLottieVue } from '@lottiefiles/dotlottie-vue'
const { t } = useLang()

const props = defineProps({
  placeHolder: String,
  img: String,
  required: Boolean,
  modelValue: String,
  type: {
    type: String,
    default: 'text'
  }
})

const newWidth = 20
const newHeight = 20

const resizedImg = computed(() => {
  if (!props.img) return ''
  return props.img
    .replace(/width="[^"]+"/, `width="${newWidth}"`)
    .replace(/height="[^"]+"/, `height="${newHeight}"`)
})

defineEmits(['update:modelValue', 'blur:modelValue'])
</script>
