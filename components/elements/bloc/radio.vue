<template>
    <div :class="selected ? 'flex items-center justify-center w-3 h-3 min-w-[12px] max-w-[12px] m-2.5 rounded-full cursor-pointer bg-greny' : 'flex items-center justify-center w-3.5 h-3.5 min-w-[14px] max-w-[14px] m-2.5 border-2 rounded-full cursor-pointer border-darkly dark:border-whitly'" @click="select">
    </div>
</template>

<script setup>
    const emit = defineEmits(['changed'])

    const props = defineProps({
        selected: {type: Boolean, default: false}
    })

    const select = () => {
        emit('changed')
    }
</script>