<template>
    <router-link @click="resetStorage" :to="link" class="flex items-center justify-center p-2.5 mx-2.5 text-lg rounded-lg cursor-pointer bg-gradient-to-r from-green-400 to-lime-400 text-darky">
        {{ text }}
        <div class="mx-1.25 text-darky" v-html="svg"></div>
    </router-link>
</template>

<script setup>
const prop = defineProps({
    link: String,
    text: String,
    svg: String,
})

const emit = defineEmits(['clicked'])


const resetStorage = () => {
  localStorage.setItem('productID', null)
  emit('clicked')
}

</script>
