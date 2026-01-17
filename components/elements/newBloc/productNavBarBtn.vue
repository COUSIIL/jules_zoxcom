<template>
    <div class="navBarBtn" :class="{ 'clicked': clicker }">
        <div v-html="resizeSvg(svg, 24, 24)" class="btnSvg" :style="{color: iconColor}"></div>
    </div>


</template>

<script setup>

import iconsFilled from '../../../public/iconsFilled.json'
import icons from '../../../public/icons.json'

const props = defineProps({
    text: {String, default: ''},
    svg: {String, default: 'back'},
    iconColor: {String, default: 'currentColor'},
    clicker: {Boolean, default: false},
})

var resizeSvg = (svg, width, height) => {

  if(iconsFilled[svg]) {
    return iconsFilled[svg]
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
  } else if(icons[svg]) {
    return icons[svg]
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
  } else {
    return icons['svg']
    .replace(/width="[^"]+"/, `width="${width}"`)
    .replace(/height="[^"]+"/, `height="${height}"`)
  }

}


</script>

<style scoped>

.navBarBtn{
  display: flex;
  justify-content: end;
  align-items: center;
  flex-direction: column;
  width: 40px;
  min-width: 40px;
  height: 70px;
  border-radius: 0 0 20px 20px;
  cursor: pointer;
  background-color: var(--color-zioly220);
  box-shadow: 2px 4px 2px 1px rgba(0, 0, 0, 0.3);
  border: 3px solid var(--color-zioly1);
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}
.dark .navBarBtn {
  background-color: var(--color-zioly220);
  box-shadow: 2px 2px 2px 2px rgba(0, 0, 0, 1);
  border: 3px solid var(--color-zioly4);
}

.clicked{
  height: 100px;
  background-color: var(--color-rangly);
  border: 3px solid var(--color-rangy);
}
.dark .clicked{
  height: 100px;
  background-color: var(--color-rangly);
  border: 3px solid var(--color-rangy);
}
.btnSvg {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 30px;
  height: 30px;
  background-color: var(--color-whitly);
  border-radius: 50%;
  margin: 5px;
  
}
.dark .btnSvg {
  background-color: var(--color-darkow);
  
}

</style>