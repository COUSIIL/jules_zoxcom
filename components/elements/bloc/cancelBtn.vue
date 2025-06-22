<template>
    <button @click="clicked" class="router_link">
        {{ text }}
        <div class="iconCancelBtn" v-html="svg">

        </div>
    </button>
</template>

<script setup>
const prop = defineProps({
    text: String,
    svg: String,
})

const emit = defineEmits(['clicked'])


const clicked = () => {
  emit('clicked')
}

</script>

<style scoped>
.router_link {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-inline: 10px;
  padding: 10px;
  border-radius: 8px;
  background: linear-gradient(90deg, #ff8059, #edaf3d);
  color: var(--color-darky);
  font-size: 16px;
  cursor: pointer;
  box-shadow: 0 4px 8px #00000050;
}

.dark .router_link {
  box-shadow: 0 4px 8px #000000b0;
}

.iconCancelBtn {
  margin-inline: 5px;
  color: var(--color-darky);
}
.iconCancelBtn svg {
  width: 20px;
  height: 20px;
  padding-inline: 0;
}

</style>
