<template>
  <div class="floating-input2">
    <input
      :type="type"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur:modelValue', $event.target.value)"
      :required="required"
    />
    <label class="floated">
      <div class="iconTextInput" v-if="img" v-html="resizedImg" style="margin-inline: 2px;"></div>
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

<style scoped>

.iconTextInput svg {
  width: 16px;
  height: 16px;
  padding-inline: 0;
}

.floating-input2 {
  position: relative;
  width: calc(100% - 10px);
  margin-inline: 5px;
  margin-block: 10px;
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
