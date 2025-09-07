<template>
    <button @click="clicked" class="flex items-center justify-center p-2.5 mx-2.5 text-base rounded-lg cursor-pointer bg-gradient-to-r from-red-400 to-orange-400 text-darky shadow-lg dark:shadow-2xl">
        {{ text }}
        <div class="mx-1.25 text-darky" v-html="svg"></div>
    </button>
</template>

<script setup>
const prop = defineProps({
    text: String,
    svg: String,
})

const emit = defineEmits(['clicked'])


const clicked = () => {
  emit('clicked')
}

</script>
