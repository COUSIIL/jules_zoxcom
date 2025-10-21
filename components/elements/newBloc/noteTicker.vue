<template>
  <div ref="container" class="noteBox" aria-hidden="false">
    <!-- track contient 2 copies du même contenu pour un défilement seamless -->
    <div
      ref="track"
      class="ticker__track"
      :class="{ 'no-loop': !isOverflowing }"
      :style="{
        '--duration': duration + 's',
        '--item-width': itemWidth + 'px',
        '--gap': gap + 'px',
        '--scroll-distance': scrollDistance + 'px'
      }"
    >
      <!-- item (mesuré) -->
      <div ref="item" class="ticker__item" dir="auto">
        <slot />
      </div>

      <!-- copie (aria-hidden pour lecteurs d'écran) -->
      <div class="ticker__item" aria-hidden="true" dir="auto">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  speed: { type: Number, default: 100 }, // px par seconde (ajuste la vitesse)
  gap: { type: Number, default: 32 }     // espace (px) entre les deux copies
})

const container = ref(null)
const item = ref(null)
const track = ref(null)

const isOverflowing = ref(false)
const itemWidth = ref(0)
const scrollDistance = ref(0)
const duration = ref(0)
const gap = props.gap

// calcule et applique les variables CSS nécessaires
const update = async () => {
  await nextTick()
  if (!container.value || !item.value || !track.value) return

  const cW = container.value.clientWidth
  const iW = item.value.offsetWidth

  itemWidth.value = iW
  isOverflowing.value = iW > cW

  // distance à parcourir = largeur item + gap
  scrollDistance.value = iW + gap

  // durée = distance / vitesse (px/s)
  duration.value = scrollDistance.value / (props.speed || 100)

  // On met aussi les variables CSS (utile pour keyframes)
  track.value.style.setProperty('--item-width', `${iW}px`)
  track.value.style.setProperty('--gap', `${gap}px`)
  track.value.style.setProperty('--duration', `${duration.value}s`)
  track.value.style.setProperty('--scroll-distance', `${scrollDistance.value}px`)
}

let roContainer = null
let roItem = null
let resizeHandler = null

onMounted(() => {
  update()

  // ResizeObserver est préférable pour capter changements de contenu / police / taille
  if (window.ResizeObserver) {
    roContainer = new ResizeObserver(update)
    roItem = new ResizeObserver(update)
    if (container.value) roContainer.observe(container.value)
    if (item.value) roItem.observe(item.value)
  } else {
    resizeHandler = () => update()
    window.addEventListener('resize', resizeHandler)
  }
})

onUnmounted(() => {
  if (roContainer) roContainer.disconnect()
  if (roItem) roItem.disconnect()
  if (resizeHandler) window.removeEventListener('resize', resizeHandler)
})
</script>

<style scoped>
.noteBox {
  position: relative;
  width: 100%;
  min-height: 20px;
  overflow: hidden;
}

/* piste flex contenant deux copies du texte */
.ticker__track {
  display: flex;
  align-items: center;
  gap: var(--gap, 32px);
  white-space: nowrap;
  will-change: transform;
  /* animation activée seulement si isOverflowing (sinon no-loop class la désactive) */
  animation: scroll var(--duration, 10s) linear infinite;
}

/* élément texte (chaque copie) */
.ticker__item {
  display: inline-block;
  white-space: nowrap;
  padding: 4px 8px;
  font-weight: 600;
  /* style d'exemple — adapte au besoin */
  background-color: #ffe5e53d;
  border-radius: 4px;
}

/* dark mode example — adapte si tu as un .dark parent */
.dark .noteBox .ticker__item {
  background-color: #180f1f3d;
}

/* Si le texte ne dépasse pas — on désactive l'animation et on cache la 2ème copie */
.ticker__track.no-loop {
  animation: none;
  transform: none;
}
.ticker__track.no-loop .ticker__item:nth-child(2) {
  display: none;
}

/* keyframes : translation de 0 -> -scroll-distance (px) */
@keyframes scroll {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(calc(var(--scroll-distance, 200px) * -1));
  }
}
</style>
