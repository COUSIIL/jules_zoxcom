<template>

    <nav v-if="isMounted" class="checkbox-wrapper-59">
        <label class="switch">
            <input v-model="isToggle" type="checkbox" @click="$emit('toggle', isToggle)">
            <span class="slider"></span>
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

  
  <style>
    .checkbox-wrapper-59 input[type="checkbox"] {
      visibility: hidden;
      display: none;
    }
  
    .checkbox-wrapper-59 *,
    .checkbox-wrapper-59 ::after,
    .checkbox-wrapper-59 ::before {
      box-sizing: border-box;
    }
  
    .checkbox-wrapper-59 .switch {
      width: 30px; /* Réduit */
      height: 15px; /* Réduit */
      position: relative;
      display: inline-block;
    }

    .checkbox-wrapper-59 .slider {
      position: absolute;
      top: 0;
      bottom: 0;
      left: 0;
      right: 0;
      border-radius: 30px;
      box-shadow: 0 0 0 1px #777, 0 0 2px #777;
      cursor: pointer;
      border: 2px solid transparent;
      overflow: hidden;
      transition: 0.2s;
    }

    .checkbox-wrapper-59 .slider:before {
      position: absolute;
      content: "";
      width: 100%;
      height: 100%;
      background-color: #777;
      border-radius: 30px;
      transform: translateX(-28px);
      transition: 0.2s;
    }

    .checkbox-wrapper-59 input:checked + .slider:before {
      transform: translateX(2px);
      background-color: var(--color-rangy);
    }

    .checkbox-wrapper-59 input:checked + .slider {
      box-shadow: 0 0 0 1px var(--color-rangy), 0 0 4px var(--color-rangy);
    }
  </style>
  
  