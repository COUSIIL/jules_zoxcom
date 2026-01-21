<template>
  <div class="spacer"></div>
  <div class="container">
      <h2>{{ t('business information') }}</h2>

      <Inputer :placeHolder="t('factory / supplier name')" :required="false" v-model="modelValue.factoryName"/>
      <Inputer :placeHolder="t('production / import time')" :required="false" v-model="modelValue.productionTime"/>
      <Inputer :placeHolder="t('target audience')" :required="false" v-model="modelValue.targetAudience"/>
      <Inputer :placeHolder="t('genre')" :required="false" v-model="modelValue.genre"/>

      <div style="width: 100%; margin-top: 20px;">
          <h3>{{ t('internal information') }}</h3>
          <textarea v-model="modelValue.internalInfo" class="internal-area" :placeholder="t('internal notes...')"></textarea>
      </div>

  </div>
  <div class="spacer"></div>
</template>

<script setup>
import Inputer from './bloc/input.vue';

const { t } = useLang()

const props = defineProps({
  modelValue: { type: Object, required: true }
})
</script>

<style scoped>
.spacer{ height: 80px; }
.container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 90%;
    background-color: var(--color-whitly);
    border-radius: 10px;
    box-shadow: 2px 2px 2px 1px rgba(0, 0, 0, 0.1);
    margin-block: 5px;
    padding: 20px;
}
.dark .container {
    background-color: var(--color-darkly);
    box-shadow: 2px 2px 2px 1px rgba(0, 0, 0, 0.8);
}
.internal-area {
    width: 100%;
    height: 150px;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid var(--color-rangy);
    background-color: var(--color-whity);
    color: inherit;
    resize: vertical;
}
.dark .internal-area {
    background-color: var(--color-darkow);
    color: var(--color-whizy);
}
</style>