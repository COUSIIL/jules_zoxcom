<template>
    <button @click="clicked" class="cancel-btn">
        {{ text }}
        <div class="cancel-btn__icon" v-html="svg">

        </div>
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
