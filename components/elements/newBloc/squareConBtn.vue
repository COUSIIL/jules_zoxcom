<template>
    <button
        type="button"
        class="btnBoxerSquare"
        :class="{ active: lom }"
        @click="clicker()"
      >
      <div class="svgBox" v-html="resizeSvg(iconsFilled[icon], width, height)">

      </div>
    </button>

</template>

<script setup>

import iconsFilled from '../../../public/iconsFilled.json';
import icons from '../../../public/icons.json';

const resizeSvg = (svg, width, height) => {
  if(svg) {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`);
  } else if (icons[svg]) {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`);
  } else {
    svg = icons['svg']
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`);
  }
  
};

const props = defineProps({
    icon: {String, default: 'svg'},
    width: {Number, default: 24},
    height: {Number, default: 24}
})

const emit = defineEmits(['click:ok'])

const lom = ref(false);

const clicker = () => {
    
  lom.value = true
  setTimeout(() => {
    lom.value = false
    
  }, 150) // 1000 ms = 1 seconde
  emit('click:ok')
}

</script>

<style>

.btnBoxerSquare {
  height: 45px;
  width: 45px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  font-size: clamp(12px, 1.6vw, 14px);
  display: flex;
  align-items: center;
  gap: 8px;
  box-sizing: border-box;
  justify-content: center;
  cursor: pointer;
  font-weight: 700;
  border-radius: 8px;
  flex: 0 0 auto;
  margin-inline: 5px;
  transition:
    color 0.35s ease,
    background 0.35s ease,
    box-shadow 0.35s ease,
    transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.svgBox {
  width: 90%; height: 90%;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  border: 2px solid #b3a4bb2d;
  
}
.dark .svgBox {
  border: 2px solid #21162798;
  
}


/* Inactif (ressorti, comme un bouton qui dépasse) */
.btnBoxerSquare:not(.active) {
  color: var(--color-zioly1);
  background: var(--color-whity);
  border-radius: 8px;
  box-shadow: 
     2px  2px 2px 1px #aca7afc2;
  transform: translateY(-2px); /* bouton ressorti */
}
.dark .btnBoxerSquare:not(.active) {
  background: var(--color-darky);
  box-shadow: 
      2px 2px 2px 1px rgba(0, 0, 0, 0.761);
}

/* Actif (enfoncé comme un clic) */
.btnBoxerSquare .active {
  color: var(--color-zioly1);
  background: var(--color-whity);
  border-radius: 8px;
  transform: translateY(2px); /* bouton enfoncé */
}
.dark .btnBoxerSquare .active {
  background: var(--color-darky);
}

</style>