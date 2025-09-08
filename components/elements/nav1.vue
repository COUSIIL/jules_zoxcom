<template>
  <nav
    v-if="isMounted"
    class="sticky top-0 z-[5000] flex h-[50px] w-full items-center justify-center bg-[var(--color-whitly)] backdrop-blur-[10px] shadow-[0_4px_8px_var(--color-tioly)] dark:bg-[var(--color-darkly)] dark:shadow-[0_0px_8px_var(--color-darky)]"
  >
    <NuxtLink to="/" class="m-[5px] flex w-2/5 min-w-[120px] justify-start">
      <img v-if="!isDark" :src="logoDark" :alt="t('site logo')" class="h-10" />
      <img v-else :src="logoWhite" :alt="t('site logo')" class="h-10" />
    </NuxtLink>

    <div class="w-full"></div>

    <NuxtLink v-if="isAuthenticated && isDeasy" to="/diniChat" class="mx-[5px] flex min-w-[24px] max-w-[24px] cursor-pointer justify-center">
      <img v-if="!isDark" :src="varqWhite" alt="Dini icon" class="h-6" />
      <img v-else :src="varqDark" alt="Dini white icon" class="h-6" />
    </NuxtLink>

    <button @click="toggleDarkMode" class="m-[5px] flex w-10 min-w-10 max-w-10 cursor-pointer justify-center transition-all duration-500 ease-in-out">
      <svg
        v-if="isDark"
        key="dark"
        class="text-[--color-whity]"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        width="24"
        height="24"
        fill="none"
      >
        <path d="M17 12C17 14.7614 14.7614 17 12 17C9.23858 17 7 14.7614 7 12C7 9.23858 9.23858 7 12 7C14.7614 7 17 9.23858 17 12Z" stroke="currentColor" stroke-width="1.5" />
        <path d="M12 2V3.5M12 20.5V22M19.0708 19.0713L18.0101 18.0106M5.98926 5.98926L4.9286 4.9286M22 12H20.5M3.5 12H2M19.0713 4.92871L18.0106 5.98937M5.98975 18.0107L4.92909 19.0714" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
      </svg>

      <svg v-else key="light" class="text-[--color-darkly]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
        <path d="M18.6911 3.07767L19.395 4.49715C19.491 4.69475 19.7469 4.88428 19.9629 4.92057L21.2388 5.1343C22.0547 5.27141 22.2467 5.86824 21.6587 6.457L20.6668 7.45709C20.4989 7.62646 20.4069 7.9531 20.4589 8.18699L20.7428 9.425C20.9668 10.4049 20.4509 10.784 19.591 10.2718L18.3951 9.55808C18.1791 9.42903 17.8232 9.42903 17.6032 9.55808L16.4073 10.2718C15.5514 10.784 15.0315 10.4009 15.2554 9.425L15.5394 8.18699C15.5914 7.9531 15.4994 7.62646 15.3314 7.45709L14.3395 6.457C13.7556 5.86824 13.9436 5.27141 14.7595 5.1343L16.0353 4.92057C16.2473 4.88428 16.5033 4.69475 16.5993 4.49715L17.3032 3.07767C17.6872 2.30744 18.3111 2.30744 18.6911 3.07767Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        <path d="M3 11.8049C3 17.1594 7.34065 21.5 12.6951 21.5C17.101 21.5 20.8204 18.5611 22 14.5367C20.5791 15.5691 18.8306 16.1779 16.94 16.1779C12.1804 16.1779 8.32208 12.3196 8.32208 7.56005C8.32208 5.66937 8.93094 3.9209 9.96326 2.5C5.9389 3.67959 3 7.39904 3 11.8049Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path>
      </svg>
    </button>

    <ProfileBtn :user="user" />

    <button v-if="isAuthenticated" @click="sideBar" class="m-[5px] flex w-10 min-w-10 max-w-10 cursor-pointer justify-center transition-all duration-500 ease-in-out">
      <svg v-if="isVisible" :class="isDark ? 'text-[--color-whitly]' : 'text-[--color-darkly]'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
        <path d="M2 12C2 8.3109 2 6.46633 2.81382 5.1588C3.1149 4.67505 3.48891 4.2543 3.91891 3.91557C5.08116 3.00003 6.72077 3.00003 10 3.00003H14C17.2792 3.00003 18.9188 3.00003 20.0811 3.91557C20.5111 4.2543 20.8851 4.67505 21.1862 5.1588C22 6.46633 22 8.3109 22 12C22 15.6892 22 17.5337 21.1862 18.8413C20.8851 19.325 20.5111 19.7458 20.0811 20.0845C18.9188 21 17.2792 21 14 21H10C6.72077 21 5.08116 21 3.91891 20.0845C3.48891 19.7458 3.1149 19.325 2.81382 18.8413C2 17.5337 2 15.6892 2 12Z" stroke="currentColor" stroke-width="1.5"></path>
        <path d="M14.5 3.00003L14.5 21" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path>
        <path d="M18 7.00006H19M18 10.0001H19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
      </svg>
      <svg v-else :class="isDark ? 'text-[--color-whitly]' : 'text-[--color-darkly]'" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none">
        <path d="M4 5L20 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        <path d="M4 12L20 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
        <path d="M4 19L20 19" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
      </svg>
    </button>
  </nav>
</template>

<script setup>
import ProfileBtn from './bloc/profileBtn.vue'
import { useLang } from '~/composables/useLang'

const { t } = useLang()

defineProps({
  user: String,
  isMounted: Boolean,
  isDark: Boolean,
  isAuthenticated: Boolean,
  isDeasy: Boolean,
  logoDark: String,
  logoWhite: String,
  varqWhite: String,
  varqDark: String,
  isVisible: Boolean,
})

const emit = defineEmits(['darkMode', 'sideBar', 'viewMenu', 'handleLogout'])

const toggleDarkMode = () => {
  emit('darkMode')
}

const sideBar = () => {
  emit('sideBar')
}
</script>