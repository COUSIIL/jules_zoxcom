<template>
    <nav v-if="isVisible && isMounted" class="fixed top-0 left-0 flex items-center justify-center w-full h-full bg-black bg-opacity-50 z-1000">
      <div class="p-5 text-center rounded-lg shadow-lg bg-whity dark:bg-darkly">
        <p>{{ message }}</p>
        <div class="flex justify-center mt-4 gap-2.5">
          <button class="px-4 py-2 text-white bg-green-500 border-none rounded-md cursor-pointer" @click="this.$emit('confirm')">Yes</button>
          <button class="px-4 py-2 text-white bg-red-500 border-none rounded-md cursor-pointer" @click="this.$emit('cancel')">No</button>
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