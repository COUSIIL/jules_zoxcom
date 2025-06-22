<template>
  <button class="gBtn" :style="{ backgroundColor: color }" :id="value" type="button">
    <div class="holder" v-if="text">{{ t(text) }}</div>
    <div class="svg" :style="{color: color}" v-html="resizedImg" />
  </button>
</template>

<script setup>

const { t } = useLang()

const props = defineProps({ svg: String, text: String, value: Number, color: String })

const newWidth = 24
const newHeight = 24

const resizedImg = computed(() => {
  if (!props.svg) return ''
  return props.svg
    .replace(/width="[^"]+"/, `width="${newWidth}"`)
    .replace(/height="[^"]+"/, `height="${newHeight}"`)
})


</script>

<style>

.gBtn {
  height: 32px;
  border-radius: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: visible;
  margin: 5px;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.holder {
  height: 28px;
  padding: 10px;
  margin-inline: 2px;
  background-color: var(--color-whitly);
  border-radius: 14px;
  display: flex;
  align-items: center;
  font-size: 14px;
}

.dark .holder {
  background-color: var(--color-darkly);
}

.gBtn .svg {
  height: 30px;
  width: 30px;
  border-radius: 14px;
  pointer-events: none;
  display: flex;
  align-items: center;
  justify-content: center;
}
.gBtn .svg svg {
  color: var(--color-whitly);
}


</style>
