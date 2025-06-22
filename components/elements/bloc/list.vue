<template>
  <div class="dropdown1" :style="{ backgroundColor: color }">
    <div class="selected1" @click="toggleDropdown">
      {{ options.length || t('your list') }}
    </div>

    <ul v-if="showDropdown" class="dropdown-list1">
      <li v-for="(option, index) in options" :key="option.value" class="dropdown-item1">
        <span @click="selectOption(option.value)" style="display: flex; align-items: center; gap: 10px;">
          <div v-if="typeof option.value === 'string' && option.value.startsWith('#')"  style="width: 30px; height: 30px; border-radius: 50%;" :style="{backgroundColor: option.value}">

          </div>
          {{ option.label }}
        </span>
        <button class="delete-btn1" @click.stop="deleteOption(index)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" color="#ff5555" fill="none">
                <path d="M19.0005 4.99988L5.00049 18.9999M5.00049 4.99988L19.0005 18.9999" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </button>
      </li>
    </ul>
  </div>
</template>

<script setup>
const { t } = useLang()

const props = defineProps({
  modelValue: String,
  options: Array,
  placeHolder: String,
  color: String
})
const emit = defineEmits(['update:modelValue', 'update:options'])

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
  inputValue.value = val
  emit('update:modelValue', val)
  showDropdown.value = false
}

function deleteOption(index) {
  const newOptions = [...props.options]
  newOptions.splice(index, 1)
  emit('update:options', newOptions)
}
</script>

<style scoped>

.imageCircle {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
  display: block; /* ou inline-block selon ton contexte */
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
  background: linear-gradient(to right, var(--color-whity), var(--color-whiby));
  border-radius: 24px;
  padding: 4px;
  height: 30px;
  width: 100%;
}
.dark .selected1 {
  background: linear-gradient(to right, var(--color-darky), var(--color-darkiw));
}

.dropdown-list1 {
  position: absolute;
  background: var(--color-whitly);
  list-style: none;
  margin: 0;
  padding: 4px;
  border-radius: 22px;
  box-shadow: 0 0 6px rgba(0,0,0,0.2);
  width: 100%;
  z-index: 10;
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

.delete-btn1 {
  background: transparent;
  border: none;
  cursor: pointer;
  color: red;
  font-size: 14px;
}


/* Réutilise le style du label flottant et du conteneur .gBtn */
</style>
