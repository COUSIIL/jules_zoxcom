<template>

    <div :class="selected ? 'activated' : 'notSelected'" @click="select">
          
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

<style>
.notSelected {
    display: flex; /* Ajout pour centrer correctement */
    align-items: center; /* Centrage vertical */
    justify-content: center; /* Centrage horizontal */
    min-width: 12px;
    max-width: 12px;
    height: 12px;
    margin: 12px;
    cursor: pointer;
    border: 2px solid var(--color-darkly); /* Correction de la propriété border */
    border-radius: 50%; /* Correction pour un cercle parfait */
}
.dark .notSelected{
    border: 2px solid var(--color-whitly);
}

.activated {
    display: flex; /* Ajout pour centrer correctement */
    align-items: center; /* Centrage vertical */
    justify-content: center; /* Centrage horizontal */
    min-width: 12px;
    max-width: 12px;
    height: 12px;
    margin: 12px;
    cursor: pointer;
    background-color: var(--color-greny);
    border-radius: 50%; /* Correction pour un cercle parfait */
}
</style>