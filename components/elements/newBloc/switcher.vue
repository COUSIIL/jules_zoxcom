<template>
  <div class="switchGrid">
    <!-- Slider animé -->

    <button v-if="props.has1" type="button" :class="{ active: lom }" @click="clicker(1)">
      <div v-if="props.img1" v-html="resizeSvg(img1, 24, 24)"></div>
      <h1>
        {{ label1 }}
      </h1>
      
    </button>

    <button v-if="props.has2" type="button" :class="{ active: !lom }" @click="clicker(2)">
      <div v-if="props.img2" v-html="resizeSvg(img2, 24, 24)"></div>
      <h1>
        {{ label2 }}
      </h1>
    </button>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import icons from '../../../public/icons.json';
import iconsFilled from '../../../public/iconsFilled.json';

const resizeSvg = (svg, width, height) => {
  if (iconsFilled[svg]) {
    svg = iconsFilled[svg]
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`);
  } else {
    svg = icons[svg]
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`);
  }

};

const props = defineProps({
  position: { type: Number, default: 0 },
  label1: { type: String, default: 'label 1' },
  img1: { type: String, default: 'svg' },
  img2: { type: String, default: 'svg' },
  label2: { type: String, default: 'label 2' },
  has1: { type: Boolean, default: true },
  has2: { type: Boolean, default: true },
})


let lom = ref();

const emit = defineEmits(['click:1', 'click:2'])

watch(() => props.position, v => {
  if (v === 1) {
    lom.value = true
  } else {
    lom.value = false
  }
})

watch(() => props.has1, v => {
  if (v === true) {
    lom.value = false
  } else {
    lom.value = true
  }
})

onMounted(() => {
  if (props.position === 1 && props.has1) {
    emit('click:1')
    lom.value = true
  } else {
    emit('click:2')
    lom.value = false
  }
})

const clicker = (val) => {
  if (val === 1) {
    emit('click:1')
    //lom.value = false
  } else {
    emit('click:2')
    //lom.value = true
  }
};

</script>

<style>
.switchGrid {
  position: relative;
  width: 100%;
  height: 40px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
  overflow: hidden;
  margin: 10px;
  gap: 5px;
  padding-inline: 5px;
}



/* Boutons */
.switchGrid button {
  flex: 1;
  min-width: 120px;
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

.switchGrid button h1 {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 12px;

}

.switchGrid button svg {
  margin-inline: 5px;
}

/* Inactif (ressorti, comme un bouton qui dépasse) */
.switchGrid button:not(.active) {
  height: 30px;
  background: var(--color-whitly);
  border-radius: 8px;
  box-shadow:
    2px 2px 4px 2px #aca7afc2;
  transform: translateY(-1px);
  /* bouton ressorti */
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
  transform: translateY(1px);
  /* bouton enfoncé */
  box-shadow:
    inset 0 0 4px rgba(0, 170, 255, 0.5),
    inset 0 0 8px rgba(0, 170, 255, 0.4),
    inset 0 0 4px rgba(0, 170, 255, 0.35);
}

.dark .switchGrid button.active {
  background: var(--color-darkly);
  box-shadow:
    inset 0 0 4px rgba(0, 170, 255, 0.7),
    inset 0 0 8px rgba(0, 170, 255, 0.6),
    inset 0 0 4px rgba(0, 170, 255, 0.5),
    inset 0 0 4px rgba(0, 170, 255, 0.35);
}

.switchGrid button.active svg {
  color: rgba(0, 170, 255, 0.5);

}

.dark .switchGrid button.active svg {
  color: rgba(0, 170, 255, 0.7);

}
</style>