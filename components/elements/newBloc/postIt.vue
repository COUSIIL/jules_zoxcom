<script setup>
import { ref, watch, onMounted } from 'vue';

// ✅ définir les props UNE SEULE FOIS
const props = defineProps({
  modelValue: { type: String, default: '' },
  color: { type: String, default: '#ffef6c' }, // couleur par défaut (jaune post-it)
  size: { type: [Number, String], default: 200 }, // taille (px)
  rotate: { type: Number, default: 0 } // rotation optionnelle
});

const emit = defineEmits(['update:modelValue']);
const contentRef = ref(null);

// 🔄 Mettre à jour l’éditeur si modelValue change depuis le parent
watch(
  () => props.modelValue,
  (val) => {
    if (contentRef.value && contentRef.value.innerText !== val) {
      contentRef.value.innerText = val ?? '';
    }
  }
);

// 🖊️ Émettre la nouvelle valeur à chaque saisie
function onInput(e) {
  const text = e.target.innerText;
  emit('update:modelValue', text);
}

// ⚙️ Initialiser le texte au montage
onMounted(() => {
  if (contentRef.value && (props.modelValue ?? '') !== contentRef.value.innerText) {
    contentRef.value.innerText = props.modelValue ?? '';
  }
});
</script>

<template>
  <div
    class="postit"
    :style="{
      width: Number(size) + 'px',
      height: Number(size) + 'px',
      transform: `rotate(${rotate}deg)`
    }"
    role="group"
    aria-label="Post-it editable"
  >
    <div class="tape" aria-hidden="true"></div>

    <div class="postit-body" :style="{ backgroundColor: color }">
      <div
        ref="contentRef"
        class="postit-content"
        contenteditable="true"
        role="textbox"
        aria-multiline="true"
        spellcheck="true"
        @blur="onInput"
      ></div>
    </div>
  </div>
</template>

<style scoped>
.postit {
  display: inline-block;
  position: relative;
  user-select: text;
  cursor: text;
  margin: 12px;
  transition: transform 0.12s ease;
}

/* Scotch transparent */
.postit .tape {
  position: absolute;
  left: 50%;
  transform: translateX(-50%) rotate(-6deg);
  top: -12px;
  width: 70px;
  height: 18px;
  background: linear-gradient(90deg, rgba(255,255,255,0.85), rgba(255,255,255,0.7));
  border-radius: 3px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.18);
  z-index: 3;
  filter: saturate(0.9);
  opacity: 0.95;
}

/* Corps du post-it */
.postit-body {
  width: 100%;
  height: 100%;
  border-radius: 8px;
  box-shadow:
    0 6px 18px rgba(0,0,0,0.18),
    0 2px 6px rgba(0,0,0,0.08);
  padding: 12px;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  overflow: hidden; /* Contient la zone scrollable */
  position: relative;
}

/* Zone éditable et scrollable */
.postit-content {
  width: 100%;
  height: 100%;
  outline: none;
  white-space: pre-wrap; /* Préserve les retours à la ligne */
  overflow: auto;        /* Scroll interne */
  padding-right: 6px;
  font-size: 14px;
  font-weight: 500;
  line-height: 1.3;
  color: #222;
  background: transparent;
  caret-color: #111;
  -webkit-overflow-scrolling: touch;
}

/* Scrollbar discrète */
.postit-content::-webkit-scrollbar {
  width: 8px;
}
.postit-content::-webkit-scrollbar-thumb {
  border-radius: 8px;
  background: rgba(0,0,0,0.12);
}
</style>
