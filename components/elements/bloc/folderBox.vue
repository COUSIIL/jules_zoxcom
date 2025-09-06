<template>
  <div style="width: 100px; position: relative;">
    <div class="id">
      id:{{ folder.id }}
      <!-- Toggle pour activer/désactiver la suppression -->
      <div
        class="toggle-delete"
        :class="folder.markedForDelete ? 'selected1' : 'notSelected1'"
        @click.stop="emitToggleDelete"
        title="Marquer pour suppression"
      ></div>
    </div>
    <div @click="emitToggle" class="image-box">
      <span v-html="resizeSvg(icons['folder'], 28, 28)"></span>

      
    </div>

    <div class="floating-label">
      <input
        v-if="folder"
        type="text"
        :title="folder.name"
        v-model="folder.name"
        class="input-name"
        @blur="emitRename(folder.name)"
      />
    </div>
  </div>
</template>

<script setup>

import icons from '~/public/icons.json'

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

<style scoped>
.image-box {
  position: relative;
  width: 100px;
  height: 100px;
  border-radius: 16px;
  overflow: hidden;
  background-color: var(--color-whizy);
  color: var(--color-darky);
  display: flex;
  justify-content: center;
  align-items: center;
}
.dark .image-box {
  background-color: var(--color-darky);
  color: var(--color-whizy);
}

.toggle-delete {
  display: flex;
  justify-content: center;
  align-items: center;
}

.notSelected1 {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  cursor: pointer;
  border: 2px solid var(--color-darkly);
  border-radius: 50%;
  background-color: transparent;
}
.dark .notSelected1 {
  border: 2px solid var(--color-whitly);
}

.selected1 {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 14px;
  height: 14px;
  cursor: pointer;
  background-color: var(--color-rady);
  border-radius: 50%;
}

.floating-label {
  height: 30px;
  width: 100%;
  margin: 5px auto 0;
  background-color: var(--color-zioly4);
  color: var(--color-whitly);
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 4px;
  backdrop-filter: blur(4px);
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}
.dark .floating-label {
  background-color: var(--color-whizy);
  color: var(--color-darky);
}

.id {
  width: 100%;
  text-align: left;
  white-space: nowrap;
  overflow: hidden;    
  text-overflow: ellipsis;
  font-size: 12px;
  margin-inline: 5px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.input-name {
  width: 100%;
  border: none;
  background: transparent;
  color: inherit;
  font-size: 10px;
  outline: none;
  flex: 1;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.input-name::placeholder {
  color: var(--color-whitly);
}
</style>
