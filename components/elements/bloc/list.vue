<template>
  <div class="relative w-full max-w-xs min-w-[150px] p-0.5 rounded-3xl cursor-pointer" :style="{ backgroundColor: color }">
    <div class="w-full h-8 p-1 rounded-3xl bg-gradient-to-r from-whity to-whiby dark:from-darky dark:to-darkiw" @click="toggleDropdown">
      {{ options.length || t('your list') }}
    </div>

    <ul v-if="showDropdown" class="absolute z-10 w-full p-1 m-0 list-none bg-white rounded-3xl shadow-lg dark:bg-darky dark:shadow-2xl">
      <li v-for="(option, index) in options" :key="option.value" class="flex items-center justify-between px-2 py-1 hover:bg-whizy dark:hover:bg-zioly1 rounded-3xl">
        <span @click="selectOption(option.value)" class="flex items-center gap-2.5">
          <div v-if="typeof option.value === 'string' && option.value.startsWith('#')" class="w-8 h-8 rounded-full" :style="{backgroundColor: option.value}"></div>
          {{ option.label }}
        </span>
        <button class="text-sm text-red-500 bg-transparent border-none cursor-pointer" @click.stop="deleteOption(index)">
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
