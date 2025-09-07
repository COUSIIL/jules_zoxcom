<template>
  <button
    class="m-[5px] flex h-8 cursor-pointer items-center justify-center overflow-visible rounded-full shadow-md"
    :class="`bg-${color}`"
    :id="value"
    type="button"
  >
    <div
      v-if="text"
      class="mx-0.5 flex h-7 items-center rounded-full bg-whitly p-2.5 text-sm dark:bg-darkly"
    >
      {{ t(text) }}
    </div>
    <div
      class="pointer-events-none flex h-[30px] w-[30px] items-center justify-center rounded-full text-whitly"
      v-html="resizedImg"
    />
  </button>
</template>

<script setup>
const { t } = useLang()

const props = defineProps({
  svg: String,
  text: String,
  value: Number,
  color: String,
})

const newWidth = 24
const newHeight = 24

const resizedImg = computed(() => {
  if (!props.svg) return ''
  return props.svg
    .replace(/width="[^"]+"/, `width="${newWidth}"`)
    .replace(/height="[^"]+"/, `height="${newHeight}"`)
})
</script>
