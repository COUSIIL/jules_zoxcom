<template>
    <button @click="clicked" class="flex items-center justify-center p-2.5 mx-2.5 text-base rounded-lg cursor-pointer bg-gradient-to-r from-green-400 to-lime-400 text-darky">
        {{ text }}
        <div class="mx-2.5 text-darky" v-html="svg"></div>
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
