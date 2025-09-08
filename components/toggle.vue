<template>
  <nav v-if="isMounted" class="toggle-switch">
    <label class="toggle-switch__label">
      <input v-model="isToggle" type="checkbox" @click="$emit('toggle', isToggle)" class="toggle-switch__input">
      <span class="toggle-switch__slider"></span>
    </label>
  </nav>
</template>

<script>
export default {
  name: 'Toggle',
  data() {
    return {
      isMounted: false,
      isToggle: false,
    }
  },
  props: {
    toggle: {
      type: Boolean,
      default: false,
    },
  },
  mounted() {
    this.isToggle = this.toggle;
    this.isMounted = true;
  },
  methods: {
  },
};
</script>
  