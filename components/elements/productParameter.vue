<template>
  <div class="spacer"></div>
  <div class="container">
      <h2>{{ t('parameters') }}</h2>

      <div class="formRow" @click="modelValue.ratingActive = !modelValue.ratingActive">
        <span>{{ t('rating and comment active') }}</span>
        <Radio :selected="modelValue.ratingActive"/>
      </div>

      <div class="formRow" @click="modelValue.liveChatActive = !modelValue.liveChatActive">
        <span>{{ t('live chat active') }}</span>
        <Radio :selected="modelValue.liveChatActive"/>
      </div>

      <div class="formRow" @click="modelValue.countdownActive = !modelValue.countdownActive">
        <span>{{ t('countdown offer active') }}</span>
        <Radio :selected="modelValue.countdownActive"/>
      </div>

      <div class="formRow" @click="modelValue.gamblingActive = !modelValue.gamblingActive">
        <span>{{ t('gambling game active') }}</span>
        <Radio :selected="modelValue.gamblingActive"/>
      </div>

      <div class="formRow" @click="modelValue.aiHelperActive = !modelValue.aiHelperActive">
        <span>{{ t('ai helper active') }}</span>
        <Radio :selected="modelValue.aiHelperActive"/>
      </div>

      <div class="formRow" @click="modelValue.aspaActive = !modelValue.aspaActive">
        <span>{{ t('aspa (smart price adjust) active') }}</span>
        <Radio :selected="modelValue.aspaActive"/>
      </div>

      <div class="formRow" @click="modelValue.salesCanalActive = !modelValue.salesCanalActive">
        <span>{{ t('sales canal active') }}</span>
        <Radio :selected="modelValue.salesCanalActive"/>
      </div>
  </div>
  <div class="spacer"></div>
</template>

<script setup>
import Radio from './bloc/radio.vue';

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
    padding: 10px;
}
.dark .container {
    background-color: var(--color-darkly);
    box-shadow: 2px 2px 2px 1px rgba(0, 0, 0, 0.8);
}
.formRow {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 10px;
    margin-block: 5px;
    background-color: var(--color-whity);
    border-radius: 20px;
    cursor: pointer;
}
.dark .formRow { background-color: var(--color-darkow); }
</style>