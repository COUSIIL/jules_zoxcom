<template>
  <div class="floating-input">
    <input
      :type="type"
      :value="inputValue"
      @input="onInput"
      @blur="onBlur"
      :required="required"
      :placeholder="holder"
      :readonly="props.lock"
      :class="{ locked: props.lock }"
    />
    <span class="lock-icon" @click="emit('toggleLock')">
      <div v-if="props.lock" v-html="resizeSvg(icons['lock'], 18, 18)">

      </div>
      <div v-else v-html="resizeSvg(icons['unLock'], 18, 18)">

      </div>
    </span>
    <label>
      <div v-html="resizedImg" style="margin-inline: 2px;"></div>
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
