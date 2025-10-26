<template>
    <div class="switchGrid">
      <!-- Slider animé -->

      <button
        type="button"
        :class="{ active: lom }"
        @click="clicker(1)"
      >
        <div v-html="resizeSvg(icons['click'], 24, 24)"></div>
        this day
      </button>

      <button
        type="button"
        :class="{ active: !lom }"
        @click="clicker(2)"
      >
        <div v-html="resizeSvg(icons['partition'], 24, 24)"></div>
        personalized
      </button>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import icons from '../../../public/icons.json';

const resizeSvg = (svg, width, height) => {
  return svg
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`);
};


const lom = ref(true);

const emit = defineEmits(['click:1', 'click:2'])

const clicker = (val) => {
    if(val === 1) {
        emit('click:1')
        lom.value = true
    } else {
        emit('click:2')
        lom.value = false
    }
};

</script>

<style>
.switchGrid {
  position: relative;
  width: 300px;
  height: 60px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
  overflow: hidden;
  margin: 10px;
  gap: 5px;
}



/* Boutons */
.switchGrid button {
  flex: 1;
  max-width: 120px;
  border: none;
  background: transparent;
  font-weight: bold;
  cursor: pointer;
  z-index: 1;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;

  /* Transition fluide pour l’effet de clic */
  transition:
    color 0.35s ease,
    background 0.35s ease,
    box-shadow 0.35s ease,
    transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Inactif (ressorti, comme un bouton qui dépasse) */
.switchGrid button:not(.active) {
  height: 30px;
  background: var(--color-whitly);
  border-radius: 8px;
  box-shadow: 
     2px  2px 4px 2px #aca7afc2;
  transform: translateY(-2px); /* bouton ressorti */
}
.dark .switchGrid button:not(.active) {
  background: var(--color-darkly);
  box-shadow: 
      2px 2px 4px 2px rgba(0, 0, 0, 0.761);
}

/* Actif (enfoncé comme un clic) */
.switchGrid button.active {
  height: 30px;
  background: var(--color-whitly);
  border-radius: 8px;
  transform: translateY(2px); /* bouton enfoncé */
}
.dark .switchGrid button.active {
  background: var(--color-darkly);
}


</style>