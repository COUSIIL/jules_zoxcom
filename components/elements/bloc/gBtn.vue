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

