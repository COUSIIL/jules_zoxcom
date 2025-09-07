<template>
  <div class="relative w-24">
    <div class="flex items-center justify-between w-full mx-1.25 overflow-hidden text-xs text-left whitespace-nowrap text-ellipsis">
      id:{{ folder.id }}
      <!-- Toggle pour activer/désactiver la suppression -->
      <div
        class="flex items-center justify-center"
        :class="folder.markedForDelete ? 'selected1' : 'notSelected1'"
        @click.stop="emitToggleDelete"
        :title="t('Mark for deletion')"
      ></div>
    </div>
    <div @click="emitToggle" class="relative flex items-center justify-center w-24 h-24 overflow-hidden rounded-2xl bg-whizy dark:bg-darky text-darky dark:text-whizy">
      <span v-html="resizeSvg(icons['folder'], 28, 28)"></span>
    </div>

    <div class="flex items-center justify-between w-full h-8 px-1.5 py-0.5 mx-auto mt-1.25 text-xs rounded-lg shadow-md bg-zioly4 text-whitly backdrop-blur-sm dark:bg-whizy dark:text-darky">
      <input
        v-if="folder"
        type="text"
        :title="folder.name"
        v-model="folder.name"
        class="w-full text-xs text-center bg-transparent border-none outline-none text-inherit overflow-hidden text-ellipsis whitespace-nowrap placeholder-whitly"
        @blur="emitRename(folder.name)"
      />
    </div>
  </div>
</template>

<script setup>

import icons from '~/public/icons.json'
const { t } = useLang()

const props = defineProps({
  folder: {
    type: Object,
    required: true,
    default: () => ({ name: 'Unnamed Folder', markedForDelete: false }),
  },
})

const emit = defineEmits(['clicked', 'rename', 'toggle-delete'])

const emitToggle = () => {
  emit('clicked')
}

const emitRename = (value) => {
  emit('rename', value)
}

const emitToggleDelete = () => {
  emit('toggle-delete', {
    id: props.folder.id,
    marked: !props.folder.markedForDelete,
  })
}

var resizeSvg = (svg, width, height) => {
    return svg
      .replace(/width="[^"]+"/, `width="${width}"`)
      .replace(/height="[^"]+"/, `height="${height}"`)
}
</script>
