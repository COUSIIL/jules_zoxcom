<template>
  <div class="gBtn" :class="{ 'slide-left': isAnimating }" :style="{ backgroundColor: color }">

      <div class="floating-input">
        <input
          :type="type"
          :value="inputValue"
          @input="onInput"
          :required="required"

          :class="type === 'color' ? '' : 'gradient-input'"
          :style="type === 'color' ? { backgroundColor: inputValue } : {}"

        />
        <label class="floated">
            <div class="iconInputBtn" v-html="resizedImg" style="margin-inline: 2px;">

            </div>
            {{ t(placeHolder) }}
            <div v-if="required" style="margin-inline: 2px;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14" color="currentColor" fill="none">
                    <path d="M10.7962 2.91338C11.4188 2.29077 12.2756 1.96039 13.1551 2.0038L18.7587 2.28039C20.3601 2.35944 21.6406 3.63993 21.7196 5.24131L21.9962 10.8449C22.0396 11.7244 21.7092 12.5811 21.0866 13.2037L13.5082 20.7822C11.8844 22.4059 9.25177 22.4059 7.62799 20.7822L3.21783 16.372C1.59406 14.7482 1.59406 12.1156 3.21783 10.4918L10.7962 2.91338Z" stroke="currentColor" stroke-width="1.5"></path>
                    <path d="M17.5002 6.5L17.4912 6.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M12.982 12.2064L10.0004 14M10.0004 14L7.01869 15.7936M10.0004 14L10.0187 17.5M10.0004 14L9.98202 10.5M10.0004 14L13 15.7063M10.0004 14L7 12.2935" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
            </div>
        </label>
      </div>
    <button v-if="svg" type="button" :style="{ color }" @click="onClicked">
      <span class="iconInputBtn2" v-html="resizedSvg" />
    </button>
  </div>
</template>

<script setup>

  const { t } = useLang()

  const props = defineProps({
    svg: String,
    value: Number,
    color: String,
    placeHolder: String,
    img: String,
    required: Boolean,
    modelValue: String,
    type: {
      String,
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
  const resizedSvg = computed(() => {
    if (!props.svg) return ''
    return props.svg
      .replace(/width="[^"]+"/, `width="${newWidth}"`)
      .replace(/height="[^"]+"/, `height="${newHeight}"`)
  })

  const isAnimating = ref(false)

  const emit = defineEmits(['update:modelValue', 'clicked'])

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

  // Émettre le clic avec la valeur actuelle
  const onClicked = () => {
    isAnimating.value = true
    emit('clicked', inputValue.value)

    // Retirer l'animation après 500ms (durée de l'animation)
    setTimeout(() => {
      isAnimating.value = false
    }, 500)
  }


</script>

