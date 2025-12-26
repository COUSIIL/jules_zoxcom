<template>
    <button v-if="!isSimple"
        type="button"
        class="btnBoxer"
        :class="{ active: lom}"
        @click="clicker()"
      >
        <div v-html="resizeSvg(svg, width, height)" class="mySvg" :style="{ color: iconColor + ' !important'}"></div>

        <p v-if="text">
          {{text}}
        </p>
          

        
    </button>

    <button v-else
        type="button"
        class="btnBoxer2"
        @click="clicker()"
      >
        <div v-html="resizeSvg(svg, width, height)" class="mySvg" :style="{ color: iconColor + ' !important'}"></div>

        <p v-if="text">
          {{text}}
        </p>
    </button>

</template>

<script setup>

import iconsFilled from '../../../public/iconsFilled.json'
import icons from '../../../public/icons.json'

const props = defineProps({
    text: {String, default: ''},
    svg: {String, default: 'back'},
    iconColor: {String, default: 'currentColor'},
    width: { type: [String, Number], default: 20 },
    height: { type: [String, Number], default: 20 },
    isSimple: {type: Boolean, default: false}
})

var resizeSvg = (svg, width, height) => {
  if(iconsFilled[svg]) {
    return iconsFilled[svg]
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
  } else {
    return icons[svg]
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
  }

}



const emit = defineEmits(['click:ok'])

const lom = ref(false);

const clicker = () => {

  lom.value = true
  setTimeout(() => {
    lom.value = false
    emit('click:ok')
  }, 150) // 1000 ms = 1 seconde
  
}

</script>

<style>

.btnBoxer {

  min-height: 25px;
  min-width: 70px;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: clamp(12px, 1.6vw, 14px);
  display: flex;
  align-items: center;
  gap: 5px;
  box-sizing: border-box;
  justify-content: center;
  cursor: pointer;
  font-weight: 700;
  white-space: nowrap;
  border-radius: 30px;
  transition:
    color 0.35s ease,
    background 0.35s ease,
    box-shadow 0.35s ease,
    transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.btnBoxer2 {
  background-color: var(--color-whiby);
  min-height: 25px;
  min-width: 50px;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: clamp(12px, 1.6vw, 14px);
  display: flex;
  align-items: center;
  gap: 2px;
  box-sizing: border-box;
  justify-content: center;
  cursor: pointer;
  font-weight: 700;
  white-space: nowrap;
  border-radius: 30px;
  transition:
    box-shadow 0.35s ease,
    transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.dark .btnBoxer2 {
  background-color: var(--color-darkow);
}
.btnBoxer2 p {
  min-width: 70px;
  margin-right: 5px; 
}

/* Inactif (ressorti, comme un bouton qui dépasse) */
.btnBoxer:not(.active) {
  color: var(--color-zioly1);
  background-color: var(--color-whitly);
  backdrop-filter: 20px;
  box-shadow: 
     2px  2px 4px 1px #aca7afc2;
  transform: translateY(-2px); /* bouton ressorti */
  height: 25px;
  display: flex;
  justify-content: center;
  align-items: center;
}
.dark .btnBoxer:not(.active) {
  background-color: var(--color-darkly);
  box-shadow: 
      2px 2px 4px 1px rgba(0, 0, 0, 0.761);
}

/* Actif (enfoncé comme un clic) */
.btnBoxer .active {
  height: 25px;
  transform: translateY(2px); /* bouton enfoncé */
}


.btnBoxer p {
  min-width: 70px;
  margin-right: 5px; 
}

.mySvg svg {
  margin-inline: 5px; 
  min-width: 20px;
}




</style>