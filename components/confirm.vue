<template>
    <nav v-if="isVisible && isMounted" class="overlay">
      <div class="modal">
        <p>{{ message }}</p>
        <div class="buttons">
          <button class="confirm" @click="this.$emit('confirm')">Yes</button>
          <button class="cancel" @click="this.$emit('cancel')">No</button>
        </div>
      </div>
    </nav>
</template>
  
  <script>
  export default {
    name: 'Confirm',
    data() {
      return {
        isMounted: false,
      }
    },
    props: {
        isVisible: {
            type: Boolean,
            default: false, // Valeur booléenne par défaut
        },
        message: {
            type: String,
            default: "You confirm this action ?",
        },
    },
    mounted() {
      this.isMounted = true;
    },
    methods: {
    },
  };
  </script>
  
  <style>
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  
  .modal {
    background: var(--color-whity);
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  }
  .dark .modal {
    background: var(--color-darkly);
  }
  
  .buttons {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 10px;
  }
  
  .buttons button {
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
  }
  
  .confirm {
    background-color: #4caf50;
    color: white;
  }
  
  .cancel {
    background-color: #f44336;
    color: white;
  }
  </style>
  