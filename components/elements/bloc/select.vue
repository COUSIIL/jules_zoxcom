<template>
  <div v-if="showDropdown" class="backClaque" @click="showDropdown = false">

  </div>
    <div class="floating-input3">
        <div class="dropdown1" :style="{ backgroundColor: color }">
          <div class="selected1" @click="toggleDropdown">
          {{ selectedLabel || t('select') }}
          </div>

          <ul v-if="showDropdown" class="dropdown-list1">
            <li
              v-for="(option, index) in options"
              :key="option.value"
              class="dropdown-item1"
              @click="selectOption(option)"
            >
              <span class="option-label">

                <img v-if="option.img" class="img-circle" :src="option.img" :alt="option.label">

                <div
                  v-if="typeof option.value === 'string' && option.value.startsWith('#')"
                  class="color-circle"
                  :style="{ backgroundColor: option.value }"
                ></div>
                <div v-if="typeof option.value === 'number'">
                  {{ option.value }}
                </div>
                {{ option.label }}
              </span>
            </li>
          </ul>
        </div>
        <label class="floated">
            <div class="iconSelect" v-html="resizedImg">

            </div>
            {{ t(placeHolder) }}
            <div v-if="required" style="margin-inline: 2px;">
                <DotLottieVue
                style="height: 16px; width: 16px"
                src="/animations/important.lottie"
                autoplay
                loop
                />
            </div>
        </label>
    </div>

</template>

<script setup>
const { t } = useLang()

import {DotLottieVue} from '@lottiefiles/dotlottie-vue'

const props = defineProps({
    modelValue: String,
    options: Array,
    placeHolder: String,
    img: String,
    required: Boolean,
    color: String,
    label: String,
    disabled: Boolean,
    class: String
})

const newWidth = 20
const newHeight = 20

const resizedImg = computed(() => {
  if (!props.img) return ''
  return props.img
    .replace(/width="[^"]+"/, `width="${newWidth}"`)
    .replace(/height="[^"]+"/, `height="${newHeight}"`)
})

const emit = defineEmits(['update:modelValue'])

const showDropdown = ref(false)
const inputValue = ref(props.modelValue || '')

const selectedLabel = computed(() =>
  props.options.find(opt => opt.value === inputValue.value)?.label || ''
)

watch(() => props.modelValue, newVal => {
  inputValue.value = newVal || ''
})

function toggleDropdown() {
  showDropdown.value = !showDropdown.value
}

function selectOption(val) {
  inputValue.value = val.value
  emit('update:modelValue', inputValue.value )
  emit('update:modelLabel', val.label)
  showDropdown.value = false
}

</script>

<style scoped>

.option-label {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.img-circle {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
  display: block; /* ou inline-block selon ton contexte */
  background-color: var(--color-whiby);
  display: flex;
  justify-content: center;
  align-items: center;
}

.color-circle {
  width: 30px; height: 30px; border-radius: 50%;
}


.iconSelect {
  margin-inline: 2px;
}

.backClaque {
  position: fixed;
  top: 0;
  right: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(2px);         /* flou principal */
  -webkit-backdrop-filter: blur(2px); /* compatibilité Safari */
  z-index: 1000;
}


.dropdown1 {
  position: relative;
  border-radius: 22px;
  padding: 2px;
  cursor: pointer;
  width: 100%;
  min-width: 150px;
  max-width: 250px;
}

.selected1 {
  width: 100%;
  font-size: 16px;
  height: 40px;
  border-radius: 24px;
  outline: none;
  background: linear-gradient(to right, var(--color-whity), var(--color-whiby));
  border: 2px solid var(--color-zioly2);
  font-size: 14px;
  align-content: center;
}
.dark .selected1 {
  background: linear-gradient(to right, var(--color-darky), var(--color-darkiw));
  border-radius: 24px;
}

.dropdown-list1 {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* Centrage parfait */
  background: var(--color-whitly);
  list-style: none;
  margin: 0;
  padding: 4px;
  border-radius: 22px;
  box-shadow: 0 0 6px rgba(0,0,0,0.2);
  width: 90%;          /* ou max-width pour adaptatif */
  max-width: 400px;    /* empêche que ce soit trop large */
  z-index: 1100;
  max-height: 200px;   /* limite la hauteur */
  overflow-y: auto;    /* scroll si besoin */
}

.dark .dropdown-list1 {
  background: var(--color-darky);
  box-shadow: 0 0 6px rgba(0,0,0,0.8);
}

.dropdown-item1 {
  display: flex;
  justify-content: space-between;
  padding: 4px 8px;
  align-items: center;
}

.dropdown-item1:hover {
  background: var(--color-whizy);
  border-radius: 22px;
}
.dark .dropdown-item1:hover {
  background: var(--color-zioly1);
  border-radius: 22px;
}

.floating-input3 {
  position: relative;

  margin-inline: 5px;
  margin-block: 10px;
}


.floating-input3 label {
  position: absolute;
  height: 18px;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  background: var(--color-zioly2);
  padding: 0 4px;
  color: var(--color-whitly);
  font-size: 14px;
  pointer-events: none;
  transition: all 0.2s ease;
  border-radius: 12px;
}


.floating-input3 label.floated {
  top: 0;
  left: 10px;
  font-size: 12px;
  color: var(--color-whity);
  transform: translateY(-50%);
  display: flex;
  justify-content: center;
  align-items: center;
}



/* Réutilise le style du label flottant et du conteneur .gBtn */
</style>
