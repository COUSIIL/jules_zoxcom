<template>
    <router-link @click="resetStorage" :to="link" class="router_link">
        {{ text }}
        <div class="iconClassBtn" v-html="svg">

        </div>
    </router-link>
</template>

<script setup>
const prop = defineProps({
    link: String,
    text: String,
    svg: String,
})

const emit = defineEmits(['clicked'])


const resetStorage = () => {
  localStorage.setItem('productID', null)
  emit('clicked')
}

</script>

<style>
.router_link {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-inline: 10px;
  padding: 10px;
  border-radius: 8px;
  background: linear-gradient(90deg, #3ded86, #b2ff59);
  color: var(--color-darky);
  font-size: 2vh;
  cursor: pointer;
}

.iconClassBtn {
  margin-inline: 5px;
  color: var(--color-darky);
}

.iconClassBtn svg {
  width: 20px;
  height: 20px;
  padding-inline: 0;
}

</style>
