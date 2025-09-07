<template>
  <div class="relative w-full max-w-md my-2.5 mx-1.25">
    <input
      :type="type"
      :value="inputValue"
      @input="onInput"
      @blur="onBlur"
      :required="required"
      :placeholder="holder"
      :readonly="props.lock"
      class="w-full h-10 px-3 py-3 text-sm border-2 rounded-lg outline-none appearance-none bg-gradient-to-r from-whity to-whiby border-zioly2 dark:from-darky dark:to-darkiw"
      :class="{ 'bg-gray-200 cursor-not-allowed text-gray-500': props.lock }"
    />
    <span class="absolute z-10 -translate-y-1/2 cursor-pointer top-1/2 right-2.5 text-zioly2" @click="emit('toggleLock')">
      <div v-if="props.lock" v-html="resizeSvg(icons['lock'], 18, 18)"></div>
      <div v-else v-html="resizeSvg(icons['unLock'], 18, 18)"></div>
    </span>
    <label class="absolute flex items-center justify-center h-4 px-1 text-xs transition-all duration-200 ease-in-out -translate-y-1/2 rounded-full pointer-events-none top-0 left-2.5 text-whity bg-zioly2">
      <div v-html="resizedImg" class="mx-0.5"></div>
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
  import icons from '~/public/icons.json'
  import { computed, ref, watch } from 'vue'

  const { t } = useLang()


  const props = defineProps({
    placeHolder: String,
    img: String,
    required: Boolean,
    modelValue: [String, Number], // support les deux types
    type: String,
    holder: String,
    lock: Boolean
  })


  const resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
  }

  const resizedImg = computed(() => {
    if (!props.img) return ''
    return resizeSvg(props.img, 18, 18)
  })

  const emit = defineEmits(['update:modelValue', 'blur:modelValue', 'toggleLock']) // 👈 ajouter toggleLock


  const inputValue = ref(props.modelValue || '')

  // Synchroniser inputValue si modelValue change de l’extérieur
  watch(() => props.modelValue, (newVal) => {
    inputValue.value = newVal || ''
  })

  // Émettre la mise à jour du v-model quand input change
  function onInput(event) {
    inputValue.value = event.target.value
    emit('update:modelValue', inputValue.value)
  }

  function onBlur(event) {
    inputValue.value = event.target.value
    emit('blur:modelValue', inputValue.value)
  }

</script>
