<template>
  <nav v-if="isVisible && isMounted" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 animate-fadeIn">
    <div class="flex flex-col items-center justify-center min-w-[260px] max-w-[300px] p-7 text-center transition-all duration-300 ease-in-out rounded-2xl shadow-2xl animate-scaleIn bg-whity text-darky dark:bg-darkly dark:text-whity">
      <DotLottieVue
        class="w-12 h-12"
        src="/animations/important.lottie"
        autoplay
        loop
      />
      <p class="my-5 text-base">{{ message }}</p>
      <div class="flex justify-center gap-3">
        <button class="px-4 py-2 text-sm font-medium text-white bg-green-500 border-none rounded-lg cursor-pointer transition-transform ease-in-out duration-200 hover:bg-green-600" @click="$emit('confirm')">Yes</button>
        <button class="px-4 py-2 text-sm font-medium text-white bg-red-500 border-none rounded-lg cursor-pointer transition-transform ease-in-out duration-200 hover:bg-red-600" @click="$emit('cancel')">No</button>
      </div>
    </div>
  </nav>
</template>

<script>
import { DotLottieVue } from '@lottiefiles/dotlottie-vue'

export default {
  name: 'Confirm',
  components: {
    DotLottieVue,
  },
  data() {
    return {
      isMounted: false,
    }
  },
  props: {
    isVisible: {
      type: Boolean,
      default: false,
    },
    message: {
      type: String,
      default: "You confirm this action ?",
    },
  },
  mounted() {
    this.isMounted = true
  },
}
</script>
