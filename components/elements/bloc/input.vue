<template>
  <div class="floating-input2">
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
    <label class="floated">
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

<style scoped>


.floating-input2 {
  position: relative;
  width: calc(100% - 10px);
  margin-inline: 5px;
  margin-block: 10px;
}

.floating-input2 input.locked {
  background-color: #f3f3f3;
  cursor: not-allowed;
  color: #666;
}

.floating-input2 .lock-icon {
  position: absolute;
  right: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--color-zioly2);
  cursor: pointer;
  z-index: 2;
}

.floating-input2 input {
  width: 100%;
  padding: 12px 12px 8px;
  font-size: 16px;
  height: 40px;
  border-radius: 8px;
  outline: none;
  background: linear-gradient(to right, var(--color-whity), var(--color-whiby));
  border: 2px solid var(--color-zioly2);
  font-size: 14px;
}

.dark .floating-input2 input {
  background: linear-gradient(to right, var(--color-darky), var(--color-darkiw));
}

/* Supprime les flèches des champs number */
.floating-input2 input[type="number"]::-webkit-inner-spin-button,
.floating-input2 input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.floating-input2 input[type="number"] {
  -moz-appearance: textfield;
}

.floating-input2 label {
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


.floating-input2 label.floated {
  top: 0;
  left: 10px;
  font-size: 12px;
  color: var(--color-whity);
  transform: translateY(-50%);
  display: flex;
  justify-content: center;
  align-items: center;
}
</style>
